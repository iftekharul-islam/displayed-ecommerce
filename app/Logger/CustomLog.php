<?php

namespace App\Logger;

use Illuminate\Support\Facades\Log;



class CustomLog
{
    public static function emergency($message, array $context = [])
    {
        self::logMessage(__FUNCTION__, $message, $context);
    }

    public static function alert($message, array $context = [])
    {
        self::logMessage(__FUNCTION__, $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        self::logMessage(__FUNCTION__, $message, $context);
    }

    public static function error($message, array $context = [])
    {
        self::logMessage(__FUNCTION__, $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        self::logMessage(__FUNCTION__, $message, $context);
    }

    public static function notice($message, array $context = [])
    {
        self::logMessage(__FUNCTION__, $message, $context);
    }

    public static function info($message, array $context = [])
    {
        self::logMessage(__FUNCTION__, $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        self::logMessage(__FUNCTION__, $message, $context);
    }


    private static function logMessage($function, $message, array $context = [])
    {
        $logMessage = "$message\n*Data*\n";
        foreach ($context as $key => $value) {
            $logMessage .= "$key: $value\n";
        }

        try {
            if (config('app.env') === 'local') {
                Log::channel('slack_channel_error_log')->$function(
                    $logMessage
                );
            } else {
                Log::channel('slack_channel_error_log_in_production')->$function(
                    $logMessage
                );
            }
        } catch (\Throwable $th) {

            logExceptionInSlack($th);
        }
    }


    // Call custom log like this
    // CustomLog::warning($message, $context);

}
