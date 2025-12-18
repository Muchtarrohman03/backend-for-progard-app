<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\JobSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobSubmissionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JobSubmission');
    }

    public function view(AuthUser $authUser, JobSubmission $jobSubmission): bool
    {
        return $authUser->can('View:JobSubmission');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JobSubmission');
    }

    public function update(AuthUser $authUser, JobSubmission $jobSubmission): bool
    {
        return $authUser->can('Update:JobSubmission');
    }

    public function delete(AuthUser $authUser, JobSubmission $jobSubmission): bool
    {
        return $authUser->can('Delete:JobSubmission');
    }

    public function restore(AuthUser $authUser, JobSubmission $jobSubmission): bool
    {
        return $authUser->can('Restore:JobSubmission');
    }

    public function forceDelete(AuthUser $authUser, JobSubmission $jobSubmission): bool
    {
        return $authUser->can('ForceDelete:JobSubmission');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JobSubmission');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JobSubmission');
    }

    public function replicate(AuthUser $authUser, JobSubmission $jobSubmission): bool
    {
        return $authUser->can('Replicate:JobSubmission');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JobSubmission');
    }

}