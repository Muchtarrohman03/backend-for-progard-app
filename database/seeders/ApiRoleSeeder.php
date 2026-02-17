<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ApiRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions for API
        $permissions = [

            // JobSubmission
            'api.view.job_submission',
            'api.create.job_submission',
            'api.update.job_submission',

            // Absence
            'api.view.absence',
            'api.create.absence',
            'api.update.absence',

            // Overtime
            'api.view.overtime',
            'api.create.overtime',
            'api.update.overtime',

            // My own data
            'api.view.my_submissions',
            'api.view.my_absences',
            'api.view.my_overtimes',

            //job categories
            'api.view.job_categories',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'api',
            ]);
        }

        // Roles
        $gardener = Role::firstOrCreate(['name' => 'gardener', 'guard_name' => 'api']);
        $supervisor = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'api']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'api']);
        $site_manager = Role::firstOrCreate(['name' => 'site_manager', 'guard_name' => 'api']);

        // Gardener permissions
        $gardener->syncPermissions([
            'api.create.job_submission',
            'api.view.job_categories',
            'api.create.absence',
            'api.create.overtime',
            'api.view.my_submissions',
            'api.view.my_absences',
            'api.view.my_overtimes',
        ]);

        // Supervisor permissions
        $supervisor->syncPermissions([
            'api.view.job_submission',
            'api.update.job_submission',
            'api.view.job_categories',

            'api.view.absence',
            'api.update.absence',

            'api.view.overtime',
            'api.update.overtime',

            'api.view.my_submissions',
            'api.view.my_absences',
            'api.view.my_overtimes',
        ]);

        // Staff permissions
        $staff->syncPermissions([
            'api.view.job_submission',
            'api.create.job_submission',
            'api.view.job_categories',

            'api.view.absence',
            'api.create.absence',

            'api.view.overtime',
            'api.create.overtime',

            'api.view.my_submissions',
            'api.view.my_absences',
            'api.view.my_overtimes',
        ]);
        // Site Manager permissions
        $site_manager->syncPermissions([
            'api.view.job_submission',
            'api.update.job_submission',
            'api.view.absence',
            'api.update.absence',
            'api.view.overtime',
            'api.update.overtime',
            'api.view.my_submissions',
            'api.view.my_absences',
            'api.view.my_overtimes',
        ]);
    }
}
