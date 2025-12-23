<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Onboard a new employee
     * Only HR and Company Admin can create employees
     */
    public function onboard(Request $request)
    {
        try {
            // Check authorization - only HR and Company Admin can create employees
            $user = $request->user();
            $userRole = $user->role->name ?? null;

            if (!in_array($userRole, ['Company Admin', 'HR'])) {
                return response()->json([
                    'message' => 'Unauthorized. Only HR and Company Admin can create employees.',
                    'status' => false
                ], 403);
            }

            // Validate required fields
            $validated = $request->validate([
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'dateOfBirth' => 'nullable|date',
                'gender' => 'nullable|in:Male,Female,Other',
                'photo' => 'nullable|image|mimes:jpeg,png,gif|max:5120', // 5MB

                // Address
                'street' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zipCode' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',

                // Professional
                'employeeId' => 'required|string|unique:employee_profiles,employee_code',
                'department' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'salary' => 'nullable|numeric|min:0',
                'joiningDate' => 'nullable|date',
                'manager' => 'nullable|string|max:255',
                'status' => 'nullable|in:Active,Inactive,On Leave',

                // Qualifications
                'qualification' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'university' => 'nullable|string|max:255',
                'graduationYear' => 'nullable|integer|min:1900|max:' . date('Y'),

                // Company
                'company_id' => 'required|exists:companies,id',

                // Documents
                'documents' => 'nullable|array',
                'documents.*.name' => 'required_with:documents|string|max:255',
                'documents.*.file' => 'required_with:documents|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120', // 5MB
            ]);

            // Get the employee role ID
            $employeeRole = Role::where('name', 'Employee')->first();
            if (!$employeeRole) {
                return response()->json([
                    'message' => 'Employee role not found in system',
                    'status' => false
                ], 500);
            }

            // Create User
            $password = Str::random(12);
            $user = User::create([
                'name' => $validated['firstName'] . ' ' . $validated['lastName'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'company_id' => $validated['company_id'],
                'role_id' => $employeeRole->id,
                'phone' => $validated['phone'] ?? null,
                'is_active' => true,
            ]);

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store(
                    "employees/{$user->id}/photos",
                    'public'
                );
            }

            // Handle documents
            $documents = [];
            if ($request->hasFile('documents')) {
                // Get the document names from the request
                $documentNames = $request->input('documents.*.name', []);
                $documentFiles = $request->file('documents');

                foreach ($documentFiles as $index => $document) {
                    $fileName = $document->getClientOriginalName();
                    $docPath = $document->store(
                        "employees/{$user->id}/documents",
                        'public'
                    );

                    $documents[] = [
                        'name' => $documentNames[$index] ?? 'Document',
                        'path' => $docPath,
                        'original_name' => $fileName,
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }

            // Create Employee Profile
            $employeeProfile = EmployeeProfile::create([
                'user_id' => $user->id,
                'company_id' => $validated['company_id'],
                'employee_code' => $validated['employeeId'],
                'date_of_birth' => $validated['dateOfBirth'] ?? null,
                'gender' => strtolower($validated['gender'] ?? null),
                'employee_photo' => $photoPath,

                // Address
                'street_address' => $validated['street'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'zip_code' => $validated['zipCode'] ?? null,
                'country' => $validated['country'] ?? null,

                // Professional
                'department' => $validated['department'],
                'position' => $validated['position'],
                'salary' => $validated['salary'] ?? null,
                'joining_date' => $validated['joiningDate'] ?? null,
                'status' => strtolower($validated['status'] ?? 'active'),

                // Qualifications
                'qualification' => $validated['qualification'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
                'university' => $validated['university'] ?? null,
                'graduation_year' => $validated['graduationYear'] ?? null,

                // Documents
                'documents' => !empty($documents) ? json_encode($documents) : null,
            ]);

            return response()->json([
                'message' => 'Employee successfully onboarded',
                'status' => true,
                'data' => [
                    'user_id' => $user->id,
                    'employee_id' => $employeeProfile->employee_code,
                    'name' => $user->name,
                    'email' => $user->email,
                    'temporary_password' => $password, // Send via email in production
                    'profile' => $employeeProfile
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error onboarding employee',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee profile
     */
    public function show($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Check authorization
            $authUser = auth()->user();
            if ($authUser->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => false
                ], 403);
            }

            $employeeProfile = EmployeeProfile::where('user_id', $userId)->firstOrFail();

            return response()->json([
                'status' => true,
                'data' => [
                    'user' => $user,
                    'profile' => $employeeProfile
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Employee not found',
                'status' => false
            ], 404);
        }
    }

    /**
     * Update employee profile
     */
    public function update(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Check authorization - only HR and Company Admin
            $authUser = $request->user();
            $userRole = $authUser->role->name ?? null;

            if (!in_array($userRole, ['Company Admin', 'HR'])) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => false
                ], 403);
            }

            if ($authUser->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => false
                ], 403);
            }

            $employeeProfile = EmployeeProfile::where('user_id', $userId)->firstOrFail();

            // Validate
            $validated = $request->validate([
                'firstName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'dateOfBirth' => 'nullable|date',
                'gender' => 'nullable|in:Male,Female,Other',
                'photo' => 'nullable|image|mimes:jpeg,png,gif|max:5120',

                'street' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zipCode' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',

                'department' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:255',
                'salary' => 'nullable|numeric|min:0',
                'joiningDate' => 'nullable|date',
                'manager' => 'nullable|string|max:255',
                'status' => 'nullable|in:Active,Inactive,On Leave',

                'qualification' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'university' => 'nullable|string|max:255',
                'graduationYear' => 'nullable|integer|min:1900|max:' . date('Y'),
            ]);

            // Update user
            if (isset($validated['firstName']) || isset($validated['lastName'])) {
                $name = ($validated['firstName'] ?? explode(' ', $user->name)[0]) . ' ' .
                        ($validated['lastName'] ?? explode(' ', $user->name)[1] ?? '');
                $user->update(['name' => trim($name)]);
            }

            if (isset($validated['phone'])) {
                $user->update(['phone' => $validated['phone']]);
            }

            // Handle photo update
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($employeeProfile->employee_photo) {
                    Storage::disk('public')->delete($employeeProfile->employee_photo);
                }
                
                $photoPath = $request->file('photo')->store(
                    "employees/{$user->id}/photos",
                    'public'
                );
                $employeeProfile->update(['employee_photo' => $photoPath]);
            }

            // Update profile fields
            $profileData = [];
            if (isset($validated['dateOfBirth'])) $profileData['date_of_birth'] = $validated['dateOfBirth'];
            if (isset($validated['gender'])) $profileData['gender'] = strtolower($validated['gender']);
            if (isset($validated['street'])) $profileData['street_address'] = $validated['street'];
            if (isset($validated['city'])) $profileData['city'] = $validated['city'];
            if (isset($validated['state'])) $profileData['state'] = $validated['state'];
            if (isset($validated['zipCode'])) $profileData['zip_code'] = $validated['zipCode'];
            if (isset($validated['country'])) $profileData['country'] = $validated['country'];
            if (isset($validated['department'])) $profileData['department'] = $validated['department'];
            if (isset($validated['position'])) $profileData['position'] = $validated['position'];
            if (isset($validated['salary'])) $profileData['salary'] = $validated['salary'];
            if (isset($validated['joiningDate'])) $profileData['joining_date'] = $validated['joiningDate'];
            if (isset($validated['status'])) $profileData['status'] = strtolower($validated['status']);
            if (isset($validated['qualification'])) $profileData['qualification'] = $validated['qualification'];
            if (isset($validated['specialization'])) $profileData['specialization'] = $validated['specialization'];
            if (isset($validated['university'])) $profileData['university'] = $validated['university'];
            if (isset($validated['graduationYear'])) $profileData['graduation_year'] = $validated['graduationYear'];

            $employeeProfile->update($profileData);

            return response()->json([
                'message' => 'Employee profile updated successfully',
                'status' => true,
                'data' => [
                    'user' => $user,
                    'profile' => $employeeProfile
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating employee',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all employees in company
     */
    public function index(Request $request)
    {
        try {
            $authUser = $request->user();
            $companyId = $authUser->company_id;

            $employees = User::where('company_id', $companyId)
                ->whereHas('role', function ($query) {
                    $query->where('name', 'Employee');
                })
                ->with('employeeProfile')
                ->paginate(15);

            return response()->json([
                'status' => true,
                'data' => $employees
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching employees',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete employee
     */
    public function delete(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Check authorization
            $authUser = $request->user();
            $userRole = $authUser->role->name ?? null;

            if (!in_array($userRole, ['Company Admin'])) {
                return response()->json([
                    'message' => 'Only Company Admin can delete employees',
                    'status' => false
                ], 403);
            }

            if ($authUser->company_id !== $user->company_id) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => false
                ], 403);
            }

            // Delete employee profile and associated files
            $employeeProfile = EmployeeProfile::where('user_id', $userId)->first();
            if ($employeeProfile) {
                // Delete photo
                if ($employeeProfile->employee_photo) {
                    Storage::disk('public')->delete($employeeProfile->employee_photo);
                }

                // Delete documents
                if ($employeeProfile->documents) {
                    $docs = json_decode($employeeProfile->documents, true);
                    foreach ($docs as $doc) {
                        Storage::disk('public')->delete($doc['path']);
                    }
                }

                // Delete directory
                Storage::disk('public')->deleteDirectory("employees/{$user->id}");
                $employeeProfile->delete();
            }

            // Delete user
            $user->delete();

            return response()->json([
                'message' => 'Employee deleted successfully',
                'status' => true
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting employee',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
