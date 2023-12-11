<?php

namespace GiveConvertKit\FormExtension\DonationForm\Fields;

use Give\Framework\FieldsAPI\Checkbox;
use Give\Framework\FieldsAPI\Field;

class ConvertKitField extends Checkbox
{
    protected $selectedForm;
    protected $tagSubscribers;

    public const TYPE = 'convertkit';

    /**
     * @unreleased
     */
    public function selectedForm(string $selectedForm): ConvertKitField
    {
        $this->selectedForm = $selectedForm;
        return $this;
    }

    /**
     * @unreleased
     */
    public function getSelectedForm(): string
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
