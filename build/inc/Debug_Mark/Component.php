<?php
/**
 * Wpmyte\Debug_Mark\Component class
 *
 * @package wpmyte
 */

namespace wpmyte\Debug_Mark;

use wpmyte\Component_Interface;

/**
 * A class to handle textdomains and other schema filter related logic..
 */
class Component implements Component_Interface {
	/**
	 * Gets the unique identifier for the plugin component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() {
		return 'debug_mark';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'wpseo_head', array( $this, 'add_debug_mark' ), 100 );
	}

	/**
	 * Add a debug mark for the support team of "The Events Calendar"
	 *
	 * With this mark we can spot issues with the Schema, that are not the fault
	 * of the original plugin.
	 */
	public function add_debug_mark() {
		echo sprintf(
			'<!-- The Schema has been enhanced with %1$s %2$s -->',
			str_replace( '&amp;', '&', esc_html( WPMYTE_PLUGIN_NAME ) ),
			'v' . WPMYTE_PLUGIN_VERSION
		);
	}
}
