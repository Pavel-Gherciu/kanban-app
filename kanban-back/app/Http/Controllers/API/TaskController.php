<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;


class TaskController extends Controller
{
    public function index(Project $project, Board $board)
    {
        if (Auth::user()->id !== $project->user_id || $board->project_id !== $project->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cacheKey = "project_{$project->id}_board_{$board->id}_tasks";

        $tasks = Cache::remember($cacheKey, 60, function () use ($board) {
            return $board->tasks()->orderBy('position')->get();
        });

        return response()->json($tasks);
    }

    public function store(Request $request, Project $project, Board $board)
    {
        if (Auth::user()->id !== $project->user_id || $board->project_id !== $project->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'image_id' => 'nullable|integer',
            'date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $maxPosition = $board->tasks()->max('position') ?? 0;

        $task = $board->tasks()->create([
            'title' => $request->title,
            'type' => $request->type,
            'color' => $request->color,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'image_id' => $request->image_id,
            'date' => $request->date,
            'position' => $maxPosition + 1,
        ]);

        $cacheKey = "project_{$project->id}_board_{$board->id}_tasks";
        Cache::forget($cacheKey);

        return response()->json($task, 201);
    }

    public function show(Project $project, Board $board, Task $task)
    {
        if (
            Auth::user()->id !== $project->user_id ||
            $board->project_id !== $project->id ||
            $task->board_id !== $board->id
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($task);
    }

    public function update(Request $request, Project $project, Board $board, Task $task)
    {
        if (
            Auth::user()->id !== $project->user_id ||
            $board->project_id !== $project->id ||
            $task->board_id !== $board->id
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|max:7',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'image_id' => 'nullable|integer',
            'date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task->update($request->only([
            'title',
            'type',
            'color',
            'description',
            'image_url',
            'image_id',
            'date',
        ]));

        $cacheKey = "project_{$project->id}_board_{$board->id}_tasks";
        Cache::forget($cacheKey);

        return response()->json($task);
    }

    public function destroy(Project $project, Board $board, Task $task)
    {
        if (
            Auth::user()->id !== $project->user_id ||
            $board->project_id !== $project->id ||
            $task->board_id !== $board->id
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->delete();

        $tasks = $board->tasks()->orderBy('position')->get();
        foreach ($tasks as $index => $remainingTask) {
            $remainingTask->update(['position' => ($index + 1) * 10]);
        }

        $cacheKey = "project_{$project->id}_board_{$board->id}_tasks";
        Cache::forget($cacheKey);

        return response()->json(null, 204);
    }

    public function updatePositions(Request $request, Project $project, Board $board)
    {
        if (
            Auth::user()->id !== $project->user_id ||
            $board->project_id !== $project->id
        ) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        foreach ($request->tasks as $taskData) {
            $task = Task::where('id', $taskData['id'])->where('board_id', $board->id)->first();

            if ($task) {
                $task->update([
                    'position' => $taskData['position'],
                    'board_id' => $taskData['board_id'] ?? $board->id,
                ]);
            }
        }

        $cacheKey = "project_{$project->id}_board_{$board->id}_tasks";
        Cache::forget($cacheKey);

        return response()->json(['message' => 'Task positions updated successfully.'], 200);
    }

    public function updateBoards(Request $request, Project $project)
    {
        if (Auth::user()->id !== $project->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.boardId' => 'required|exists:boards,id',
            'tasks.*.position' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        foreach ($request->tasks as $taskData) {
            $task = Task::find($taskData['id']);
            $board = Board::find($taskData['boardId']);

            if ($board->project_id !== $project->id) {
                return response()->json(['error' => 'Board does not belong to the project'], 403);
            }

            $task->update([
                'board_id' => $taskData['boardId'],
                'position' => $taskData['position'],
            ]);
        }

        return response()->json(['message' => 'Task boards updated successfully.'], 200);
    }
}
