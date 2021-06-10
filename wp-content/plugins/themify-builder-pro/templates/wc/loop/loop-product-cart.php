<?php
/**
 * Template to display products in Themify cart
 * @package themify
 * @since 1.0.0
 */

global $woocommerce,$product;
$carts = array_reverse( $woocommerce->cart->get_cart() );
$isNew=function_exists('wc_get_cart_remove_url');
if(isset($product)){
	$_post = clone $product;
}
foreach ( $carts as $key => $cart_item ) :
	// Add support for MNM plugin
	if( isset( $cart_item['mnm_container'] ) || $cart_item['quantity']<=0) continue;

	$product  = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $key );
	if ( $product->exists()  &&  apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $key ) ): 
		$product_permalink=apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cart_item ) : '', $cart_item, $key );
		$name=apply_filters( 'woocommerce_cart_item_name', $product->get_name(), $cart_item, $key );
		$hasLink=!empty($product_permalink);
	    ?>

		<div class="tbp_cart_product">
			<a href="<?php echo $isNew===true? wc_get_cart_remove_url( $key ) : $woocommerce->cart->get_remove_url( $key ); ?>" data-cart_item_key="<?php echo $key; ?>" class="tbp_remove_from_cart_button remove_from_cart_button remove-item tf_close"></a>

			<figure class="tbp_cart_image tf_left">
				<?php themify_product_cart_image_start(); // hook ?>
				<?php if($hasLink===true):?>
					<a href="<?php echo esc_url($product_permalink) ?>">
				<?php endif;?>
					<?php
						$_product_thumbnail =$_product_thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image('woocommerce_thumbnail',array('loading'=>'lazy','decode'=>'async','alt'=>$name)), $cart_item, $key );
						if ( ! empty( $_product_thumbnail ) ) {
							echo $_product_thumbnail;
						}
						else {
							?>
							<img width="40" height="40" src="<?php echo TBP_URL ?>public/img/cart-holder.png" loading="lazy" decode="async" alt="<?php echo $name?>">
							<?php
						}
					?>
				<?php if($hasLink===true):?>
					</a>
				<?php endif;?>
				<?php themify_product_cart_image_end(); // hook ?>
			</figure>
			<div class="tbp_cart_details">
				<h3 class="tbp_cart_title">
					<?php if($hasLink===true):?>
						<a href="<?php echo esc_url($product_permalink) ?>">
					<?php endif;?>
						<?php echo $name; ?>
					<?php if($hasLink===true):?>
					</a>
				<?php endif;?>
				</h3>
				<p class="tbp_cart_quantity"><?php echo sprintf(__('x %d', 'tbp'), $cart_item['quantity']); ?></p>
			</div>
		</div>
		<!--/product -->

	<?php endif; ?>

<?php endforeach; 
if(isset($_post)){
	$product=clone $_post;
	unset($_post);
}
else{
	unset($product);
}

