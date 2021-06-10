<?php

final class Tbp_Dynamic_Query {

	private static $field_name = 'tbpdq';


	public static function run() {
		add_action( 'themify_builder_module_render_vars', array( __CLASS__, 'themify_builder_module_render_vars' ) );
		add_filter( 'themify_builder_ajax_admin_vars', array( __CLASS__, 'themify_builder_ajax_vars' ) );
		add_filter( 'themify_builder_ajax_front_vars', array( __CLASS__, 'themify_builder_ajax_vars' ) );
	}

	public static function themify_builder_ajax_vars( $vars ) {
		$vars['DynamicQuery'] = array(
			'input' => array(
				'id' => self::$field_name,
				'type' => 'toggle_switch',
				'label' => __('Dynamic Query', 'tbp'),
				'options' => array(
					'off' => array( 'value' =>  'dis', 'name' => 'off' ),
					'on' => array( 'value' => 'en', 'name' => 'on' ),
				),
				'help' => __( 'Use this on Builder Pro archive template only. The archive view (category or tag pages) will use this module to display the posts.', 'tbp' ),
			),
		);

		return $vars;
	}

	/**
	 * Runs just before a module is rendered, enable Dynamic Query if applicable
	 *
	 * @return array
	 */
	public static function themify_builder_module_render_vars( $vars ) {
		/**
		 * Reset the "pre_get_posts" filter
		 * This is to ensure that filter is applied only once and does not affect other modules.
		 */
		remove_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );

		if ( isset( $vars['mod_settings'][ self::$field_name ] ) && $vars['mod_settings'][ self::$field_name ] === 'on' && ( is_archive() || is_home() ) ) {
			add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
		}

		return $vars;
	}

	/**
	 * Replace all the query vars of the current query with global $wp_query
	 *
	 */
	public static function pre_get_posts( $query ) {
		global $wp_query;

		/**
		 * In case this is the last module in the page and there are other queries running
		 * after this, reset "pre_get_posts" again to ensure this filter runs only once.
		 */
		remove_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );

		$query->query_vars = $wp_query->query_vars;
		if ( isset( $query->query['posts_per_page'] ) ) {
			$query->query_vars['posts_per_page'] = $query->query['posts_per_page'];
		}
		if ( isset( $query->query['offset'] ) ) {
			$query->query_vars['offset'] = $query->query['offset'];
		}
		if ( isset( $query->query['paged'] ) ) {
			$query->query_vars['paged'] = $query->query['paged'];
		}
		if ( ! $wp_query->is_home() ) {
			$query->query_vars['ignore_sticky_posts'] = 1;
		}
	}
}