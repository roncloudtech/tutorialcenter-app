<?php

namespace App\Http\Controllers;

use App\Events\UserActivityEvent;
use App\Helpers\NotificationManager;
use App\Mail\GuardianEmailVerification;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class GuardianController extends Controller
{
    public function index()
    {
        $guardians = Guardian::all();
        return response()->json($guardians);
    }

    /**
     * Store a newly created guardian in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'nullable|email|unique:guardians,email',
            'phone' => 'nullable|string|unique:guardians,phone',
            'password' => 'required|string|min:8',
            'gender' => 'nullable|in:Male,Female,Others',
            'profile_picture' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'location' => 'nullable|string',
            'home_address' => 'nullable|string',
            'students_ids' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

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

        // Create guardian
        try {
            $verification_code = rand(100000, 999999);
            $guardian = new Guardian;
            $guardian->firstname = $request->input('firstname');
            $guardian->lastname = $request->input('lastname');
            $guardian->email = $request->input('email');
            $guardian->phone = $request->input('phone');
            $guardian->password = $request->input('password');
            if ($request->has('gender')) {
                $guardian->gender = $request->input('gender');
            }
            $guardian->profile_picture = $request->input('profile_picture');
            $guardian->date_of_birth = $request->input('date_of_birth');
            $guardian->location = $request->input('location');
            $guardian->home_address = $request->input('home_address');
            $guardian->students_ids = $request->input('students_ids');
            $guardian->verification_code = $verification_code;
            $guardian->save();

            // Send verification code
            if ($request->email) {
                Mail::to($guardian->email)->send(new \App\Mail\GuardianEmailVerification($guardian));
            } else if ($request->phone) {
                // $smsResponse = $termii->sendSms($guardian->phone, "Your verification code is $verification_code");

                \Log::info('Termii SMS response', [
                    'phone' => $guardian->phone,
                    // 'response' => $smsResponse
                ]);
            }

            return response()->json([
                'message' => 'Verification code sent.',
                'guardian' => $guardian,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 500);
        }
    }

    // Email Verification
    public function verify(Request $request)
    {
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
            $guardian = Guardian::where('email', $request->identifier)
                ->orWhere('phone', $request->identifier)
                ->first();
            if (!$guardian || $guardian->verification_code !== $request->code) {
                return response()->json(['message' => 'Verification Failed'], 400);
            }

            if ($guardian->email && $guardian->email === $request->identifier) {
                Guardian::where('email', $guardian->email)->update([
                    'email_verified_at' => now(),
                    'verification_code' => null,
                ]);
            }
            if ($guardian->phone && $guardian->phone === $request->identifier) {
                Guardian::where('phone', $guardian->email)->update([
                    'email_verified_at' => now(),
                    'verification_code' => null,
                ]);
            }
            $guardian->save();

            return response()->json([
                'message' => 'Verified successfully.'
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error,
            ], 500);
        }

    }

    /**
     * Display the specified guardian.
     */
    public function show(Guardian $guardian)
    {
        return response()->json($guardian);
    }

    /**
     * Update the specified guardian in storage.
     */
    public function update(Request $request, Guardian $guardian)
    {
        // Validate incoming data
        $data = $request->validate([
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:guardians,email,' . $guardian->guardian_id . ',guardian_id',
            'phone' => 'nullable|string|unique:guardians,phone,' . $guardian->guardian_id . ',guardian_id',
            'password' => 'nullable|string|min:6',
            'gender' => 'nullable|in:male,female,others',
            'profile_picture' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'location' => 'nullable|string',
            'home_address' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,disable',
            'students_ids' => 'nullable|array',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $guardian->update($data);

        return response()->json($guardian);
    }

    /**
     * Remove the specified guardian from storage.
     */
    public function destroy(Guardian $guardian)
    {
        $guardian->delete();
        return response()->json(['message' => 'Guardian deleted successfully']);
    }

    /**
     * Example login method for guardians.
     */
    public function login(Request $request)
    {
        // 1️⃣ Validate input
        $validator = Validator::make($request->all(), [
            'identifier' => 'required', // email or phone (future-proof)
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        $identifier = $request->input('identifier');

        // 2️⃣ Find guardian by email (or phone if added later)
        $guardian = Guardian::where('email','=', $identifier)->orWhere('phone','=', $identifier)->first();

        if (!$guardian) {
            return response()->json([
                'message' => 'Invalid Login.',
            ], 401);
        }

        // 3️⃣ Check password
        if (!Hash::check($request->password, $guardian->password)) {
            return response()->json([
                'message' => 'Invalid Login.',
            ], 401);
        }

        // 4️⃣ Delete old tokens and create new token
        $guardian->tokens()->delete();
        $token = $guardian->createToken('guardian-token')->plainTextToken;

        // 5️⃣ Fire audit log & notification event
        event(new UserActivityEvent(
            actor: $guardian,
            action: 'guardian_login',
            subject: $guardian,
            description: "Guardian logged in: {$guardian->firstname} {$guardian->lastname}, {$identifier}",
            changes: [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        ));

        // 6️⃣ Dispatch notifications (guardian only for now)
        NotificationManager::notify(
            recipient: $guardian,
            type: 'guardian_login',
            message: "Guardian {$guardian->firstname} {$guardian->lastname} logged in.",
            subject: $guardian
        );

        // 7️⃣ Return successful login response
        return response()->json([
            'message' => 'Login successful',
            'guardian' => $guardian,
            'token' => $token,
        ], 200);
    }


    // resending email verification code
    public function resendCode(Request $request)
    {
        $email = $request->input('email');
        $phone = $request->input('phone');

        // Make sure at least one is provided
        if (!$email && !$phone) {
            return response()->json([
                'message' => 'Provide at least email or phone number'
            ], 422);
        }

        // Find guardian by email or phone
        if ($email) {
            $guardian = Guardian::where('email', $email)->first();
        } elseif ($phone) {
            $guardian = Guardian::where('phone', $phone)->first();
        } else {
            $guardian = null;
        }

        if (!$guardian) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($guardian->verified) {
            return response()->json([
                'message' => 'User already verified'
            ], 400);
        }

        // Generate new verification code
        $verification_code = rand(100000, 999999);

        $guardian->update([
            'verification_code' => $verification_code,
        ]);

        // Send via email if available
        if ($guardian->email) {
            Mail::to($guardian->email)->send(new GuardianEmailVerification($guardian));
        }

        // Send via phone if available
        if ($guardian->phone) {
            // resend code to phone number logics here
        }

        return response()->json([
            'message' => 'Verification code resent successfully'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->noContent();
    }

}

