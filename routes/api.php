<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyRegistrationController;
use Illuminate\Support\Facades\Route;


// ====== COMPANY REGISTRATION ======
Route::post('/register-company', [CompanyRegistrationController::class, 'register']);
Route::get('/verify-email', [CompanyRegistrationController::class, 'verifyEmail']);
Route::post('/resend-verification-email', [CompanyRegistrationController::class, 'resendVerificationEmail']);
Route::get('/subscription-plans', [CompanyRegistrationController::class, 'getSubscriptionPlans']);

// ====== AUTHENTICATION ======
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::get('/roles', [AuthController::class, 'getRoles']);

// ====== PROTECTED ROUTES ======
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



