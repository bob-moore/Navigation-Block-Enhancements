<?php
/**
 * Package utility helpers.
 *
 * @package Bmd\NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements;

/**
 * Utility methods for resolving package paths, URLs, and general helpers.
 */
class Utilities
{
	/**
	 * Get the package root path.
	 *
	 * @return string Absolute normalized path to the package root.
	 */
	public static function getPath(): string
	{
		return wp_normalize_path( dirname( __DIR__ ) );
	}

	/**
	 * Get the package URL.
	 *
	 * @return string Absolute URL to the package root.
	 */
	public static function getUrl(): string
	{
		$path = wp_normalize_path( dirname( __DIR__ ) );

		return 'theme' === self::getUsageContext()
			? self::buildThemeUrl( $path )
			: plugin_dir_url( $path );
	}

	/**
	 * Build a theme URL for a package located inside the active theme.
	 *
	 * @param string $path Absolute package path.
	 *
	 * @return string Absolute URL to the package path.
	 */
	protected static function buildThemeUrl( string $path = '' ): string
	{
		$relative_path = str_replace(
			get_theme_file_path(),
			'',
			$path
		);

		return get_theme_file_uri( $relative_path );
	}

	/**
	 * Determine whether the package is loaded from a plugin or theme.
	 *
	 * @return string Either `plugin` or `theme`.
	 */
	protected static function getUsageContext(): string
	{
		$path = wp_normalize_path( dirname( __DIR__ ) );

		return str_contains( $path, get_theme_file_path() )
			? 'theme'
			: 'plugin';
	}

	/**
	 * Check if a class uses a specified parent class, interface, or trait.
	 *
	 * @param string|object $instance_or_class Class or object to check.
	 * @param string        $needle            Interface, class, or trait name.
	 *
	 * @return bool
	 */
	public static function classUses( $instance_or_class, string $needle ): bool
	{
		$class_name = self::className( $instance_or_class );

		if ( empty( $class_name ) ) {
			return false;
		}

		switch ( true ) {
			case class_exists( $needle ):
				return is_subclass_of( $class_name, $needle ) || $class_name === $needle;
			case interface_exists( $needle ):
				return self::implements( $class_name, $needle );
			case trait_exists( $needle ):
				return self::uses( $class_name, $needle );
			default:
				return false;
		}
	}

	/**
	 * Get the string class name of an object or name.
	 *
	 * @param string|object $instance_or_class Either an object or class name.
	 *
	 * @return string|false
	 */
	public static function className( $instance_or_class )
	{
		switch ( true ) {
			case is_object( $instance_or_class ):
				$name = get_class( $instance_or_class );
				break;
			case is_string( $instance_or_class ) && '' !== $instance_or_class && class_exists( $instance_or_class ):
				$name = $instance_or_class;
				break;
			default:
				$name = false;
		}

		return $name;
	}

	/**
	 * Recursively check if a class uses a given trait.
	 *
	 * @param string|object $instance_or_class Instance or class name.
	 * @param string        $trait_class       Trait name to check.
	 *
	 * @return bool
	 */
	public static function uses( $instance_or_class, string $trait_class ): bool
	{
		$class_name = is_object( $instance_or_class ) ? get_class( $instance_or_class ) : $instance_or_class;

		if ( ! is_string( $class_name ) || '' === $class_name || ! class_exists( $class_name ) ) {
			return false;
		}

		return in_array( $trait_class, self::getTraits( $class_name ), true );
	}

	/**
	 * Check if a class implements an interface.
	 *
	 * @param string|object $instance_or_class Instance or class name.
	 * @param string        $interface_class   Interface class to check against.
	 *
	 * @return bool
	 */
	public static function implements( $instance_or_class, string $interface_class ): bool
	{
		$class_name = is_object( $instance_or_class ) ? get_class( $instance_or_class ) : $instance_or_class;

		if ( ! is_string( $class_name ) || '' === $class_name || ! class_exists( $class_name ) ) {
			return false;
		}

		return in_array( $interface_class, class_implements( $class_name ), true );
	}

	/**
	 * Recursively get all traits used by a class and its parents.
	 *
	 * @param object|string $instance_or_class Class to check.
	 *
	 * @return array<string>
	 */
	public static function getTraits( $instance_or_class ): array
	{
		$class_name = is_object( $instance_or_class ) ? get_class( $instance_or_class ) : $instance_or_class;

		if ( ! is_string( $class_name ) || '' === $class_name || ! class_exists( $class_name ) ) {
			return [];
		}

		$traits  = self::usesTrait( $class_name );
		$parents = class_parents( $class_name );

		if ( ! empty( $parents ) ) {
			foreach ( $parents as $parent ) {
				$traits += self::usesTrait( $parent );
			}
		}

		return array_unique( $traits );
	}

	/**
	 * Get traits used directly by a single class.
	 *
	 * @param string|object $instance_or_class Class to check.
	 *
	 * @return array<string>
	 */
	public static function usesTrait( $instance_or_class ): array
	{
		$class_name = is_object( $instance_or_class ) ? get_class( $instance_or_class ) : $instance_or_class;

		if ( ! is_string( $class_name ) || '' === $class_name || ! class_exists( $class_name ) ) {
			return [];
		}

		$traits = class_uses( $class_name );

		return ! empty( $traits ) ? $traits : [];
	}

	/**
	 * Check if an array is a sequential list (not associative).
	 *
	 * @param mixed $array Array to check.
	 *
	 * @return bool
	 */
	public static function isList( $array ): bool
	{
		if ( ! is_array( $array ) ) {
			return false;
		}

		if ( empty( $array ) ) {
			return true;
		}

		if ( function_exists( 'array_is_list' ) ) {
			return array_is_list( $array );
		}

		return array_keys( $array ) === range( 0, count( $array ) - 1 );
	}

	/**
	 * Transform a value into a boolean.
	 *
	 * @param mixed $value Value to transform.
	 *
	 * @return bool
	 */
	public static function truthyFalsy( $value ): bool
	{
		switch ( true ) {
			case is_bool( $value ):
				return $value;
			case is_string( $value ):
				return in_array( strtolower( $value ), [ 'true', 'yes', 'on', '1' ], true );
			case is_numeric( $value ):
				return (bool) $value;
			default:
				return false;
		}
	}

	/**
	 * Check if a particular plugin is active.
	 *
	 * @param string $plugin dir/name.php of the plugin to check.
	 *
	 * @return bool
	 */
	public static function isPluginActive( string $plugin ): bool
	{
		if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_PLUGIN_DIR' ) ) {
			return false;
		}

		include_once \ABSPATH . 'wp-admin/includes/plugin.php';

		return is_file( WP_PLUGIN_DIR . '/' . $plugin ) && is_plugin_active( $plugin );
	}

	/**
	 * Replace slashes, spaces, hyphens, and camelCase transitions with
	 * underscores, then convert to lowercase.
	 *
	 * @param string $raw_string String to slugify.
	 *
	 * @return string
	 */
	public static function slugify( string $raw_string ): string
	{
		$string = str_replace( [ '\\', '/', ' ', '-' ], '_', $raw_string );
		$string = preg_replace( '/([a-z0-9])([A-Z])/', '$1_$2', $string );
		$string = preg_replace( '/_+/', '_', $string );

		return strtolower( $string );
	}

	/**
	 * Replace spaces, underscores, and slashes with hyphens and convert to lowercase.
	 *
	 * @param string $raw_string String to hyphenate.
	 *
	 * @return string
	 */
	public static function hyphenate( string $raw_string ): string
	{
		return strtolower( str_replace( [ '\\', '/', ' ', '_' ], '-', $raw_string ) );
	}

	/**
	 * Recursively merge two arrays, deep-merging nested arrays by key.
	 *
	 * @param array<mixed> $array1 First array.
	 * @param array<mixed> $array2 Second array.
	 *
	 * @return array<mixed>
	 */
	public static function arrayMerge( array $array1, array $array2 ): array
	{
		$merged = $array1;

		foreach ( $array2 as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = self::arrayMerge( $merged[ $key ], $value );
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}
}
