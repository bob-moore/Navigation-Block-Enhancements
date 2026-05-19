<?php
/**
 * Bootstrap this starter into a named WordPress plugin.
 *
 * @package Placeholder_Plugin
 */

declare(strict_types=1);

$root = dirname(__DIR__);

$options = getopt(
	'',
	[
		'name:',
		'slug:',
		'namespace:',
		'package:',
		'description:',
		'author:',
		'author-email:',
		'plugin-uri:',
		'author-uri:',
		'block-namespace:',
		'main-file:',
		'dry-run',
		'force',
		'help',
	]
);

if (isset($options['help'])) {
	echo <<<HELP
Usage:
  composer run create-plugin
  composer run create-plugin -- --name="My Plugin" --slug="my-plugin" --namespace="Acme\\MyPlugin"

Options:
  --name             Plugin display name. Required in non-interactive shells.
  --slug             Plugin slug and text-domain base. Defaults from --name.
  --namespace        Root PHP namespace. Defaults from --name.
  --package          Composer package name. Defaults to {slug}/{slug}.
  --description      Plugin description.
  --author           Plugin author name.
  --author-email     Plugin author email.
  --plugin-uri       Plugin URI.
  --author-uri       Author URI.
  --block-namespace  Block namespace prefix. Defaults to the slug.
  --main-file        Main plugin file. Defaults to {slug}.php.
  --dry-run          Show what would change without writing files.
  --force            Allow overwriting an existing target main file.
  --help             Show this help text.

HELP;
	exit(0);
}

$interactive = function_exists('stream_isatty') ? stream_isatty(STDIN) : true;

try {
	$config = build_config($options, $interactive);
	bootstrap_plugin($root, $config, isset($options['dry-run']), isset($options['force']));
} catch (Throwable $exception) {
	fwrite(STDERR, "\nError: {$exception->getMessage()}\n");
	exit(1);
}

/**
 * @param array<string, mixed> $options
 *
 * @return array<string, string>
 */
function build_config(array $options, bool $interactive): array
{
	$name = get_value($options, 'name', 'Plugin name', '', $interactive);

	if ('' === $name) {
		throw new RuntimeException('A plugin name is required. Pass --name="My Plugin" or run in an interactive shell.');
	}

	$slug            = get_value($options, 'slug', 'Plugin slug', slugify($name), $interactive);
	$namespace       = get_value($options, 'namespace', 'PHP namespace', studly_namespace($name), $interactive);
	$package         = get_value($options, 'package', 'Composer package', "{$slug}/{$slug}", $interactive);
	$description     = get_value($options, 'description', 'Description', 'A custom WordPress plugin.', $interactive);
	$author          = get_value($options, 'author', 'Author name', 'Plugin Author', $interactive);
	$author_email    = get_value($options, 'author-email', 'Author email', 'author@example.com', $interactive);
	$plugin_uri      = get_value($options, 'plugin-uri', 'Plugin URI', 'https://example.com', $interactive);
	$author_uri      = get_value($options, 'author-uri', 'Author URI', $plugin_uri, $interactive);
	$block_namespace = get_value($options, 'block-namespace', 'Block namespace', $slug, $interactive);
	$main_file       = get_value($options, 'main-file', 'Main plugin file', "{$slug}.php", $interactive);

	validate_slug($slug, 'Plugin slug');
	validate_slug($block_namespace, 'Block namespace');
	validate_namespace($namespace);
	validate_package($package);
	validate_main_file($main_file);

	$namespace_clean = trim($namespace, '\\');

	return [
		'name'                => $name,
		'slug'                => $slug,
		'slug_underscore'     => str_replace('-', '_', $slug),
		'namespace'           => $namespace_clean,
		'namespace_json'      => str_replace('\\', '\\\\', $namespace_clean),
		'namespace_doc'       => str_replace('\\', '_', $namespace_clean),
		'package'             => $package,
		'package_deps'        => "{$package}-dependencies",
		'description'         => $description,
		'description_text'    => rtrim($description, '.'),
		'description_sent'    => sentence($description),
		'author'              => $author,
		'author_email'        => $author_email,
		'author_full'         => "{$author} <{$author_email}>",
		'plugin_uri'          => $plugin_uri,
		'author_uri'          => $author_uri,
		'block_namespace'     => $block_namespace,
		'main_file'           => $main_file,
		'package_vendor'      => package_vendor($package),
		'project'             => package_project($package),
		'compiled_container'  => str_replace('\\', '', $namespace_clean) . 'CompiledContainer',
	];
}

/**
 * @param array<string, mixed> $options
 */
function get_value(array $options, string $key, string $label, string $default, bool $interactive): string
{
	if (isset($options[$key])) {
		return trim((string) $options[$key]);
	}

	if (! $interactive) {
		return $default;
	}

	$prompt = '' === $default ? "{$label}: " : "{$label} [{$default}]: ";
	$value  = prompt_input($prompt);

	if (false === $value || '' === trim($value)) {
		return $default;
	}

	return trim($value);
}

function prompt_input(string $prompt): string|false
{
	if (function_exists('readline')) {
		return readline($prompt);
	}

	fwrite(STDOUT, $prompt);

	return fgets(STDIN);
}

/**
 * @param array<string, string> $config
 */
function bootstrap_plugin(string $root, array $config, bool $dry_run, bool $force): void
{
	$source_main = "{$root}/Plugin.php";
	$target_main = "{$root}/{$config['main_file']}";

	if (! is_file($source_main) && ! is_file($target_main)) {
		throw new RuntimeException('Could not find Plugin.php or the configured main plugin file.');
	}

	if (is_file($source_main) && $source_main !== $target_main && is_file($target_main) && ! $force) {
		throw new RuntimeException("{$config['main_file']} already exists. Pass --force to overwrite it.");
	}

	$replacements = replacement_map($config);
	$files        = collect_files($root);
	$changed      = [];

	foreach ($files as $file) {
		$contents = file_get_contents($file);

		if (false === $contents) {
			throw new RuntimeException("Unable to read {$file}.");
		}

		$updated = str_replace(array_keys($replacements), array_values($replacements), $contents);

		if ($updated === $contents) {
			continue;
		}

		$changed[] = relative_path($root, $file);

		if (! $dry_run && false === file_put_contents($file, $updated)) {
			throw new RuntimeException("Unable to write {$file}.");
		}
	}

	if (is_file($source_main) && $source_main !== $target_main) {
		$changed[] = relative_path($root, $source_main) . ' -> ' . relative_path($root, $target_main);

		if (! $dry_run) {
			if (is_file($target_main) && $force && ! unlink($target_main)) {
				throw new RuntimeException("Unable to remove existing {$config['main_file']}.");
			}

			if (! rename($source_main, $target_main)) {
				throw new RuntimeException("Unable to rename Plugin.php to {$config['main_file']}.");
			}
		}
	}

	if ($dry_run) {
		echo "Dry run complete. Files that would change:\n";
	} else {
		echo "Plugin bootstrap complete.\n";
	}

	foreach (array_unique($changed) as $path) {
		echo " - {$path}\n";
	}

	echo "\nNext steps:\n";
	echo " - Run composer dump-autoload\n";
	echo " - Run composer install if scoped dependencies are not built yet\n";
	echo " - Run npm install && npm run compile if you need block/assets output\n";
}

/**
 * @param array<string, string> $config
 *
 * @return array<string, string>
 */
function replacement_map(array $config): array
{
	$map = [
		'Placeholder\\\\Plugin'              => $config['namespace_json'],
		'Placeholder\\Plugin'                => $config['namespace'],
		'Placeholder_Plugin'                 => $config['namespace_doc'],
		'Placeholder Plugin'                 => $config['name'],
		'placeholder/plugin-dependencies'    => $config['package_deps'],
		'placeholder/plugin'                 => $config['package'],
		'placeholder/example-block'          => "{$config['block_namespace']}/example-block",
		'PlaceholderPluginCompiledContainer' => $config['compiled_container'],
		'placeholder_plugin_loaded'          => "{$config['slug_underscore']}_loaded",
		'placeholder_plugin'                 => $config['slug_underscore'],
		'Starter plugin framework.'          => $config['description_sent'],
		'Starter plugin framework'           => $config['description_text'],
		'Plugin Author <author@example.com>' => $config['author_full'],
		'Plugin Author'                      => $config['author'],
		'author@example.com'                 => $config['author_email'],
		'Author URI:  https://example.com'   => "Author URI:  {$config['author_uri']}",
		'https://example.com'                => $config['plugin_uri'],
		'vendor/{PROJECT}'                   => "{$config['package_vendor']}/{$config['project']}",
		'{PROJECT}'                          => $config['project'],
		'"Plugin.php"'                       => "\"{$config['main_file']}\"",
		'Plugin.php'                         => $config['main_file'],
	];

	uksort(
		$map,
		static function (string $left, string $right): int {
			return strlen($right) <=> strlen($left);
		}
	);

	return $map;
}

/**
 * @return list<string>
 */
function collect_files(string $root): array
{
	$files    = [];
	$iterator = new RecursiveIteratorIterator(
		new RecursiveCallbackFilterIterator(
			new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
			static function (SplFileInfo $file): bool {
				$skip = [
					'.git',
					'.phpunit.cache',
					'.claude',
					'.codex',
					'.cursor',
					'build',
					'cache',
					'node_modules',
					'vendor',
					'_playground',
				];

				if ($file->isDir() && in_array($file->getFilename(), $skip, true)) {
					return false;
				}

				return ! ($file->isDir() && str_contains(str_replace('\\', '/', $file->getPathname()), '/.github/skills'));
			}
		)
	);

	foreach ($iterator as $file) {
		if (! $file instanceof SplFileInfo || ! $file->isFile()) {
			continue;
		}

		$path = $file->getPathname();

		if (should_skip_file($path)) {
			continue;
		}

		$files[] = $path;
	}

	sort($files);

	return $files;
}

function should_skip_file(string $path): bool
{
	$normalized = str_replace('\\', '/', $path);

	if (str_ends_with($normalized, '/scripts/create-plugin.php')) {
		return true;
	}

	$allowed_extensions = [
		'css',
		'js',
		'json',
		'md',
		'neon',
		'php',
		'scss',
		'ts',
		'tsx',
		'txt',
		'xml',
		'yml',
	];

	$extension = pathinfo($path, PATHINFO_EXTENSION);

	if (in_array($extension, $allowed_extensions, true)) {
		return false;
	}

	return ! str_ends_with($normalized, '.code-workspace');
}

function validate_slug(string $slug, string $label): void
{
	if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
		throw new RuntimeException("{$label} must use lowercase letters, numbers, and hyphens.");
	}
}

function validate_namespace(string $namespace): void
{
	if (! preg_match('/^[A-Z_][A-Za-z0-9_]*(?:\\\\[A-Z_][A-Za-z0-9_]*)*$/', trim($namespace, '\\'))) {
		throw new RuntimeException('PHP namespace must be a valid namespace such as Acme\\MyPlugin.');
	}
}

function validate_package(string $package): void
{
	if (! preg_match('/^[a-z0-9_.-]+\/[a-z0-9_.-]+$/', $package)) {
		throw new RuntimeException('Composer package must look like vendor/package.');
	}
}

function validate_main_file(string $main_file): void
{
	if (! preg_match('/^[a-z0-9][a-z0-9_-]*\\.php$/', $main_file)) {
		throw new RuntimeException('Main plugin file must be a lowercase PHP filename such as my-plugin.php.');
	}
}

function slugify(string $value): string
{
	$value = strtolower(trim($value));
	$value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
	$value = trim($value, '-');

	return '' === $value ? 'custom-plugin' : $value;
}

function studly_namespace(string $value): string
{
	return studly($value);
}

function studly(string $value): string
{
	$parts = preg_split('/[^A-Za-z0-9]+/', $value, -1, PREG_SPLIT_NO_EMPTY);

	if (! is_array($parts) || [] === $parts) {
		return 'CustomPlugin';
	}

	return implode('', array_map(static fn (string $part): string => ucfirst(strtolower($part)), $parts));
}

function sentence(string $value): string
{
	$value = trim($value);

	if ('' === $value) {
		return '';
	}

	return preg_match('/[.!?]$/', $value) ? $value : "{$value}.";
}

function package_project(string $package): string
{
	$parts = explode('/', $package);

	return $parts[1] ?? $package;
}

function package_vendor(string $package): string
{
	$parts = explode('/', $package);

	return $parts[0] ?? 'vendor';
}

function relative_path(string $root, string $path): string
{
	return ltrim(str_replace($root, '', $path), '/');
}
