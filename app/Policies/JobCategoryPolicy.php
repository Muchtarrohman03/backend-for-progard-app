<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JobCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JobCategory');
    }

    public function view(AuthUser $authUser, JobCategory $jobCategory): bool
    {
        return $authUser->can('View:JobCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JobCategory');
    }

    public function update(AuthUser $authUser, JobCategory $jobCategory): bool
    {
        return $authUser->can('Update:JobCategory');
    }

    public function delete(AuthUser $authUser, JobCategory $jobCategory): bool
    {
        return $authUser->can('Delete:JobCategory');
    }

    public function restore(AuthUser $authUser, JobCategory $jobCategory): bool
    {
        return $authUser->can('Restore:JobCategory');
    }

    public function forceDelete(AuthUser $authUser, JobCategory $jobCategory): bool
    {
        return $authUser->can('ForceDelete:JobCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JobCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JobCategory');
    }

    public function replicate(AuthUser $authUser, JobCategory $jobCategory): bool
    {
        return $authUser->can('Replicate:JobCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JobCategory');
    }

}