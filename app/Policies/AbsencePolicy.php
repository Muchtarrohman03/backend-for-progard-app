<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Absence;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsencePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Absence');
    }

    public function view(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('View:Absence');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Absence');
    }

    public function update(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('Update:Absence');
    }

    public function delete(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('Delete:Absence');
    }

    public function restore(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('Restore:Absence');
    }

    public function forceDelete(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('ForceDelete:Absence');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Absence');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Absence');
    }

    public function replicate(AuthUser $authUser, Absence $absence): bool
    {
        return $authUser->can('Replicate:Absence');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Absence');
    }

}