<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_ArchiveDescription extends Tbp_Dynamic_Item {

	function get_category() {
		return 'post';
	}

	function get_type() {
		return array( 'text', 'textarea', 'wp_editor' );
	}

	function get_label() {
		return __( 'Archive Description', 'tbp' );
	}

	function get_value( $args = array() ) {
		$value = get_the_archive_description();

		return $value;
	}

	function get_options() {
		return array();
	}
}
