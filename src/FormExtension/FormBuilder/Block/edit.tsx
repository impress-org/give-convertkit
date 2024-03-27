import { __ } from "@wordpress/i18n";
import { BlockEditProps } from "@wordpress/blocks";
import { InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  SelectControl,
  TextControl,
  ToggleControl,
} from "@wordpress/components";
import BlockPlaceholder from "./BlockPlaceholder";
import { getWindowData } from "./window";
import { BlockNotice } from "@givewp/form-builder-library";

const { forms, tags } = getWindowData();
const formsListOptions = forms.map(function ({ id, name }) {
  return { value: id, label: name };
});
const tagsListOptions = tags.map(function ({ id, name }) {
  return { value: id, label: name };
});

/**
 * @unreleased
 */
export default function Edit({
  attributes,
  setAttributes,
}: BlockEditProps<any>) {
  const { defaultChecked, label, selectedForm, tagSubscribers } = attributes;
  const { settingsUrl, requiresSetup } = getWindowData();
  return (
    <>
      <BlockPlaceholder {...{ defaultChecked, label }} />
      <InspectorControls>
        <PanelBody
          title={__("Field Settings", "give-convertkit")}
          initialOpen={true}
        >
          {requiresSetup ? (
            <BlockNotice
              title={__("ConvertKit requires setup", "give")}
              description={__(
                "This block requires your settings to be configured in order to use.",
                "give"
              )}
              anchorText={__("Connect your ConvertKit account", "give")}
              href={settingsUrl}
            />
          ) : (
            <div className={"givewp-convertkit-controls"}>
              <TextControl
                label={__("Custom Label", "give-convertkit")}
                value={label}
                help={__(
                  "Customize the label for the ConvertKit opt-in checkbox",
                  "give-convertkit"
                )}
                onChange={(value) => setAttributes({ label: value })}
              />

              <ToggleControl
                label={__("Opt-in Default", "give-convertkit")}
                checked={defaultChecked}
                onChange={() =>
                  setAttributes({ defaultChecked: !defaultChecked })
                }
                help={__(
                  "Customize the newsletter opt-in option for this form.",
                  "give-convertkit"
                )}
              />

              <SelectControl
                label={__("ConvertKit Form", "give-convertkit")}
                value={selectedForm ?? ""}
                onChange={(value) => setAttributes({ selectedForm: value })}
                options={[
                  {
                    value: "",
                    label: __("Select a Form", "give-convertkit"),
                    disabled: true,
                  },
                  ...formsListOptions,
                ]}
              />

              <SelectControl
                multiple={true}
                label={__("Tag Subscribers", "give-convertkit")}
                value={tagSubscribers}
                onChange={(value) => setAttributes({ tagSubscribers: value })}
                options={tagsListOptions}
              />
            </div>
          )}
        </PanelBody>
      </InspectorControls>
    </>
  );
}
