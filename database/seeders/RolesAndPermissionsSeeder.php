<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;
use App\Constants\RolesConstant;
use Spatie\Permission\Models\Role;
use App\Constants\PermissionConstant;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'Dashboard' => [
                [
                    'name' => PermissionConstant::DASHBOARD_ACCESS['name'],
                    'label' => PermissionConstant::DASHBOARD_ACCESS['label'],
                    'code' => PermissionConstant::DASHBOARD_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
            ],

            'Roles' => [
                [
                    'name' => PermissionConstant::ROLES_ACCESS['name'],
                    'label' => PermissionConstant::ROLES_ACCESS['label'],
                    'code' => PermissionConstant::ROLES_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::ROLES_CREATE['name'],
                    'label' => PermissionConstant::ROLES_CREATE['label'],
                    'code' => PermissionConstant::ROLES_CREATE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['create'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::ROLES_EDIT['name'],
                    'label' => PermissionConstant::ROLES_EDIT['label'],
                    'code' => PermissionConstant::ROLES_EDIT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['edit'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::ROLES_DELETE['name'],
                    'label' => PermissionConstant::ROLES_DELETE['label'],
                    'code' => PermissionConstant::ROLES_DELETE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['delete'],
                    'guard_name' => 'api'
                ],
            ],

            'Permissions' => [
                [
                    'name' => PermissionConstant::PERMISSIONS_ACCESS['name'],
                    'label' => PermissionConstant::PERMISSIONS_ACCESS['label'],
                    'code' => PermissionConstant::PERMISSIONS_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::PERMISSIONS_EDIT['name'],
                    'label' => PermissionConstant::PERMISSIONS_EDIT['label'],
                    'code' => PermissionConstant::PERMISSIONS_EDIT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['edit'],
                    'guard_name' => 'api'
                ],
            ],

            'Users' => [
                [
                    'name' => PermissionConstant::USERS_ACCESS['name'],
                    'label' => PermissionConstant::USERS_ACCESS['label'],
                    'code' => PermissionConstant::USERS_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::USERS_CREATE['name'],
                    'label' => PermissionConstant::USERS_CREATE['label'],
                    'code' => PermissionConstant::USERS_CREATE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['create'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::USERS_EDIT['name'],
                    'label' => PermissionConstant::USERS_EDIT['label'],
                    'code' => PermissionConstant::USERS_EDIT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['edit'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::USERS_DELETE['name'],
                    'label' => PermissionConstant::USERS_DELETE['label'],
                    'code' => PermissionConstant::USERS_DELETE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['delete'],
                    'guard_name' => 'api'
                ]
            ],

            'Users Profile' => [
                [
                    'name' => PermissionConstant::USERS_PROFILE_ACCESS['name'],
                    'label' => PermissionConstant::USERS_PROFILE_ACCESS['label'],
                    'code' => PermissionConstant::USERS_PROFILE_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::USERS_PROFILE_EDIT['name'],
                    'label' => PermissionConstant::USERS_PROFILE_EDIT['label'],
                    'code' => PermissionConstant::USERS_PROFILE_EDIT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['edit'],
                    'guard_name' => 'api'
                ],
            ],

            'Campaigns' => [
                [
                    'name' => PermissionConstant::CAMPAIGNS_ACCESS['name'],
                    'label' => PermissionConstant::CAMPAIGNS_ACCESS['label'],
                    'code' => PermissionConstant::CAMPAIGNS_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::CAMPAIGNS_CREATE['name'],
                    'label' => PermissionConstant::CAMPAIGNS_CREATE['label'],
                    'code' => PermissionConstant::CAMPAIGNS_CREATE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['create'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::CAMPAIGNS_EDIT['name'],
                    'label' => PermissionConstant::CAMPAIGNS_EDIT['label'],
                    'code' => PermissionConstant::CAMPAIGNS_EDIT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['edit'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::CAMPAIGNS_DELETE['name'],
                    'label' => PermissionConstant::CAMPAIGNS_DELETE['label'],
                    'code' => PermissionConstant::CAMPAIGNS_DELETE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['delete'],
                    'guard_name' => 'api'
                ]
            ],

            'TLD' => [
                [
                    'name' => PermissionConstant::TLDS_ACCESS['name'],
                    'label' => PermissionConstant::TLDS_ACCESS['label'],
                    'code' => PermissionConstant::TLDS_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::TLDS_CREATE['name'],
                    'label' => PermissionConstant::TLDS_CREATE['label'],
                    'code' => PermissionConstant::TLDS_CREATE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['create'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::TLDS_EDIT['name'],
                    'label' => PermissionConstant::TLDS_EDIT['label'],
                    'code' => PermissionConstant::TLDS_EDIT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['edit'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::TLDS_DELETE['name'],
                    'label' => PermissionConstant::TLDS_DELETE['label'],
                    'code' => PermissionConstant::TLDS_DELETE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['delete'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::TLDS_IMPORT['name'],
                    'label' => PermissionConstant::TLDS_IMPORT['label'],
                    'code' => PermissionConstant::TLDS_IMPORT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['others'],
                    'guard_name' => 'api'
                ]
            ],

            'Short Urls' => [
                [
                    'name' => PermissionConstant::SHORT_URLS_ACCESS['name'],
                    'label' => PermissionConstant::SHORT_URLS_ACCESS['label'],
                    'code' => PermissionConstant::SHORT_URLS_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::SHORT_URLS_CREATE['name'],
                    'label' => PermissionConstant::SHORT_URLS_CREATE['label'],
                    'code' => PermissionConstant::SHORT_URLS_CREATE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['create'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::SHORT_URLS_EDIT['name'],
                    'label' => PermissionConstant::SHORT_URLS_EDIT['label'],
                    'code' => PermissionConstant::SHORT_URLS_EDIT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['edit'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::SHORT_URLS_DELETE['name'],
                    'label' => PermissionConstant::SHORT_URLS_DELETE['label'],
                    'code' => PermissionConstant::SHORT_URLS_DELETE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['delete'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::SHORT_URLS_IMPORT['name'],
                    'label' => PermissionConstant::SHORT_URLS_IMPORT['label'],
                    'code' => PermissionConstant::SHORT_URLS_IMPORT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['others'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::SHORT_URLS_EXPORT['name'],
                    'label' => PermissionConstant::SHORT_URLS_EXPORT['label'],
                    'code' => PermissionConstant::SHORT_URLS_EXPORT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['others'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::SHORT_URLS_LATEST_DOMAINS_EXPORT['name'],
                    'label' => PermissionConstant::SHORT_URLS_LATEST_DOMAINS_EXPORT['label'],
                    'code' => PermissionConstant::SHORT_URLS_LATEST_DOMAINS_EXPORT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['others'],
                    'guard_name' => 'api'
                ]
            ],

            'Excluded Domains' => [
                [
                    'name' => PermissionConstant::EXCLUDED_DOMAIN_ACCESS['name'],
                    'label' => PermissionConstant::EXCLUDED_DOMAIN_ACCESS['label'],
                    'code' => PermissionConstant::EXCLUDED_DOMAIN_ACCESS['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['access'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::EXCLUDED_DOMAIN_CREATE['name'],
                    'label' => PermissionConstant::EXCLUDED_DOMAIN_CREATE['label'],
                    'code' => PermissionConstant::EXCLUDED_DOMAIN_CREATE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['create'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::EXCLUDED_DOMAIN_EDIT['name'],
                    'label' => PermissionConstant::EXCLUDED_DOMAIN_EDIT['label'],
                    'code' => PermissionConstant::EXCLUDED_DOMAIN_EDIT['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['edit'],
                    'guard_name' => 'api'
                ],
                [
                    'name' => PermissionConstant::EXCLUDED_DOMAIN_DELETE['name'],
                    'label' => PermissionConstant::EXCLUDED_DOMAIN_DELETE['label'],
                    'code' => PermissionConstant::EXCLUDED_DOMAIN_DELETE['code'],
                    'group' => PermissionConstant::PERMISSION_GROUP['delete'],
                    'guard_name' => 'api'
                ]
            ],

        ];

        foreach ($modules as $key => $permissions) {
            $module = Module::updateOrCreate(
                ['name' => $key],
                ['name' => $key]
            );

            foreach ($permissions as $permission) {
                Permission::updateOrCreate([
                    'name' => $permission['name'],
                ], [
                    'name' => $permission['name'],
                    'label' => $permission['label'],
                    'group' => $permission['group'],
                    'code' => $permission['code'],
                    'guard_name' => $permission['guard_name'],
                    'module_id' => $module->id
                ]);
            }
        }

        // roles
        $roles = [
            ['name' => RolesConstant::EMPLOYEE, 'guard_name' => 'api'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate([
                'name' => $role['name'],
            ], [
                'name' => $role['name'],
                'guard_name' => $role['guard_name']
            ]);
        }


        $permissions = Permission::all();
        $role = Role::updateOrCreate(
            ['name' => RolesConstant::ADMIN],
            [
                'name' => RolesConstant::ADMIN,
                'guard_name' => 'api'
            ]
        );
        $role->givePermissionTo($permissions);
    }
}
