type BlockSettings = {
	attributes?: Record< string, unknown >;
};

export const addCustomAttributes = (
	settings: BlockSettings,
	name: string
) => {
	if ( 'core/navigation' !== name ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			navItemFocusColor: {
				type: 'string',
				default: '',
			},
			navItemFocusBackgroundColor: {
				type: 'string',
				default: '',
			},
		},
	};
};
