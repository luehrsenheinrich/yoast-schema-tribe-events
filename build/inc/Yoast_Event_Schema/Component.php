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
use WPSEO_Schema_Image;
use Tribe__Events__JSON_LD__Event;
use Tribe__Events__Template__Month;
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

		// We might have to modify the webpage schema.
		add_filter( 'wpseo_schema_webpage', array( $this, 'maybe_transform_webpage' ) );
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
			// The single event view.
			return true;
		} elseif ( tribe_is_month() ) {
			// The month event view.
			return true;
		} elseif ( is_front_page() && tribe_get_option( 'wpmyteOutputSchemaOnFrontpage' ) ) {
			// The frontpage.
			return true;
		}

		return false;
	}
	/**
	 * Adds our Event piece of the graph.
	 * Partially lifted from the 'Tribe__JSON_LD__Abstract' class.
	 *
	 * @see https://docs.theeventscalendar.com/reference/classes/tribe__json_ld__abstract/
	 * @return array $graph Event Schema markup
	 */
	public function generate() {
		$posts = array();

		if ( is_singular( 'tribe_events' ) ) {
			global $post;
			$posts[] = $post;
		} elseif (
			tribe_is_month()
		) {
			$posts = $this->get_month_events();
		} elseif (
			is_front_page()
			&& tribe_get_option( 'wpmyteOutputSchemaOnFrontpage' )
		) {
			$posts = $this->get_upcoming_events();
		}

		$tribe_data = $this->get_tribe_schema( $posts );
		$tribe_data = $this->transform_tribe_schema( $tribe_data );

		$data = array();
		foreach ( $tribe_data as $t ) {
			// Cast the schema object as array, the Yoast Class can't handle objects.
			$data[] = (array) $t;
		}

		// If the resulting array only has one entry, print it directly.
		if ( count( $data ) === 1 ) {
			$data = $data[0];
		} elseif ( count( $data ) === 0 ) {
			$data = false;
		}

		return $data;
	}

	/**
	 * Get and return the schema markup for a collection of posts.
	 * If the posts array is empty, only the current post is returned.
	 *
	 * @param  array $posts The collection of posts we want schema markup for.
	 *
	 * @return array        The tribe schema for these posts.
	 */
	private function get_tribe_schema( array $posts = [] ) {
		$args       = array(
			// We do not want the @context to be shown.
			'context' => false,
		);
		$tribe_data = Tribe__Events__JSON_LD__Event::instance()->get_data( $posts, $args );
		$type       = strtolower( esc_attr( Tribe__Events__JSON_LD__Event::instance()->type ) );

		foreach ( $tribe_data as $post_id => $_data ) {
			Tribe__Events__JSON_LD__Event::instance()->set_type( $post_id, $type );
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
		$tribe_data = apply_filters( "wpmyte_json_ld_{$type}_data", $tribe_data, $args );

		return $tribe_data;
	}

	/**
	 * Transform the tribe schema markup and adapt it to the Yoast SEO standard.
	 *
	 * @param  array $data The data retrieved from the TEC plugin.
	 *
	 * @return array       The transformed event data.
	 */
	private function transform_tribe_schema( array $data = [] ) {
		$new_data = array();

		foreach ( $data as $post_id => $d ) {

			// Generate an @id for the event.
			$d->{'@id'} = get_permalink( $post_id ) . '#' . strtolower( esc_attr( $d->{'@type'} ) );

			// Transform the post_thumbnail from the url to the @id of #primaryimage.
			if ( has_post_thumbnail( $post_id ) ) {
				if ( is_single() && false ) {
					// On a single view we can assume that Yoast SEO already printed the
					// image schema for the post thumbnail.
					$d->image = (object) [
						'@id' => get_permalink( $post_id ) . '#primaryimage',
					];
				} else {
					$image_id     = get_post_thumbnail_id( $post_id );
					$schema_id    = get_permalink( $post_id ) . '#primaryimage';
					$schema_image = new WPSEO_Schema_Image( $schema_id );
					$d->image     = $schema_image->generate_from_attachment_id( $image_id );
				}
			}

			$new_data[ $post_id ] = $d;
		}

		return $new_data;
	}

	/**
	 * Get an array of events for the requested month.
	 *
	 * @return array An array of posts of the custom post type event.
	 */
	private function get_month_events() {
		$wp_query = tribe_get_global_query_object();

		$event_date = $wp_query->get( 'eventDate' );

		$month = empty( $event_date )
			? tribe_get_month_view_date()
			: $wp_query->get( 'eventDate' );

		$args = [
			'eventDisplay'   => 'custom',
			'start_date'     => Tribe__Events__Template__Month::calculate_first_cell_date( $month ),
			'end_date'       => Tribe__Events__Template__Month::calculate_final_cell_date( $month ),
			'posts_per_page' => -1,
			'hide_upcoming'  => true,
		];

		$posts = tribe_get_events( $args );

		return $posts;
	}

	/**
	 * Get an array of events that are upcoming.
	 *
	 * @return array An array of posts of the custom post type event.
	 */
	private function get_upcoming_events() {
		$args = [
			'eventDisplay'   => 'custom',
			'start_date'     => 'now',
			'posts_per_page' => tribe_get_option( 'wpmyteEventsOnFrontpage', get_option( 'posts_per_page' ) ),
			'hide_upcoming'  => true,
		];

		$posts = tribe_get_events( $args );

		return $posts;
	}

	/**
	 * Transform the WebPage graph piece on single event views
	 *
	 * @param  array $data The WebPage Graph Piece.
	 *
	 * @return array       The modified WebPage Graph Piece
	 */
	public function maybe_transform_webpage( $data ) {
		if ( is_singular( 'tribe_events' ) ) {
			$data['mainEntityOfPage'] = [ '@id' => $this->context->canonical . '#event' ];
		}

		return $data;
	}
}
