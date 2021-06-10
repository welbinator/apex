<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_CustomField extends Tbp_Dynamic_Item {

	function get_category() {
		return 'advanced';
	}

	function get_type() {
		return array( 'text', 'textarea', 'image', 'wp_editor', 'url', 'custom_css' );
	}

	function get_label() {
		return __( 'Custom Field', 'tbp' );
	}

	function get_value( $args = array() ) {
		$value = '';
		if ( ! empty( $args['custom_field'] ) ) {
			$enable_shortcodes = isset( $args['custom_field_shortcode'] ) && $args['custom_field_shortcode'] === 'yes';
			if ( empty( $args['post_id'] ) ) {
				$the_query = Tbp_Utils::get_actual_query();
				if($the_query===null || $the_query->have_posts()){
					if($the_query!==null){
						$the_query->the_post();
					}
					$value = $this->get_meta( get_the_id(), $args['custom_field'], $enable_shortcodes );
					if ( ! empty( $the_query ) ) {
						wp_reset_postdata();
					}
				}
			} else {
				$value = $this->get_meta( $args['post_id'], $args['custom_field'], $enable_shortcodes );
			}
		}

		return $value;
	}

	function get_meta( $post_id, $meta_key, $enable_shortcodes ) {
		$value = get_post_meta( $post_id, $meta_key, true );

		if ( $enable_shortcodes ) {
			$value = do_shortcode( $value );
		}

		return $value;
	}

	function get_options() {
		return array(
			array(
				'label' => __( 'Custom Field', 'tbp' ),
				'id' => 'custom_field',
				'type' => 'autocomplete',
				'dataset' => 'custom_fields',
			),
			array(
				'label' => __( 'Enable Shortcodes', 'tbp' ),
				'id' => 'custom_field_shortcode',
				'type' => 'select',
				'options' => array(
					'no' => __( 'No', 'tbp' ),
					'yes' => __( 'Yes', 'tbp' ),
				),
				'help' => __( 'Enable parsing shortcodes on the custom field value.', 'tbp' ),
			),
			array(
				'label' => __( 'Post ID', 'tbp' ),
				'id' => 'post_id',
				'type' => 'number',
				'help' => __( 'Leave empty to get the data from current post in the loop.', 'tbp' ),
			),
		);
	}
}
