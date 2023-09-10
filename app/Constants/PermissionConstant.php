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
    public const USERS_SOFT_DELETE_ACCESS = [
        'name' => 'users_soft_delete_access',
        'label' => 'SoftDelete',
        'code' => 3005,
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
    public const CAMPAIGNS_SOFT_DELETE_ACCESS = [
        'name' => 'campaigns_soft_delete_access',
        'label' => 'SoftDelete',
        'code' => 5005,
    ];

    // TLD
    public const TLDS_ACCESS = [
        'name' => 'tlds_access',
        'label' => 'Access',
        'code' => 6000,
    ];
    public const TLDS_CREATE = [
        'name' => 'tlds_create',
        'label' => 'Create',
        'code' => 6001,
    ];
    public const TLDS_EDIT = [
        'name' => 'tlds_edit',
        'label' => 'Edit',
        'code' => 6003,
    ];
    public const TLDS_DELETE = [
        'name' => 'tlds_delete',
        'label' => 'Delete',
        'code' => 6004,
    ];
    public const TLDS_IMPORT = [
        'name' => 'tlds_import',
        'label' => 'Import',
        'code' => 6005,
    ];

    // Short Urls
    public const SHORT_URLS_ACCESS = [
        'name' => 'short_urls_access',
        'label' => 'Access',
        'code' => 7000,
    ];
    public const SHORT_URLS_CREATE = [
        'name' => 'short_urls_create',
        'label' => 'Create',
        'code' => 7001,
    ];
    public const SHORT_URLS_EDIT = [
        'name' => 'short_urls_edit',
        'label' => 'Edit',
        'code' => 7003,
    ];
    public const SHORT_URLS_DELETE = [
        'name' => 'short_urls_delete',
        'label' => 'Delete',
        'code' => 7004,
    ];
    public const SHORT_URLS_IMPORT = [
        'name' => 'short_urls_import',
        'label' => 'Import',
        'code' => 7005,
    ];
    public const SHORT_URLS_EXPORT = [
        'name' => 'short_urls_export',
        'label' => 'Export',
        'code' => 7006,
    ];
    public const SHORT_URLS_LATEST_DOMAINS_EXPORT = [
        'name' => 'short_urls_latest_domains_export',
        'label' => 'Latest Domains Export',
        'code' => 7007,
    ];
    public const SHORT_URLS_TLD_UPDATE = [
        'name' => 'short_urls_tld_update',
        'label' => 'Tld Update',
        'code' => 7008,
    ];
    public const SHORT_URLS_ORIGINAL_DOMAINS_SHOW = [
        'name' => 'short_urls_original_domains_show',
        'label' => 'Original Domains Show',
        'code' => 7009,
    ];
    public const SHORT_URLS_VERIFY_VALID_DOMAINS = [
        'name' => 'short_urls_verify_valid_domains',
        'label' => 'Verify Valid Domains',
        'code' => 70010,
    ];
    public const SHORT_URLS_VERIFY_INVALID_DOMAINS = [
        'name' => 'short_urls_verify_invalid_domains',
        'label' => 'Verify Invalid Domains',
        'code' => 70011,
    ];

    // Excluded Domain
    public const EXCLUDED_DOMAIN_ACCESS = [
        'name' => 'excluded_domains_access',
        'label' => 'Access',
        'code' => 8000,
    ];
    public const EXCLUDED_DOMAIN_CREATE = [
        'name' => 'excluded_domains_create',
        'label' => 'Create',
        'code' => 8001,
    ];
    public const EXCLUDED_DOMAIN_EDIT = [
        'name' => 'excluded_domains_edit',
        'label' => 'Edit',
        'code' => 8003,
    ];
    public const EXCLUDED_DOMAIN_DELETE = [
        'name' => 'excluded_domains_delete',
        'label' => 'Delete',
        'code' => 8004,
    ];

    // Report Download
    public const REPORT_DOWNLOAD_ACCESS = [
        'name' => 'report_download_access',
        'label' => 'Access',
        'code' => 9000,
    ];
}
