// @ts-ignore
const {checkbox: Checkbox} = window.givewp.form.templates.fields;

/**
 * @unreleased
 */
export default function FieldTemplate({ErrorMessage, label, defaultChecked, inputProps}) {
    return (
        <div className={'givewp-convertkit-field'}>
            <Checkbox
                Label={() => label}
                ErrorMessage={ErrorMessage}
                inputProps={{
                    defaultChecked: defaultChecked,
                    ...inputProps,
                }}
            />
        </div>
    );
}
