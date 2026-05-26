<?php
/**
 * Hook registrar — wires all static WordPress actions and filters.
 *
 * @package Bmd\NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements;

use DI\Attribute\Inject;

/**
 * Registers all static WordPress actions and filters for the plugin.
 *
 * PHP-DI calls registerActions() and registerFilters() automatically after
 * construction via method injection. All hook registration lives here so no
 * other class ever calls add_action() or add_filter() directly.
 */
class Controller extends Module
{
	/**
	 * Register WordPress action hooks.
	 *
	 * @param Providers\Assets $assets Asset provider.
	 *
	 * @return void
	 */
	#[Inject]
	public function registerActions(
		Providers\Assets $assets,
	): void {
		add_action( 'enqueue_block_editor_assets', [ $assets, 'enqueueEditorAssets' ] );
		add_action( 'init', [ $assets, 'enqueueBlockStyles' ] );
	}

	/**
	 * Register WordPress filter hooks.
	 *
	 * @param Transformers\DropDown $dropdown      Dropdown transformer.
	 * @param Transformers\Colors   $colors        Color transformer.
	 * @param Transformers\Modal    $modal         Modal transformer.
	 *
	 * @return void
	 */
	#[Inject]
	public function registerFilters(
		Transformers\DropDown $dropdown,
		Transformers\Colors $colors,
		Transformers\Modal $modal,
	): void {
		add_filter( 'render_block_core/navigation', [ $dropdown, 'renderBlock' ], 10, 2 );
		add_filter( 'render_block_core/navigation', [ $colors, 'renderBlock' ], 10, 2 );

		if ( apply_filters( "{$this->package}_enable_dev_mode", false ) ) {
			add_filter( 'render_block_core/navigation', [ $modal, 'removeFocusOut' ], 10, 2 );
		}
	}

	/**
	 * Get a service instance from the container.
	 *
	 * @param string $service Fully-qualified class name or container entry key.
	 *
	 * @return object|null
	 */
	public static function getInstance( string $service ): ?object
	{
		return Main::getInstance( $service );
	}
}
