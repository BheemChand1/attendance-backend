<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyRegistrationController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;


// ====== COMPANY REGISTRATION ======
Route::post('/register-company', [CompanyRegistrationController::class, 'register']);
Route::get('/verify-email', [CompanyRegistrationController::class, 'verifyEmail']);
Route::post('/resend-verification-email', [CompanyRegistrationController::class, 'resendVerificationEmail']);
Route::get('/subscription-plans', [CompanyRegistrationController::class, 'getSubscriptionPlans']);

// ====== AUTHENTICATION ======
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::get('/roles', [AuthController::class, 'getRoles']);

// ====== PROTECTED ROUTES ======
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ====== EMPLOYEE MANAGEMENT ======
Route::middleware('auth:sanctum')->group(function () {
    // Onboard new employee (HR and Company Admin only)
    Route::post('/employees/onboard', [EmployeeController::class, 'onboard']);
    
    // Get all employees in company
    Route::get('/employees', [EmployeeController::class, 'index']);
    
    // Get specific employee
    Route::get('/employees/{userId}', [EmployeeController::class, 'show']);
    
    // Update employee profile (HR and Company Admin only)
    Route::put('/employees/{userId}', [EmployeeController::class, 'update']);
    
    // Delete employee (Company Admin only)
    Route::delete('/employees/{userId}', [EmployeeController::class, 'delete']);
});



