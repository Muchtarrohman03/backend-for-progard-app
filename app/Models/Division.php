<?php

namespace App\Models;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = ['name'];
    public function profiles()
    {
        return $this->hasMany(UserProfile::class);
    }
}
