<?php
/**
 * Plugin Name:       Navigation Block Enhancements
 * Plugin URI:        https://github.com/bob-moore/navigation-block-enhancements
 * Author:            Bob Moore
 * Author URI:        https://www.bobmoore.dev
 * Description:       Adds rendering enhancements for the core Navigation block.
 * Version:           0.1.1
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

/**
 * All plugin functionality is contained in this class so it can be consumed
 * through Composer without automatically registering WordPress hooks.
 */
$plugin = new NavBlockEnhancements();
$plugin->mount();
