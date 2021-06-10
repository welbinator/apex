<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder Pro
 */
/**
 * @link https://getwooplugins.com/plugins/woocommerce-variation-swatches/
 */
class Themify_Builder_Plugin_Compat_wooVariationSwatches {

	/**
	 * Add Variation Swatches to AP module output
	 */
	static function init() {
		add_action( 'tbp_after_shop_loop_item', 'wvs_pro_archive_variation_template' );
	}
}