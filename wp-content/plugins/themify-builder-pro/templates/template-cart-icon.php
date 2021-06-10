<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Cart Icon
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if ( themify_is_woocommerce_active() && isset( $GLOBALS['woocommerce']->cart ) ) :

	wp_enqueue_script( 'tbp' );

    $fields_default = array(
	'icon' => 'ti-shopping-cart',
	'style' => 'slide',
	'bubble' => 'off',
	'sub_total' => 'off',
	'alignment' => '',
	'animation_effect' => ''
    );
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $fields_default=null;
    $element_id =$args['module_ID'];
    $builder_id=$args['builder_id'];
    $mod_name=$args['mod_name'];
    $container_class = apply_filters('themify_builder_module_classes', array(
	'module',
	'module-' . $mod_name,
	$element_id,
	'tbp_cart_icon_style_' . $fields_args['style']
	    ), $mod_name, $element_id,$fields_args);
    if(!empty($fields_args['alignment'])){
		$container_class[] = 'tf_text'.$fields_args['alignment'][0];
    } 
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
	$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
	'class' => implode(' ', $container_class),
	    )), $fields_args, $mod_name,$element_id);
    
    $args=null;
	$cart_is_dropdown = 'dropdown' === $fields_args['style'];
    if ($cart_is_dropdown === false){
		$container_props['data-id']=$element_id;
    }
	if(Themify_Builder::$frontedit_active===false){
		$container_props['data-lazy']=1;
	}
    ?>
    <!-- Cart Icon module -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
	<?php
	$container_props=$container_class=null;
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	global $woocommerce;
	$total = $woocommerce->cart->get_cart_contents_count();
	?>
        <div class="tbp_cart_icon_container">
	    <a href="<?php echo $cart_is_dropdown === true ? wc_get_cart_url() : '#'.$element_id.'_tbp_cart'; ?>">
	    <?php if ('on' === $fields_args['sub_total']): ?>
			<span class="tbp_cart_amount"><?php echo $woocommerce->cart->get_cart_subtotal(); ?></span>
	    <?php endif; ?>
		<i class="tbp_shop_cart_icon"><?php echo themify_get_icon($fields_args['icon'])?></i>
		    <?php if ('on' === $fields_args['bubble']): ?>
			<span class="tbp_cart_count<?php echo $total <= 0 ? ' tbp_cart_empty' : ''; ?>"><?php echo $total; ?></span>
		    <?php endif; ?>
	    </a>
	    <?php if ($cart_is_dropdown === false): ?>
		<div id="<?php echo $element_id; ?>_tbp_cart" class="tbp_sidemenu sidemenu-off tbp_slide_cart tf_scrollbar">
		    <a id="<?php echo $element_id; ?>_tbp_close" class="tf_close tbp_cart_icon_close"></a>
		<?php endif; ?>

		<?php self::retrieve_template('wc/shopdock.php'); ?>

		<?php if ($cart_is_dropdown === false): ?>
		</div>
		<!-- /#slide-cart -->
	    <?php endif; ?>
        </div>
    </div>
    <!-- /Cart Icon module -->
<?php endif;

