import { BaseControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useEffect, useState } from "react";
import ReactSelect from "react-select";

import "./styles.scss";

type TagControlProps = {
  id: string;
  label: string;
  help: string;
  tagOptions: tag[];
  selectedTags: string[];
  onChange: (tag: string[]) => void;
};

type tag = { value: string; label: string };

/**
 * @unrleased
 */
export default function TagControls({
  id,
  label,
  help,
  tagOptions,
  selectedTags,
  onChange,
}: TagControlProps) {
  const [filteredValues, setFilteredValues] = useState<tag[]>([]);
  
  useEffect(() => {
    setFilteredValues(handleDefaultValues());
  }, [selectedTags, tagOptions]);
  
  const handleDefaultValues = () => {
    if (selectedTags) {
      return tagOptions.filter(({ value, label }) =>
          selectedTags?.includes(String(value) || label)
      );
    }
    return [];
  };
  
  const handleChange = (tags: tag[]) => {
    const newTags = tags.map(({ value }) => value);
    onChange(newTags);
  };

  return (
    <BaseControl
      id={id}
      className={"givewp-convertkit-tag-controls"}
      label={label}
      help={help}
      __nextHasNoMarginBottom={true}
    >
      <ReactSelect
        isMulti
        name={"subscriptionTagListControl"}
        placeholder={__("Add subscription tags to this form", 'give-convertkit')}
        value={filteredValues}
        options={tagOptions}
        onChange={handleChange}
      />
    </BaseControl>
  );
}
