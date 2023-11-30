<?php

namespace GiveConvertKit\FormExtension\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Blocks\BlockModel;

class AddBlockToNewForms
{
    public function __invoke(DonationForm $form)
    {
        if($this->isEnabledGlobally()) {
            $form->blocks->insertAfter('givewp/email', BlockModel::make([
                'name' => 'givewp-convertkit/convertkit',
                'attributes' => [
                    'label' => $this->getLabel(),
                    'defaultChecked' => $this->getDefaultChecked(),
                    'selectedForm' => $this->getSelectedForm(),
                    'tagSubscribers' => $this->getSelectedTags(),
                ],
            ]));
        }
    }

    public function isEnabledGlobally(): bool
    {
        return give_is_setting_enabled(give_get_option( 'give_convertkit_show_subscribe_checkbox'));
    }

    public function getLabel(): string
    {
        return give_get_option('give_convertkit_label');
    }

    protected function getDefaultChecked()
    {
        return give_is_setting_enabled(give_get_option('give_convertkit_checked_default'));
    }

    protected function getSelectedForm()
    {
        return give_get_option('give_convertkit_list');
    }

    protected function getSelectedTags()
    {
        return give_get_option('_give_convertkit_tags');
    }
}
