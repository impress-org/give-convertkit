import {CheckboxControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import './styles.scss';
import {getWindowData} from '../window';
import {createInterpolateElement} from '@wordpress/element';

/**
 * @since 2.0.0
 */
export default function BlockPlaceholder({checked, label}) {
	const {requiresSetup, settingsUrl} = getWindowData();

	return (
		<div
			className={`givewp-mailchimp-block-placeholder
			${requiresSetup && 'givewp-mailchimp-block-placeholder--invalid'}`}
		>
			{requiresSetup ? (
				createInterpolateElement(
					__(
						'This block requires additional setup. Go to your <a>Settings</a> to connect your Mailchimp account.',
						'give'
					),
					{
						a: <a href={settingsUrl} target="_blank" rel="noopener noreferrer" />,
					}
				)
			) : (
				<CheckboxControl checked={checked} label={label} onChange={null} disabled={true} />
			)}
		</div>
	);
}
