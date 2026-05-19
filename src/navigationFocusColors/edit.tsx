import { createHigherOrderComponent } from '@wordpress/compose';
import {
	InspectorControls,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import type { BlockEditProps } from '../../types';

type NavigationFocusColorAttributes = {
	className?: string;
	navItemFocusBackgroundColor: string;
	navItemFocusColor: string;
};

type NavigationBlockEditProps =
	BlockEditProps< NavigationFocusColorAttributes >;

export const Edit = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props: NavigationBlockEditProps ) => {
		const { attributes, isSelected, name, setAttributes } = props;

		if ( 'core/navigation' !== name ) {
			return <BlockEdit { ...props } />;
		}

		const colorGradientSettings = useMultipleOriginColorsAndGradients();
		const { navItemFocusBackgroundColor, navItemFocusColor } = attributes;
		const hasFocusColors = Boolean(
			navItemFocusColor || navItemFocusBackgroundColor
		);
		const handleFocusColorChange = ( value?: string ) => {
			setAttributes( {
				navItemFocusColor: value ?? '',
			} );
		};
		const handleFocusBackgroundColorChange = ( value?: string ) => {
			setAttributes( {
				navItemFocusBackgroundColor: value ?? '',
			} );
		};

		return (
			<>
				<BlockEdit { ...props } />
				{ isSelected && (
					<InspectorControls group="color">
						<ColorGradientSettingsDropdown
							settings={ [
								{
									label: __(
										'Text - Hover',
										'mwf-cornerstone'
									),
									colorValue: navItemFocusColor,
									onColorChange: handleFocusColorChange,
									clearable: true,
									resetAllFilter: () => ( {
										navItemFocusColor: '',
									} ),
								},
								{
									label: __(
										'Background - Hover',
										'mwf-cornerstone'
									),
									colorValue: navItemFocusBackgroundColor,
									onColorChange:
										handleFocusBackgroundColorChange,
									clearable: true,
									resetAllFilter: () => ( {
										navItemFocusBackgroundColor: '',
									} ),
								},
							] }
							panelId={ props.clientId }
							hasColorsOrGradients={ hasFocusColors }
							disableCustomColors={ false }
							enableAlpha
							__experimentalIsRenderedInSidebar
							{ ...colorGradientSettings }
						/>
					</InspectorControls>
				) }
			</>
		);
	};
}, 'withNavigationFocusColorControls' );
