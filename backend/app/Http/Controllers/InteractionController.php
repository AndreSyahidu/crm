<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InteractionController extends Controller
{
    /**
     * Get all interactions
     */
    public function index(Request $request)
    {
        $query = Interaction::with(['lead', 'user']);

        // Filter by lead
        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $interactions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($interactions);
    }

    /**
     * Store a new interaction
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'type' => 'required|in:call,email,meeting,note,whatsapp,task,status_change,other',
            'subject' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $interaction = Interaction::create([
            'lead_id' => $request->lead_id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'subject' => $request->subject,
            'notes' => $request->notes,
            'duration_minutes' => $request->duration_minutes,
            'metadata' => $request->metadata ? json_encode($request->metadata) : null,
        ]);

        // Update lead's last_contact_at
        Lead::where('id', $request->lead_id)
            ->update(['last_contact_at' => now()]);

        return response()->json([
            'message' => 'Interaction logged successfully',
            'interaction' => $interaction->load(['lead', 'user'])
        ], 201);
    }

    /**
     * Get a specific interaction
     */
    public function show($id)
    {
        $interaction = Interaction::with(['lead', 'user'])->findOrFail($id);

        return response()->json($interaction);
    }

    /**
     * Update an interaction
     */
    public function update(Request $request, $id)
    {
        $interaction = Interaction::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|required|in:call,email,meeting,note,whatsapp,task,status_change,other',
            'subject' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = $request->only(['type', 'subject', 'notes', 'duration_minutes']);

        if ($request->has('metadata')) {
            $updateData['metadata'] = json_encode($request->metadata);
        }

        $interaction->update($updateData);

        return response()->json([
            'message' => 'Interaction updated successfully',
            'interaction' => $interaction->fresh(['lead', 'user'])
        ]);
    }

    /**
     * Delete an interaction
     */
    public function destroy($id)
    {
        $interaction = Interaction::findOrFail($id);
        $interaction->delete();

        return response()->json([
            'message' => 'Interaction deleted successfully'
        ]);
    }
}
