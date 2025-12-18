<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | ADMIN ROLE CONFIG
        |--------------------------------------------------------------------------
        |
        | Admin: CRUD untuk JobCategory, JobSubmission, Absence, Overtime.
        |        Tidak boleh akses User.
        |
        */

        $adminPermissions = [
            // Absence
            "ViewAny:Absence",
            "View:Absence",
            "Create:Absence",
            "Update:Absence",
            "Delete:Absence",
            "Restore:Absence",
            "ForceDelete:Absence",
            "ForceDeleteAny:Absence",
            "RestoreAny:Absence",
            "Replicate:Absence",
            "Reorder:Absence",

            // JobCategory
            "ViewAny:JobCategory",
            "View:JobCategory",
            "Create:JobCategory",
            "Update:JobCategory",
            "Delete:JobCategory",
            "Restore:JobCategory",
            "ForceDelete:JobCategory",
            "ForceDeleteAny:JobCategory",
            "RestoreAny:JobCategory",
            "Replicate:JobCategory",
            "Reorder:JobCategory",

            // JobSubmission
            "ViewAny:JobSubmission",
            "View:JobSubmission",
            "Create:JobSubmission",
            "Update:JobSubmission",
            "Delete:JobSubmission",
            "Restore:JobSubmission",
            "ForceDelete:JobSubmission",
            "ForceDeleteAny:JobSubmission",
            "RestoreAny:JobSubmission",
            "Replicate:JobSubmission",
            "Reorder:JobSubmission",

            // Overtime
            "ViewAny:Overtime",
            "View:Overtime",
            "Create:Overtime",
            "Update:Overtime",
            "Delete:Overtime",
            "Restore:Overtime",
            "ForceDelete:Overtime",
            "ForceDeleteAny:Overtime",
            "RestoreAny:Overtime",
            "Replicate:Overtime",
            "Reorder:Overtime",

            // Dashboard Widgets
            "View:StatsOverview",
            "View:WidgetAbsenceChart",
            "View:WidgetJobSubmissionsChart",
            "View:WidgetOvertimeChart",
        ];


        /*
        |--------------------------------------------------------------------------
        | MANAGER ROLE CONFIG
        |--------------------------------------------------------------------------
        |
        | Manager:
        | - VIEW ONLY untuk Absence, JobCategory, JobSubmission, Overtime
        | - CRUD untuk User
        |
        */

        $managerPermissions = [

            // ==== VIEW-ONLY of 4 resources ====
            // Absence
            "ViewAny:Absence",
            "View:Absence",

            // JobCategory
            "ViewAny:JobCategory",
            "View:JobCategory",

            // JobSubmission
            "ViewAny:JobSubmission",
            "View:JobSubmission",

            // Overtime
            "ViewAny:Overtime",
            "View:Overtime",

            // Dashboard Widgets
            "View:StatsOverview",
            "View:WidgetAbsenceChart",
            "View:WidgetJobSubmissionsChart",
            "View:WidgetOvertimeChart",

            // ==== FULL CRUD USER ====
            "ViewAny:User",
            "View:User",
            "Create:User",
            "Update:User",
            "Delete:User",
            "Restore:User",
            "ForceDelete:User",
            "ForceDeleteAny:User",
            "RestoreAny:User",
            "Replicate:User",
            "Reorder:User",
        ];


        $rolesWithPermissions = json_encode([
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'permissions' => $adminPermissions,
            ],
            [
                'name' => 'manager',
                'guard_name' => 'web',
                'permissions' => $managerPermissions,
            ]
        ]);

        // no direct permissions
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeder for admin + manager completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            $roleModel = Utils::getRoleModel();
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
