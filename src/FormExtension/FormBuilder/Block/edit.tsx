import {BlockEditProps} from "@wordpress/blocks";
import BlockPlaceholder from "./BlockPlaceholder";
import BlockInspectorControls from "./BlockInspectorControls";

/**
 * @since 2.0.0
 */
export default function Edit({
  attributes,
  setAttributes,
}: BlockEditProps<any>) {
  const { defaultChecked, label } = attributes;
  return (
    <>
      <BlockPlaceholder {...{ defaultChecked, label }} />
      <BlockInspectorControls {...{ attributes, setAttributes }} />
    </>
  );
}
