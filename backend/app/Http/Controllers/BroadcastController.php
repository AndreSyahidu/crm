<?php

namespace App\Http\Controllers;

use App\Models\BroadcastCampaign;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class BroadcastController extends Controller
{
    public function index()
    {
        $campaigns = BroadcastCampaign::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($campaigns);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'message' => 'required|string',
            'media_url' => 'nullable|url',
            'segment_filter' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
            'lead_ids' => 'nullable|array',
            'lead_ids.*' => 'exists:leads,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $campaign = BroadcastCampaign::create([
            'name' => $request->name,
            'message' => $request->message,
            'media_url' => $request->media_url,
            'segment_filter' => $request->segment_filter,
            'scheduled_at' => $request->scheduled_at,
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
            'created_by' => auth()->id()
        ]);

        // If lead_ids provided, create recipients
        if ($request->has('lead_ids')) {
            $campaign->total_recipients = count($request->lead_ids);
            $campaign->save();

            foreach ($request->lead_ids as $leadId) {
                $campaign->recipients()->create([
                    'lead_id' => $leadId,
                    'status' => 'pending'
                ]);
            }
        }

        return response()->json([
            'message' => 'Broadcast campaign created successfully',
            'campaign' => $campaign
        ], 201);
    }

    public function show($id)
    {
        $campaign = BroadcastCampaign::with(['creator', 'recipients.lead'])
            ->findOrFail($id);

        return response()->json($campaign);
    }

    public function update(Request $request, $id)
    {
        $campaign = BroadcastCampaign::findOrFail($id);

        if (in_array($campaign->status, ['sending', 'completed'])) {
            return response()->json([
                'error' => 'Cannot update campaign that is sending or completed'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'message' => 'string',
            'media_url' => 'nullable|url',
            'scheduled_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $campaign->update($request->all());

        return response()->json([
            'message' => 'Campaign updated successfully',
            'campaign' => $campaign
        ]);
    }

    public function destroy($id)
    {
        $campaign = BroadcastCampaign::findOrFail($id);

        if ($campaign->status === 'sending') {
            return response()->json([
                'error' => 'Cannot delete campaign that is currently sending'
            ], 400);
        }

        $campaign->delete();

        return response()->json([
            'message' => 'Campaign deleted successfully'
        ]);
    }

    public function preview(Request $request, $id)
    {
        $campaign = BroadcastCampaign::findOrFail($id);
        $leads = $campaign->getTargetLeads();

        return response()->json([
            'total_recipients' => $leads->count(),
            'leads' => $leads->take(10),
            'message' => $campaign->message
        ]);
    }

    public function previewBySegment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'segment_id' => 'nullable|exists:segments,id',
            'segment_filter' => 'nullable|array',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get leads based on segment
        $leads = collect();

        if ($request->has('segment_id')) {
            $segment = \App\Models\Segment::findOrFail($request->segment_id);
            $leads = $segment->getLeads();
        } elseif ($request->has('segment_filter')) {
            // Apply custom filter
            $query = Lead::query();

            if (isset($request->segment_filter['status'])) {
                $query->where('status', $request->segment_filter['status']);
            }
            if (isset($request->segment_filter['tags'])) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->whereIn('tags.id', $request->segment_filter['tags']);
                });
            }
            if (isset($request->segment_filter['source'])) {
                $query->where('source', $request->segment_filter['source']);
            }

            $leads = $query->get();
        } else {
            $leads = Lead::all();
        }

        return response()->json([
            'total_recipients' => $leads->count(),
            'leads' => $leads->take(10),
            'message' => $request->message
        ]);
    }

    public function start($id)
    {
        $campaign = BroadcastCampaign::findOrFail($id);

        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            return response()->json([
                'error' => 'Campaign already started or completed'
            ], 400);
        }

        // Start campaign
        $campaign->start();

        // Send to recipients via queue
        dispatch(function () use ($campaign) {
            $campaign->sendToRecipients();
        });

        return response()->json([
            'message' => 'Campaign started successfully',
            'campaign' => $campaign
        ]);
    }

    public function pause($id)
    {
        $campaign = BroadcastCampaign::findOrFail($id);

        if ($campaign->status !== 'sending') {
            return response()->json([
                'error' => 'Can only pause sending campaigns'
            ], 400);
        }

        $campaign->status = 'paused';
        $campaign->save();

        return response()->json([
            'message' => 'Campaign paused successfully'
        ]);
    }

    public function resume($id)
    {
        $campaign = BroadcastCampaign::findOrFail($id);

        if ($campaign->status !== 'paused') {
            return response()->json([
                'error' => 'Can only resume paused campaigns'
            ], 400);
        }

        $campaign->status = 'sending';
        $campaign->save();

        dispatch(function () use ($campaign) {
            $campaign->sendToRecipients();
        });

        return response()->json([
            'message' => 'Campaign resumed successfully'
        ]);
    }

    public function stats($id)
    {
        $campaign = BroadcastCampaign::findOrFail($id);
        $campaign->updateStats();

        return response()->json([
            'total_recipients' => $campaign->total_recipients,
            'sent_count' => $campaign->sent_count,
            'delivered_count' => $campaign->delivered_count,
            'read_count' => $campaign->read_count,
            'reply_count' => $campaign->reply_count,
            'sent_rate' => $campaign->total_recipients > 0
                ? round(($campaign->sent_count / $campaign->total_recipients) * 100, 2)
                : 0,
            'delivery_rate' => $campaign->sent_count > 0
                ? round(($campaign->delivered_count / $campaign->sent_count) * 100, 2)
                : 0,
            'read_rate' => $campaign->delivered_count > 0
                ? round(($campaign->read_count / $campaign->delivered_count) * 100, 2)
                : 0,
            'reply_rate' => $campaign->delivered_count > 0
                ? round(($campaign->reply_count / $campaign->delivered_count) * 100, 2)
                : 0
        ]);
    }
}
