=== Navigation Block Enhancements ===
Contributors: Bob Moore
Tags: navigation, gutenberg, block editor, menus, accessibility
Requires at least: 6.7
Tested up to: 6.7
Stable tag: 0.2.0
Requires PHP: 8.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enhances the core Navigation block output for vertical and overlay menu behavior.

== Description ==

Navigation Block Enhancements adjusts rendered markup for the core Navigation block to improve behavior in vertical and responsive overlay contexts.

This plugin currently:

* Removes focusout handlers from the Navigation block subtree for vertical layouts.
* Keeps submenu behavior consistent for vertical navigation output.
* Enqueues shared Navigation block styles from the plugin build assets.
* Provides a reusable service class that can also be consumed through Composer in your own plugin or theme.
* Provides a `BasicPlugin` interface for type-safe integration in consuming packages.

== Installation ==

= Install as a WordPress plugin =

1. Build assets with `npm run build`.
2. Package with `npm run plugin-zip` or zip this plugin directory.
3. In WordPress admin, go to Plugins > Add New Plugin > Upload Plugin.
4. Upload and activate Navigation Block Enhancements.

= Install via Composer in your own plugin or theme =

1. Add the package as a dependency in your consuming project:

`composer require bmd/navigation-block-enhancements`

2. Ensure your project loads Composer autoloading (for example, `require_once __DIR__ . '/vendor/autoload.php';`).
3. Instantiate and hook the service in your bootstrap code:

`use Bmd\NavBlockEnhancements;`
`use Bmd\BasicPlugin;`
`$enhancements = new NavBlockEnhancements();`
`/** @var BasicPlugin $enhancements */`
`$enhancements->mount();`

== Frequently Asked Questions ==

= Does this register a custom block? =

No. It enhances output of the existing core/navigation block.

= Can I use this without activating the plugin? =

Yes. Because `composer.json` defines this package as a `library`, you can include it in your own plugin or theme and wire hooks yourself.

= What PHP and WordPress versions are required? =

WordPress 6.7+ and PHP 8.2+.

== Changelog ==

= 0.2.0 =

* Added GitHub-based auto-update support via scoped `bmd/github-wp-updater` dependency.
* Plugin will now surface updates through the WordPress admin when new releases are published on GitHub.

= 0.1.4 =

* Added a `BasicPlugin` interface for type-safe usage in consuming packages.
* Updated `NavBlockEnhancements` to implement the interface.
* Updated documentation and release metadata for 0.1.4.

= 0.1.3 =

* Bumped plugin and Composer package versions to 0.1.3.
* Updated documentation to reflect current package usage and release metadata.

= 0.1.1 =

* Added `mount()` to register hooks in one call.
* Updated plugin bootstrap flow to use the service mount method.

= 0.1.0 =

* Initial release.
* Added core/navigation output processing for vertical layouts.
* Added shared Navigation block stylesheet enqueue from build assets.

== Upgrade Notice ==

= 0.1.4 =

Adds the `BasicPlugin` interface and updates integration docs.

= 0.1.3 =

Documentation and version metadata update.

= 0.1.0 =

Initial release.