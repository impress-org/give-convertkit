<?php

namespace GiveConvertKit\FormExtension\Actions;

use GiveConvertKit\ConvertKitAPI\API;

class EnqueueFormBuilderScripts
{
    /**
     * @unreleased
     * @var string
     */
    protected $styleSrc;

    /**
     * @unreleased
     * @var string
     */
    protected $scriptSrc;

    /**
     * @unreleased
     * @var array
     */
    protected $scriptAsset;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->styleSrc = GIVE_CONVERTKIT_URL . 'build/FormBuilder.css';
        $this->scriptSrc = GIVE_CONVERTKIT_URL . 'build/FormBuilder.js';
        $this->scriptAsset = require GIVE_CONVERTKIT_DIR . 'build/FormBuilder.asset.php';
    }

    /**
     * @unreleased
     */
    public function __invoke()
    {
        $convertkit = give(API::class);

        wp_enqueue_script('givewp-form-extension-convertkit', $this->scriptSrc, $this->scriptAsset['dependencies']);
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
