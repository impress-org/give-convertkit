import { PanelBody, TextControl, ToggleControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";

import { __ } from "@wordpress/i18n";
import { BlockNotice } from "@givewp/form-builder-library";
import { getWindowData } from "../window";
import ListsControl from "./ListsControl";
import TagControls from "./TagsControl";

export default function BlockInspectorControls({ attributes, setAttributes }) {
  const { settingsUrl, requiresSetup, forms, tags } = getWindowData();
  const {
    label,
    defaultChecked,
    tagSubscribers,
    selectedForms = [""],
  } = attributes;

  const tagsListOptions = tags.map(function ({ id, name }) {
    return { value: id, label: name };
  });

  return (
    <InspectorControls>
      <PanelBody
        title={__("Field Settings", "give-convertkit")}
        initialOpen={true}
      >
        {requiresSetup ? (
          <BlockNotice
            title={__("ConvertKit requires setup", "give-convertkit")}
            description={__(
              "This block requires your settings to be configured in order to use.",
              "give-convertkit"
            )}
            anchorText={__(
              "Connect your ConvertKit account",
              "give-convertkit"
            )}
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

            <ListsControl
              id={"givewp-convertkit-tag-controls"}
              onChange={(values) => setAttributes({ selectedForms: values })}
              lists={forms}
              selectedLists={selectedForms}
            />

            <TagControls
              id={"givewp-convertkit-controls-tags"}
              help={__(
                "These tags will be applied to Subscribers based on the form they used to sign up.",
                "give-convertkit"
              )}
              label={__("Subscriber Tags", "give-convertkit")}
              onChange={(tag) => setAttributes({ tagSubscribers: tag })}
              tagOptions={tagsListOptions}
              selectedTags={tagSubscribers}
            />
          </div>
        )}
      </PanelBody>
    </InspectorControls>
  );
}
