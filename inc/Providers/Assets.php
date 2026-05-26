<?php
/**
 * Asset provider.
 *
 * @package Bmd\NavBlockEnhancements
 * @author  Bob Moore <bob@bobmoore.dev>
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link    https://github.com/bob-moore/Navigation-Block-Enhancements
 */

namespace Bmd\NavBlockEnhancements\Providers;

use Bmd\NavBlockEnhancements\Module;
use Bmd\NavBlockEnhancements\Services;

/**
 * Enqueues editor controls and shared block styles.
 */
class Assets extends Module
{
	protected const EDITOR_SCRIPT_HANDLE = 'navigation-block-enhancements-editor';
	protected const FRONTEND_SCRIPT_HANDLE = 'navigation-block-enhancements-frontend';
	protected const FRONTEND_STYLE_HANDLE  = 'navigation-block-enhancements-frontend-styles';
	protected const ADMIN_SCRIPT_HANDLE    = 'navigation-block-enhancements-admin';
	protected const ADMIN_STYLE_HANDLE     = 'navigation-block-enhancements-admin-styles';
	protected const BLOCK_STYLE_HANDLE     = 'navigation-block-enhancements-styles';

	/**
	 * Constructor.
	 *
	 * @param Services\ScriptLoader $script_loader Script loader service.
	 * @param Services\StyleLoader  $style_loader  Style loader service.
	 */
	public function __construct(
		protected Services\ScriptLoader $script_loader,
		protected Services\StyleLoader $style_loader,
	) {
	}

	/**
	 * Enqueue editor script.
	 *
	 * @return void
	 */
	public function enqueueEditorAssets(): void
	{
		$this->script_loader->enqueue(
			handle: self::EDITOR_SCRIPT_HANDLE,
			src: 'build/editor.js'
		);
	}

	/**
	 * Enqueue frontend script and styles.
	 *
	 * @return void
	 */
	public function enqueueFrontendAssets(): void
	{
		$this->script_loader->enqueue(
			handle: self::FRONTEND_SCRIPT_HANDLE,
			src: 'build/frontend.js'
		);

		$this->style_loader->enqueue(
			handle: self::FRONTEND_STYLE_HANDLE,
			src: 'build/frontend.css'
		);
	}

	/**
	 * Enqueue admin script and styles.
	 *
	 * @return void
	 */
	public function enqueueAdminAssets(): void
	{
		$this->script_loader->enqueue(
			handle: self::ADMIN_SCRIPT_HANDLE,
			src: 'build/admin.js'
		);

		$this->style_loader->enqueue(
			handle: self::ADMIN_STYLE_HANDLE,
			src: 'build/admin.css'
		);
	}

	/**
	 * Register a block-specific stylesheet for the core Navigation block.
	 *
	 * @return void
	 */
	public function enqueueBlockStyles(): void
	{
		$this->style_loader->enqueueBlockStyle(
			block_name: 'core/navigation',
			handle: self::BLOCK_STYLE_HANDLE,
			src: 'build/style.css',
		);
	}
}
