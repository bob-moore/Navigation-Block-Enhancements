<?php
/**
 * Base module class.
 *
 * @package Bmd\NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements;

use DI\Attribute\Inject;

/**
 * Abstract base for all injectable plugin classes.
 *
 * Provides package slug injection from the DI container so that every
 * module can use it in filter/action names and asset handles without
 * receiving it manually through a constructor chain.
 */
abstract class Module
{
	#[Inject( 'package' )]
	protected string $package = '';
}
