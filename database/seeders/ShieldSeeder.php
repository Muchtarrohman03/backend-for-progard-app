<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | ADMIN ROLE CONFIG
        |--------------------------------------------------------------------------
        | Admin:
        | - CRUD Absence, JobCategory, JobSubmission, Overtime
        | - TIDAK boleh akses User
        |
        */

        $adminPermissions = [

            // ================== Absence ==================
            "ViewAny:Absence",
            "View:Absence",
            "Create:Absence",
            "Update:Absence",
            "Delete:Absence",
            "Restore:Absence",
            "RestoreAny:Absence",
            "ForceDelete:Absence",
            "ForceDeleteAny:Absence",
            "Replicate:Absence",
            "Reorder:Absence",

            // ================== JobCategory ==================
            "ViewAny:JobCategory",
            "View:JobCategory",
            "Create:JobCategory",
            "Update:JobCategory",
            "Delete:JobCategory",
            "Restore:JobCategory",
            "RestoreAny:JobCategory",
            "ForceDelete:JobCategory",
            "ForceDeleteAny:JobCategory",
            "Replicate:JobCategory",
            "Reorder:JobCategory",

            // ================== JobSubmission ==================
            "ViewAny:JobSubmission",
            "View:JobSubmission",
            "Create:JobSubmission",
            "Update:JobSubmission",
            "Delete:JobSubmission",
            "Restore:JobSubmission",
            "RestoreAny:JobSubmission",
            "ForceDelete:JobSubmission",
            "ForceDeleteAny:JobSubmission",
            "Replicate:JobSubmission",
            "Reorder:JobSubmission",

            // ================== Overtime ==================
            "ViewAny:Overtime",
            "View:Overtime",
            "Create:Overtime",
            "Update:Overtime",
            "Delete:Overtime",
            "Restore:Overtime",
            "RestoreAny:Overtime",
            "ForceDelete:Overtime",
            "ForceDeleteAny:Overtime",
            "Replicate:Overtime",
            "Reorder:Overtime",

            // ================== Dashboard Widgets ==================
            "View:StatsOverview",
            "View:WidgetAbsenceChart",
            "View:WidgetJobSubmissionsChart",
            "View:WidgetOvertimeChart",
        ];

        /*
        |--------------------------------------------------------------------------
        | ROLES DEFINITION
        |--------------------------------------------------------------------------
        */

        $rolesWithPermissions = json_encode([
            [
                'name' => 'admin',
                'guard_name' => 'web',
                'permissions' => $adminPermissions,
            ],
            [
                'name' => 'superadmin',
                'guard_name' => 'web',
                'permissions' => [], // FULL ACCESS via Gate::before()
            ],
        ]);

        // No direct permissions
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeder for admin & superadmin completed.');
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

                if (! empty($rolePlusPermission['permissions'])) {
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

    protected static function makeDirectPermissions(string $directPermissions): void
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
