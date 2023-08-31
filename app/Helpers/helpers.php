<?php

use Carbon\Carbon;
use App\Constants\ShortUrlConstant;
use Illuminate\Support\Facades\Log;

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


// Check if the user has a permission or abort
if (!function_exists('hasPermissionTo')) {
    /**
     * Check if the authenticated user has a specific permission.
     * If not, abort with a 403 error.
     *
     * @param  string  $permission
     * @return void
     */
    function hasPermissionTo(string $permission): void
    {
        if (!auth()->user()->hasPermissionTo($permission)) {
            abort(403, 'Access Denied – You don’t have permission to access');
        }
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

// get short url status
if (!function_exists('getShortUrlStatus')) {
    function getShortUrlStatus(int $status, $expiredAt): int
    {
        $currentDate = now()->format('Y-m-d');
        $expiredDate = Carbon::make($expiredAt)->format('Y-m-d');

        if ($expiredDate < $currentDate || $status === ShortUrlConstant::EXPIRED) {
            return ShortUrlConstant::EXPIRED;
        } elseif ($expiredDate > $currentDate && $status === ShortUrlConstant::VALID) {
            return ShortUrlConstant::VALID;
        } else {
            return ShortUrlConstant::INVALID;
        }
    }
}

// Get Slack Notification
if (!function_exists('logExceptionInSlack')) {

    function logExceptionInSlack(Throwable $exception)
    {

        $message = $exception->getMessage();
        $code = $exception->getCode();
        $trace = $exception->getTrace();
        $traceAsString = $exception->getTraceAsString();

        $callStack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $file = isset($callStack[0]['file']) ? $callStack[0]['file'] : 'N/A';
        $line = isset($callStack[0]['line']) ? $callStack[0]['line'] : 'N/A';

        foreach ($trace as $traceEntry) {
            if (isset($traceEntry['file']) && $traceEntry['file'] === $file) {
                $line = isset($traceEntry['line']) ? $traceEntry['line'] : $line;
                break;
            }
        }

        if (config('app.env') === 'local') {
            Log::channel('slack_channel_error_log')->error(
                "{$message}\n" .
                    "*Code*: {$code}\n" .
                    "*File*\n{$file}\n" .
                    "*Line*\n{$line}\n" .
                    "*Backtrace as String*: \n{$traceAsString}"
            );
        } else {
            Log::channel('slack_channel_error_log_in_production')->error(
                "{$message}\n" .
                    "*Code*: {$code}\n" .
                    "*File*\n{$file}\n" .
                    "*Line*\n{$line}\n" .
                    "*Backtrace as String*: \n{$traceAsString}"
            );
        }

    }
}
