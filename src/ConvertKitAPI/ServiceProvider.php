<?php

namespace GiveConvertKit\ConvertKitAPI;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     * @inheritDoc
     */
    public function register(): void
    {
        give()->singleton(API::class, static function () {
            $apiKey = trim(give_get_option('give_convertkit_api', ''));
            $apiSecret = trim(give_get_option('give_convertkit_api_secret', ''));
            
            return new API($apiKey, $apiSecret);
        });
    }
    
    /**
     * @unreleased
     * @inheritDoc
     */
    public function boot(): void
    {
        //
    }
}
