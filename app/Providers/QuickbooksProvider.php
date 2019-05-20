<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use QuickBooksOnline\API\DataService\DataService;

class QuickbooksProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('quickbooks', function () {
            return DataService::Configure([
                'auth_mode'    => 'oauth2',
                'ClientID'     => env('QUICKBOOKS_CLIENT_ID', ""),
                'ClientSecret' => env('QUICKBOOKS_CLIENT_SECRET', ""),
                //'RedirectURI'  => env('QUICKBOOKS_REDIRECT_URI', ""),
                'scope'        => "com.intuit.quickbooks.accounting",
                'baseUrl'      => "Development",
            ]);
        });
    }
}
