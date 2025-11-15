<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['lead', 'assignedUser', 'creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->get('overdue') === 'true') {
            $query->overdue();
        }

        if ($request->get('due_today') === 'true') {
            $query->dueToday();
        }

        $tasks = $query->orderBy('due_date')->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'nullable|exists:leads,id',
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = Task::create(array_merge($request->all(), [
            'created_by' => auth()->id(),
            'status' => 'pending'
        ]));

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load(['lead', 'assignedUser'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'priority' => 'in:low,medium,high,urgent',
            'status' => 'in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($request->all());

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->load(['lead', 'assignedUser'])
        ]);
    }

    public function complete($id)
    {
        $task = Task::findOrFail($id);
        $task->complete();

        return response()->json([
            'message' => 'Task marked as completed',
            'task' => $task
        ]);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    public function myTasks()
    {
        $tasks = Task::where('assigned_to', auth()->id())
            ->with(['lead'])
            ->orderBy('due_date')
            ->get()
            ->groupBy('status');

        return response()->json([
            'pending' => $tasks->get('pending', collect()),
            'in_progress' => $tasks->get('in_progress', collect()),
            'completed' => $tasks->get('completed', collect()),
            'overdue' => Task::where('assigned_to', auth()->id())
                ->overdue()
                ->with(['lead'])
                ->get()
        ]);
    }
}
