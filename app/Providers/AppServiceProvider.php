<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

use FilesystemIterator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // DB::table('requests')->truncate();

        // $this->delete_dir_recursively(storage_path('app/public/download/'), false);
        // $this->delete_dir_recursively(storage_path('app/requests/'), false);
    }

    private function delete_dir_recursively($dir, $remove_itself = true)
    {
        foreach ((new FilesystemIterator($dir)) as $file) {
            if (is_dir($file) && !is_link($file)) {
                $this->delete_dir_recursively($file);
            } else {
                unlink($file);
            }
        }
        if ($remove_itself) {
            rmdir($dir);
        }
    }
}
