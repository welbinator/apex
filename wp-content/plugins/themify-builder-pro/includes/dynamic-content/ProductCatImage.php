<?php
/**
 * @package    Themify Builder Pro
 * @link       https://themify.me/
 */
class Tbp_Dynamic_Item_ProductCatImage extends Tbp_Dynamic_Item {

	function is_available() {
		return themify_is_woocommerce_active();
	}

	function get_category() {
		return 'wc';
	}

	function get_type() {
		return array( 'image', 'url' );
	}

	function get_label() {
		return __( 'Product Category Image', 'tbp' );
	}

	function get_value( $args = array() ) {
		global $product;

		$value = '';
		if ( is_product_category() ) {
			global $wp_query;
			$cat = $wp_query->get_queried_object();
			$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true ); 
			$value = wp_get_attachment_url( $thumbnail_id );
		} else if ( ! empty( $product ) ) {
			$terms = get_the_terms( $product->get_ID(), 'product_cat' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms[0] ) ) {
				$thumbnail_id = get_term_meta( $terms[0]->term_id, 'thumbnail_id', true ); 
				$value = wp_get_attachment_url( $thumbnail_id );
			}
		}

		return $value;
	}

	function get_options() {
		return array(
		);
	}
}
