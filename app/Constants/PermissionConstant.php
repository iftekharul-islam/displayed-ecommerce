<?php

namespace App\Constants;

class PermissionConstant
{
    public const PERMISSION_GROUP = [
        'access' => 'access',
        'create' => 'create',
        'view' => 'view',
        'edit' => 'edit',
        'delete' => 'delete',
        'others' => 'others',
    ];

    // Dashboard
    public const DASHBOARD_ACCESS = [
        'name' => 'dashboard_access',
        'label' => 'Access',
        'code' => 10,
    ];

    // Roles
    public const ROLES_ACCESS = [
        'name' => 'roles_access',
        'label' => 'Access',
        'code' => 1000,
    ];
    public const ROLES_CREATE = [
        'name' => 'roles_create',
        'label' => 'Create',
        'code' => 1001,
    ];
    public const ROLES_EDIT = [
        'name' => 'roles_edit',
        'label' => 'Edit',
        'code' => 1003,
    ];
    public const ROLES_DELETE = [
        'name' => 'Roles Delete',
        'label' => 'roles_delete',
        'code' => 1004,
    ];

    // permissions
    public const PERMISSIONS_ACCESS = [
        'name' => 'permissions_access',
        'label' => 'Access',
        'code' => 2000,
    ];
    public const PERMISSIONS_EDIT = [
        'name' => 'permissions_edit',
        'label' => 'Edit',
        'code' => 2003,
    ];

    // Users
    public const USERS_ACCESS = [
        'name' => 'users_access',
        'label' => 'Access',
        'code' => 3000,
    ];
    public const USERS_CREATE = [
        'name' => 'users_create',
        'label' => 'Create',
        'code' => 3001,
    ];
    public const USERS_EDIT = [
        'name' => 'users_edit',
        'label' => 'Edit',
        'code' => 3003,
    ];
    public const USERS_DELETE = [
        'name' => 'users_delete',
        'label' => 'Delete',
        'code' => 3004,
    ];
    public const USERS_PROFILE_ACCESS = [
        'name' => 'users_profile_access',
        'label' => 'Access',
        'code' => 4001,
    ];
    public const USERS_PROFILE_EDIT = [
        'name' => 'users_profile_edit',
        'label' => 'Edit',
        'code' => 4002,
    ];

    // Campaigns
    public const CAMPAIGNS_ACCESS = [
        'name' => 'campaigns_access',
        'label' => 'Access',
        'code' => 5000,
    ];
    public const CAMPAIGNS_CREATE = [
        'name' => 'campaigns_create',
        'label' => 'Create',
        'code' => 5001,
    ];
    public const CAMPAIGNS_EDIT = [
        'name' => 'campaigns_edit',
        'label' => 'Edit',
        'code' => 5003,
    ];
    public const CAMPAIGNS_DELETE = [
        'name' => 'campaigns_delete',
        'label' => 'Delete',
        'code' => 5004,
    ];

    // TLD
    public const TLD_ACCESS = [
        'name' => 'tld_access',
        'label' => 'Access',
        'code' => 6000,
    ];
    public const TLD_CREATE = [
        'name' => 'tld_create',
        'label' => 'Create',
        'code' => 6001,
    ];
    public const TLD_EDIT = [
        'name' => 'tld_edit',
        'label' => 'Edit',
        'code' => 6003,
    ];
    public const TLD_DELETE = [
        'name' => 'tld_delete',
        'label' => 'Delete',
        'code' => 6004,
    ];
}
