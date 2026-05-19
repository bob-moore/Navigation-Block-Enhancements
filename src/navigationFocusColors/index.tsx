import { addFilter } from '@wordpress/hooks';

import { addCustomAttributes } from './attributes';
import { Edit } from './edit';
import { BlockListBlock } from './editor';

addFilter(
	'blocks.registerBlockType',
	'mwf-cornerstone/navigation-focus-color-attributes',
	addCustomAttributes
);

addFilter(
	'editor.BlockEdit',
	'mwf-cornerstone/navigation-focus-color-controls',
	Edit
);

addFilter(
	'editor.BlockListBlock',
	'mwf-cornerstone/navigation-focus-color-editor-preview',
	BlockListBlock
);
