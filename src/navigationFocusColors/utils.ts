type FocusColorAttributes = {
	className?: string;
	navItemFocusBackgroundColor?: string;
	navItemFocusColor?: string;
	style?: Record< string, string >;
};

const FOCUS_COLOR_CLASS_NAMES = [
	'has-navigation-focus-color',
	'has-navigation-focus-background-color',
];

export const getClassNameWithFocusColors = (
	className = '',
	attributes: FocusColorAttributes
) => {
	const classNames = className.split( /\s+/ ).filter( ( value ) => {
		return value && ! FOCUS_COLOR_CLASS_NAMES.includes( value );
	} );

	if ( attributes.navItemFocusColor ) {
		classNames.push( 'has-navigation-focus-color' );
	}

	if ( attributes.navItemFocusBackgroundColor ) {
		classNames.push( 'has-navigation-focus-background-color' );
	}

	return classNames.join( ' ' );
};

export const getFocusColorStyle = ( attributes: FocusColorAttributes ) => {
	return {
		...( attributes.style ?? {} ),
		...( attributes.navItemFocusColor
			? {
					'--core-nav-focus-color': attributes.navItemFocusColor,
			  }
			: {} ),
		...( attributes.navItemFocusBackgroundColor
			? {
					'--core-nav-focus-background-color':
						attributes.navItemFocusBackgroundColor,
			  }
			: {} ),
	};
};
