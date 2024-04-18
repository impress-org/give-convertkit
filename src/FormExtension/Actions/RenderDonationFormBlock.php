<?php

namespace GiveConvertKit\FormExtension\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use GiveConvertKit\ConvertKitAPI\API;
use GiveConvertKit\FormExtension\DonationForm\Fields\ConvertKitField;

class RenderDonationFormBlock
{
    /**
     * Renders the ConvertKit field for the donation form block.
     *
     * @param Node|null  $node The node instance.
     * @param BlockModel $block The block model instance.
     * @param int        $blockIndex The index of the block.
     *
     * @return ConvertKitField
     * @throws EmptyNameException
     */
    public function __invoke($node, BlockModel $block, int $blockIndex)
    {
        $convertkit = give(API::class);

        if ($convertkit->validateApiCredentials()) {
            return null;
        }

        return ConvertKitField::make('convertkit')
            ->label((string)$block->getAttribute('label'))
            ->checked((bool)$block->getAttribute('defaultChecked'))
            ->selectedForm((string)$block->getAttribute('selectedForm'))
            ->tagSubscribers((array)$block->getAttribute('tagSubscribers'))
            ->scope(function (ConvertKitField $field, $value, Donation $donation) {
                // If the field is checked, subscribe the donor to the list.
                if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    $convertkit = give(API::class);
                    $subscriber = \GiveConvertKit\ConvertKitAPI\Subscriber::fromDonor($donation->donor);

                    if ($field->getSelectedForm()) {
                        $convertkit->subscribeToFormList($field->getSelectedForm(), $subscriber);
                    }

                    if ($field->getTagSubscribers()) {
                        foreach ($field->getTagSubscribers() as $tagId) {
                            $convertkit->subscriberToTag($tagId, $subscriber);
                        }
                    }
                }
            });
    }

}
