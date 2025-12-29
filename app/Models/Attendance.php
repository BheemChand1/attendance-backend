<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function photos()
    {
        return $this->hasMany(AttendancePhoto::class);
    }

    public function checkInPhoto()
    {
        return $this->hasOne(AttendancePhoto::class)->where('type', 'check_in');
    }

    public function checkOutPhoto()
    {
        return $this->hasOne(AttendancePhoto::class)->where('type', 'check_out');
    }
}
