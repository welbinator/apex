<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly
/**
 * Template Archive Description
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
	'css' => '',
	'animation_effect' => ''
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$element_id =$args['module_ID'];
$builder_id=$args['builder_id'];
$mod_name=$args['mod_name'];
$container_class = apply_filters( 'themify_builder_module_classes', array(
	'module',
	'module-' . $mod_name,
	$element_id,
	$fields_args['css']
), $mod_name, $element_id,$fields_args );
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
<!-- Archive Description module -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null; 
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	$the_query = Tbp_Utils::get_actual_query();
	if ($the_query===null || $the_query->have_posts() ){
	    if($the_query!==null){
		$the_query->the_post();
	    }

        $description = is_author() ? get_the_author_meta( 'description' ) : term_description();
		/** This filter is documented in wp-includes/general-template.php */
		$description = apply_filters( 'get_the_archive_description', $description );

	    if ($description === '' && Themify_Builder::$frontedit_active===true) {
			$description = '<p>'.__( 'Archive description', 'tbp').'</p>';
	    }
	    echo $description;
	    if($the_query!==null){
		wp_reset_postdata();
	    }
	}
    ?>
</div>
<!-- /Archive Description module -->
