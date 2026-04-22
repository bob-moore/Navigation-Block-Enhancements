# Navigation Block Enhancements

Navigation Block Enhancements is a WordPress plugin and Composer library that modifies rendered output for the core Navigation block and enqueues shared navigation styles.

## What It Does

- Hooks into `render_block_core/navigation` and processes navigation markup with `WP_HTML_Tag_Processor`.
- For vertical navigation output, removes `data-wp-on--focusout` handlers in the menu subtree.
- Registers block-scoped styles for `core/navigation` from this package's `build/style-index.css`.
- Resolves build file URLs from either `WP_CONTENT_DIR` or `ABSPATH`, which supports Composer usage in different project layouts.

## Requirements

- WordPress 6.7+
- PHP 8.2+

## Installation

### Use as a standard plugin

1. Build assets:

```bash
npm run build
```

2. Package:

```bash
npm run plugin-zip
```

3. Upload and activate in wp-admin: Plugins > Add New Plugin > Upload Plugin.

### Use via Composer in your own plugin or theme

This package is published as a Composer `library` in `composer.json`:

```json
{
  "name": "bmd/navigation-block-enhancements",
  "type": "library"
}
```

1. Require it in your consuming project:

```bash
composer require bmd/navigation-block-enhancements
```

2. Load Composer autoloading in your bootstrap (if not already loaded):

```php
require_once __DIR__ . '/vendor/autoload.php';
```

3. Instantiate and register hooks where appropriate:

```php
use Bmd\NavBlockEnhancements;

$enhancements = new NavBlockEnhancements();

$enhancements->mount();
```

## Changelog

### 0.2.0

- Added `mount()` method to `NavBlockEnhancements` that registers all WordPress hooks in one call (`enqueue_block_assets` and `render_block_core/navigation`).
- Simplified plugin bootstrap: replaced individual `add_action`/`add_filter` calls with `$plugin->mount()`.
- When using the library via Composer, call `$enhancements->mount()` after instantiation instead of wiring hooks manually.

### 0.1.0

- Initial release.
- Added vertical navigation output processing.
- Added shared navigation stylesheet enqueue for core/navigation.

## License

GPL-2.0-or-later. See https://www.gnu.org/licenses/gpl-2.0.html.