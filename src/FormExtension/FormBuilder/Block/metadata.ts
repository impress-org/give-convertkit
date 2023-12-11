import type {BlockConfiguration} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';

/**
 * @unreleased
 */
const metadata: BlockConfiguration = {
    name: 'givewp-convertkit/convertkit',
    title: __('ConvertKit', 'give-convertkit'),
    description: __(
        'Easily integrate ConvertKit opt-ins within your Give donation forms.',
        'give-convertkit'
    ),
    category: 'addons',
    supports: {
        multiple: false,
    },
    attributes: {
        label: {
            type: 'string',
            default: __('Subscribe to newsletter?', 'give'),
        },
        defaultChecked: {
            type: 'boolean',
            default: true,
        },
        selectedForm: {
            type: 'string',
        },
        tagSubscribers: {
            type: 'array',
            default: [],
        },
    },
};

export default metadata;
