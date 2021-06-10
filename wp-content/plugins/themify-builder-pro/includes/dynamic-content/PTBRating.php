<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_PTBRating extends Tbp_Dynamic_Item {

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
		return __( 'PTB Custom Fields (Rating)', 'tbp' );
	}

	function get_value( $args = array() ) {

		if ( empty( $args['field'] ) ) {
			return;
		}
		
		$args = wp_parse_args( $args, array(
			'icon' => 'fa-star',
			'size' => 'small',
			'vcolor' => 'rgba(250, 225, 80, 1)',
		) );
		list( $post_type, $field_name ) = explode( ':', $args['field'] );

		$ptb = PTB::get_option()->get_options();
		if ( isset( $ptb['cpt'][ $post_type ]['meta_boxes'][ $field_name ] ) ) {
			$field_config = $ptb['cpt'][ $post_type ]['meta_boxes'][ $field_name ];
		} else {
			return;
		}

		$cf_value = get_post_meta( get_the_ID(), "ptb_{$field_name}", true );
		if ( is_string( $cf_value ) ) {
			$value = $cf_value;
		} else if ( is_array( $cf_value ) && isset( $cf_value['total'] ) ) {
			$value = $cf_value['total'];
		} else {
			$value = 0;
		}

		/* the $meta_data argument in PTB contains $post data too */
		$post = get_post( get_the_ID(), ARRAY_A );
		$cf_value = array_merge( (array) $cf_value, $post );

		$icon = themify_get_icon( $args['icon'] );
		$stars_count = $field_config['stars_count'];
		$readonly = ! empty( $field_config['readonly'] ) ? 'ptb_extra_readonly_rating' : '';
		$uniqid = uniqid();
		$lang = PTB_Utils::get_current_language_code();
		$data = array(
			'size' => $args['size'],
			'vcolor' => $args['vcolor'],
			'icon' => $args['icon'],
		);

		ob_start();
		echo apply_filters( 'ptb_template_publicrating', false, array_merge( $field_config, array( 'key' => $field_name ) ), $data, $cf_value, $lang, false, $uniqid );
		return ob_get_clean();
	}

	function get_options() {
		$options = array();

		/* collect "text" field types in all post types */
		$ptb = PTB::$options->get_custom_post_types();
		foreach ( $ptb as $post_type_key => $post_type ) {
			if ( is_array( $post_type->meta_boxes ) ) {
				foreach ( $post_type->meta_boxes as $key => $field ) {
					if ( $field['type'] === 'rating' ) {
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
				'label' => __( 'Size', 'tbp' ),
				'id' => 'size',
				'type' => 'select',
				'options' => array(
					'small' => __( 'Small', 'tbp' ),
					'medium' => __( 'Medium', 'tbp' ),
					'large' => __( 'Large', 'tbp' ),
				),
			),
			array(
				'id' => 'icon',
				'type' => 'icon',
				'label' => __( 'Icon', 'tbp' ),
			),
			array(
				'id' => 'vcolor',
				'type' => 'color',
				'label' => __( 'Color', 'tbp' ),
			),
		);
	}
}