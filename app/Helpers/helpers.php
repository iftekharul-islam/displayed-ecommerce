<?php

if (!function_exists('to_boolean')) {

    /**
     * Convert to boolean
     *
     * @param $booleable
     * @return boolean
     */
    function to_boolean($booleable): bool
    {
        return filter_var($booleable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}

if (!function_exists('hasPermissionTo')) {

    /**
     * Convert to boolean
     *
     * @param $booleable
     * @return boolean
     */
    function hasPermissionTo(string $permission): bool
    {
        if (auth()->user()->hasPermissionTo($permission)) {
            return true;
        }

        abort(403, 'Access Denied – You don’t have permission to access');
    }
}
