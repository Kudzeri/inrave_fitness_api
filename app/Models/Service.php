<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['title', 'description', 'price', 'image'];

    public function trainers()
    {
        return $this->belongsToMany(Trainer::class);
    }

}
