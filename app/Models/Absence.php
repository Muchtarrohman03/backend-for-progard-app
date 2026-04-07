<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Absence extends Model
{
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->evidence
            ? asset('storage/' . $this->evidence)
            : null;
    }
    public function deleteEvidence(): void
    {
        if ($this->evidence && Storage::disk('public')->exists($this->evidence)) {
            Storage::disk('public')->delete($this->evidence);
        }
    }
    protected $fillable = [
        'reason',
        'start',
        'end',
        'description',
        'employee_id',
        'evidence',
        'status',
        'approved_by',
    ];

    protected static function booted()
    {
        static::updated(function (Absence $absence) {
            if ($absence->wasChanged('status')) {
                event(new \App\Events\AbsenceStatusUpdated($absence));
            }
        });

        static::created(function (Absence $absence) {
            event(new \App\Events\AbsenceCreated($absence));
        });
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
