<?php

namespace App\Http\Controllers;

use App\Models\Segment;
use App\Services\SegmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SegmentController extends Controller
{
    protected $segmentService;

    public function __construct(SegmentService $segmentService)
    {
        $this->segmentService = $segmentService;
    }

    public function index()
    {
        $segments = Segment::with('creator')
            ->withCount('leads')
            ->orderBy('name')
            ->get();

        return response()->json($segments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'filter_rules' => 'required|array',
            'is_dynamic' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $segment = Segment::create(array_merge($request->all(), [
            'created_by' => auth()->id(),
            'cached_count' => 0
        ]));

        // Calculate initial count
        if ($segment->is_dynamic) {
            $segment->syncLeads();
        }

        return response()->json([
            'message' => 'Segment created successfully',
            'segment' => $segment
        ], 201);
    }

    public function show($id)
    {
        $segment = Segment::with(['creator', 'leads'])
            ->findOrFail($id);

        return response()->json($segment);
    }

    public function update(Request $request, $id)
    {
        $segment = Segment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'filter_rules' => 'array',
            'is_dynamic' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $segment->update($request->all());

        // Recalculate if filter rules changed
        if ($request->has('filter_rules') && $segment->is_dynamic) {
            $segment->syncLeads();
        }

        return response()->json([
            'message' => 'Segment updated successfully',
            'segment' => $segment
        ]);
    }

    public function destroy($id)
    {
        $segment = Segment::findOrFail($id);
        $segment->delete();

        return response()->json([
            'message' => 'Segment deleted successfully'
        ]);
    }

    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter_rules' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $leads = $this->segmentService->applyFilters($request->filter_rules);

        return response()->json([
            'count' => $leads->count(),
            'leads' => $leads->take(10)
        ]);
    }

    public function refresh($id)
    {
        $segment = Segment::findOrFail($id);

        if (!$segment->is_dynamic) {
            return response()->json([
                'error' => 'Can only refresh dynamic segments'
            ], 400);
        }

        $segment->syncLeads();

        return response()->json([
            'message' => 'Segment refreshed successfully',
            'count' => $segment->cached_count
        ]);
    }
}
