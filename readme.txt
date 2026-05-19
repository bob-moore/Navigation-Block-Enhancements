=== Navigation Block Enhancements ===
Contributors: Bob Moore
Tags: navigation, gutenberg, block editor, menus, colors
Requires at least: 6.9
Tested up to: 7.0
Stable tag: 0.3.0
Requires PHP: 8.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enhance the core Navigation block with better vertical submenu behavior and hover/focus color controls.

== Description ==

Navigation Block Enhancements improves the WordPress Navigation block (`core/navigation`) without registering a custom replacement block.

This plugin currently:

* Improves click-open submenu behavior for vertical Navigation blocks.
* Removes Navigation block focusout handlers from vertical menu output so submenus do not collapse unexpectedly while users move through the menu.
* Styles vertical click-open submenus as in-flow menu sections.
* Provides an opt-in development mode that keeps responsive Navigation overlays open while inspecting them in browser developer tools.
* Replaces the core sibling submenu icon with a generated chevron on the clickable Navigation toggle.
* Adds Text - Hover and Background - Hover controls to the Navigation block Color panel.
* Supports alpha values, clearing, Reset All, editor preview, and frontend rendering for hover/focus colors.
* Applies hover/focus colors to `:hover`, `:focus`, and `:focus-visible` states on Navigation item links.
* Outputs CSS custom properties (`--core-nav-focus-color`, `--core-nav-focus-background-color`) on the Navigation wrapper.
* Surfaces GitHub release updates in WordPress admin.
* Ships plugin banner and icon assets for release/update metadata.
* Provides a reusable `Controller` class for Composer-based embedding in other plugins or themes.

== Installation ==

= Install as a WordPress plugin =

1. Download the latest release zip from GitHub releases.
2. In WordPress admin, go to Plugins > Add New Plugin > Upload Plugin.
3. Upload and activate Navigation Block Enhancements.

= Build from source =

1. Install dependencies with `npm ci` and `composer install`.
2. Build assets with `npm run build`.
3. Package with `npm run plugin-zip`.
4. Upload the generated zip in WordPress admin.

= Install via Composer in your own plugin or theme =

1. Add the package as a dependency in your consuming project:

`composer require bmd/navigation-block-enhancements`

2. Ensure your project loads Composer autoloading.
3. Instantiate and mount the controller in your bootstrap code:

`use Bmd\NavBlockEnhancements\Controller;`
`$plugin = new Controller( $dependency_url, $dependency_path, false );`
`$plugin->mount();`

The constructor expects the public URL and filesystem path pointing to the Navigation Block Enhancements dependency root. Leave the third argument `false` for Composer-embedded usage unless your project manages its own compiled container cache.

== Frequently Asked Questions ==

= Does this register a custom block? =

No. It enhances the existing core/navigation block.

= Where are the hover color controls? =

Select a Navigation block, open the block sidebar, and open the Color panel. The plugin adds Text - Hover and Background - Hover controls.

= Which states use the selected colors? =

Colors apply to Navigation item `:hover`, `:focus`, and `:focus-visible` states.

= How do I keep the responsive overlay open while debugging? =

Enable development mode with this filter:

`add_filter( 'navigation_block_enhancements_enable_dev_mode', '__return_true' );`

When enabled, the plugin removes the overlay container focusout handler so the overlay does not close when browser developer tools receive focus. This processing is skipped when the Navigation block overlay menu setting is `never`.

= Can I use this without activating it as a plugin? =

Yes. This package can be required through Composer and mounted from another plugin or theme with `Bmd\NavBlockEnhancements\Controller`.

= What PHP and WordPress versions are required? =

WordPress 6.9+ and PHP 8.2+.

== Changelog ==

= 1.0.0 =

* Rebuilt the plugin around a focused PHP-DI controller and provider/processor services.
* Added editor controls for Navigation item hover/focus text and background colors.
* Added editor preview and frontend rendering for Navigation hover/focus colors.
* Added CSS custom properties for Navigation hover/focus colors.
* Improved vertical click-open submenu behavior for the core Navigation block.
* Added an opt-in dev-mode filter for keeping responsive Navigation overlays open while inspecting them.
* Replaced the core sibling submenu icon with a generated chevron on the clickable Navigation toggle.
* Added GitHub release update support and plugin directory-style image assets.
* Scoped release dependencies to reduce conflicts with other plugins.

== Upgrade Notice ==

= 1.0.0 =

Adds Navigation hover/focus color controls and a rebuilt controller/provider/processor architecture.
