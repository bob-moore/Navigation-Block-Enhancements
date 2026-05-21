<?php
/**
 * Core navigation processor.
 *
 * @package Bmd_NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements\Processors;

use Bmd\NavBlockEnhancements\Module;

/**
 * Processes rendered core navigation block markup.
 */
class Colors extends Module
{
	/**
	 * Add frontend focus/hover color variables to the core/navigation wrapper.
	 *
	 * @param string               $block_content : html output of the block.
	 * @param array<string, mixed> $block : parsed block.
	 *
	 * @return string
	 */
	public function renderBlock( string $block_content, array $block ): string
	{
		$focus_color            = $block['attrs']['navItemFocusColor'] ?? '';
		$focus_background_color = $block['attrs']['navItemFocusBackgroundColor'] ?? '';

		if ( empty( $focus_color ) && empty( $focus_background_color ) ) {
			return $block_content;
		}

		$processor = new \WP_HTML_Tag_Processor( $block_content );

		if ( ! $processor->next_tag( [ 'class_name' => 'wp-block-navigation' ] ) ) {
			return $block_content;
		}

		$existing_classes = preg_split( '/\s+/', (string) $processor->get_attribute( 'class' ) );
		$existing_classes = false !== $existing_classes ? $existing_classes : [];

		$classes = array_filter(
			$existing_classes,
			static fn ( string $class_name ): bool => ! in_array(
				$class_name,
				[
					'has-navigation-focus-color',
					'has-navigation-focus-background-color',
				],
				true
			)
		);

		$classes = array_unique(
			array_merge(
				$classes,
				[
					! empty( $focus_color ) ? 'has-navigation-focus-color' : '',
					! empty( $focus_background_color ) ? 'has-navigation-focus-background-color' : '',
				]
			)
		);

		$classes = array_filter( $classes );

		$existing_style = trim( (string) $processor->get_attribute( 'style' ) );

		if ( '' !== $existing_style && ! str_ends_with( $existing_style, ';' ) ) {
			$existing_style .= ';';
		}

		$styles = array_filter(
			[
				$existing_style,
				! empty( $focus_color )
					? sprintf( '--core-nav-focus-color:%s;', sanitize_text_field( $focus_color ) )
					: '',
				! empty( $focus_background_color )
					? sprintf( '--core-nav-focus-background-color:%s;', sanitize_text_field( $focus_background_color ) )
					: '',
			]
		);

		$processor->set_attribute( 'class', implode( ' ', $classes ) );
		$processor->set_attribute( 'style', implode( '', $styles ) );

		return $processor->get_updated_html();
	}
}
