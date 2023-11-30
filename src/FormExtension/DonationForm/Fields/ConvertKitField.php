<?php

namespace GiveConvertKit\FormExtension\DonationForm\Fields;

use Give\Framework\FieldsAPI\Field;

class ConvertKitField extends Field
{
    protected $label;
    protected $selectedForm;
    protected $defaultChecked;
    protected $tagSubscribers;

    public const TYPE = 'convertkit';

    /**
     * @unreleased
     */
    public function label(string $label): ConvertKitField
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return $this->label;
    }

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
    public function defaultChecked(bool $defaultChecked): ConvertKitField
    {
        $this->defaultChecked = $defaultChecked;
        return $this;
    }

    /**
     * @unreleased
     */
    public function getDefaultChecked(): bool
    {
        return $this->defaultChecked;
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
