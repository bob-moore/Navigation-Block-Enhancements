<?php
/**
 * Plugin service container and hook loader.
 *
 * @package Bmd_NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements;

/**
 * Builds the plugin container and registers WordPress hooks.
 */
class Controller
{
	/**
	 * Shared service container.
	 *
	 * @var \DI\Container|null
	 */
	protected static ?\DI\Container $services = null;
	/**
	 * Container builder instance.
	 *
	 * @var \DI\ContainerBuilder<\DI\Container>|null
	 */
	protected static ?\DI\ContainerBuilder $container_builder = null;

	/**
	 * Container configuration definitions.
	 *
	 * @var array<string, mixed>
	 */
	protected array $config = [];

	/**
	 * Constructor.
	 *
	 * @param string $url   Plugin directory URL.
	 * @param string $path  Plugin directory path.
	 * @param bool   $cache Whether to compile the container into the plugin cache directory.
	 */
	public function __construct(
		string $url = '',
		string $path = '',
		bool $cache = false,
	) {
		$this->config = array_merge(
			[
				'config.url'   => $url,
				'config.path'  => $path,
				'config.cache' => $cache,
			],
			$this->config
		);
	}

	/**
	 * Build the service container.
	 *
	 * @return void
	 */
	protected function initContainer(): void
	{
		self::$container_builder = new \DI\ContainerBuilder();

		self::$container_builder->useAttributes( true );

		$this->config = array_merge(
			[
				// Services.
				Services\FilePathResolver::class => \DI\autowire(),
				Services\UrlResolver::class      => \DI\autowire(),
				// Providers.
				Providers\AssetLoader::class => \DI\autowire(),
				// Processors.
				Processors\DropDown::class => \DI\autowire(),
				Processors\Colors::class => \DI\autowire(),
				Processors\Modal::class => \DI\autowire(),
			],
			$this->config
		);

		if ( $this->config['config.cache'] ) {
			$cache_key = 'container' . md5( json_encode( $this->config ) );

			if ( ! is_file( dirname( __DIR__ ) . '/cache/' . $cache_key . '.php' ) ) {
				$this->clearContainerCache();
			}

			self::$container_builder->enableCompilation(
				dirname( __DIR__ ) . '/cache',
				$cache_key
			);
		}

		self::$container_builder->addDefinitions( $this->config );

		self::$services = self::$container_builder->build();
	}
	/**
	 * Clear all compiled container files from the cache directory.
	 *
	 * @return void
	 */
	protected function clearContainerCache(): void
	{
		$files = glob( dirname( __DIR__ ) . '/cache/container*.php' );

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				unlink( $file );
			}
		}
	}

	/**
	 * Update the root URL used by URL-aware services.
	 *
	 * @param string $url Plugin directory URL.
	 *
	 * @return void
	 */
	public function setUrl( string $url ): void
	{
		if ( ! did_action( 'navigation_block_enhancements_loaded' ) ) {
			$this->config['config.url'] = $url;
		} else {
			self::$services->get( Services\UrlResolver::class )->setUrl( $url );
			self::$services->set( 'config.url', $url );
		}
	}

	/**
	 * Update the root path used by file path-aware services.
	 *
	 * @param string $path Plugin directory path.
	 *
	 * @return void
	 */
	public function setPath( string $path ): void
	{
		if ( ! did_action( 'navigation_block_enhancements_loaded' ) ) {
			$this->config['config.path'] = $path;
		} else {
			self::$services->get( Services\FilePathResolver::class )->setDir( $path );
			self::$services->set( 'config.path', $path );
		}
	}

	/**
	 * Register all plugin hooks.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		if ( ! self::$services instanceof \DI\Container ) {
			$this->initContainer();
		}

		self::$services->set( 'config.path', ! empty( $this->config['config.path'] ) ? $this->config['config.path'] : Utilities::getPath() );
		self::$services->set( 'config.url', ! empty( $this->config['config.url'] ) ? $this->config['config.url'] : Utilities::getUrl() );

		$this->mountActions();
		$this->mountFilters();

		do_action( 'navigation_block_enhancements_loaded' );
	}

	/**
	 * Register WordPress actions.
	 *
	 * @return void
	 */
	protected function mountActions(): void
	{
		add_action(
			'enqueue_block_editor_assets',
			[
				self::$services->get( Providers\AssetLoader::class ),
				'enqueueEditorAssets',
			]
		);
		add_action(
			'init',
			[
				self::$services->get( Providers\AssetLoader::class ),
				'enqueueBlockStyles',
			]
		);
	}

	/**
	 * Register WordPress filters.
	 *
	 * @return void
	 */
	protected function mountFilters(): void
	{
		add_filter(
			'render_block_core/navigation',
			[
				self::$services->get( Processors\DropDown::class ),
				'renderBlock',
			],
			10,
			2
		);
		add_filter(
			'render_block_core/navigation',
			[
				self::$services->get( Processors\Colors::class ),
				'renderBlock',
			],
			10,
			2
		);

		if ( apply_filters( 'navigation_block_enhancements_enable_dev_mode', false ) ) {
			add_filter(
				'render_block_core/navigation',
				[
					self::$services->get( Processors\Modal::class ),
					'removeFocusOut',
				],
				10,
				2
			);
		}
	}

	/**
	 * Get a service from the initialized container.
	 *
	 * @param string $service Service class or entry name.
	 *
	 * @return object|null Service instance, or null before bootstrap has run.
	 */
	public static function getInstance( string $service ): ?object
	{
		return self::$services instanceof \DI\Container && self::$services->has( $service )
			? self::$services->get( $service )
			: null;
	}
}
