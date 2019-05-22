<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
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
        $this->app->singleton(DataService::class, function () {
//            Cache::flush();
//            Cache::forget('accessTokenKey');

            $dataService = [
                'auth_mode' => 'oauth2',
                'ClientID' => env('QUICKBOOKS_CLIENT_ID', ""),
                'ClientSecret' => env('QUICKBOOKS_CLIENT_SECRET', ""),
                'QBORealmID' => env('QUICKBOOKS_REALM_ID', ""),
                'RedirectURI' => env('QUICKBOOKS_REDIRECT_URI', ""),
                'baseUrl' => "Development",
            ];

            if(!Cache::has('accessTokenKey') && Cache::has('refreshTokenKey')){
                $dataService['refreshTokenKey'] = Cache::get('refreshTokenKey');
            }

            if(Cache::has('accessTokenKey')){
                $dataService['accessTokenKey'] = Cache::get('accessTokenKey');
            }

            return tap(DataService::Configure($dataService), function ($dataService) {
                $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
                if(!Cache::has('refreshTokenKey')){
                    $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken(env('QUICKBOOKS_AUTHORIZATON_CODE'), env('QUICKBOOKS_REALM_ID', ""));
                    $dataService->updateOAuth2Token($accessTokenObj);
//                    dd($accessTokenObj->getAccessTokenExpiresAt());
                    Cache::put('accessTokenKey', $accessTokenObj->getAccessToken(), 1800);

                    Cache::forever('refreshTokenKey', $accessTokenObj->getRefreshToken());
                }else{
                    if(!Cache::has('accessTokenKey')){
                        $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();
                        $error = $OAuth2LoginHelper->getLastError();
                        if(!$error){
                            $dataService->updateOAuth2Token($refreshedAccessTokenObj);
                                Cache::put('accessTokenKey', $refreshedAccessTokenObj->getAccessToken());
                        }
                    }

                }
                return $dataService;
            });
        });
    }
}
