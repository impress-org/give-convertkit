<?php

namespace GiveConvertKit\FormExtension;

use Give\Helpers\Hooks;
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
        //
    }

    /**
     * @since 2.0.0
     * @inheritDoc
     */
    public function boot(): void
    {
        Hooks::addAction('givewp_form_builder_new_form', Actions\AddBlockToNewForms::class);
        Hooks::addAction('givewp_form_builder_enqueue_scripts', Actions\EnqueueFormBuilderScripts::class);
        Hooks::addAction('givewp_donation_form_enqueue_scripts', Actions\EnqueueDonationFormScripts::class);

        Hooks::addFilter(
            'givewp_donation_form_block_render_givewp-convertkit/convertkit',
            Actions\RenderDonationFormBlock::class,
            '__invoke',
            10,
            4
        );
    }
}
