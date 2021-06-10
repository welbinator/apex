<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_PTBRepeatableText extends Tbp_Dynamic_Item {

	function is_available() {
		return function_exists( 'run_ptb' );
	}

	function get_category() {
		return 'ptb';
	}

	function get_type() {
		return array( 'wp_editor' );
	}

	function get_label() {
		return __( 'PTB Custom Fields (Repeatable Text)', 'tbp' );
	}

	function get_value( $args = array() ) {

		if ( empty( $args['field'] ) ) {
			return;
		}
		
		$args = wp_parse_args( $args, array(
			'icon' => 'fas fa-circle',
			'color' => '',
		) );
		$output = '';
		list( $post_type, $field_name ) = explode( ':', $args['field'] );
		$value = get_post_meta( get_the_ID(), "ptb_{$field_name}", true );
		if ( is_array( $value ) ) {
			// repeatable Text fields
			$output = '<div class="module-icon icon_vertical">';
			foreach ( $value as $icon_text ) {
				$output .= '<div class="module-icon-item">';
					$output .=  '<i class="' . themify_get_icon( $args['icon'] ) . '" style="color: ' . $args['color'] . '"></i>';
					$output .= $icon_text;
				$output .= '</div>';
			}
			$output .= '</div>';
		}

		return $output;
	}

	function get_options() {
		$options = array();

		/* collect "text" field types in all post types */
		$ptb = PTB::$options->get_custom_post_types();
		foreach ( $ptb as $post_type_key => $post_type ) {
			if ( is_array( $post_type->meta_boxes ) ) {
				foreach ( $post_type->meta_boxes as $key => $field ) {
					if ( $field['type'] === 'text' && ! empty( $field['repeatable'] ) ) {
					    $label = PTB_Utils::get_label( $post_type->plural_label );
					    $name = PTB_Utils::get_label( $field['name'] );
					    $options[ "{$post_type_key}:{$key}" ] = sprintf( '%s: %s', $label, $name );
					}
				}
			}
		}

		return array(
			array(
				'label' => __( 'Field', 'tbp' ),
				'id' => 'field',
				'type' => 'select',
				'options' => $options,
			),
			array(
				'label' => __( 'Icon', 'tbp' ),
				'id' => 'icon',
				'type' => 'icon',
			),
			array(
				'id' => 'color',
				'type' => 'color',
				'label' => __( 'Icon Color', 'tbp' ),
			),
		);
	}
}
