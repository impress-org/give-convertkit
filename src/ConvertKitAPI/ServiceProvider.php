<?php

namespace GiveConvertKit\ConvertKitAPI;

use Give\Helpers\Hooks;
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
            $apiKey = trim( give_get_option( 'give_convertkit_api', '' ) );
            return new API($apiKey);
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
