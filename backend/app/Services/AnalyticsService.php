<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Deal;
use App\Models\WhatsappMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getDashboardMetrics()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'overview' => $this->getOverviewMetrics(),
            'monthly' => $this->getMonthlyMetrics($thisMonth, $lastMonth),
            'pipeline' => $this->getPipelineMetrics(),
            'conversion' => $this->getConversionMetrics(),
            'whatsapp' => $this->getWhatsAppMetrics($today),
            'attention_needed' => $this->getAttentionNeeded(),
        ];
    }

    protected function getOverviewMetrics()
    {
        return [
            'total_leads' => Lead::count(),
            'total_customers' => Lead::where('status', 'won')->count(),
            'active_deals' => Deal::whereHas('stage', function ($q) {
                $q->whereNotIn('slug', ['won', 'lost']);
            })->count(),
        ];
    }

    protected function getMonthlyMetrics($thisMonth, $lastMonth)
    {
        return [
            'new_leads_this_month' => Lead::where('created_at', '>=', $thisMonth)->count(),
            'new_leads_last_month' => Lead::where('created_at', '>=', $lastMonth)
                ->where('created_at', '<', $thisMonth)->count(),
            'won_deals_this_month' => Lead::where('status', 'won')
                ->where('converted_at', '>=', $thisMonth)->count(),
            'revenue_this_month' => Lead::where('status', 'won')
                ->where('converted_at', '>=', $thisMonth)
                ->sum('actual_revenue'),
        ];
    }

    protected function getPipelineMetrics()
    {
        return [
            'pipeline_value' => Lead::whereNotIn('status', ['won', 'lost'])
                ->sum('expected_revenue'),
            'average_deal_value' => Lead::where('status', 'won')
                ->avg('actual_revenue'),
            'total_pipeline_deals' => Deal::count(),
        ];
    }

    protected function getConversionMetrics()
    {
        $total = Lead::count();
        $won = Lead::where('status', 'won')->count();

        return [
            'conversion_rate' => $total > 0 ? round(($won / $total) * 100, 2) : 0,
            'total_converted' => $won,
        ];
    }

    protected function getWhatsAppMetrics($today)
    {
        return [
            'messages_today' => WhatsappMessage::whereDate('created_at', $today)->count(),
            'inbound_today' => WhatsappMessage::whereDate('created_at', $today)
                ->where('direction', 'inbound')->count(),
            'outbound_today' => WhatsappMessage::whereDate('created_at', $today)
                ->where('direction', 'outbound')->count(),
            'unread_count' => WhatsappMessage::whereNull('read_at')
                ->where('direction', 'inbound')->count(),
        ];
    }

    protected function getAttentionNeeded()
    {
        return [
            'leads_need_follow_up' => Lead::needsFollowUp()->count(),
            'deals_closing_soon' => Deal::closingSoon()->count(),
            'overdue_tasks' => DB::table('tasks')
                ->where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
        ];
    }

    public function calculateLeadScore(Lead $lead)
    {
        $score = 0;

        // Interactions score
        $score += $lead->interactions()->count() * 5;

        // WhatsApp engagement
        $score += $lead->whatsappMessages()
            ->where('direction', 'inbound')
            ->count() * 3;

        // Deal value score
        if ($lead->expected_revenue > 0) {
            $score += min($lead->expected_revenue / 1000, 50);
        }

        // Recent activity bonus
        if ($lead->last_contact_at && $lead->last_contact_at->diffInDays(now()) < 7) {
            $score += 20;
        }

        // Tags bonus
        $score += $lead->tags()->count() * 2;

        // Deals bonus
        $score += $lead->deals()->count() * 10;

        return min($score, 100);
    }
}
