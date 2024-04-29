<?php

namespace GiveConvertKit\FormExtension\DonationForm\Fields;

use Give\Framework\FieldsAPI\Checkbox;

class ConvertKitField extends Checkbox
{
    public const TYPE = 'convertkit';

    protected $selectedForm;

    protected $tagSubscribers;

    /**
     * @unreleased
     */
    public function selectedForms(array|string $selectedForm): ConvertKitField
    {
        $this->selectedForm = $selectedForm;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getSelectedForms(): array|string
    {
        return $this->selectedForm;
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
