<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\DailyStat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDailyRecap extends Command
{
    protected $signature = 'crm:daily-recap';
    protected $description = 'Send daily recap email to all users';

    public function handle()
    {
        $this->info('Sending daily recap emails...');

        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

        // Calculate daily stats
        $stats = [
            'new_leads' => Lead::whereDate('created_at', $yesterday)->count(),
            'qualified_leads' => Lead::where('status', 'qualified')
                ->whereDate('updated_at', $yesterday)->count(),
            'won_deals' => Lead::where('status', 'won')
                ->whereDate('converted_at', $yesterday)->count(),
            'lost_deals' => Lead::where('status', 'lost')
                ->whereDate('updated_at', $yesterday)->count(),
            'total_revenue' => Lead::where('status', 'won')
                ->whereDate('converted_at', $yesterday)
                ->sum('actual_revenue'),
            'whatsapp_sent' => \DB::table('whatsapp_messages')
                ->where('direction', 'outbound')
                ->whereDate('created_at', $yesterday)
                ->count(),
            'whatsapp_received' => \DB::table('whatsapp_messages')
                ->where('direction', 'inbound')
                ->whereDate('created_at', $yesterday)
                ->count(),
        ];

        // Save to daily_stats
        DailyStat::updateOrCreate(
            ['date' => $yesterday->toDateString()],
            $stats
        );

        // Send to all active users
        $users = User::where('is_active', true)->get();

        foreach ($users as $user) {
            try {
                // Get user-specific stats
                $userStats = [
                    'my_new_leads' => Lead::where('assigned_to', $user->id)
                        ->whereDate('created_at', $yesterday)->count(),
                    'my_won_deals' => Lead::where('assigned_to', $user->id)
                        ->where('status', 'won')
                        ->whereDate('converted_at', $yesterday)->count(),
                    'my_tasks_due_today' => \DB::table('tasks')
                        ->where('assigned_to', $user->id)
                        ->whereDate('due_date', $today)
                        ->where('status', 'pending')
                        ->count(),
                    'my_overdue_tasks' => \DB::table('tasks')
                        ->where('assigned_to', $user->id)
                        ->where('due_date', '<', $today)
                        ->where('status', 'pending')
                        ->count(),
                ];

                // Send daily recap email
                Mail::to($user->email)->send(new \App\Mail\DailyRecapMail($stats, $userStats));

                $this->info("Sent recap to {$user->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info('Daily recap completed!');
    }
}
