<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Storage;

class CourseController extends Controller
{
    /**
     * Display a list of all active courses.
     */
    public function index()
    {
        $courses = Course::active()->get();

        return response()->json($courses);
    }

    /**
     * Store a new course.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:courses,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'course_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Class creation failed.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Create the course
        try {
            $existingCourse = Course::where('name', $request->name)->first();
            if ($existingCourse) {
                return response()->json([
                    'message' => "Course with this $request->name already exists.",
                ], 422);
            }
            
            // Handle course image upload
            if ($request->hasFile('course_image')) {
                $courseImagePath = $request->file('course_image')->store('courses', 'public');
            }

            $course = Course::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'status' => 'active',
                'price' => $request->price ?? 0.00,
                'course_image' => $courseImagePath ?? null,
            ]);

            return response()->json([
                'message' => 'course created successfully.',
                'course' => $course
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error checking existing course: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single course by ID or slug.
     */
    public function show($identifier)
    {
        $course = Course::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->firstOrFail();

        return response()->json($course);
    }

    /**
     * Update an existing course.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:courses,name,' . $course->id,
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
            'price' => 'nullable|numeric|min:0',
            'course_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('course_image')) {
            // Delete old image from storage if it exists
            if ($course->course_image && Storage::disk('public')->exists($course->course_image)) {
                Storage::disk('public')->delete($course->course_image);
            }

            // Store new image
            $courseImagePath = $request->file('course_image')->store('courses', 'public');
        }

        $course->update([
            'name' => $request->name ?? $course->name,
            'slug' => Str::slug($request->name ?? $course->name),
            'description' => $request->description ?? $course->description,
            'status' => $request->status ?? $course->status,
            'price' => $request->price ?? $course->price,
            'course_image' => $courseImagePath ?? $course->course_image,
        ]);

        return response()->json([
            'message' => 'Course updated successfully.',
            'course' => $course
        ]);
    }

    /**
     * Delete (soft delete) a course.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully.'
        ]);
    }
}
