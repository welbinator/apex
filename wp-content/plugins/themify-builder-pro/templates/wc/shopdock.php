<?php
/**
 * Template for cart icon
 * @package Themify Builder Pro
 * @since 1.0.0
 */
?>
<div class="tbp_shopdock tf_overflow">
	<?php
	// check whether cart is not empty
	if ( !empty( WC()->cart->get_cart() )):
		if(Tbp_Utils::isAjax())://we don't need to render on page loading,wc will do it by js even if we will render
		?>
        <div class="tbp_cart_wrap tf_textl tf_box">
            <div class="tbp_cart_list">
				<?php Themify_Builder_Component_Base::retrieve_template('wc/loop/loop-product-cart.php');?>
            </div>
            <!-- /cart-list -->
            <div class="tbp_cart_checkout_wrap">
                <p class="tbp_cart_total">
                    <span class="tbp_cart_amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                    <a class="tbp_view_cart" href="<?php echo esc_url( wc_get_cart_url() ) ?>">
						<?php _e( 'view cart', 'tbp' ) ?>
                    </a>
                </p>
                <p class="tbp_checkout_button tf_right">
                    <button type="submit" onClick="document.location.href = '<?php echo wc_get_checkout_url(); ?>'; return false;"><?php _e( 'Checkout', 'tbp') ?></button>
                </p>
                <!-- /checkout-botton -->
            </div>
        </div>
        <!-- /#cart-wrap -->
		<?php endif; // cart whether is not empty?>
	<?php else: ?>
		<?php
		if(function_exists('themify_get_shop_permalink')){
		    $shop_permalink= themify_get_shop_permalink();
		}
		elseif(function_exists('themify_shop_pageId')){
		   $shop_permalink=get_permalink(themify_shop_pageId());
		}
		else{
		    $shop_permalink = get_permalink(wc_get_page_id('shop'));
		}
		?>
		<span class="tbp_empty_shopdock tf_textl tf_box">
			<?php printf( __( 'Your cart is empty. Go to <a href="%s">Shop</a>', 'tbp' ), $shop_permalink ); ?>
		</span>
	<?php endif; // cart whether is not empty?>
</div>
