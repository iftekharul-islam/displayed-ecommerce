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

// remove http or https from url
if (!function_exists('removeHttpOrHttps')) {

    function removeHttpOrHttps(string $url): string
    {
        return rtrim(str_replace(['http://', 'https://'], '', $url), '/');
    }
}

// extract Tld from domain
if (!function_exists('extractTldFromDomain')) {

    function extractTldFromDomain(?string $domain): ?string
    {
        if (!$domain) {
            return null;
        }

        $domain = removeHttpOrHttps($domain);
        $domain = preg_replace('/^www\./', '', $domain);

        $domainParts = explode('.', $domain);
        $tld = end($domainParts);
        $tldWithDot = '.' . $tld;

        return $tldWithDot;
    }
}

// get Code From Url
if (!function_exists('getCodeFromUrl')) {

    function getCodeFromUrl(?string $input): ?string
    {
        if (!$input) {
            return null;
        }

        $parsed_url = parse_url($input);

        if (isset($parsed_url['host'])) {
            $path = $parsed_url['path'];
            $code = substr($path, strrpos($path, '/') + 1);
        } else {
            $code = $input;
        }

        return $code;
    }
}

// get price without $ sign
if (!function_exists('getPriceWithoutDollarSign')) {

    function getPriceWithoutDollarSign(?string $price): ?string
    {
        if (!$price) {
            return null;
        }

        return floatval(preg_replace('/[^0-9.]/', '', $price));
    }
}
