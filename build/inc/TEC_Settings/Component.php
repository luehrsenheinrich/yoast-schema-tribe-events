<?php
/**
 * Wpmyte\Debug_Mark\Component class
 *
 * @package wpmyte
 */

namespace wpmyte\TEC_Settings;

use wpmyte\Component_Interface;
use Tribe__Settings_Tab;
use Tribe__Main;

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
		return 'tec_settings';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'tribe_settings_do_tabs', array( $this, 'do_tab' ) );
	}

	/**
	 * Add the settings tab to the TEC Settings.
	 *
	 * @return void
	 */
	public function do_tab() {
		$this->tab = new Tribe__Settings_Tab(
			'wpmyte',
			__( 'Schema', 'wpmyte' ),
			array(
				'fields' => $this->get_fields(),
			)
		);
	}

	/**
	 * Compose the fields needed to display our settings page.
	 *
	 * @return $fields The array of fields to be displayed in our settings tab.
	 */
	private function get_fields() {
		$fields = array();

		$fields = array_merge(
			$fields,
			array(
				'info-start'           => array(
					'type' => 'html',
					'html' => '<div id="modern-tribe-info">',
				),
				'info-box-title'       => array(
					'type' => 'html',
					'html' => '<h2>' . WPMYTE_PLUGIN_NAME . '</h2>',
				),
				'info-box-description' => array(
					'type' => 'html',
					'html' => '<p>'
						. __( 'This addon acts as a bridge between Yoast SEO and The Events Calendar.', 'wpmyte' )
						. '</p><p>'
						. sprintf(
							/* translators: The URL to a yoast article */
							__( 'The settings below control the Schema output of your calendar. <a href="%s" target="_blank">Here you can find</a> more information about how the Yoast SEO plugin handles Schema.', 'wpmyte' ),
							'https://developer.yoast.com/features/schema/specification/'
						)
						. '</p><p>'
						. __( 'If you would like to know more about WP Munich, our plugins, themes and services, consider subscribing to our newsletter.', 'wpmyte' )
						. '</p><p>'
						. '<a href="http://eepurl.com/cN4BZ9" target="_blank">' . __( 'Subscribe now!', 'wpmyte' ) . '</a>'
						. '</p>',
				),
				'info-end'             => array(
					'type' => 'html',
					'html' => '</div>',
				),
			)
		);

		$fields = array_merge(
			$fields,
			array(
				'wpmyteFrontpageSettingsTitle'  => array(
					'type' => 'html',
					'html' => '<h3>' . esc_html__( 'Frontpage Settings', 'wpmyte' ) . '</h3>',
				),
				'wpmyteOutputSchemaOnFrontpage' => array(
					'type'            => 'checkbox_bool',
					'label'           => esc_html__( 'Output Schema on frontpage', 'wpmyte' ),
					'tooltip'         => esc_html__( 'Output the event schema for upcoming events on the frontpage, if it is not already a view of The Event Calendar.', 'wpmyte' ),
					'default'         => false,
					'validation_type' => 'boolean',
				),
				'wpmyteEventsOnFrontpage'       => array(
					'type'            => 'text',
					'label'           => esc_html__( 'Number of events to output for Schema', 'wpmyte' ),
					'size'            => 'small',
					'default'         => get_option( 'posts_per_page' ),
					'validation_type' => 'positive_int',
				),
			)
		);

		return $fields;
	}
}
