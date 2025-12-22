<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\CompanySubscription;
use App\Notifications\VerifyCompanyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CompanyRegistrationController extends Controller
{
    /**
     * Get all available subscription plans
     */
    public function getSubscriptionPlans()
    {
        $plans = Subscription::where('is_active', true)->get([
            'id', 'name', 'description', 'price', 'max_employees', 'storage_gb', 'support_level'
        ]);

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Register a new company with subscription
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            // Company info
            'company_name' => 'required|string|max:255|unique:companies,name',
            'company_email' => 'required|email|unique:companies,email',
            'company_phone' => 'required|string|max:20',
            'company_address' => 'required|string|max:500',
            'company_size' => 'required|string',
            'location' => 'required|string|max:100',

            // Company Admin info
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_phone' => 'required|string|max:20',
            'admin_password' => 'required|string|min:8|confirmed',

            // Subscription plan
            'subscription_id' => 'required|exists:subscriptions,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        try {
            // Create Company
            $company = Company::create([
                'name' => $validated['company_name'],
                'email' => $validated['company_email'],
                'phone' => $validated['company_phone'],
                'address' => $validated['company_address'],
                'company_size' => $validated['company_size'],
                'location' => $validated['location'],
                'is_active' => true,
            ]);

            // Get Company Admin Role
            $adminRole = Role::where('slug', 'company_admin')->firstOrFail();

            // Generate verification token
            $verificationToken = Str::random(60);

            // Create Company Admin User
            $admin = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'phone' => $validated['admin_phone'],
                'company_id' => $company->id,
                'role_id' => $adminRole->id,
                'is_active' => false, // Inactive until email is verified
            ]);

            // Store verification token
            $admin->email_verification_token = $verificationToken;
            $admin->save();

            // Get Subscription Plan
            $subscription = Subscription::findOrFail($validated['subscription_id']);

            // Create Company Subscription
            $startDate = Carbon::today();
            $endDate = $validated['billing_cycle'] === 'yearly' 
                ? $startDate->copy()->addYear() 
                : $startDate->copy()->addMonth();

            CompanySubscription::create([
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'price' => $subscription->price,
                'billing_cycle' => $validated['billing_cycle'],
                'next_billing_date' => $endDate,
                'employee_count' => 1, // Only admin for now
            ]);

            // Send verification email
            $admin->notify(new VerifyCompanyEmail($verificationToken, $company->name));

            return response()->json([
                'success' => true,
                'message' => 'Company registered successfully. Please verify your email.',
                'data' => [
                    'company_id' => $company->id,
                    'admin_id' => $admin->id,
                    'company_name' => $company->name,
                    'admin_email' => $admin->email,
                    'subscription_plan' => $subscription->name,
                    'note' => 'Verification email sent to ' . $admin->email,
                ],
                // Only for local testing - remove in production
                'verification_token' => config('app.env') === 'local' ? $verificationToken : null,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify company admin email
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification token'
            ], 400);
        }

        // Activate user and clear token
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        // Activate company
        $user->company->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully. Your company is now active.',
            'data' => [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'company_name' => $user->company->name,
            ]
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', false)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or already verified'
            ], 404);
        }

        // Generate new verification token
        $verificationToken = Str::random(60);
        $user->email_verification_token = $verificationToken;
        $user->save();

        // Send email
        $user->notify(new VerifyCompanyEmail($verificationToken, $user->company->name));

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent. Please check your inbox.'
        ]);
    }
}
