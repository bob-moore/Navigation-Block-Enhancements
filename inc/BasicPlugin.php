<?php
/**
 * Interface for basic plugin structure.
 *
 * PHP Version 8.2
 *
 * @package    Bmd\NavigationBlockEnhancements
 * @author     Bob Moore <bob@bobmoore.dev>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       https://www.bobmoore.dev
 * @since      1.0.0
 */

namespace Bmd;

/**
 * Interface for basic plugin structure.
 */
interface BasicPlugin
{
    /**
    * Mount the plugin.
    *
    * @return void
    */
    public function mount(): void;
    /**
    * Setter for the URL property.
    *
    * @param string $url string URL to set.
    *
    * @return void
    */
    public function setUrl( string $url ): void;

    /**
    * Setter for the path property.
    *
    * @param string $path string path to set.
    *
    * @return void
    */
    public function setPath( string $path ): void;
}