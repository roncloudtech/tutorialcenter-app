<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    /**
     * Display a list of all published subjects.
     */
    public function index()
    {
        $subjects = Subject::where('status', 'published')->latest()->get();
        return response()->json([$subjects, 'length' => count($subjects)], 200);
    }

    /**
     * Store a new subject.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'courses_ids' => 'nullable|array',
            'tutors_assignees' => 'nullable|array',
            'departments' => 'nullable|array',
            'created_by' => 'required|exists:staffs,id',
            'status' => 'in:published,draft,archived',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subject = Subject::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'description' => $request->description,
            'courses_ids' => $request->courses_ids ?? [],
            'tutors_assignees' => $request->tutors_assignees ?? [],
            'departments' => $request->departments ?? [],
            'created_by' => $request->created_by,
            'status' => $request->status ?? 'draft',
        ]);

        return response()->json([
            'message' => 'Subject created successfully.',
            'subject'  => $subject
        ], 201);
    }

    /**
     * Show a specific published subject.
     */
    public function show($id)
    {
        $subject = Subject::where('id', $id)->where('status', 'published')->first();

        if (!$subject) {
            return response()->json(['message' => 'Subject not found.'], 404);
        }

        return response()->json($subject, 200);
    }

    /**
     * Update a subject.
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'              => 'sometimes|string|max:255',
            'description'       => 'sometimes|string',
            'courses_ids'      => 'nullable|array',
            'tutors_assignees'  => 'nullable|array',
            'departments'       => 'nullable|array',
            'status'            => 'in:published,draft,archived',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subject->update($request->only([
            'name',
            'description',
            'courses_ids',
            'tutors_assignees',
            'departments',
            'status',
        ]));

        if ($request->has('name')) {
            $subject->slug = Str::slug($request->name) . '-' . uniqid();
            $subject->save();
        }

        return response()->json(['message' => 'Subject updated.', 'subject' => $subject], 200);
    }

    /**
     * Soft delete a subject.
     */
    public function destroy($id)
    {
        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json(['message' => 'Subject not found.'], 404);
        }

        $subject->delete();

        return response()->json(['message' => 'Subject deleted.'], 200);
    }
}
