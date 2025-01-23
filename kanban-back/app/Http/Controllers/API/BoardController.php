<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    public function index(Project $project)
    {
        if (Auth::user()->id !== $project->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $boards = $project->boards()->orderBy('position')->with('tasks')->get();

        return response()->json($boards);
    }


    public function store(Request $request, Project $project)
    {
        if (Auth::user()->id !== $project->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $maxPosition = $project->boards()->max('position') ?? 0;

        $board = $project->boards()->create([
            'name' => $request->name,
            'position' => $maxPosition + 10,
        ]);

        return response()->json($board, 201);
    }

    public function show(Project $project, Board $board)
    {
        if (Auth::user()->id !== $project->user_id || $board->project_id !== $project->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $board->load(['tasks' => function($query) {
            $query->orderBy('position');
        }]);

        return response()->json($board);
    }

    public function update(Request $request, Project $project, Board $board)
    {
        if (Auth::user()->id !== $project->user_id || $board->project_id !== $project->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $board->update([
            'name' => $request->name,
        ]);

        return response()->json($board);
    }

    public function destroy(Project $project, Board $board)
    {
        if (Auth::user()->id !== $project->user_id || $board->project_id !== $project->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $board->delete();

        $boards = $project->boards()->orderBy('position')->get();
        foreach ($boards as $index => $remainingBoard) {
            $remainingBoard->update(['position' => ($index + 1)]);
        }

        return response()->json(null, 204);
    }

    public function updatePositions(Request $request, Project $project)
    {
        if (Auth::user()->id !== $project->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'boards' => 'required|array',
            'boards.*.id' => 'required|exists:boards,id',
            'boards.*.position' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        foreach ($request->boards as $boardData) {
            $board = Board::find($boardData['id']);

            if ($board->project_id === $project->id) {
                $board->update(['position' => $boardData['position']]);
            } else {
                return response()->json(['error' => 'Board does not belong to the project'], 403);
            }
        }

        return response()->json(['message' => 'Board positions updated successfully.'], 200);
    }
}
