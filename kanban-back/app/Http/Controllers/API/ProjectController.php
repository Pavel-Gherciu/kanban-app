<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Auth::user()->projects()->with('boards.tasks')->get();
        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Auth::user()->projects()->create([
            'name' => $request->name,
        ]);

        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        if (Auth::user()->id !== $project->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->load('boards.tasks');
        return response()->json($project);
    }

    public function update(Request $request, Project $project)
    {
        if (Auth::user()->id !== $project->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project->update([
            'name' => $request->name,
        ]);

        return response()->json($project);
    }

    public function destroy(Project $project)
    {
        if (Auth::user()->id !== $project->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->delete();
        return response()->json(null, 204);
    }
}
