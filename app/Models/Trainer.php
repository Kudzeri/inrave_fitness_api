<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    protected $fillable = ['first_name', 'last_name', 'description', 'photo'];

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

}
