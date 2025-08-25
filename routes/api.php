<?php
// composer require laravel/sanctum
// php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"


use App\Http\Controllers\CourseController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\GuardianController;

// Student API Routes
Route::prefix('students')->group(function () {
    Route::get('/', [StudentController::class, 'index']); // List all students
    Route::post('/register', [StudentController::class, 'store']); // Create a new student
    Route::post('/verify', [StudentController::class, 'verify']); // Email Verification
    Route::get('{student}', [StudentController::class, 'show']); // Show specific student
    Route::put('{student}', [StudentController::class, 'update']); // Update a student
    Route::delete('{student}', [StudentController::class, 'destroy']); // Delete a student


    Route::post('login', [StudentController::class, 'login']); // Login student
    Route::post('/enroll', [StudentController::class, 'enroll']); //student enroll in a course


    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogleStudent']);  //student login or register using google auth
});

// Guardian API Routes
Route::prefix('guardians')->group(function () {
    Route::get('/', [GuardianController::class, 'index']);            // List all guardians
    Route::post('/register', [GuardianController::class, 'store']);           // Create a new guardian
    Route::post('/verify', [GuardianController::class, 'verify']); // Email Verification
    Route::get('/{guardian}', [GuardianController::class, 'show']);   // Show single guardian
    Route::put('/{guardian}', [GuardianController::class, 'update']); // Update guardian
    Route::delete('/{guardian}', [GuardianController::class, 'destroy']); // Delete guardian
    Route::post('/login', [GuardianController::class, 'login']);      // Guardian login

    Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogleGuardian']);  //gurdian login or register using google auth(redirect to google)
});

// student and guardian Shared google callback(callback url in google console need to be changed to api for this to work)
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

// Staff API Routes
Route::prefix('staffs')->group(function () {
    Route::get('/', [StaffController::class, 'index']); // List all staff
    Route::post('/register', [StaffController::class, 'store']); // Create a new staff
    Route::post('/verify', [StaffController::class, 'verify']); // Email Verification
    Route::get('/{staff}', [StaffController::class, 'show']); // Show specific staff
    Route::put('/{staff}', [StaffController::class, 'update']); // Update a staff
    Route::delete('/{staff}', [StaffController::class, 'destroy']); // Delete a staff
    Route::post('/login', [StaffController::class, 'login']); //staff login
    
    Route::middleware('auth:sanctum')->group(function (){
        // Route::post('/register', [StaffController::class, 'store']); // Create a new staff

    });
    // Section Routes
    Route::post('/createclass', [CourseController::class, 'store']);
});


// Course (Classes) API Routes 
Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);         // List all active course
    Route::post('/', [CourseController::class, 'store']);        // Create courses
    Route::get('/{id}', [CourseController::class, 'show']);      // Show course by ID or slug
    Route::put('/{id}', [CourseController::class, 'update']);    // Update course
    Route::delete('/{id}', [CourseController::class, 'destroy']); // Delete course
});

// Subject API Routes
Route::prefix('subjects')->group(function () {
    Route::get('/', [SubjectController::class, 'index']);         // List all published subjects
    Route::post('/', [SubjectController::class, 'store']);        // Create subject
    Route::get('/{id}', [SubjectController::class, 'show']);      // Show published subject by ID
    Route::put('/{id}', [SubjectController::class, 'update']);    // Update subject
    Route::delete('/{id}', [SubjectController::class, 'destroy']); // Delete subject
});


