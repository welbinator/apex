<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_ArchiveTitle extends Tbp_Dynamic_Item {

	function get_category() {
		return 'post';
	}

	function get_type() {
		return array( 'text', 'textarea', 'wp_editor' );
	}

	function get_label() {
		return __( 'Archive Title', 'tbp' );
	}

	function get_value( $args = array() ) {
		$value = '';
		if ( is_category() || is_tag() || is_tax() ) {
			$value = single_term_title( '', false );
		} else if ( is_author() ) {
			$value = get_the_author();
		} else if ( themify_is_shop() ) {
			$value = woocommerce_page_title( false );
		} else if ( is_post_type_archive() ) {
			$value = post_type_archive_title( '', false );
		}

		return $value;
	}

	function get_options() {
		return array();
	}
}
