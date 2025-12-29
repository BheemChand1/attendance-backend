<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'employee_code',
        'date_of_birth',
        'gender',
        'employee_photo',
        'street_address',
        'city',
        'state',
        'zip_code',
        'country',
        'department',
        'position',
        'salary',
        'joining_date',
        'status',
        'qualification',
        'specialization',
        'university',
        'graduation_year',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'salary' => 'decimal:2',
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

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }
}
