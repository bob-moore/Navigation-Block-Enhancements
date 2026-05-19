<?php
/**
 * Bootstrap file for unit tests.
 *
 * @package Bmd_NavBlockEnhancements
 */
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

require_once dirname( __FILE__ ) . '/wp-function-mocks.php';

define( 'TEST_UNIT_PACKAGE_NAME', 'navigation_block_enhancements' );

WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();
