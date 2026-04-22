<?php
/**
 * Navigation block enhancements service.
 *
 * PHP Version 8.2
 *
 * @package    Bmd\NavigationBlockEnhancements
 * @author     Bob Moore <bob@bobmoore.dev>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       https://www.bobmoore.dev
 * @since      1.0.0
 */

namespace Bmd;

/**
 * Service class for navigation block enhancements.
 */
class NavBlockEnhancements
{
	/**
	 * Register all WordPress hooks for navigation block enhancements.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		add_action( 'enqueue_block_assets', [ $this, 'enqueueStyles' ] );
		add_filter( 'render_block_core/navigation', [ $this, 'processNavigationBlock' ], 10, 2 );
	}

	/**
	 * Register the shared stylesheet for the core/navigation block.
	 *
	 * Uses wp_enqueue_block_style so the stylesheet is only loaded on pages
	 * that render a core/navigation block, in both frontend and editor.
	 *
	 * @return void
	 */
	public function enqueueStyles(): void
	{
		$relative_path = 'style-index.css';
		$style_file    = $this->buildPath( $relative_path );

		if ( ! is_file( $style_file ) ) {
			return;
		}

		$src = $this->buildUrl( $relative_path );

		if ( empty( $src ) ) {
			return;
		}

		wp_enqueue_block_style(
			'core/navigation',
			[
				'handle' => 'bmd-navigation-block-enhancements',
				'src'    => $src,
				'ver'    => (string) filemtime( $style_file ),
				'path'   => $style_file,
			]
		);
	}

	/**
	 * Build an absolute path inside the package build directory.
	 *
	 * @param string $relative_path Relative file path inside build.
	 *
	 * @return string
	 */
	protected function buildPath( string $relative_path ): string
	{
		return wp_normalize_path(
			dirname( __DIR__ ) . '/build/' . ltrim( $relative_path, '/' )
		);
	}

	/**
	 * Resolve a build file path into a public URL.
	 *
	 * @param string $relative_path Relative file path inside build.
	 *
	 * @return string
	 */
	protected function buildUrl( string $relative_path ): string
	{
		$absolute_path = $this->buildPath( $relative_path );
		$resolved_path = realpath( $absolute_path );

		if ( false !== $resolved_path ) {
			$absolute_path = wp_normalize_path( $resolved_path );
		}

		$content_dir = wp_normalize_path( WP_CONTENT_DIR );

		if ( str_starts_with( $absolute_path, $content_dir ) ) {
			$relative = ltrim(
				substr( $absolute_path, strlen( $content_dir ) ),
				'/'
			);

			return content_url( $relative );
		}

		$root_dir = wp_normalize_path( ABSPATH );

		if ( str_starts_with( $absolute_path, $root_dir ) ) {
			$relative = ltrim(
				substr( $absolute_path, strlen( $root_dir ) ),
				'/'
			);

			return site_url( $relative );
		}

		return '';
	}

	/**
	 * Process navigation block output for vertical layouts.
	 *
	 * @param string               $block_content The rendered block output.
	 * @param array<string, mixed> $block         Parsed block data.
	 *
	 * @return string
	 */
	public function processNavigationBlock( string $block_content, array $block ): string
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

	/**
	 * Remove focusout handler for the mobile modal container.
	 *
	 * This is mainly used for testing purposes to prevent the block from closing
	 * when running focus-related tests, but it could also be useful for users
	 * who want to disable the focusout behavior on mobile.
	 *
	 * @param string               $block_content The rendered block output.
	 * @param array<string, mixed> $block         Parsed block data.
	 *
	 * @return string
	 */
	public function removeFocusOutForModal( string $block_content, array $block ): string
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
