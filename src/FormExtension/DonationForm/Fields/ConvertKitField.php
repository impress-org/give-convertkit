<?php

namespace GiveConvertKit\FormExtension\DonationForm\Fields;

use Give\Framework\FieldsAPI\Checkbox;

class ConvertKitField extends Checkbox
{
    public const TYPE = 'convertkit';

    protected $selectedForms;

    protected $tagSubscribers;

    /**
     * @since 2.0.0
     */
    public function selectedForms(array $selectedForms): ConvertKitField
    {
        $this->selectedForms = $selectedForms;

        return $this;
    }

    /**
     * @since 2.0.0
     */
    public function getSelectedForms(): array
    {
        return $this->selectedForms;
    }

    /**
     * @since 2.0.0
     */
    public function tagSubscribers(array $tagSubscribers): ConvertKitField
    {
        $this->tagSubscribers = $tagSubscribers;

        return $this;
    }

    /**
     * @since 2.0.0
     */
    public function getTagSubscribers(): array
    {
        return $this->tagSubscribers;
    }
}
