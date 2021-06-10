<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Search Form
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'style' => 'form',
    'autocomplete' => '',
    'placeholder' => '',
    'button' => 'yes',
    'icon' => 'icon',
    'button_icon'=>'no',
    'button_t' => '',
    'css' => '',
    'animation_effect' => '',
    'post_type' => 'any'
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
if(!file_exists(THEMIFY_DIR.'/includes/search-box.php')){
    $fields_args['style'] = 'overlay'===$fields_args['style'] ?'form':$fields_args['style'];
    $fields_args['autocomplete']='';
}
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$element_id =$args['module_ID'];
$builder_id=$args['builder_id'];
$container_class = apply_filters( 'themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id,
    $fields_args['css']
), $mod_name, $element_id, $fields_args );

if($fields_args['button_icon']==='yes'){
    $container_class[] = 'tb_search_overlay';
}
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' => implode( ' ', $container_class ),
)), $fields_args, $mod_name, $element_id);
if('overlay'===$fields_args['style'] && !empty($fields_args['post_type']) && 'any' !== $fields_args['post_type']){
    $container_props['data-post-type'] = esc_attr($fields_args['post_type']);
}
$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
    $container_props['data-ajax'] = 'overlay'===$fields_args['style'] ? 'overlay' : ('on'===$fields_args['autocomplete']?'dropdown':false);
    if(false!==$container_props['data-ajax']){
        wp_enqueue_script( 'tbp' );
    }
}
?>
<!-- Search Form module -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null; 
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	if('overlay'===$fields_args['style']): ?>
        <span class="tbp_icon_search"><?php echo themify_get_icon('search','ti');?></span>
        <?php include( THEMIFY_DIR.'/includes/search-box.php' );?>
    <?php
    else:
	do_action( 'pre_get_search_form' );
    ?>
    <form role="search" method="get" class="tbp_searchform" action="<?php echo  esc_url( home_url( '/' ) ); ?>">
	<div class="tf_rel tf_inline_b">
		<?php if ( $fields_args['button_icon'] === 'yes' ): ?>
			<span class="tbp_icon_search overlay"><?php echo themify_get_icon('search','ti'); ?></span>
		<?php endif; ?>
		<input type="text" name="s" title="<?php esc_attr_e( $fields_args['placeholder'] ); ?>" placeholder="<?php esc_attr_e( $fields_args['placeholder'] ); ?>" value="<?php echo get_search_query(); ?>">
	</div>
	<?php if(!empty($fields_args['post_type']) && 'any' !== $fields_args['post_type']): ?>
	<input type="hidden" name="post_type" value="<?php echo $fields_args['post_type']; ?>">
	<?php endif; ?>
	<?php if ( $fields_args['button'] === 'yes' ): ?>
		<button type="submit" class="module-buttons">
		<?php echo $fields_args['icon'] === 'text' && $fields_args['button_t'] !== '' ? esc_attr( $fields_args['button_t'] ) : '<span class="tbp_icon_search">'.themify_get_icon('search','ti').'</span>'; ?>
	    </button>
	<?php endif; ?>
    </form>
    <?php endif; ?>
</div>
<!-- /Search Form module -->
