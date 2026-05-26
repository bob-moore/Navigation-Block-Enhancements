<?php
/**
 * Core navigation transformer.
 *
 * @package Bmd_NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements\Transformers;

use Bmd\NavBlockEnhancements\Module;

/**
 * Transforms rendered core navigation block markup.
 */
class DropDown extends Module
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
			$transformer = new \WP_HTML_Tag_Processor( $block_content );
			$in_nav = false;

			while ( $transformer->next_tag() ) {
				/**
				* We want to wait until we are in the nav menu to start
				* mutating attributes. Otherwise we mess with the modal open/close
				*/
				if ( 'UL' === $transformer->get_tag() && ! $in_nav ) {
					$in_nav = true;
				}

				if ( ! $in_nav ) {
					continue;
				}
				/**
				* Remove focusout for all vertical "dropdowns"
				*/
				$transformer->remove_attribute( 'data-wp-on--focusout' );
			}

			$block_content = $transformer->get_updated_html();
		}

		return $block_content;
	}
}
