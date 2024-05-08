<?php

namespace GiveConvertKit\ConvertKitAPI;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 2.0.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 2.0.0
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
     * @since 2.0.0
     * @inheritDoc
     */
    public function boot(): void
    {
        //
    }
}
