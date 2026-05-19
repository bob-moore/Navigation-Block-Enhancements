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

/**
 * Processes rendered core navigation block markup.
 */
class Modal
{
	/**
	 * Remove focusout handler for the mobile modal container.
	 *
	 * This is mainly used for development to prevent the overlay from closing
	 * while browser developer tools have focus.
	 *
	 * @param string               $block_content The rendered block output.
	 * @param array<string, mixed> $block         Parsed block data.
	 *
	 * @return string
	 */
	public function renderBlock( string $block_content, array $block ): string
	{
		$overlay_menu = $block['attrs']['overlayMenu'] ?? 'mobile';

		if ( 'never' === $overlay_menu ) {
			return $block_content;
		}

		$processor = new \WP_HTML_Tag_Processor( $block_content );

		if (
			$processor->next_tag(
				[
					'class'    => 'wp-block-navigation__responsive-container',
					'tag_name' => 'div',
				]
			)
		) {
			$processor->remove_attribute( 'data-wp-on--focusout' );
			return $processor->get_updated_html();
		}

		return $block_content;
	}
}
