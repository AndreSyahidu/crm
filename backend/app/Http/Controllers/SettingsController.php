<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        // Return settings with proper types
        return response()->json([
            'company_name' => $settings['company_name'] ?? 'WhatsApp CRM',
            'company_email' => $settings['company_email'] ?? '',
            'company_phone' => $settings['company_phone'] ?? '',
            'timezone' => $settings['timezone'] ?? 'Asia/Jakarta',
            'currency' => $settings['currency'] ?? 'IDR',
            'whatsapp_rate_limit' => (int)($settings['whatsapp_rate_limit'] ?? 20),
            'auto_create_leads' => filter_var($settings['auto_create_leads'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'auto_response_enabled' => filter_var($settings['auto_response_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'auto_response_message' => $settings['auto_response_message'] ?? '',
            'daily_recap_time' => $settings['daily_recap_time'] ?? '08:00',
            'notify_new_lead' => filter_var($settings['notify_new_lead'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'notify_deal_won' => filter_var($settings['notify_deal_won'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:50',
            'timezone' => 'nullable|string',
            'currency' => 'nullable|string|max:10',
        ]);

        foreach ($request->only(['company_name', 'company_email', 'company_phone', 'timezone', 'currency']) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget('settings');

        return response()->json([
            'message' => 'General settings updated successfully'
        ]);
    }

    /**
     * Update WhatsApp settings
     */
    public function updateWhatsApp(Request $request)
    {
        $request->validate([
            'whatsapp_rate_limit' => 'nullable|integer|min:1|max:100',
            'auto_create_leads' => 'nullable|boolean',
            'auto_response_enabled' => 'nullable|boolean',
            'auto_response_message' => 'nullable|string',
        ]);

        foreach ($request->only(['whatsapp_rate_limit', 'auto_create_leads', 'auto_response_enabled', 'auto_response_message']) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
            );
        }

        Cache::forget('settings');

        return response()->json([
            'message' => 'WhatsApp settings updated successfully'
        ]);
    }

    /**
     * Update email notification settings
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'daily_recap_time' => 'nullable|string',
            'notify_new_lead' => 'nullable|boolean',
            'notify_deal_won' => 'nullable|boolean',
        ]);

        foreach ($request->only(['daily_recap_time', 'notify_new_lead', 'notify_deal_won']) as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
            );
        }

        Cache::forget('settings');

        return response()->json([
            'message' => 'Email settings updated successfully'
        ]);
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        try {
            Cache::flush();
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return response()->json([
                'message' => 'Cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system logs
     */
    public function logs(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');

        if (!File::exists($logFile)) {
            return response()->json(['logs' => []]);
        }

        try {
            $logContent = File::get($logFile);
            $logs = [];

            // Parse log file (simplified - in production use proper log parser)
            $lines = explode("\n", $logContent);
            $limit = (int)($request->query('limit', 100));

            // Get last N lines
            $recentLines = array_slice($lines, -$limit);

            foreach ($recentLines as $line) {
                if (empty(trim($line))) continue;

                // Simple parsing - extract timestamp, level, message
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?\.(\w+): (.+)/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'level' => strtolower($matches[2]),
                        'category' => 'Application',
                        'message' => $matches[3] ?? $line
                    ];
                } else {
                    // Fallback for lines that don't match pattern
                    if (stripos($line, 'error') !== false) {
                        $level = 'error';
                    } elseif (stripos($line, 'warning') !== false) {
                        $level = 'warning';
                    } else {
                        $level = 'info';
                    }

                    $logs[] = [
                        'timestamp' => now()->toDateTimeString(),
                        'level' => $level,
                        'category' => 'Application',
                        'message' => substr($line, 0, 200)
                    ];
                }
            }

            return response()->json(['logs' => array_reverse($logs)]);
        } catch (\Exception $e) {
            return response()->json([
                'logs' => [],
                'error' => 'Failed to read logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear system logs
     */
    public function clearLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');

            if (File::exists($logFile)) {
                File::put($logFile, '');
            }

            return response()->json([
                'message' => 'System logs cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to clear logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset demo data (for testing purposes)
     */
    public function resetDemo()
    {
        try {
            // Only allow in non-production environments
            if (app()->environment('production')) {
                return response()->json([
                    'message' => 'Cannot reset demo data in production'
                ], 403);
            }

            // Truncate all data tables (preserve structure)
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            $tables = [
                'broadcast_recipients',
                'broadcast_campaigns',
                'follow_up_enrollments',
                'follow_up_steps',
                'follow_up_sequences',
                'journey_milestones',
                'interactions',
                'tasks',
                'lead_tags',
                'tags',
                'message_queue',
                'whatsapp_messages',
                'deals',
                'segments',
                'leads',
                'daily_stats',
            ];

            foreach ($tables as $table) {
                DB::table($table)->truncate();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Optionally re-seed demo data
            Artisan::call('db:seed', ['--class' => 'DemoDataSeeder']);

            return response()->json([
                'message' => 'Demo data reset successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reset demo data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
