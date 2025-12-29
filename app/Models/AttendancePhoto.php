<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePhoto extends Model
{
    const UPDATED_AT = null; // No updated_at column

    protected $fillable = [
        'attendance_id',
        'type',
        'photo_path',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Relationships
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
