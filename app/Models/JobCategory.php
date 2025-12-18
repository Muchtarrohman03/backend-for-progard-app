<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
    public function submissions()
    {
        return $this->hasMany(JobSubmission::class, 'category_id');
    }
    public function overtimes()
    {
        return $this->hasMany(Overtime::class, 'category_id');
    }
}
