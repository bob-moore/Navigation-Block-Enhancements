<?php
/**
 * DropDown transformer tests.
 *
 * @package Bmd_NavBlockEnhancements
 */

namespace {
	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		/**
		 * Minimal processor double for exercising the transformer outside WordPress.
		 */
		class WP_HTML_Tag_Processor
		{
			/**
			 * Markup under transformation.
			 *
			 * @var string
			 */
			private string $html;

			/**
			 * Whether the processor has yielded its test tag.
			 *
			 * @var bool
			 */
			private bool $has_yielded_tag = false;

			/**
			 * @param string $html Markup to transform.
			 */
			public function __construct( string $html )
			{
				$this->html = $html;
			}

			/**
			 * @param mixed $query Ignored test query.
			 * @return bool Whether a tag is available.
			 */
			public function next_tag( $query = null ): bool
			{
				if ( $this->has_yielded_tag ) {
					return false;
				}

				$this->has_yielded_tag = true;
				return true;
			}

			/**
			 * @return string Tag name.
			 */
			public function get_tag(): string
			{
				return 'UL';
			}

			/**
			 * @param string $name Attribute name.
			 * @return void
			 */
			public function remove_attribute( string $name ): void
			{
				$this->html = str_replace( ' ' . $name . '="actions.handleMenuFocusout"', '', $this->html );
			}

			/**
			 * @param string $name Attribute name.
			 * @param string $value Attribute value.
			 * @return void
			 */
			public function set_attribute( string $name, string $value ): void
			{
				$this->html = preg_replace(
					'/ ' . preg_quote( $name, '/' ) . '="[^"]*"/',
					' ' . $name . '="' . htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ) . '"',
					$this->html
				);
			}

			/**
			 * @return string Transformed markup.
			 */
			public function get_updated_html(): string
			{
				return $this->html;
			}
		}
	}
}

namespace Bmd\NavBlockEnhancements\PHPUnit {

	use Bmd\NavBlockEnhancements\Transformers\DropDown;
	use WP_Mock\Tools\TestCase;

	/**
	 * @covers \Bmd\NavBlockEnhancements\Transformers\DropDown
	 */
	final class DropDownTest extends TestCase
	{
		/**
		 * The vertical navigation inside a WP 7.1 overlay must preserve click state.
		 *
		 * WordPress' submenu state inherits the parent overlay's open state. A local
		 * overlayOpenedBy context prevents the core binding from treating every
		 * submenu as open before its toggle is clicked, while the existing vertical
		 * click-dropdown transform still removes the focusout handler.
		 *
		 * @return void
		 */
		public function testShadowsTheParentOverlayStateForOverlaySubmenus(): void
		{
			$markup = '<ul><li class="has-child" data-wp-context="{ &quot;submenuOpenedBy&quot;: { &quot;click&quot;: false, &quot;hover&quot;: false, &quot;focus&quot;: false }, &quot;type&quot;: &quot;submenu&quot;, &quot;modal&quot;: null, &quot;previousFocus&quot;: null }" data-wp-on--focusout="actions.handleMenuFocusout"></li></ul>';
			$block  = array(
				'attrs' => array(
					'_isWithinOverlayTemplatePart' => true,
					'layout'                       => array( 'orientation' => 'vertical' ),
					'submenuVisibility'            => 'click',
				),
			);
			$expected = '<ul><li class="has-child" data-wp-context="{ &quot;submenuOpenedBy&quot;: { &quot;click&quot;: false, &quot;hover&quot;: false, &quot;focus&quot;: false }, &quot;overlayOpenedBy&quot;: { &quot;click&quot;: false, &quot;hover&quot;: false, &quot;focus&quot;: false }, &quot;type&quot;: &quot;submenu&quot;, &quot;modal&quot;: null, &quot;previousFocus&quot;: null }"></li></ul>';

			$this->assertSame( $expected, ( new DropDown() )->renderBlock( $markup, $block ) );
		}

		/**
		 * The click-dropdown enhancement must not alter other submenu modes.
		 *
		 * @return void
		 */
		public function testLeavesNonClickOverlayMenusUnchanged(): void
		{
			$markup = '<ul><li class="has-child" data-wp-context="{ &quot;submenuOpenedBy&quot;: { &quot;click&quot;: false, &quot;hover&quot;: false, &quot;focus&quot;: false }, &quot;type&quot;: &quot;submenu&quot;, &quot;modal&quot;: null, &quot;previousFocus&quot;: null }" data-wp-on--focusout="actions.handleMenuFocusout"></li></ul>';
			$block  = array(
				'attrs' => array(
					'_isWithinOverlayTemplatePart' => true,
					'layout'                       => array( 'orientation' => 'vertical' ),
					'submenuVisibility'            => 'hover',
				),
			);

			$this->assertSame( $markup, ( new DropDown() )->renderBlock( $markup, $block ) );
		}
	}
}
