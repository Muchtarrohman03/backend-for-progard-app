<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Division;

class UserProfile extends Model
{
    protected $fillable = ['user_id', 'name', 'gender', 'division_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
