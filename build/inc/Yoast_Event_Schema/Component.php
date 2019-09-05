<?php
/**
 * Wpmyte\Yoast_Event_Schema\Component class
 *
 * @package wpmyte
 */

namespace wpmyte\Yoast_Event_Schema;
use wpmyte\Component_Interface;
use WPSEO_Graph_Piece;
use WPSEO_Schema_Context;
use Tribe__Events__JSON_LD__Event;
use function load_plugin_textdomain;

/**
 * A class to handle textdomains and other Yoast Event Schema related logic..
 */
class Component implements Component_Interface, WPSEO_Graph_Piece {
	/**
	 * A value object with context variables.
	 *
	 * @var WPSEO_Schema_Context
	 */
	private $context;

	/**
	 * Schema_Event constructor.
	 *
	 * @param WPSEO_Schema_Context $context Value object with context variables.
	 */
	public function __construct( WPSEO_Schema_Context $context ) {
		$this->context = $context;
	}

	/**
	 * Gets the unique identifier for the plugin component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() {
		return 'yoast_event_schema';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {}

	/**
	 * Determines whether or not a piece should be added to the graph.
	 *
	 * @return bool
	 */
	public function is_needed() {
		if ( is_single() && 'tribe_events' === get_post_type() ) {
			return true;
		}

		return false;
	}
	/**
	 * Adds our Event piece of the graph.
	 *
	 * @return array $graph Event Schema markup
	 */
	public function generate() {
		global $post;
		$data = array();

		$args       = array();
		$tribe_data = Tribe__Events__JSON_LD__Event::instance()->get_data( $post );
		$type       = strtolower( esc_attr( Tribe__Events__JSON_LD__Event::instance()->type ) );
		Tribe__Events__JSON_LD__Event::instance()->set_type( $post, $type );

		foreach ( $tribe_data as $post_id => $_data ) {
			// Register this post as done already.
			Tribe__Events__JSON_LD__Event::instance()->register( $post_id );
		}

		/**
		 * Allows the event data to be modifed by themes and other plugins.
		 *
		 * @example tribe_json_ld_thing_data
		 * @example tribe_json_ld_event_data
		 *
		 * @param array $data objects representing the Google Markup for each event.
		 * @param array $args the arguments used to get data
		 */
		$tribe_data = apply_filters( "tribe_json_ld_{$type}_data", $tribe_data, $args );

		// Strip the post ID indexing before returning.
		$tribe_data = array_values( $tribe_data );

		if ( count( $tribe_data ) > 0 ) {
			$_tribe_data = (array) $tribe_data[0];

			unset( $_tribe_data['@context'] );

			if ( has_post_thumbnail( $post->ID ) ) {
				$_tribe_data['image'] = get_the_post_thumbnail_url( get_the_ID(), 'full' );
			}

			$data[] = $_tribe_data;
		}

		return $data;
	}
}
