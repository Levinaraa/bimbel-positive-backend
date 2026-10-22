<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 1. IMPORT CONTROLLERS
// =========================================================================

// --- Auth & Payment ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;

// --- Admin Controllers ---
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminCourseController;
use App\Http\Controllers\Api\Admin\PackageController;
use App\Http\Controllers\Api\Admin\AdminAbsensiController;
use App\Http\Controllers\Api\Admin\AdminTransactionController;

// --- Siswa Controllers ---
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\TryoutController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\RekapController;

// --- Guru Controllers (Namespace Teacher) ---
// Kita gunakan alias 'TeacherTryout' agar tidak bentrok dengan TryoutController milik siswa
use App\Http\Controllers\Api\Teacher\TryoutController as TeacherTryout;
use App\Http\Controllers\Api\Teacher\CourseManagementController as TeacherCourse;
use App\Http\Controllers\Api\Teacher\RekapController as TeacherRekap;

/*
|--------------------------------------------------------------------------
| API Routes Lengkap - Learning Management System
|--------------------------------------------------------------------------
*/

// =========================================================================
// 2. PUBLIC ROUTES (Tanpa Login)
// =========================================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/courses', [CourseController::class, 'index']);   
Route::get('/packages', [PackageController::class, 'index']); 
Route::get('/tutors', [AuthController::class, 'tutors']);     

// Midtrans Webhook
Route::post('/payment/notification', [PaymentController::class, 'notificationHandler']);


// =========================================================================
// 3. PROTECTED ROUTES (Wajib Login)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- User Profile & Logout ---
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::post('/user/update', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/rekap', [RekapController::class, 'index']);
    
    Route::get('/schedules/today', [CourseController::class, 'getTodaySchedule']);

    // =====================================================================
    // A. FITUR SISWA
    // =====================================================================
    
    // LMS
    Route::get('/courses/{id}', [CourseController::class, 'show']); 
    Route::get('/materials/{id}/comments', [CommentController::class, 'index']); 
    Route::post('/materials/{id}/comments', [CommentController::class, 'store']); 

    // Tryout
    Route::get('/tryouts', [TryoutController::class, 'index']);         
    Route::get('/tryouts/{id}', [TryoutController::class, 'show']);      
    Route::post('/tryouts/{id}/submit', [TryoutController::class, 'submit']); 
    Route::get('/tryouts/{id}/discussion', [TryoutController::class, 'discussion']); 

    // Pembayaran
    Route::post('/payment/charge', [PaymentController::class, 'createTransaction']); 
    Route::get('/payment/history', [PaymentController::class, 'history']); 


    // =====================================================================
    // B. FITUR GURU (Prefix: /teacher)
    // =====================================================================
    Route::prefix('teacher')->group(function () {
        
        // Dashboard
        Route::get('/rekap', [TeacherRekap::class, 'index']);

        // Manajemen Tryout (CRUD)
        Route::get('/tryouts', [TeacherTryout::class, 'index']);     // List Tryout
        
        // --- [FIX] PERBAIKAN UTAMA ANDA DI SINI ---
        // Route ini WAJIB ADA agar halaman Input Soal tidak error 405/404
        Route::get('/tryouts/{id}', [TeacherTryout::class, 'show']); // Detail Tryout
        // ------------------------------------------

        Route::post('/tryouts', [TeacherTryout::class, 'store']);    // Buat Baru
        Route::put('/tryouts/{id}', [TeacherTryout::class, 'update']); // Edit Header
        Route::delete('/tryouts/{id}', [TeacherTryout::class, 'destroy']); // Hapus
        
        // Input Soal
        Route::post('/tryouts/{id}/questions', [TeacherTryout::class, 'storeQuestion']); 

        // Manajemen Materi
        Route::post('/courses/{courseId}/weeks', [TeacherCourse::class, 'storeWeek']);     
        Route::post('/weeks/{weekId}/materials', [TeacherCourse::class, 'storeMaterial']); 
    });


    // =====================================================================
    // C. FITUR ADMIN (Prefix: /admin)
    // =====================================================================
    Route::prefix('admin')->group(function () {
        
        // Dashboard
        Route::get('/stats', [DashboardController::class, 'stats']);

        // User Management
        Route::get('/teachers', [AdminUserController::class, 'getTeachers']);
        Route::get('/students', [AdminUserController::class, 'getStudents']);
        Route::post('/users', [AdminUserController::class, 'store']);        
        Route::put('/users/{id}', [AdminUserController::class, 'update']);   
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']); 

        // Course Management
        Route::get('/courses', [AdminCourseController::class, 'index']);
        Route::post('/courses', [AdminCourseController::class, 'store']);
        Route::post('/courses/{id}', [AdminCourseController::class, 'update']); 
        Route::delete('/courses/{id}', [AdminCourseController::class, 'destroy']);

        // Pricing Management
        Route::get('/packages', [PackageController::class, 'index']);
        Route::post('/packages', [PackageController::class, 'store']);
        Route::put('/packages/{id}', [PackageController::class, 'update']);
        Route::delete('/packages/{id}', [PackageController::class, 'destroy']);
        Route::get('/packages-chart', [PackageController::class, 'chartData']); 

        // Payment & Absensi
        Route::get('/payments', [AdminTransactionController::class, 'index']);
        Route::post('/payments/delete', [AdminTransactionController::class, 'destroy']);
        Route::get('/absensi/classes', [AdminAbsensiController::class, 'getActiveClasses']);
        Route::get('/absensi/teachers', [AdminAbsensiController::class, 'getTeacherAttendance']);
        Route::get('/absensi/students', [AdminAbsensiController::class, 'getStudentAttendance']);
    });

});