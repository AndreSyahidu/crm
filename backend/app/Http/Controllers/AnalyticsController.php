<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Deal;
use App\Models\WhatsappMessage;
use App\Models\BroadcastCampaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $stats = [
            // Overview
            'total_leads' => Lead::count(),
            'total_customers' => Lead::where('status', 'won')->count(),
            'active_deals' => Deal::whereHas('stage', function ($q) {
                $q->where('slug', '!=', 'won')->where('slug', '!=', 'lost');
            })->count(),

            // This month
            'new_leads_this_month' => Lead::where('created_at', '>=', $thisMonth)->count(),
            'new_leads_last_month' => Lead::where('created_at', '>=', $lastMonth)
                ->where('created_at', '<', $thisMonth)->count(),
            'won_deals_this_month' => Lead::where('status', 'won')
                ->where('converted_at', '>=', $thisMonth)->count(),
            'revenue_this_month' => Lead::where('status', 'won')
                ->where('converted_at', '>=', $thisMonth)
                ->sum('actual_revenue'),

            // Pipeline
            'pipeline_value' => Lead::whereNotIn('status', ['won', 'lost'])->sum('expected_revenue'),
            'average_deal_value' => Lead::where('status', 'won')->avg('actual_revenue'),

            // Conversion
            'conversion_rate' => $this->getConversionRate(),

            // WhatsApp
            'whatsapp_messages_today' => WhatsappMessage::whereDate('created_at', $today)->count(),
            'whatsapp_inbound_today' => WhatsappMessage::whereDate('created_at', $today)
                ->where('direction', 'inbound')->count(),
            'whatsapp_outbound_today' => WhatsappMessage::whereDate('created_at', $today)
                ->where('direction', 'outbound')->count(),

            // Needs attention
            'leads_need_follow_up' => Lead::needsFollowUp()->count(),
            'deals_closing_soon' => Deal::closingSoon()->count()
        ];

        return response()->json($stats);
    }

    public function leadsBySource()
    {
        $data = Lead::select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->get();

        return response()->json($data);
    }

    public function leadsByStatus()
    {
        $data = Lead::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json($data);
    }

    public function conversionFunnel()
    {
        $funnel = [
            ['stage' => 'New Leads', 'count' => Lead::where('status', 'new')->count()],
            ['stage' => 'Contacted', 'count' => Lead::where('status', 'contacted')->count()],
            ['stage' => 'Qualified', 'count' => Lead::where('status', 'qualified')->count()],
            ['stage' => 'Proposal', 'count' => Lead::where('status', 'proposal')->count()],
            ['stage' => 'Negotiation', 'count' => Lead::where('status', 'negotiation')->count()],
            ['stage' => 'Won', 'count' => Lead::where('status', 'won')->count()],
        ];

        return response()->json($funnel);
    }

    public function revenueOverTime(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $data = Lead::where('status', 'won')
            ->where('converted_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(converted_at) as date'),
                DB::raw('SUM(actual_revenue) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    public function leadsOverTime(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $data = Lead::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                'source'
            )
            ->groupBy('date', 'source')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    public function teamPerformance()
    {
        $reps = User::where('role', 'sales_rep')
            ->withCount([
                'assignedLeads',
                'assignedLeads as won_count' => function ($q) {
                    $q->where('status', 'won');
                }
            ])
            ->with(['assignedLeads' => function ($q) {
                $q->where('status', 'won');
            }])
            ->get()
            ->map(function ($user) {
                $revenue = $user->assignedLeads->sum('actual_revenue');
                $conversionRate = $user->assigned_leads_count > 0
                    ? round(($user->won_count / $user->assigned_leads_count) * 100, 2)
                    : 0;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'total_leads' => $user->assigned_leads_count,
                    'won_deals' => $user->won_count,
                    'total_revenue' => $revenue,
                    'conversion_rate' => $conversionRate,
                    'average_deal_value' => $user->won_count > 0
                        ? round($revenue / $user->won_count, 2)
                        : 0
                ];
            });

        return response()->json($reps);
    }

    public function whatsappActivity(Request $request)
    {
        $days = $request->get('days', 7);
        $startDate = Carbon::now()->subDays($days);

        $data = WhatsappMessage::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN direction = "inbound" THEN 1 ELSE 0 END) as inbound'),
                DB::raw('SUM(CASE WHEN direction = "outbound" THEN 1 ELSE 0 END) as outbound')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    public function broadcastPerformance()
    {
        $campaigns = BroadcastCampaign::where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'sent_count' => $campaign->sent_count,
                    'delivered_count' => $campaign->delivered_count,
                    'read_count' => $campaign->read_count,
                    'reply_count' => $campaign->reply_count,
                    'delivery_rate' => $campaign->sent_count > 0
                        ? round(($campaign->delivered_count / $campaign->sent_count) * 100, 2)
                        : 0,
                    'read_rate' => $campaign->delivered_count > 0
                        ? round(($campaign->read_count / $campaign->delivered_count) * 100, 2)
                        : 0,
                    'reply_rate' => $campaign->delivered_count > 0
                        ? round(($campaign->reply_count / $campaign->delivered_count) * 100, 2)
                        : 0
                ];
            });

        return response()->json($campaigns);
    }

    public function topLeads()
    {
        $leads = Lead::where('status', '!=', 'lost')
            ->orderBy('expected_revenue', 'desc')
            ->take(10)
            ->with(['assignedUser', 'tags'])
            ->get();

        return response()->json($leads);
    }

    public function lostDealsReasons()
    {
        $reasons = Lead::where('status', 'lost')
            ->whereNotNull('lost_reason')
            ->select('lost_reason', DB::raw('COUNT(*) as count'))
            ->groupBy('lost_reason')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json($reasons);
    }

    protected function getConversionRate()
    {
        $total = Lead::count();
        $won = Lead::where('status', 'won')->count();

        if ($total === 0) return 0;

        return round(($won / $total) * 100, 2);
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'leads');
        $format = $request->get('format', 'csv');

        // This is a placeholder - implement actual export logic
        return response()->json([
            'message' => 'Export functionality to be implemented',
            'type' => $type,
            'format' => $format
        ]);
    }
}
