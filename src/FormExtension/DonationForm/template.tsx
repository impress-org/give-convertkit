import "./styles.scss"

// @ts-ignore
const {checkbox: Checkbox} = window.givewp.form.templates.fields;

/**
 * @since 2.0.0
 */
export default function FieldTemplate({ErrorMessage, label, checked, inputProps}) {
    return (
        <div className={'givewp-convertkit-field'}>
            <Checkbox
                Label={() => label}
                ErrorMessage={ErrorMessage}
                inputProps={{
                    defaultChecked: checked,
                    ...inputProps,
                }}
            />
        </div>
    );
}
