<?php

namespace GiveConvertKit\FormExtension\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Blocks\BlockModel;
use GiveConvertKit\ConvertKitAPI\API;

/**
 * @since 2.0.0
 */
class AddBlockToNewForms
{
    /**
     * @since 2.0.0
     */
    public function __invoke(DonationForm $form)
    {
        $convertkit = give(API::class);

        if ( ! $this->isEnabledGlobally() || ! $convertkit->validateApiCredentials()) {
            return;
        }

        $form->blocks->insertAfter(
            'givewp/email',
            BlockModel::make([
                'name'       => 'givewp-convertkit/convertkit',
                'attributes' => [
                    'label'          => $this->getLabel(),
                    'defaultChecked' => $this->getDefaultChecked(),
                    'selectedForms'  => $this->getSelectedForms(),
                    'tagSubscribers' => $this->getSelectedTags(),
                ],
            ])
        );
    }

    /**
     * @since 2.0.0
     */
    public function isEnabledGlobally(): bool
    {
        return give_is_setting_enabled(give_get_option('give_convertkit_show_subscribe_checkbox'));
    }

    /**
     * @since 2.0.0
     */
    public function getLabel(): string
    {
        return give_get_option('give_convertkit_label', __('Subscribe to our newsletter?'));
    }

    /**
     * @since 2.0.0
     */
    protected function getDefaultChecked()
    {
        return give_is_setting_enabled(give_get_option('give_convertkit_checked_default'));
    }

    /**
     * @since 2.0.0
     */
    protected function getSelectedForms()
    {
        return (array)give_get_option('give_convertkit_list', []);
    }

    /**
     * @since 2.0.0
     */
    protected function getSelectedTags()
    {
        return give_get_option('_give_convertkit_tags');
    }
}
