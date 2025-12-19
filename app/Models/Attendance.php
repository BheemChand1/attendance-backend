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
}
