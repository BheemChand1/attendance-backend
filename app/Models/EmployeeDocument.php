<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'mime_type',
        'description',
    ];

    // Relationship
    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
