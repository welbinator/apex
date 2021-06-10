<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_ACFFile extends Tbp_Dynamic_Item {

	function is_available() {
		return class_exists( 'ACF' );
	}

	function get_category() {
		return 'acf';
	}

	function get_type() {
		return array( 'text', 'textarea', 'wp_editor', 'file', 'url' );
	}

	function get_label() {
		return __( 'ACF (File)', 'tbp' );
	}

	function get_value( $args = array() ) {
		$value = '';
		if ( ! empty( $args['key'] ) ) {
			list( $group_id, $field_id ) = explode( ':', $args['key'] );
			$cf_value = get_field( $field_id );
			if ( ! empty( $cf_value['url'] ) ) {
				$value = $cf_value['url'];
			}
		}

		return $value;
	}

	function get_options() {
		return array(
			array(
				'label' => __( 'Field', 'tbp' ),
				'id' => 'key',
				'type' => 'select',
				'options' => Tbp_Utils::get_acf_fields_by_type( 'file' ),
			),
		);
	}
}
