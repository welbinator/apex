<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template WooCommerce Notices
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (themify_is_woocommerce_active()):
    $fields_default = array(
	'css' => '',
	'animation_effect' => ''
    );
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $fields_default=null;
    $mod_name=$args['mod_name'];
    $element_id =$args['module_ID'];
    $builder_id=$args['builder_id'];
    $container_class = apply_filters('themify_builder_module_classes', array(
	'module',
	'module-' . $mod_name,
	$element_id,
	$fields_args['css']
	    ), $mod_name, $element_id, $fields_args);
    
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
	$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
	'class' => implode(' ', $container_class),
	    )), $fields_args, $mod_name, $element_id);
    $args=null;
	if(Themify_Builder::$frontedit_active===false){
		$container_props['data-lazy']=1;
	}
    ?>
    <!-- WooCommerce Notices module -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
	<?php 
	$container_props=$container_class=null; 
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	if (function_exists('wc_print_notices')) {
		wc_print_notices();
	}
	?>
    </div>
    <!-- /WooCommerce Notices module -->
<?php endif; ?>
