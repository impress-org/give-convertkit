<?php

namespace GiveConvertKit\FormExtension\DonationForm\Fields;

use Give\Framework\FieldsAPI\Checkbox;

class ConvertKitField extends Checkbox
{
    public const TYPE = 'convertkit';

    protected $selectedForms;

    protected $tagSubscribers;

    /**
     * @unreleased
     */
    public function selectedForms(array $selectedForms): ConvertKitField
    {
        $this->selectedForms = $selectedForms;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getSelectedForms(): array
    {
        return $this->selectedForms;
    }

    /**
     * @unreleased
     */
    public function tagSubscribers(array $tagSubscribers): ConvertKitField
    {
        $this->tagSubscribers = $tagSubscribers;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getTagSubscribers(): array
    {
        return $this->tagSubscribers;
    }
}
