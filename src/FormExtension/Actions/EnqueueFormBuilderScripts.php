<?php

namespace GiveConvertKit\FormExtension\Actions;

use GiveConvertKit\ConvertKitAPI\API;

class EnqueueFormBuilderScripts
{
    /**
     * @since 2.0.0
     * @var string
     */
    protected $styleSrc;

    /**
     * @since 2.0.0
     * @var string
     */
    protected $scriptSrc;

    /**
     * @since 2.0.0
     * @var array
     */
    protected $scriptAsset;

    /**
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->styleSrc = GIVE_CONVERTKIT_URL . 'build/FormBuilder.css';
        $this->scriptSrc = GIVE_CONVERTKIT_URL . 'build/FormBuilder.js';
        $this->scriptAsset = require GIVE_CONVERTKIT_DIR . 'build/FormBuilder.asset.php';
    }

    /**
     * @since 2.0.0
     */
    public function __invoke()
    {
        $convertkit = give(API::class);

        wp_enqueue_script('givewp-form-extension-convertkit', $this->scriptSrc, $this->scriptAsset['dependencies'], false, true);
        wp_localize_script('givewp-form-extension-convertkit', 'GiveConvertKit', [
            'requiresSetup' => ! $convertkit->validateApiCredentials(),
            'settingsUrl'   => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=addons'),
            'forms'         => $convertkit->getForms(),
            'tags'          => $convertkit->getTags(),
        ]);

        wp_enqueue_style(
            'givewp-form-extension-convertkit',
            $this->styleSrc
        );
    }
}
