<?php
/**
 * Plugin Name:       Navigation Block Enhancements
 * Plugin URI:        https://github.com/bob-moore/navigation-block-enhancements
 * Author:            Bob Moore
 * Author URI:        https://www.bobmoore.dev
 * Description:       Adds rendering enhancements for the core Navigation block.
 * Version:           0.1.3
 * Requires at least: 6.7
 * Tested up to:      6.7
 * Requires PHP:      8.2
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       navigation-block-enhancements
 *
 * @package           navigation-block-enhancements
 */

use Bmd\NavBlockEnhancements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

function create_navigation_block_enhancements_plugin(): void
{
	$plugin = new NavBlockEnhancements();

	$plugin->mount(
		plugin_dir_url( __FILE__ ),
		plugin_dir_path( __FILE__ )
	);
}
