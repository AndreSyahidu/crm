<?php

namespace App\Http\Controllers;

use App\Models\FollowUpSequence;
use App\Models\FollowUpStep;
use App\Models\FollowUpEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FollowUpSequenceController extends Controller
{
    /**
     * Get all follow-up sequences
     */
    public function index()
    {
        $sequences = FollowUpSequence::withCount('steps', 'enrollments')
            ->with('steps')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($sequences);
    }

    /**
     * Store a new follow-up sequence
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:manual,status_change,time_based',
            'trigger_value' => 'nullable|string',
            'is_active' => 'boolean',
            'steps' => 'array',
            'steps.*.delay_days' => 'required|integer|min:0',
            'steps.*.delay_hours' => 'required|integer|min:0',
            'steps.*.action_type' => 'required|in:send_message,send_email,create_task,assign_tag',
            'steps.*.action_content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sequence = FollowUpSequence::create([
            'name' => $request->name,
            'description' => $request->description,
            'trigger_type' => $request->trigger_type,
            'trigger_value' => $request->trigger_value,
            'is_active' => $request->is_active ?? true,
        ]);

        // Create steps if provided
        if ($request->has('steps')) {
            foreach ($request->steps as $stepData) {
                FollowUpStep::create([
                    'sequence_id' => $sequence->id,
                    'step_number' => $stepData['step_number'] ?? 1,
                    'delay_days' => $stepData['delay_days'],
                    'delay_hours' => $stepData['delay_hours'],
                    'action_type' => $stepData['action_type'],
                    'action_content' => $stepData['action_content'],
                    'action_metadata' => $stepData['action_metadata'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Follow-up sequence created successfully',
            'sequence' => $sequence->load('steps')
        ], 201);
    }

    /**
     * Get a specific follow-up sequence
     */
    public function show($id)
    {
        $sequence = FollowUpSequence::with(['steps', 'enrollments.lead'])
            ->withCount('enrollments')
            ->findOrFail($id);

        return response()->json($sequence);
    }

    /**
     * Update a follow-up sequence
     */
    public function update(Request $request, $id)
    {
        $sequence = FollowUpSequence::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'sometimes|required|in:manual,status_change,time_based',
            'trigger_value' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sequence->update($request->only([
            'name',
            'description',
            'trigger_type',
            'trigger_value',
            'is_active'
        ]));

        return response()->json([
            'message' => 'Follow-up sequence updated successfully',
            'sequence' => $sequence->load('steps')
        ]);
    }

    /**
     * Delete a follow-up sequence
     */
    public function destroy($id)
    {
        $sequence = FollowUpSequence::findOrFail($id);

        // Delete all associated steps and enrollments
        $sequence->steps()->delete();
        $sequence->enrollments()->delete();
        $sequence->delete();

        return response()->json([
            'message' => 'Follow-up sequence deleted successfully'
        ]);
    }

    /**
     * Toggle sequence active status
     */
    public function toggleActive($id)
    {
        $sequence = FollowUpSequence::findOrFail($id);
        $sequence->update(['is_active' => !$sequence->is_active]);

        return response()->json([
            'message' => 'Sequence status updated successfully',
            'is_active' => $sequence->is_active
        ]);
    }

    /**
     * Enroll a lead in a sequence
     */
    public function enroll(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sequence = FollowUpSequence::findOrFail($id);

        // Check if lead is already enrolled
        $existingEnrollment = FollowUpEnrollment::where('sequence_id', $id)
            ->where('lead_id', $request->lead_id)
            ->where('status', '!=', 'completed')
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'message' => 'Lead is already enrolled in this sequence'
            ], 409);
        }

        $enrollment = FollowUpEnrollment::create([
            'sequence_id' => $id,
            'lead_id' => $request->lead_id,
            'status' => 'active',
            'current_step' => 0,
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Lead enrolled successfully',
            'enrollment' => $enrollment
        ], 201);
    }

    /**
     * Unenroll a lead from a sequence
     */
    public function unenroll(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $enrollment = FollowUpEnrollment::where('sequence_id', $id)
            ->where('lead_id', $request->lead_id)
            ->where('status', 'active')
            ->firstOrFail();

        $enrollment->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Lead unenrolled successfully'
        ]);
    }

    /**
     * Get sequence stats
     */
    public function stats($id)
    {
        $sequence = FollowUpSequence::findOrFail($id);

        $stats = [
            'total_enrollments' => $sequence->enrollments()->count(),
            'active_enrollments' => $sequence->enrollments()->where('status', 'active')->count(),
            'completed_enrollments' => $sequence->enrollments()->where('status', 'completed')->count(),
            'cancelled_enrollments' => $sequence->enrollments()->where('status', 'cancelled')->count(),
        ];

        return response()->json($stats);
    }
}
