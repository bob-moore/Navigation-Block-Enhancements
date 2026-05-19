<?php
/**
 * Asset provider.
 *
 * @package Bmd_NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements\Providers;

use Bmd\NavBlockEnhancements\Services;

/**
 * Enqueues editor controls and shared block styles.
 */
class AssetLoader
{
	/**
	 * Constructor.
	 *
	 * @param Services\UrlResolver      $url_resolver       URL resolver.
	 * @param Services\FilePathResolver $file_path_resolver File path resolver.
	 */
	public function __construct(
		protected Services\UrlResolver $url_resolver,
		protected Services\FilePathResolver $file_path_resolver
	) {
	}

	/**
	 * Enqueue editor script and styles.
	 *
	 * @return void
	 */
	public function enqueueEditorAssets(): void
	{
		$asset_data = $this->getAssetData( 'editor' );

		wp_enqueue_script(
			handle: 'navigation-block-enhancements-editor',
			src: $this->url_resolver->resolve( 'build/editor.js' ),
			deps: $asset_data['dependencies'],
			ver: $asset_data['version']
		);
	}

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * @return void
	 */
	public function enqueueFrontendAssets(): void
	{
		$asset_data = $this->getAssetData( 'frontend' );

		wp_enqueue_script(
			handle: 'navigation-block-enhancements-frontend',
			src: $this->url_resolver->resolve( 'build/frontend.js' ),
			deps: $asset_data['dependencies'],
			ver: $asset_data['version']
		);

		wp_enqueue_style(
			handle: 'navigation-block-enhancements-frontend-styles',
			src: $this->url_resolver->resolve( 'build/frontend.css' ),
			deps: [],
			ver: $asset_data['version']
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public function enqueueAdminAssets(): void
	{
		$asset_data = $this->getAssetData( 'admin' );

		wp_enqueue_script(
			handle: 'navigation-block-enhancements-admin',
			src: $this->url_resolver->resolve( 'build/admin.js' ),
			deps: $asset_data['dependencies'],
			ver: $asset_data['version']
		);

		wp_enqueue_style(
			handle: 'navigation-block-enhancements-admin-styles',
			src: $this->url_resolver->resolve( 'build/admin.css' ),
			deps: [],
			ver: $asset_data['version']
		);
	}

	/**
	 * Register block styles for a specific block.
	 *
	 * @return void
	 */
	public function enqueueBlockStyles(): void
	{
		wp_enqueue_block_style(
			'core/navigation',
			[
				'handle' => 'navigation-block-enhancements-styles',
				'src'    => $this->url_resolver->resolve( 'build/style.css' ),
				'ver'    => '1.0.0',
				'path'   => $this->file_path_resolver->resolve( 'build/style.css' ),
			]
		);
	}

	/**
	 * Resolve script dependency metadata from WordPress build asset files.
	 *
	 * @param string $key Build asset key without the `.asset.php` suffix.
	 *
	 * @return array{dependencies: array<int, string>, version: string|null}
	 */
	protected function getAssetData( string $key ): array
	{
		$asset_file = $this->file_path_resolver->resolve( "build/{$key}.asset.php" );

		if ( ! is_file( $asset_file ) ) {
			return [
				'dependencies' => [],
				'version'      => null,
			];
		}

		$data = include $asset_file;

		if ( ! is_array( $data ) ) {
			return [
				'dependencies' => [],
				'version'      => null,
			];
		}

		$dependencies = $data['dependencies'] ?? [];
		$version      = $data['version'] ?? null;

		return [
			'dependencies' => is_array( $dependencies ) ? $dependencies : [],
			'version'      => is_string( $version ) ? $version : null,
		];
	}
}
