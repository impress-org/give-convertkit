import './styles.scss';

type BlockNoticeProps = {
	title: string;
	description: string;
	anchorText: string;
	href: string;
};

export default function BlockNotice({title, description, anchorText, href}: BlockNoticeProps) {
	return (
		<div className={'givewp-block-settings-notice'}>
			<span className={'givewp-block-settings-notice__title'}>{title}</span>
			<span className={'givewp-block-settings-notice__description'}>{description}</span>
			<a className={'givewp-block-settings-notice__anchor'} href={href} target={'_blank'} rel={'noreferrer'}>
				{anchorText}
			</a>
		</div>
	);
}
