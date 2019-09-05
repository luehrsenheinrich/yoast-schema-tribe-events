<?php
/**
 * Wpmyte\Schema_Filter\Component class
 *
 * @package wpmyte
 */

namespace wpmyte\Schema_Filter;
use wpmyte\Component_Interface;
use wpmyte\Yoast_Event_Schema;
use function add_filter;
use is_plugin_active;

/**
 * A class to handle textdomains and other schema filter related logic..
 */
class Component implements Component_Interface {
	/**
	 * Collection of plugins required for this plugin to work properly.
	 * The strings are formated to be used with `is_plugin_active()`.
	 *
	 * @var array
	 */
	public $required_plugins = array(
		'wordpress-seo/wp-seo.php',
		'the-events-calendar/the-events-calendar.php',
	);

	/**
	 * Gets the unique identifier for the plugin component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() {
		return 'schema_filter';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		// Disable the default schema of The Events Calendar.
		add_filter( 'tribe_events_widget_jsonld_enabled', '__return_false', 100, 1 );
		add_filter( 'tribe_json_ld_markup', '__return_empty_string', 100, 1 );
		add_filter( 'wpseo_schema_graph_pieces', array( $this, 'add_graph_pieces' ), 11, 2 );
	}

	/**
	 * Add custom graph pieces to the schema markup.
	 *
	 * @param array                 $pieces  Graph pieces to output.
	 * @param \WPSEO_Schema_Context $context Object with context variables.
	 *
	 * @return array $pieces Graph pieces to output.
	 */
	public function add_graph_pieces( $pieces, $context ) {
		if ( $this->has_required_plugins() ) {
			$pieces[] = new Yoast_Event_Schema\Component( $context );
		}

		return $pieces;
	}

	/**
	 * Helper function to check that every required plugin is activated.
	 *
	 * @return boolean true if ALL plugins are active, false otherwise.
	 */
	private function has_required_plugins() {
		foreach ( $this->required_plugins as $plugin ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$is_activated = is_plugin_active( $plugin );

			if ( $is_activated ) {
				return true;
			}
		}

		return false;
	}
}
