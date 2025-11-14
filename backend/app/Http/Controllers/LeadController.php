<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Interaction;
use App\Models\JourneyMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['assignedUser', 'tags', 'deals']);

        // Filters
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('source')) {
            $query->bySource($request->source);
        }

        if ($request->has('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('company', 'like', "%{$request->search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $leads = $query->paginate($perPage);

        return response()->json($leads);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'whatsapp_number' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'source' => 'in:whatsapp,manual,website,referral,other',
            'status' => 'in:new,contacted,qualified,proposal,negotiation,won,lost',
            'expected_revenue' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lead = Lead::create($request->except('tags'));

        // Attach tags
        if ($request->has('tags')) {
            $lead->tags()->attach($request->tags);
        }

        // Create initial interaction
        Interaction::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'title' => 'Lead Created',
            'description' => 'Lead created manually via CRM'
        ]);

        // Create milestone
        JourneyMilestone::create([
            'lead_id' => $lead->id,
            'name' => 'Lead Created',
            'description' => 'Lead entered the CRM system',
            'icon' => 'user-plus'
        ]);

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead->load(['assignedUser', 'tags'])
        ], 201);
    }

    public function show($id)
    {
        $lead = Lead::with([
            'assignedUser',
            'tags',
            'deals.stage',
            'interactions.user',
            'whatsappMessages' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(50);
            },
            'tasks.assignedUser',
            'journeyMilestones',
            'objectionLogs.template',
            'followUpEnrollments.sequence'
        ])->findOrFail($id);

        return response()->json($lead);
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'phone' => 'string|max:50',
            'email' => 'nullable|email|max:255',
            'whatsapp_number' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'status' => 'in:new,contacted,qualified,proposal,negotiation,won,lost',
            'expected_revenue' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldData = $lead->toArray();
        $lead->update($request->except('tags'));

        // Update tags
        if ($request->has('tags')) {
            $lead->tags()->sync($request->tags);
        }

        // Log changes as interaction
        $changes = array_diff_assoc($request->except('tags'), $oldData);
        if (!empty($changes)) {
            Interaction::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => 'note',
                'title' => 'Lead Updated',
                'description' => 'Lead information updated',
                'metadata' => $changes
            ]);
        }

        // Check if status changed to won or lost
        if (isset($changes['status'])) {
            if ($changes['status'] === 'won') {
                JourneyMilestone::create([
                    'lead_id' => $lead->id,
                    'name' => 'Deal Won',
                    'description' => 'Successfully converted to customer',
                    'icon' => 'trophy'
                ]);
            } elseif ($changes['status'] === 'lost') {
                JourneyMilestone::create([
                    'lead_id' => $lead->id,
                    'name' => 'Deal Lost',
                    'description' => 'Lead marked as lost',
                    'icon' => 'x-circle'
                ]);
            }
        }

        return response()->json([
            'message' => 'Lead updated successfully',
            'lead' => $lead->load(['assignedUser', 'tags'])
        ]);
    }

    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return response()->json([
            'message' => 'Lead deleted successfully'
        ]);
    }

    public function updateScore($id)
    {
        $lead = Lead::findOrFail($id);
        $score = $lead->updateLeadScore();

        return response()->json([
            'message' => 'Lead score updated',
            'lead_score' => $score
        ]);
    }

    public function timeline($id)
    {
        $lead = Lead::findOrFail($id);

        $timeline = collect();

        // Add interactions
        $interactions = $lead->interactions()->with('user')->get()->map(function ($item) {
            return [
                'type' => 'interaction',
                'data' => $item,
                'timestamp' => $item->created_at
            ];
        });

        // Add WhatsApp messages
        $messages = $lead->whatsappMessages()->get()->map(function ($item) {
            return [
                'type' => 'whatsapp',
                'data' => $item,
                'timestamp' => $item->created_at
            ];
        });

        // Add milestones
        $milestones = $lead->journeyMilestones()->get()->map(function ($item) {
            return [
                'type' => 'milestone',
                'data' => $item,
                'timestamp' => $item->achieved_at
            ];
        });

        // Merge and sort
        $timeline = $timeline
            ->concat($interactions)
            ->concat($messages)
            ->concat($milestones)
            ->sortByDesc('timestamp')
            ->values();

        return response()->json($timeline);
    }

    public function stats()
    {
        $stats = [
            'total' => Lead::count(),
            'by_status' => Lead::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'by_source' => Lead::select('source', DB::raw('count(*) as count'))
                ->groupBy('source')
                ->get(),
            'high_value' => Lead::highValue()->count(),
            'needs_follow_up' => Lead::needsFollowUp()->count(),
            'total_revenue' => Lead::where('status', 'won')->sum('actual_revenue'),
            'pipeline_value' => Lead::whereNotIn('status', ['won', 'lost'])->sum('expected_revenue'),
        ];

        return response()->json($stats);
    }

    public function bulkAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'assigned_to' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Lead::whereIn('id', $request->lead_ids)
            ->update(['assigned_to' => $request->assigned_to]);

        return response()->json([
            'message' => 'Leads assigned successfully',
            'count' => count($request->lead_ids)
        ]);
    }

    public function bulkTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->lead_ids as $leadId) {
            $lead = Lead::find($leadId);
            $lead->tags()->syncWithoutDetaching($request->tag_ids);
        }

        return response()->json([
            'message' => 'Tags applied successfully',
            'count' => count($request->lead_ids)
        ]);
    }
}
