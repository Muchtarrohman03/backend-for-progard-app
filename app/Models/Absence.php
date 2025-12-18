<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->evidence
            ? asset('storage/' . $this->evidence)
            : null;
    }
    protected $fillable = [
        'reason',
        'start',
        'end',
        'description',
        'employee_id',
        'evidence',
        'status',
    ];
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
