<?php
/**
 * Application entry point — builds the DI container and mounts all services.
 *
 * @package Bmd\NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements;

/**
 * Owns the DI container, builds it once, then delegates hook registration
 * to Controller via PHP-DI method injection.
 */
class Main
{
	/**
	 * Shared service container, built once per request.
	 *
	 * @var \DI\Container|null
	 */
	protected static ?\DI\Container $services = null;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $config Configuration overrides.
	 */
	public function __construct( protected array $config = [] )
	{
		$this->setConfig( $config );
	}

	/**
	 * Merge provided config with defaults.
	 *
	 * @param array<string, mixed> $config Configuration overrides.
	 *
	 * @return void
	 */
	public function setConfig( array $config ): void
	{
		$this->config = array_merge(
			[
				'environment' => wp_get_environment_type(),
				'package'     => 'navigation_block_enhancements',
				'path'        => Utilities::getPath(),
				'url'         => Utilities::getUrl(),
			],
			$config
		);
	}

	/**
	 * Definition file loaded into the container.
	 *
	 * @return string
	 */
	protected function getDefinitionsFile(): string
	{
		return __DIR__ . '/definitions.php';
	}

	/**
	 * Directory where compiled container files are stored.
	 *
	 * @return string
	 */
	protected function getContainerCacheDirectory(): string
	{
		return dirname( __DIR__ ) . '/cache';
	}

	/**
	 * Normalize config values before hashing them into a cache key.
	 *
	 * @param mixed $value Config value.
	 *
	 * @return mixed
	 */
	protected function normalizeCacheKeyValue( mixed $value ): mixed
	{
		if ( is_array( $value ) ) {
			ksort( $value );

			return array_map( [ $this, 'normalizeCacheKeyValue' ], $value );
		}

		if ( is_object( $value ) ) {
			return get_class( $value );
		}

		if ( is_resource( $value ) ) {
			return get_resource_type( $value );
		}

		return $value;
	}

	/**
	 * Build the cache key used in the compiled container class name.
	 *
	 * @return string
	 */
	protected function getContainerCacheKey(): string
	{
		$definitions_file = $this->getDefinitionsFile();

		return substr(
			hash(
				'sha256',
				serialize(
					[
						'config'      => $this->normalizeCacheKeyValue( $this->config ),
						'definitions' => is_file( $definitions_file )
							? filemtime( $definitions_file ) . ':' . filesize( $definitions_file )
							: null,
					]
				)
			),
			0,
			16
		);
	}

	/**
	 * Get the compiled container class name for the current config.
	 *
	 * @return string
	 */
	protected function getCompiledContainerClass(): string
	{
		return $this->config['package'] . '_' . $this->getContainerCacheKey();
	}

	/**
	 * Determine whether the compiled container can be used or generated.
	 *
	 * @param string $cache_dir Cache directory.
	 * @param string $class     Compiled container class name.
	 *
	 * @return bool
	 */
	protected function canUseCompiledContainer( string $cache_dir, string $class ): bool
	{
		if ( is_readable( "{$cache_dir}/{$class}.php" ) ) {
			return true;
		}

		return is_dir( $cache_dir )
			? is_writable( $cache_dir )
			: wp_mkdir_p( $cache_dir ) && is_writable( $cache_dir );
	}

	/**
	 * Build the DI container.
	 *
	 * @return void
	 */
	protected function initContainer(): void
	{
		$builder = new \DI\ContainerBuilder();
		$builder->useAttributes( true );

		if ( 'production' === $this->config['environment'] ) {
			$cache_dir       = $this->getContainerCacheDirectory();
			$container_class = $this->getCompiledContainerClass();

			if ( $this->canUseCompiledContainer( $cache_dir, $container_class ) ) {
				$builder->enableCompilation( $cache_dir, $container_class );
			}
		}

		$builder->addDefinitions( $this->config );
		$builder->addDefinitions( $this->getDefinitionsFile() );

		self::$services = $builder->build();
	}

	/**
	 * Initialize the container (if needed) then mount the controller.
	 *
	 * @return void
	 */
	public function mount(): void
	{
		if ( ! self::$services instanceof \DI\Container ) {
			$this->initContainer();
		}

		self::$services->get( Controller::class );

		do_action( "{$this->config['package']}_loaded" );
	}

	/**
	 * Set or replace a service in the built container.
	 *
	 * @param string $key   Service entry key.
	 * @param mixed  $value Service instance or value.
	 *
	 * @return void
	 * @throws \LogicException When the container has not been built.
	 */
	public static function setInstance( string $key, mixed $value ): void
	{
		if ( ! self::$services instanceof \DI\Container ) {
			throw new \LogicException( 'Cannot set service before container is built.' );
		}

		self::$services->set( $key, $value );
	}

	/**
	 * Get a service instance from the container.
	 *
	 * @param string $service Fully-qualified class name or container entry key.
	 *
	 * @return object|null The service, or null if the container is not yet built.
	 */
	public static function getInstance( string $service ): ?object
	{
		return self::$services instanceof \DI\Container && self::$services->has( $service )
			? self::$services->get( $service )
			: null;
	}
}
