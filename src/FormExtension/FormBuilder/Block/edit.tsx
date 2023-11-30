import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {InspectorControls} from '@wordpress/block-editor';
import {PanelBody, CheckboxControl, SelectControl, TextControl, ToggleControl} from '@wordpress/components';

declare const window {
        GiveConvertKit: {
            forms?: Array<{ id: string; name: string }>,
            tags?: Array<{ id: string; name: string }>,
        }
} & Window

const formsListOptions = window.GiveConvertKit.forms.map(function(form) {
    return { value: form.id, label: form.name };
});
const tagsListOptions = window.GiveConvertKit.tags.map(function(tag) {
    return { value: tag.id, label: tag.name };
});

/**
 * @unreleased
 */
export default function Edit({attributes, setAttributes}: BlockEditProps<any>) {
    const {defaultChecked, label, selectedForm, tagSubscribers} = attributes;
    return (
        <>
            <div className={'givewp-convertkit-block-placeholder'}>
                <CheckboxControl checked={defaultChecked} label={label} onChange={null} disabled={true} />
            </div>
            <InspectorControls>
                <PanelBody title={__('Field Settings', 'give-convertkit')} initialOpen={true}>
                    <div className={'givewp-convertkit-controls'}>
                        <TextControl
                            label={__('Custom Label', 'give-convertkit')}
                            value={label}
                            help={__('Customize the label for the ConvertKit opt-in checkbox', 'give-convertkit')}
                            onChange={(value) => setAttributes({label: value})}
                        />

                        <ToggleControl
                            label={__('Opt-in Default', 'give-convertkit')}
                            checked={defaultChecked}
                            onChange={() => setAttributes({defaultChecked: !defaultChecked})}
                            help={__(
                                'Customize the newsletter opt-in option for this form.',
                                'give-convertkit'
                            )}
                        />

                        <SelectControl label={__('ConvertKit Form', 'give-convertkit')} value={selectedForm ?? ''} onChange={(value) => setAttributes({selectedForm: value})}
                                       options={[
                                           {value: '', label: __('Select a Form', 'give-convertkit'), disabled: true},
                                           ...formsListOptions,
                                       ]}
                        />

                        <SelectControl multiple={true} label={__('Tag Subscribers', 'give-convertkit')} value={tagSubscribers} onChange={(value) => setAttributes({tagSubscribers: value})}
                                       options={tagsListOptions}
                        />
                    </div>
                </PanelBody>
            </InspectorControls>
        </>
    );
}
