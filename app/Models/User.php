<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method \Illuminate\Support\Collection getRoleNames()
 * @method bool hasRole(string|array $roles)
 * @method bool assignRole(string|array $roles)
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasDatabaseNotifications, HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'division',
        'gender',
        'fcm_token',
    ];


    protected $guard_name = ['api', 'web'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function jobSubmissions()
    {
        return $this->hasMany(JobSubmission::class, 'employee_id');
    }
    public function absences()
    {
        return $this->hasMany(Absence::class, 'employee_id');
    }
    public function overtimes()
    {
        return $this->hasMany(Overtime::class, 'employee_id');
    }
    public function approvedSubmissions()
    {
        return $this->hasMany(JobSubmission::class, 'approved_by');
    }
    public function approvedOvertimes()
    {
        return $this->hasMany(Overtime::class, 'approved_by');
    }
    public function approvedAbsences()
    {
        return $this->hasMany(Absence::class, 'approved_by');
    }
    public function positions()
    {
        return $this->hasMany(Position::class, 'employee_id');
    }
}
