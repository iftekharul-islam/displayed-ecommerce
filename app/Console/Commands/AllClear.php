<?php

namespace App\Console\Commands;

use App\Traits\UpdateTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AllClear extends Command
{
    use UpdateTrait;

    protected $signature = 'all:clear';

    protected $description = 'All Data Cleared';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        Artisan::call('optimize:clear');
        cache()->flush();

        $this->delete_directory(base_path('bootstrap/cache/'),false);
        $this->delete_directory(base_path('storage/app/import/'),false);
        $this->delete_directory(base_path('storage/debugbar/'),false);
        $this->delete_directory(base_path('storage/framework/cache/data/'),false);
        $this->delete_directory(base_path('storage/framework/cache/laravel-excel/'),false);
        $this->delete_directory(base_path('storage/framework/views/'),false);
//        $this->delete_directory(base_path('storage/framework/sessions/'),false);
        $this->delete_directory(base_path('storage/logs/'),false);
//        $this->remove_from_file();

        return 0;
    }

    public function remove_from_file()
    {
        //Listing all the files which are under addons directory of app/Http/Controllers/Admin/Addons
        $files = [
            "app/Http/Controllers/Admin/Addons/AffiliateController.php",
            "app/Http/Controllers/Admin/Addons/AIWriterController.php",
            "app/Http/Controllers/Admin/Addons/IshopetController.php",
            'app/Http/Controllers/Admin/Addons/OfflineMethodController.php',
            'app/Http/Controllers/Admin/Addons/OfflineRechargeController.php',
            'app/Http/Controllers/Admin/Addons/OtpController.php',
            'app/Http/Controllers/Admin/Addons/PosSystemController.php',
            'app/Http/Controllers/Admin/Addons/RamdhaniController.php',
            'app/Http/Controllers/Admin/Addons/RefundController.php',
            'app/Http/Controllers/Admin/Addons/RewardSystemController.php',
            'app/Http/Controllers/Admin/Addons/SellerSubscriptionController.php',
            'app/Http/Controllers/Admin/Addons/ShippingClassController.php',
            'app/Http/Controllers/Admin/Addons/VideoShoppingController.php',
            'app/Http/Controllers/Admin/Addons/WholeSaleProductController.php',
            'app/Http/Controllers/Admin/Addons/PackageController.php',
            'app/Repositories/Admin/Addon/AffiliateRepository.php',
            'app/Repositories/Admin/Addon/ChatSystemRepository.php',
            'app/Repositories/Admin/Addon/OfflineMethodRepository.php',
            'app/Repositories/Admin/Addon/OtpSystemRepository.php',
            'app/Repositories/Admin/Addon/PackageRepository.php',
            'app/Repositories/Admin/Addon/PosSystemRepository.php',
            'app/Repositories/Admin/Addon/SellerSubscriptionRepository.php',
            'app/Repositories/Admin/Addon/ShippingClassRepository.php',
            'app/Repositories/Admin/Addon/VideoShoppingRepository.php',
            'app/Repositories/Interfaces/Admin/Addon/AffiliateInterface.php',
            'app/Repositories/Interfaces/Admin/Addon/OfflineMethodInterface.php',
            'app/Repositories/Interfaces/Admin/Addon/PackageInterface.php',
            'app/Repositories/Interfaces/Admin/Addon/PosSystemInterface.php',
            'app/Repositories/Interfaces/Admin/Addon/SellerSubscriptionInterface.php',
            'app/Repositories/Interfaces/Admin/Addon/VideoShoppingInterface.php',
            'app/Http/Controllers/Seller/Addons/ChatSystemController.php',
            'app/Http/Controllers/Seller/Addons/PackageController.php',
            'app/Http/Controllers/Seller/Addons/RefundController.php',
            'app/Http/Controllers/Seller/Addons/VideoShoppingController.php',
            'app/Http/Controllers/Seller/Addons/WholeSaleProductController.php',
        ];
        foreach ($files as $file) {
            $file_class_name = explode('/', $file);
            $file_class_name = end($file_class_name);
            $file_class_name = explode('.', $file_class_name);
            $file_class_name = $file_class_name[0];
            $searchText = "class $file_class_name";

// Read the file into an array of lines
            $lines = file($file);

// Initialize a variable to store the line number of the first match
            $firstMatchingLineNumber = null;

// Iterate through each line
            foreach ($lines as $lineNumber => $line) {
                // Search for the text in the current line
                if (preg_match("/$searchText/", $line)) {
                    // Store the line number of the first match and break the loop
                    $firstMatchingLineNumber = $lineNumber + 1; // Add 1 because line numbers are 1-based
                }
            }
// Define the replacement text
            $replacementText = "{}";

// Read the entire file into a string
            $fileContent = file_get_contents($file);

// Split the file content into an array of lines
            $lines = explode("\n", $fileContent);

// Check if the specified line number is valid
            if ($firstMatchingLineNumber >= 1 && $firstMatchingLineNumber <= count($lines)) {
                // Remove all lines after the specified line number
                $lines = array_slice($lines, 0, $firstMatchingLineNumber);

                // Add the replacement text
                $lines[] = $replacementText;

                // Join the modified lines back into a single string
                $modifiedFileContent = implode("\n", $lines);

                // Write the modified content back to the file
                file_put_contents($file, $modifiedFileContent);

                echo "Text after line $firstMatchingLineNumber has been replaced.";
            } else {
                echo "Invalid line number. No changes were made.";
            }

        }
        foreach ($files as $file) {
            $file_class_name = explode('/', $file);
            $file_class_name = end($file_class_name);
            $file_class_name = explode('.', $file_class_name);
            $file_class_name = $file_class_name[0];
            $searchText = "interface $file_class_name";

// Read the file into an array of lines
            $lines = file($file);

// Initialize a variable to store the line number of the first match
            $firstMatchingLineNumber = null;

// Iterate through each line
            foreach ($lines as $lineNumber => $line) {
                // Search for the text in the current line
                if (preg_match("/$searchText/", $line)) {
                    // Store the line number of the first match and break the loop
                    $firstMatchingLineNumber = $lineNumber + 1; // Add 1 because line numbers are 1-based
                }
            }
// Define the replacement text
            $replacementText = "{}";

// Read the entire file into a string
            $fileContent = file_get_contents($file);

// Split the file content into an array of lines
            $lines = explode("\n", $fileContent);

// Check if the specified line number is valid
            if ($firstMatchingLineNumber >= 1 && $firstMatchingLineNumber <= count($lines)) {
                // Remove all lines after the specified line number
                $lines = array_slice($lines, 0, $firstMatchingLineNumber);

                // Add the replacement text
                $lines[] = $replacementText;

                // Join the modified lines back into a single string
                $modifiedFileContent = implode("\n", $lines);

                // Write the modified content back to the file
                file_put_contents($file, $modifiedFileContent);

                echo "Text after line $firstMatchingLineNumber has been replaced.";
            } else {
                echo "Invalid line number. No changes were made.";
            }

        }
    }
}