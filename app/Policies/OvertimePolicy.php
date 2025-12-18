<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Overtime;
use Illuminate\Auth\Access\HandlesAuthorization;

class OvertimePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Overtime');
    }

    public function view(AuthUser $authUser, Overtime $overtime): bool
    {
        return $authUser->can('View:Overtime');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Overtime');
    }

    public function update(AuthUser $authUser, Overtime $overtime): bool
    {
        return $authUser->can('Update:Overtime');
    }

    public function delete(AuthUser $authUser, Overtime $overtime): bool
    {
        return $authUser->can('Delete:Overtime');
    }

    public function restore(AuthUser $authUser, Overtime $overtime): bool
    {
        return $authUser->can('Restore:Overtime');
    }

    public function forceDelete(AuthUser $authUser, Overtime $overtime): bool
    {
        return $authUser->can('ForceDelete:Overtime');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Overtime');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Overtime');
    }

    public function replicate(AuthUser $authUser, Overtime $overtime): bool
    {
        return $authUser->can('Replicate:Overtime');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Overtime');
    }

}