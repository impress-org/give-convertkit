import {BaseControl, CheckboxControl} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {Fragment, useState} from "react";
import {lists} from "../../window";

import "./styles.scss";

/**
 * @unreleased
 */
type ListControlProps = {
  id: string;
  onChange: (values: string[]) => void;
  lists: lists[];
  selectedLists: selectedLists;
};

type selectedLists = string[];

/**
 * @unreleased
 **/
export default function ListsControl({
  id,
  onChange,
  lists,
  selectedLists,
}: ListControlProps) {
  const handleListSelection = (isChecked: boolean, id: string) => {
    if (isChecked) {
      const addListName = [...selectedLists, id];
      onChange(addListName);
    } else {
      const removeListName = selectedLists.filter((list) => list !== id);
      onChange(removeListName);
    }
  };

  return (
    <BaseControl
      id={id}
      className={"givewp-convertkit-lists-control"}
      help={
        lists
          ? __(
              "Customize the list(s) you wish donors to subscribe to if they opt-in.",
              "give-convertkit"
            )
          : __(
              "We were unable to find any email list's for your account. Please visit Constant Contact and verify you have created at least one mailing list to use this block.",
              "give-convertkit"
            )
      }
      label={__("default opt-in", "give-convertkit")}
    >
      {lists &&
        lists?.map(({ id, name }) => (
          <Fragment key={id}>
            <ListCheckboxControl
              id={id}
              name={name}
              checked={selectedLists && selectedLists.includes(id)}
              handleListSelection={handleListSelection}
            />
          </Fragment>
        ))}
    </BaseControl>
  );
}

/**
 * @unreleased
 **/
type ListCheckboxProps = {
  id: string;
  name: string;
  checked: boolean;
  handleListSelection: (isChecked: boolean, id: string) => void;
};

/**
 * @unreleased
 **/
function ListCheckboxControl({
  name,
  id,
  checked,
  handleListSelection,
}: ListCheckboxProps) {
  const [isChecked, setIsChecked] = useState<boolean>(null);

  const handleChange = () => {
    handleListSelection(!isChecked, id);
    setIsChecked(!isChecked);
  };

  return (
    <CheckboxControl
      defaultChecked={checked}
      checked={isChecked}
      id={id}
      label={name}
      onChange={handleChange}
    />
  );
}
