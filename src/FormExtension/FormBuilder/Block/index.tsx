import type {BlockConfiguration} from '@wordpress/blocks';
import metadata from './metadata';
import Icon from './icon';
import edit from './edit';

const {name} = metadata;

const settings = {
    ...metadata,
    icon: Icon,
    edit,
    save: () => null,
};

/**
 * @unreleased
 */
const block: {name: string; settings: BlockConfiguration} = {name, settings};

export default block;
