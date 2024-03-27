<?php

namespace GiveConvertKit\FormExtension\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Blocks\BlockModel;
use GiveConvertKit\ConvertKitAPI\API;

/**
 * @unreleased
 */
class AddBlockToNewForms
{
    /**
     * @unreleased
     */
    public function __invoke(DonationForm $form)
    {
        $convertkit = give(API::class);

        if ($this->isEnabledGlobally() && $convertkit->validateApiCredentials()) {
            $form->blocks->insertAfter(
                'givewp/email',
                BlockModel::make([
                    'name'       => 'givewp-convertkit/convertkit',
                    'attributes' => [
                        'label'          => $this->getLabel(),
                        'defaultChecked' => $this->getDefaultChecked(),
                        'selectedForm'   => $this->getSelectedForm(),
                        'tagSubscribers' => $this->getSelectedTags(),
                    ],
                ])
            );
        }
    }

    /**
     * @unreleased
     */
    public function isEnabledGlobally(): bool
    {
        return give_is_setting_enabled(give_get_option('give_convertkit_show_subscribe_checkbox'));
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return give_get_option('give_convertkit_label');
    }

    /**
     * @unreleased
     */
    protected function getDefaultChecked()
    {
        return give_is_setting_enabled(give_get_option('give_convertkit_checked_default'));
    }

    /**
     * @unreleased
     */
    protected function getSelectedForm()
    {
        return give_get_option('give_convertkit_list');
    }

    /**
     * @unreleased
     */
    protected function getSelectedTags()
    {
        return give_get_option('_give_convertkit_tags');
    }
}
