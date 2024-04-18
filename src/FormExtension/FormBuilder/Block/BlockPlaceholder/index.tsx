import { CheckboxControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import "./styles.scss";
import { getWindowData } from "../window";
import { createInterpolateElement } from "@wordpress/element";

/**
 * @unreleased
 */
export default function BlockPlaceholder({ defaultChecked, label }) {
  const { requiresSetup, settingsUrl } = getWindowData();

  return (
    <div
      className={`givewp-convertkit-block-placeholder
			${requiresSetup && "givewp-convertkit-block-placeholder--invalid"}`}
    >
      {requiresSetup ? (
        createInterpolateElement(
          __(
            "This block requires additional setup. Go to your <a>Settings</a> to connect your ConvertKit account.",
            "give-convertkit"
          ),
          {
            a: (
              <a href={settingsUrl} target="_blank" rel="noopener noreferrer" />
            ),
          }
        )
      ) : (
        <CheckboxControl
          checked={defaultChecked}
          label={label}
          onChange={null}
          disabled={true}
        />
      )}
    </div>
  );
}
