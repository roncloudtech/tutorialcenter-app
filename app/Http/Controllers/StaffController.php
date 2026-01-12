<?php

namespace App\Http\Controllers;

use App\Events\UserActivityEvent;
use App\Helpers\AuditLogger;
use App\Models\AuditLog;
use Carbon\Carbon;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class StaffController extends Controller
{
    /**
     * Store a newly created staff in storage.
     **/
    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:staffs,email',
            'phone' => 'nullable|string|unique:staffs,phone',
            'gender' => 'nullable|in:Male,Female,Others',
            'staff_role' => 'nullable|in:admin,tutor,adviser,staff',
            'profile_picture' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'home_address' => 'nullable|string',
            'indected_by' => 'nullable|integer|exists:staff,staff_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if (!$request->email && !$request->phone) {
            return response()->json([
                'message' => 'Email or Phone is required.'
            ], 422);
        }

        // Create staff
        try {
            /*
             *Generate unique staff ID
             *Format: TC{year}{month}{random_number}
             *Example: TC25071234 for a staff created in July 2025 with a random number
             */
            $year = date('ym');
            $rand = rand(1000, 9999);
            $staff_id = 'TC' . $year . $rand;

            $staff = Auth::guard('staff')->user();

            $staff = new Staff;
            $staff->staff_id = $staff_id;
            $staff->firstname = $request->input('firstname');
            $staff->lastname = $request->input('lastname');
            $staff->email = $request->input('email');
            $staff->phone = $request->input('phone');
            if ($request->has('gender')) {
                $staff->gender = $request->input('gender');
            }
            $staff->password = $staff_id;
            $staff->staff_role = $request->input('staff_role') ?? 'staff';
            $staff->profile_picture = $request->input('profile_picture');
            $staff->date_of_birth = $request->input('date_of_birth');
            $staff->home_address = $request->input('home_address');
            // $staff->indected_by = $request->input('indected_by');
            $staff->indected_by = $staff->id;

            $staff->verified = false;
            $staff->status = 'inactive';
            $staff->save();

            // Send verification code
            if ($request->email) {
                Mail::send('emails.staff-induction', [
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'role' => $staff->staff_role,
                    'staff_id' => $staff->staff_id,
                    'email' => $staff->email,
                    'login_link' => env('FRONTEND_URL') . '/api/staff/login',
                ], function ($message) use ($staff) {
                    $message->to($staff->email)
                        ->subject('Welcome to Tutorial Center!');
                });

            } else if ($request->phone) {
                // $smsResponse = $termii->sendSms($staff->phone, "Your verification code is $verification_code");

                \Log::info('Termii SMS response', [
                    'phone' => $staff->phone,
                    // 'response' => $smsResponse
                ]);
            }

            return response()->json([
                'message' => 'Staff created successfully. Induction mail has been sent.',
                'staff' => $staff,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 500);
        }
    }

    /** 
     * staff login method
     **/
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('staff')->attempt($credentials)) {
            $staff = Auth::guard('staff')->user();

            // Make sure staff is authenticated and saved
            if (!$staff || !$staff->id) {
                return response()->json([
                    'message' => 'Something went wrong, user not authenticated properly.'
                ], 500);
            }

            $staff->tokens()->delete();
            $token = $staff->createToken('staff-token')->plainTextToken;


            // // Using the right logger to log staff login activity
            // event(new UserActivityEvent(
            //     actor: $staff,
            //     action: 'login',
            //     description: "({$staff->staff_role}) {$staff->email} logged in successfully"
            // ));


            event(new UserActivityEvent(
                actor: $staff,
                action: 'login',
                description: "({$staff->staff_role}) {$staff->email} logged in successfully"
            ));


            return response()->json([
                'message' => 'Login successful',
                'staff' => $staff,
                'staff-token' => $token,
            ], 200);
        }

        return response()->json(['message' => 'Invalid email or password'], 401);
    }

    /** 
     * Update Staff Info
     **/
    public function update(Request $request, Staff $staff)
    {
        // Validate incoming data
        $data = $request->validate([
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:staffs,email,' . $staff->id,
            'phone' => 'nullable|string|unique:staffs,phone,' . $staff->id,
            'password' => 'nullable|string|min:6',
            'gender' => 'nullable|in:male,female,others',
            'profile_picture' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'home_address' => 'nullable|string',
            'staff_role' => 'nullable|in:admin,tutor,adviser,staff',
            'indected_by' => 'nullable|integer|exists:staff,staff_id',
        ]);

        try {
            // Update staff profile
            $staff->update($data);
            return response()->json([
                'staff' => $staff,
                'message' => 'Staff profile updated successfully',
            ], 200);

        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error->getMessage(),
                'message' => 'An error occurred while updating the student.',
            ], 500);
        }
    }

    /** 
     * Show staffs
     **/
    public function show()
    {
        $staffs = Staff::all();
        return response()->json([
            'staffs' => $staffs
        ], 200);
    }

    /**
     * ========================
     * Forgot Password
     * ========================
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:staffs,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $staff = Staff::where('email', $request->email)->first();

        // Generate token
        $token = \Str::random(60);

        // Save token in password_resets table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $staff->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        // Send token to email
        Mail::send('emails.staff-password-reset', [
            'firstname' => $staff->firstname,
            'lastname' => $staff->lastname,
            'reset_link' => env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . $staff->email,
        ], function ($message) use ($staff) {
            $message->to($staff->email)->subject('Password Reset Request');
        });

        return response()->json([
            'message' => 'Password reset link sent to your email address.',
        ], 200);
    }

    /**
     * ========================
     * Reset Password
     * ========================
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:staffs,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed', // expect password + password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        try {
            $resetRecord = DB::table('password_reset_tokens')->where('email', $request->email)->first();

            if (!$resetRecord) {
                return response()->json(['message' => 'Invalid token or email.'], 400);
            }

            // Verify token
            if (!Hash::check($request->token, $resetRecord->token)) {
                return response()->json(['message' => 'Invalid or expired token.'], 400);
            }

            // Update staff password
            $staff = Staff::where('email', $request->email)->first();
            $staff->password = Hash::make($request->password);
            $staff->email_verified_at = now();
            $staff->save();

            // Delete reset record
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'message' => 'Password reset successfully.',
                'staff' => $staff
            ], 200);
        } catch (\Exception $errors) {
            return response()->json([
                'errors' => $errors->getMessage(),
                'message' => 'Server Error Occurred',
            ], 500);
        }
    }

    /**
     * ========================
     * Logout
     * ========================
     **/
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->noContent();
    }
}


