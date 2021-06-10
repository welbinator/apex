<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_ACFGallery extends Tbp_Dynamic_Item {

	function is_available() {
		return class_exists( 'ACF' );
	}

	function get_category() {
		return 'acf';
	}

	function get_type() {
		return array( 'wp_editor', 'gallery' );
	}

	function get_label() {
		return __( 'ACF (Gallery)', 'tbp' );
	}

	function get_value( $args = array() ) {
		$value = '';
		if ( ! empty( $args['key'] ) ) {
			list( $group_id, $field_id ) = explode( ':', $args['key'] );
			$cf_value = get_field( $field_id );
			if ( ! empty( $cf_value ) ) {
				$ids = wp_list_pluck( $cf_value, 'ID' );
				$value = '[gallery ids="' .  implode( ',', $ids ) . '"]';
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
				'options' => Tbp_Utils::get_acf_fields_by_type( 'gallery' ),
			),
		);
	}
}
