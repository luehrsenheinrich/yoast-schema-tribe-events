<?php
/**
 * Wpmyte\Schema_Filter\Component class
 *
 * @package wpmyte
 */

namespace wpmyte\Schema_Filter;
use wpmyte\Component_Interface;
use wpmyte\Yoast_Event_Schema;
use Tribe__Events__Template__Month;
use function add_filter;
use function is_plugin_active;
use function add_action;
use function remove_action;

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
		add_filter( 'tribe_events_widget_jsonld_enabled', '__return_false', PHP_INT_MAX, 1 );
		add_filter( 'tribe_json_ld_markup', '__return_empty_string', PHP_INT_MAX, 1 );
		add_filter( 'wpseo_schema_graph_pieces', array( $this, 'add_graph_pieces' ), 11, 2 );

		add_action( 'wp_head', array( $this, 'unset_tec_json_ld_markup' ), 0 );
	}

	/**
	 * The lack of filters and actions makes it hard to unset the json-ld output,
	 * so we have to use this hack to suppress the json output from TEC.
	 */
	function unset_tec_json_ld_markup() {
		// We cycle through every filter registered to wp_head with prio 10.
		foreach ( $GLOBALS['wp_filter']['wp_head'][10] as $a ) {
			// If we find an instance of the fitting class with the fitting action.
			if ( is_array( $a['function'] ) && $a['function'][0] instanceof Tribe__Events__Template__Month && $a['function'][1] === 'json_ld_markup' ) {
				// We nuke it.
				remove_action( 'wp_head', array( $a['function'][0], 'json_ld_markup' ) );
			}
		}
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

			if ( ! $is_activated ) {
				// We can drop out if at least one required plugin is not activated.
				return false;
			}
		}

		// All required plugins are activated as this function would drop out returning false otherwise.
		return true;
	}
}
