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
class DropDown
{
	/**
	 * Filter rendered block content.
	 *
	 * @param string              $block_content Rendered block content.
	 * @param array<string,mixed> $block         Parsed block data.
	 *
	 * @return string
	 */
	public function renderBlock( string $block_content, array $block ): string
	{
		$orientation = $block['attrs']['layout']['orientation'] ?? 'horizontal';

		$on_click = match ( true ) {
			isset( $block['attrs']['submenuVisibility'] ) => 'click' === $block['attrs']['submenuVisibility'],
			isset( $block['attrs']['openSubmenusOnClick'] ) => $block['attrs']['openSubmenusOnClick'],
			default => false
		};

		if ( 'vertical' === $orientation ) {
			$processor = new \WP_HTML_Tag_Processor( $block_content );
			$in_nav = false;

			while ( $processor->next_tag() ) {
				/**
				* We want to wait until we are in the nav menu to start
				* mutating attributes. Otherwise we mess with the modal open/close
				*/
				if ( 'UL' === $processor->get_tag() && ! $in_nav ) {
					$in_nav = true;
				}

				if ( ! $in_nav ) {
					continue;
				}
				/**
				* Remove focusout for all vertical "dropdowns"
				*/
				$processor->remove_attribute( 'data-wp-on--focusout' );
			}

			$block_content = $processor->get_updated_html();
		}

		return $block_content;
	}
}
