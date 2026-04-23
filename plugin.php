<?php
/**
 * Plugin Name:       Navigation Block Enhancements
 * Plugin URI:        https://github.com/bob-moore/navigation-block-enhancements
 * Author:            Bob Moore
 * Author URI:        https://www.bobmoore.dev
 * Description:       Adds rendering enhancements for the core Navigation block.
 * Version:           0.2.0
 * Requires at least: 7.0
 * Tested up to:      7.0
 * Requires PHP:      8.2
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       navigation-block-enhancements
 *
 * @package           navigation-block-enhancements
 */

use Bmd\NavBlockEnhancements;
use Bmd\NavBlockEnhancements\Bmd\GithubWpUpdater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/scoped/autoload.php';

function create_navigation_block_enhancements_plugin(): void
{
	$plugin = new NavBlockEnhancements(
		plugin_dir_url( __FILE__ ),
		plugin_dir_path( __FILE__ )
	);

	$plugin->mount();

	$updater = new GithubWpUpdater(
		__FILE__,
		[
			'github.user'   => 'bob-moore',
			'github.repo'   => 'navigation-block-enhancements',
			'github.branch' => 'main',
		]
	);

	$updater->mount();
}
create_navigation_block_enhancements_plugin();
