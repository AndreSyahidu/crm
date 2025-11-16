<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $query = Deal::with(['lead', 'stage', 'assignedUser']);

        if ($request->has('stage_id')) {
            $query->byStage($request->stage_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $deals = $query->orderBy('position_in_stage')->get();

        return response()->json($deals);
    }

    public function kanban()
    {
        $stages = PipelineStage::active()
            ->ordered()
            ->with(['deals' => function ($q) {
                $q->with(['lead', 'assignedUser'])
                  ->orderBy('position_in_stage');
            }])
            ->get();

        $stats = [
            'total_value' => Deal::sum('value'),
            'weighted_value' => DB::table('deals')
                ->selectRaw('SUM(value * (probability / 100)) as weighted')
                ->value('weighted'),
            'total_deals' => Deal::count(),
            'closing_soon' => Deal::closingSoon()->count()
        ];

        return response()->json([
            'stages' => $stages,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'title' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'stage_id' => 'required|exists:pipeline_stages,id',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'priority' => 'in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Set position at end of stage
        $maxPosition = Deal::where('stage_id', $request->stage_id)->max('position_in_stage') ?? -1;

        $deal = Deal::create(array_merge($request->all(), [
            'position_in_stage' => $maxPosition + 1
        ]));

        return response()->json([
            'message' => 'Deal created successfully',
            'deal' => $deal->load(['lead', 'stage', 'assignedUser'])
        ], 201);
    }

    public function show($id)
    {
        $deal = Deal::with(['lead', 'stage', 'assignedUser'])->findOrFail($id);

        return response()->json($deal);
    }

    public function update(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'value' => 'numeric|min:0',
            'probability' => 'integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'actual_close_date' => 'nullable|date',
            'priority' => 'in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deal->update($request->all());

        return response()->json([
            'message' => 'Deal updated successfully',
            'deal' => $deal->load(['lead', 'stage', 'assignedUser'])
        ]);
    }

    public function destroy($id)
    {
        $deal = Deal::findOrFail($id);
        $deal->delete();

        return response()->json([
            'message' => 'Deal deleted successfully'
        ]);
    }

    public function moveStage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'stage_id' => 'required|exists:pipeline_stages,id',
            'position' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deal = Deal::findOrFail($id);

        // Remove from old position
        Deal::where('stage_id', $deal->stage_id)
            ->where('position_in_stage', '>', $deal->position_in_stage)
            ->decrement('position_in_stage');

        // Add to new stage
        $newPosition = $request->position ?? Deal::where('stage_id', $request->stage_id)->count();

        // Make room at new position
        Deal::where('stage_id', $request->stage_id)
            ->where('position_in_stage', '>=', $newPosition)
            ->increment('position_in_stage');

        $deal->moveToStage($request->stage_id);
        $deal->position_in_stage = $newPosition;
        $deal->save();

        return response()->json([
            'message' => 'Deal moved successfully',
            'deal' => $deal->load(['lead', 'stage', 'assignedUser'])
        ]);
    }

    public function reorder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deal = Deal::findOrFail($id);
        $oldPosition = $deal->position_in_stage;
        $newPosition = $request->position;

        if ($oldPosition === $newPosition) {
            return response()->json(['message' => 'No change']);
        }

        // Reorder deals
        if ($newPosition < $oldPosition) {
            // Moving up
            Deal::where('stage_id', $deal->stage_id)
                ->where('position_in_stage', '>=', $newPosition)
                ->where('position_in_stage', '<', $oldPosition)
                ->increment('position_in_stage');
        } else {
            // Moving down
            Deal::where('stage_id', $deal->stage_id)
                ->where('position_in_stage', '>', $oldPosition)
                ->where('position_in_stage', '<=', $newPosition)
                ->decrement('position_in_stage');
        }

        $deal->position_in_stage = $newPosition;
        $deal->save();

        return response()->json([
            'message' => 'Deal reordered successfully',
            'deal' => $deal
        ]);
    }

    public function pipelineStages(Request $request)
    {
        $stages = PipelineStage::active()
            ->ordered()
            ->with(['deals' => function ($q) use ($request) {
                $q->with(['lead', 'assignedUser'])
                  ->orderBy('position_in_stage');

                // Apply filters if provided
                if ($request->has('search') && $request->search) {
                    $q->whereHas('lead', function ($leadQuery) use ($request) {
                        $leadQuery->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('company', 'like', '%' . $request->search . '%');
                    });
                }

                if ($request->has('assigned_to') && $request->assigned_to) {
                    $q->where('assigned_to', $request->assigned_to);
                }
            }])
            ->get();

        return response()->json($stages);
    }
}
