<?php

namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class TrainingRequest extends Model
{
    protected $fillable = ['name', 'phone', 'message', 'consent'];
}
