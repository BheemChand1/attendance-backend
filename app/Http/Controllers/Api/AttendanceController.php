<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendancePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Check in - Create or update attendance record
     */
    public function checkIn(Request $request)
    {
        try {
            $validated = $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $user = $request->user();
            $today = Carbon::today();

            DB::beginTransaction();

            // Check if already checked in today
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();

            if ($attendance && $attendance->check_in) {
                return response()->json([
                    'message' => 'Already checked in today',
                    'status' => false,
                    'data' => $attendance
                ], 422);
            }

            // Create or update attendance
            if (!$attendance) {
                $attendance = Attendance::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'date' => $today,
                    'check_in' => now(),
                    'status' => 'present',
                ]);
            } else {
                $attendance->update([
                    'check_in' => now(),
                    'status' => 'present',
                ]);
            }

            // Upload photo
            $photoPath = $request->file('photo')->store(
                "attendance/{$user->id}/" . $today->format('Y-m-d'),
                'public'
            );

            // Save photo record
            AttendancePhoto::create([
                'attendance_id' => $attendance->id,
                'type' => 'check_in',
                'photo_path' => $photoPath,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Check-in successful',
                'status' => true,
                'data' => $attendance->load('photos')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error during check-in',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check out - Update attendance record
     */
    public function checkOut(Request $request)
    {
        try {
            $validated = $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $user = $request->user();
            $today = Carbon::today();

            DB::beginTransaction();

            // Find the most recent attendance that has check_in but no check_out
            // This handles cases where checkout happens on a different day (night shifts)
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->orderBy('date', 'desc')
                ->orderBy('check_in', 'desc')
                ->first();

            if (!$attendance) {
                return response()->json([
                    'message' => 'No active check-in found. Please check in first.',
                    'status' => false
                ], 422);
            }

            // Update check out time with current date and time
            $attendance->update([
                'check_out' => now(),
            ]);

            // Upload photo with today's date in path
            $photoPath = $request->file('photo')->store(
                "attendance/{$user->id}/" . $today->format('Y-m-d'),
                'public'
            );

            // Save photo record
            AttendancePhoto::create([
                'attendance_id' => $attendance->id,
                'type' => 'check_out',
                'photo_path' => $photoPath,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Check-out successful',
                'status' => true,
                'data' => $attendance->load('photos')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error during check-out',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's attendance status
     */
    public function todayStatus(Request $request)
    {
        try {
            $user = $request->user();
            $today = Carbon::today();

            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->with('photos')
                ->first();

            return response()->json([
                'status' => true,
                'data' => $attendance ?? [
                    'message' => 'No attendance record for today',
                    'checked_in' => false
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching attendance status',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance history
     */
    public function history(Request $request)
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2020|max:2100'
            ]);

            $month = $validated['month'];
            $year = $validated['year'];

            // Get the first and last day of the specified month
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $attendances = Attendance::where('user_id', $user->id)
                ->with('photos')
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $attendances,
                'month' => $month,
                'year' => $year
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching attendance history',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company attendance (HR/Admin only)
     */
    public function companyAttendance(Request $request)
    {
        try {
            $user = $request->user();
            $userRole = $user->role->name ?? null;

            if (!in_array($userRole, ['Company Admin', 'HR'])) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'status' => false
                ], 403);
            }

            $validated = $request->validate([
                'date' => 'nullable|date',
                'status' => 'nullable|in:present,absent,half_day',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $query = Attendance::where('company_id', $user->company_id)
                ->with(['user', 'photos']);

            if (isset($validated['date'])) {
                $query->where('date', $validated['date']);
            } else {
                $query->where('date', Carbon::today());
            }

            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            $perPage = $validated['per_page'] ?? 15;
            $attendances = $query->orderBy('date', 'desc')->paginate($perPage);

            return response()->json([
                'status' => true,
                'data' => $attendances
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching company attendance',
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
