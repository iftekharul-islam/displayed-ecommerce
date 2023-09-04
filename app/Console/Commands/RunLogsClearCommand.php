<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RunLogsClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-logs-clear-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = storage_path('logs');

        // Check if the logs directory exists
        if (File::isDirectory($logPath)) {
            // Get all log files in the directory
            $logFiles = File::files($logPath);

            // Delete each log file
            foreach ($logFiles as $file) {
                File::delete($file);
            }

            info('Logs cleared!');
        } else {
            info('Logs directory not found.');
        }
    }
}
