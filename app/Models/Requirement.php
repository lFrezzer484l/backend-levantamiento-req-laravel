<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $fillable = [
        'requester',
        'email',
        'description',
        'requirement_type',
        'priority',
        'titulo',
    ];
}
