<?php

namespace App\Http\Controllers;

use App\Models\Masterclass;
use Illuminate\Http\Request;

class MasterclassController extends Controller
{
    /**
     * Display a list of all masterclasses
     */
    public function index()
    {
        $masterclasses = Masterclass::with('subject', 'schedules')->get();
        return response()->json($masterclasses);
    }

    /**
     * Store a new masterclass
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'assignees_id' => 'nullable|array',
            ]);
            $masterclass = Masterclass::create([
                'subject_id' => $validated['subject_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'assignees_id' => $validated['assignees_id'] ?? [],
            ]);

            return response()->json([
                'message' => 'Masterclass created successfully',
                'masterclass' => $masterclass,
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $error->getMessage(),
            ], 500);
        }

    }

    /**
     * Display a single masterclass
     */
    public function show($id)
    {
        $masterclass = Masterclass::with('subject', 'schedules')->findOrFail($id);
        return response()->json($masterclass);
    }

    /**
     * Update an existing masterclass
     */
    public function update(Request $request, $id)
    {
        $masterclass = Masterclass::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'assignees_id' => 'nullable|array',
        ]);

        $masterclass->update($validated);

        return response()->json([
            'message' => 'Masterclass updated successfully',
            'data' => $masterclass,
        ], 200);
    }

    /**
     * Delete a masterclass
     */
    public function destroy($id)
    {
        $masterclass = Masterclass::findOrFail($id);
        $masterclass->delete();

        return response()->json(['message' => 'Masterclass deleted successfully']);
    }
}
