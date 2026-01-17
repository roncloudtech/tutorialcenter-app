<?php

namespace App\Http\Controllers;

use App\Events\UserActivityEvent;
use App\Helpers\NotificationManager;
use App\Mail\StudentEmailVerification;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Services\TermiiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Storage;

class StudentController extends Controller
{
    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request, TermiiService $termii)
    {
        // Validation Student Registration
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'nullable|email|unique:students,email',
            'phone' => 'nullable|string|unique:students,phone',
            'password' => 'required|string|min:8',
            'gender' => 'nullable|string|in:Male,Female,Others',
            'profile_picture' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'location' => 'nullable|string',
            'home_address' => 'nullable|string',
            'department' => 'nullable|string',
            'guardians_ids' => 'nullable|array',
        ]);

        // Making sure student provide their email or phone number when registering
        if (!$request->email && !$request->phone) {
            return response()->json([
                'message' => 'Email or Phone is required.'
            ], 422);
        }

        // Outputing error while registing when any occures
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $verification_code = rand(100000, 999999);
            $student = new Student;
            $student->firstname = $request->input('firstname');
            $student->lastname = $request->input('lastname');
            $student->email = $request->input('email');
            $student->phone = $request->input('phone');
            $student->password = $request->input('password');
            if ($request->has('gender')) {
                $student->gender = $request->input('gender');
            }
            $student->profile_picture = $request->input('profile_picture');
            $student->date_of_birth = $request->input('date_of_birth');
            $student->location = $request->input('location');
            $student->home_address = $request->input('home_address');
            $student->department = $request->input('department');
            $student->guardians_ids = $request->input('guardians_ids');
            $student->verification_code = $verification_code;
            $student->save();

            // Send verification code
            if ($request->email) {
                $identifier = $request->email;
                Mail::to($student->email)->send(new StudentEmailVerification($student));
            } else if ($request->phone) {
                $identifier = $request->phone;
                $smsResponse = $termii->sendSms($student->phone, "Your verification code is $verification_code");

                \Log::info('Termii SMS response', [
                    'phone' => $student->phone,
                    'response' => $smsResponse
                ]);
            }

            // Fire the event for audit + notification
            event(new UserActivityEvent(
                actor: $student,
                action: 'student_registered',
                subject: $student,
                description: "New student registered: {$student->firstname} {$student->lastname}, {$identifier}",
            ));

            return response()->json([
                'message' => 'Verification code sent.',
                'student' => $student,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /*
     * Email Verification
     */
    public function verify(Request $request)
    {
        // 1️⃣ Validate input
        $validator = Validator::make($request->all(), [
            'identifier' => 'required', // email or phone
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // 2️⃣ Find student by email or phone
            $student = Student::where('email', $request->identifier)
                ->orWhere('phone', $request->identifier)
                ->first();

            if (!$student) {
                return response()->json([
                    'message' => $request->identifier . ' does not exist',
                ], 400);
            }

            if ($student->verification_code !== $request->code) {
                return response()->json([
                    'message' => $request->code . ' is not valid',
                ], 400);
            }

            // 3️⃣ Mark verified
            $student->email_verified_at = now();
            $student->verification_code = null;
            $student->verified = 1;
            $student->save();

            // 4️⃣ Fire event for audit log + notifications
            event(new UserActivityEvent(
                actor: $student,
                action: 'student_verified',
                subject: $student,
                description: "Student verified: {$student->firstname} {$student->lastname}, {$student->email}",
            ));

            return response()->json([
                'message' => 'Verified successfully.',
            ], 200);

        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error->getMessage(),
            ], 500);
        }
    }

    /*
     * Phone Number Verification
     */
    public function sendPhoneVerification(Request $request, TermiiService $termii)
    {
        $request->validate(['phone' => 'required|string']);

        $code = rand(100000, 999999);

        $student = Student::where('phone', $request->phone)->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $student->phone_verification_code = $code;
        $student->save();

        $termii->sendSms($request->phone, "Your verification code is: $code");

        return response()->json(['message' => 'Verification code sent'], 200);
    }

    // Phone Number Verification
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string'
        ]);

        $student = Student::where('phone', $request->phone)->where('phone_verification_code', $request->code)->first();

        if (!$student) {
            return response()->json(['error' => 'Invalid code'], 400);
        }

        $student->is_phone_verified = true;
        $student->phone_verification_code = null;
        $student->save();

        return response()->json(['message' => 'Phone verified successfully'], 200);
    }

    /**
     * Display a listing of the students.
     */
    public function index()
    {
        $students = Student::all();
        return response()->json($students);
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        return response()->json($student);
    }

    /**
     * Update the specified student in storage.
     **/

    public function update(Request $request, Student $student)
    {
        // 1️⃣ Validate incoming data
        $data = $request->validate([
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'phone' => 'nullable|string|unique:students,phone,' . $student->id,
            'password' => 'nullable|string|min:6',
            'gender' => 'nullable|in:male,female,others',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'date_of_birth' => 'nullable|date',
            'location' => 'nullable|string',
            'home_address' => 'nullable|string',
            'department' => 'nullable|string',
            'guardians_ids' => 'nullable|array',
        ]);

        try {
            // 2️⃣ Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                if ($student->profile_picture && Storage::disk('public')->exists($student->profile_picture)) {
                    Storage::disk('public')->delete($student->profile_picture);
                }
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $data['profile_picture'] = $path;
            }

            // 3️⃣ Track changes for audit (exclude password)
            $changes = [];
            foreach ($data as $key => $value) {
                if ($key === 'password') {
                    continue; // skip password
                }
                if ($student->$key !== $value) {
                    $changes[$key] = [
                        'old' => $student->$key,
                        'new' => $value
                    ];
                }
            }

            // 4️⃣ Update password if provided
            if (!empty($data['password'])) {
                $student->password = $data['password'];
            }

            // 5️⃣ Update other fields
            $student->update(array_filter($data, fn($key) => $key !== 'password', ARRAY_FILTER_USE_KEY));

            // 6️⃣ Fire event for audit + notifications
            if (!empty($changes) || !empty($data['password'])) {
                if ($student->email) {
                    $identifier = $student->email;
                } else {
                    $identifier = $student->phone;
                }

                event(new UserActivityEvent(
                    actor: $student,
                    action: 'student_updated',
                    subject: $student,
                    description: "Student updated: {$student->firstname} {$student->lastname}, {$identifier}",
                    changes: $changes
                ));
            }

            // 7️⃣ Return response
            return response()->json([
                'student' => $student,
                'message' => 'Student updated successfully',
            ], 200);

        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error->getMessage(),
                'message' => 'An error occurred while updating the student.',
            ], 500);
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        try {
            $student->delete();
            return response()->json(['message' => 'Student deleted successfully'], 200);
        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error->getMessage(),
                'message' => 'An error occurred while deleting the student.',
            ], 500);
        }
    }

    /**
     * Optionally, handle login or registration for the student.
     * Example: Using email and password authentication.
     */
    public function login(Request $request)
    {
        // 1️⃣ Validate input
        $validator = Validator::make($request->all(), [
            'identifier' => 'required', // email or phone
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        $identifier = $request->input('identifier');

        // 2️⃣ Find student by email or phone
        $student = Student::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'Invalid Login.'
            ], 401);
        }

        // 3️⃣ Check password
        if (!Hash::check($request->password, $student->password)) {
            return response()->json([
                'message' => 'Invalid Login.'
            ], 401);
        }

        // 4️⃣ Delete old tokens and create new token
        $student->tokens()->delete();
        $token = $student->createToken('student-token')->plainTextToken;

        // 5️⃣ Fire audit log & notification event
        event(new UserActivityEvent(
            actor: $student,
            action: 'student_login',
            subject: $student,
            description: "Student logged in: {$student->firstname} {$student->lastname}, {$identifier}",
            changes: [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        ));

        // 6️⃣ Dispatch notifications: student + guardians + assigned adviser (if any)
        $recipients = collect([$student]);

        if ($student->assignedStaff) {
            $recipients->push($student->assignedStaff);
        }

        if ($student->guardians && $student->guardians->count()) {
            foreach ($student->guardians as $guardian) {
                $recipients->push($guardian);
            }
        }

        // Remove duplicates and notify
        $recipients->unique(fn($user) => get_class($user) . ':' . $user->id)
            ->each(function ($recipient) use ($student) {
                NotificationManager::notify(
                    recipient: $recipient,
                    type: 'student_login',
                    message: "Student {$student->firstname} {$student->lastname} logged in.",
                    subject: $student
                );
            });

        // 7️⃣ Return successful login response
        return response()->json([
            'message' => 'Login successful',
            'student' => $student,
            'token' => $token,
        ], 200);
    }



    // resending email verification code
    public function resendCode(Request $request, TermiiService $termii)
    {
        $email = $request->input('email');
        $phone = $request->input('phone');

        // Make sure at least one is provided
        if (!$email && !$phone) {
            return response()->json([
                'message' => 'Provide at least email or phone number'
            ], 422);
        }

        // Find student by email or phone
        if ($email) {
            $student = Student::where('email', $email)->first();
        } elseif ($phone) {
            $student = Student::where('phone', $phone)->first();
        } else {
            $student = null;
        }

        if (!$student) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($student->verified) {
            return response()->json([
                'message' => 'User already verified'
            ], 400);
        }

        // Generate new verification code
        $verification_code = rand(100000, 999999);

        $student->update([
            'verification_code' => $verification_code,
        ]);

        // Send via email if available
        if ($student->email) {
            Mail::to($student->email)->send(new StudentEmailVerification($student));
        }

        // Send via phone if available
        if ($student->phone) {
            $smsResponse = $termii->sendSms($student->phone, "Your verification code is $verification_code");

            \Log::info('Termii SMS response', [
                'phone' => $student->phone,
                'response' => $smsResponse
            ]);
        }

        return response()->json([
            'message' => 'Verification code resent successfully'
        ]);
    }


    //get courses and subjects student enrolled for
    public function getStudentCoursesAndSubjects($studentId)
    {
        $student = Student::select('id', 'firstname', 'lastname')
            ->with([
                'enrollments.course:id,name,slug',
                'enrollments.subjectEnrollments.subject:id,name'
            ])
            ->find($studentId);

        //gives an error message if student id passed is not existing
        if (!$student) {
            return response()->json([
                'message' => 'student does not exist'
            ], 404);
        }


        // Transform structure
        $courses = $student->enrollments->map(function ($enrollment) {
            return [
                'id' => $enrollment->course->id,
                'name' => $enrollment->course->name,
                'slug' => $enrollment->course->slug,
                'subjects' => $enrollment->subjectEnrollments->map(function ($se) {
                    return [
                        'id' => $se->subject->id,
                        'name' => $se->subject->name,
                        'slug' => $se->subject->slug,
                        'progress' => $se->progress
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'id' => $student->id,
            'firstname' => $student->firstname,
            'lastname' => $student->lastname,
            'courses' => $courses,
        ], 200);
    }


    //updates student profile picture
    // public function updateProfilePicture(Request $request, $id)
    // {
    //     // Validate the image
    //     $request->validate([
    //         'profile_picture' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
    //     ]);

    //     // Find student or return error
    //     $student = Student::find($id);
    //     if (!$student) {
    //         return response()->json(['message' => 'Student not found'], 404);
    //     }

    //     // Delete old profile picture if exists
    //     if ($student->profile_picture && Storage::disk('public')->exists($student->profile_picture)) {
    //         Storage::disk('public')->delete($student->profile_picture);
    //     }

    //     // Store new image
    //     $path = $request->file('profile_picture')->store('profile_pictures', 'public');

    //     // Update database
    //     $student->profile_picture = $path;
    //     $student->save();

    //     // Return response
    //     return response()->json([
    //         'message' => 'Profile picture updated successfully',
    //         'profile_picture_url' => asset('storage/' . $path),
    //     ], 200);
    // }

    public function updateProfilePicture(Request $request, $id)
    {
        // 1️⃣ Validate the image
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // 2️⃣ Find student
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        // 3️⃣ Track old value for audit
        $oldProfilePicture = $student->profile_picture;

        // 4️⃣ Delete old profile picture if exists
        if ($oldProfilePicture && Storage::disk('public')->exists($oldProfilePicture)) {
            Storage::disk('public')->delete($oldProfilePicture);
        }

        // 5️⃣ Store new image
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // 6️⃣ Update student
        $student->profile_picture = $path;
        $student->save();

        // 7️⃣ Fire audit + notification event
        event(new UserActivityEvent(
            actor: $student,
            action: 'update_profile_picture',
            subject: $student,
            description: "Student updated profile picture",
            changes: [
                'profile_picture' => [
                    'old' => $oldProfilePicture,
                    'new' => $path,
                ]
            ]
        ));

        // 8️⃣ Notify student + adviser + guardians
        $recipients = collect([$student]);

        if ($student->assignedStaff) {
            $recipients->push($student->assignedStaff);
        }

        if ($student->guardians && $student->guardians->count()) {
            foreach ($student->guardians as $guardian) {
                $recipients->push($guardian);
            }
        }

        $recipients->unique(fn($user) => get_class($user) . ':' . $user->id)
            ->each(function ($recipient) use ($student) {
                NotificationManager::notify(
                    recipient: $recipient,
                    type: 'update_profile_picture',
                    message: "Student {$student->firstname} {$student->lastname} updated profile picture.",
                    subject: $student
                );
            });

        // 9️⃣ Return response
        return response()->json([
            'message' => 'Profile picture updated successfully',
            'profile_picture_url' => asset('storage/' . $path),
        ], 200);
    }


    /**
     * Logout the authenticated student.
     */
    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()?->delete();
    //     return response()->noContent();
    // }
    public function logout(Request $request)
    {
        $student = $request->user();

        if (!$student) {
            return response()->json([
                'message' => 'No authenticated user found.'
            ], 401);
        }

        // 1️⃣ Delete the current access token
        $student->currentAccessToken()?->delete();

        // 2️⃣ Fire audit log
        event(new UserActivityEvent(
            actor: $student,
            action: 'student_logout',
            subject: $student,
            description: "Student logged out: {$student->firstname} {$student->lastname}"
        ));

        // 3️⃣ Send notification to student themselves (logout is personal)
        NotificationManager::notify(
            recipient: $student,
            type: 'student_logout',
            message: "You have successfully logged out.",
            subject: $student
        );

        // 4️⃣ Return response
        return response()->noContent();
    }


}


