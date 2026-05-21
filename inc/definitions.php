<?php
/**
 * PHP-DI service definitions.
 *
 * @package Bmd\NavBlockEnhancements
 */

namespace Bmd\NavBlockEnhancements;

return [
	Controller::class                => \DI\autowire(),
	// Services
	Services\FilePathResolver::class => \DI\autowire(),
	Services\UrlResolver::class      => \DI\autowire(),
	Services\ScriptLoader::class     => \DI\autowire(),
	Services\StyleLoader::class      => \DI\autowire(),
	// Providers
	Providers\Assets::class          => \DI\autowire(),
	// Processors
	Processors\DropDown::class       => \DI\autowire(),
	Processors\Colors::class         => \DI\autowire(),
	Processors\Modal::class          => \DI\autowire(),
];
