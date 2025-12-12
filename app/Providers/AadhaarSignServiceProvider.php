<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AadhaarSignServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        require_once base_path('esign/library/XMLSecurityDSig.php');
        require_once base_path('esign/library/XMLSecurityKey.php');
        require_once base_path('esign/TCPDF/tcpdf.php');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
