<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Archive Products
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (themify_is_woocommerce_active()):
    $fields_default = array(
	'layout_product' => 'grid3',
	'masonry' => 'no',
	'orderby' => 'id',
	'order' => 'desc',
	'sort' => 'no',
	'per_page' => get_option('posts_per_page'),
	'pagination' => 'yes',
	'pagination_option' => 'numbers',
	'next_link' => '',
	'prev_link' => '',
	'no_found'=>'',
	'offset'=>'',
	'archive_products' => array(),
	'css' => '',
	'animation_effect' => '',
	'terms' => '',
	'display' => 'grid',
	// Slider
	'visible_opt_slider' => '',
	'tab_visible_opt_slider' => '',
	'mob_visible_opt_slider' => '',
	'auto_scroll_opt_slider' => 0,
	'scroll_opt_slider' => '',
	'speed_opt_slider' => '',
	'effect_slider' => 'scroll',
	'pause_on_hover_slider' => 'resume',
	'play_pause_control' => 'no',
	'pagination' => 'yes',
	'wrap_slider' => 'yes',
	'show_nav_slider' => 'yes',
	'show_arrow_slider' => 'yes',
	'show_arrow_buttons_vertical' => '',
	'left_margin_slider' => '',
	'right_margin_slider' => '',
	'height_slider' => 'variable',
    );
    if (isset($args['mod_settings']['archive_products']['image']['val']['appearance_image'])) {
	$args['mod_settings']['archive_products']['image']['val']['appearance_image'] = self::get_checkbox_data($args['mod_settings']['archive_products']['image']['val']['appearance_image']);
    }
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $fields_default=null;
	if ( $fields_args['display'] === 'slider' ) {
		$fields_args['layout_product'] = '';
	}

    $mod_name=$args['mod_name'];
    $element_id =$args['module_ID'];
    $builder_id=$args['builder_id'];
    $per_page = (int)$fields_args['per_page'];

    $container_class = apply_filters('themify_builder_module_classes', array(
	'module',
	'woocommerce',
	'module-' . $mod_name,
	$element_id,
	$fields_args['css']
	    ), $mod_name,$element_id,$fields_args);
    if(isset($fields_args['archive_products']['image']['val']['appearance_image'])){
	    $container_class[] = 'module-product-image '.$fields_args['archive_products']['image']['val']['appearance_image'];
    }
    if ( isset( $fields_args['builder_content'] ) && Tbp_Utils::$isLoop === true ) {
		$fields_args['builder_id'] = $args['builder_id'];
		unset( $fields_args['archive_products'] );
		$isAPP = true;
		if ( is_string( $fields_args['builder_content'] ) ) {
			$fields_args['builder_content'] = json_decode( $fields_args['builder_content'], true );
		}
		$container_class[] = 'themify_builder_content-' . str_replace( 'tb_', '', $args['module_ID'] );
    } else {
		$isAPP = null;
    }

   	if(isset($_POST['pageId']) && Themify_Builder::$frontedit_active===true){
		Tbp_Utils::get_wc_actual_query();
	}
    $paged =  $fields_args['pagination'] === 'yes' || $fields_args['pagination'] === 'on'? self::get_paged_query() : 1;
    $query_args = array(
	'post_type' => 'product',
	'post_status' => 'publish',
	'ptb_disable'=>true,
	'tbp_aap' => true, // flag the query
	'posts_per_page' => $per_page,
	'paged' => $paged,
	'offset' => ( ( $paged - 1 ) * $per_page )
    );

	$query_args['orderby'] = $fields_args['orderby'];
	$query_args['order'] = $fields_args['order'];
	if ( $fields_args['orderby'] === 'price' ) {
		$query_args['meta_query'][ $fields_args['orderby'] ] = array(
			'key' => '_price',
			'type' => 'NUMERIC'
		);
	} else if ( $fields_args['orderby'] === 'sales' ) {
		$query_args['meta_query'][ $fields_args['orderby'] ] = array(
			'key' => 'total_sales',
			'type' => 'NUMERIC'
		);
	}
    if ( $fields_args['sort'] === 'yes' && ! empty( $_GET['orderby'] ) ) {
		$ordering_args = WC()->query->get_catalog_ordering_args();
		$query_args['orderby'] = $ordering_args['orderby'];
		$query_args['order'] = $ordering_args['order'];
		if ( $ordering_args['meta_key'] ) {
			$query_args['meta_key'] = $ordering_args['meta_key'];
		}
    }

	if($fields_args['offset']!==''){
	$query_args['offset']+=(int)$fields_args['offset'];
    }
    if ( Tbp_Public::$is_archive === true && ( is_product_category() || is_product_tag() ) ) {
		$obj = get_queried_object();
		$query_args['tax_query'] = array(
			array(
			'taxonomy' => is_product_category() ? 'product_cat' : 'product_tag',
			'field' => 'term_id',
			'terms' => $obj->term_id,
			'operator' => 'IN'
			)
		);
    } else if ( $isAPP && ! empty( $fields_args['terms'] ) ) {
		Themify_Builder_Model::parseTermsQuery( $query_args, $fields_args['terms'], 'product_cat' );
	}
    if(Tbp_Public::$isTemplatePage===true){
	 $query_args['ignore_sticky_posts']=true;
    }
	$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
	'class' => implode(' ', $container_class),
    )), $fields_args, $mod_name,$element_id);
    
    $the_query = new WP_Query($query_args);
    $query_args=$args=NULL;
    ?>
    <!-- <?php echo $mod_name?> module -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
	<?php
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	$container_props=$container_class=null;
	if ($the_query->have_posts()) :

		if ( $fields_args['display'] === 'slider' ) {
			$margin = '';
			if ( $fields_args['left_margin_slider'] !== '' ) {
				$margin = 'margin-left:'.$fields_args['left_margin_slider'].'px;';
			}
			if($fields_args['right_margin_slider']!==''){
				$margin .= 'margin-right:'.$fields_args['right_margin_slider'].'px';
			}
			$container_inner = array(
				'data-visible' => $fields_args['visible_opt_slider'],
				'data-tab-visible' => $fields_args['tab_visible_opt_slider'],
				'data-tbreakpoints' => themify_get_breakpoints('tablet_landscape')[1],
				'data-mob-visible' => $fields_args['mob_visible_opt_slider'],
				'data-mbreakpoints' => themify_get_breakpoints('mobile'),
				'data-scroll' => $fields_args['scroll_opt_slider'],
				'data-speed' => $fields_args['speed_opt_slider'] === 'slow' ? 4 : ($fields_args['speed_opt_slider'] === 'fast' ? '.5' : 1),
				'data-wrapvar' => $fields_args['wrap_slider'] !== 'no' ? 1 : 0,
				'data-slider_nav' => $fields_args['show_arrow_slider'] === 'yes' ? 1 : 0,
				'data-pager' => $fields_args['show_nav_slider'] === 'yes' ? 1 : 0,
				'data-effect' => $fields_args['effect_slider'],
				'data-height' => $fields_args['height_slider'],
			);
			if ($container_inner['data-slider_nav'] === 1 && $fields_args['show_arrow_buttons_vertical'] === 'vertical') {
				$container_inner['data-nav_out'] = 1;
				$class[] = ' themify_builder_slider_vertical';
			}
			if ($fields_args['auto_scroll_opt_slider'] && $fields_args['auto_scroll_opt_slider'] !== 'off') {
				$container_inner['data-auto'] = $fields_args['auto_scroll_opt_slider']*1000;
				$container_inner['data-pause_hover'] = $fields_args['pause_on_hover_slider'] === 'resume' ? 1 : 0;
				$container_inner['data-controller'] = $fields_args['play_pause_control'] === 'yes' ? 1 : 0;
			}
			$wrap_tag = 'div';
			$item_tag = 'div';
		} else {
			$wrap_tag = 'ul';
			$item_tag = 'li';
			if($fields_args['sort'] === 'yes'){
				$inBuilder = (isset($_POST['pageId']) && Themify_Builder::$frontedit_active===true) || (isset($_POST['pageId']) && true === $isAPP);
				if(true == $inBuilder){
					global $wp_query;
					$main_query = clone $wp_query;
					$the_query->set( 'wc_query', 'product_query');
					$wp_query = $the_query;
				}
				woocommerce_catalog_ordering();
				if(true == $inBuilder){
					$wp_query = $main_query;
					$main_query = null;
				}
			}
		}

	$class=array('builder-posts-wrap','loops-wrapper','tbp_posts_wrap','products');
	if($fields_args['masonry'] === 'yes' && in_array($fields_args['layout_product'], array('grid2', 'grid3', 'grid4', 'grid2_thumb'), true)){
	    $class[]='tbp_masonry';
	    $class[]='masonry';
	}
	$class[]=apply_filters('themify_builder_module_loops_wrapper', $fields_args['layout_product'],$fields_args,$mod_name);//deprecated backward compatibility
	$class=apply_filters( 'themify_loops_wrapper_class', $class,'product',$fields_args['layout_product'],'builder',$fields_args,$mod_name);
	$class[]='tf_clear';
	$class[]='tf_clearfix';
	
	$container_props = apply_filters('themify_builder_blog_container_props', array(
	    'class' => $class
	), 'product',$fields_args['layout_product'],$fields_args,$mod_name);
	
	if(Themify_Builder::$frontedit_active===false){
	    $container_props['data-lazy']=1;
	}
	$container_props['class']=implode(' ', $container_props['class']);
	unset($class);
	
        Tbp_Utils::disable_ptb_loop();
	    $isLoop=$ThemifyBuilder->in_the_loop===true;
	    $ThemifyBuilder->in_the_loop = true;
	    ?>
	    <<?php echo $wrap_tag; ?> <?php echo self::get_element_attributes($container_props); unset( $container_props ); ?>>

		<?php if ( $fields_args['display'] === 'slider' ) : ?>
			<div
				class="themify_builder_slider tf_carousel swiper-container tf_rel tf_overflow"
				<?php if ( Themify_Builder::$frontedit_active === false ) : ?> data-lazy="1"<?php endif; ?>
				<?php echo self::get_element_attributes( $container_inner ); ?>
			>
				<div class="swiper-wrapper tf_lazy tf_rel tf_w tf_h tf_textc">
		<?php endif; ?>

		<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>

			<?php if ( $fields_args['display'] === 'slider' ) : ?>
				<div class="swiper-slide">
					<div class="slide-inner-wrap"<?php if ( ! empty( $margin ) ) : ?> style="<?php echo $margin; ?>"<?php endif; ?>>
			<?php endif; ?>

		    <<?php echo $item_tag; ?> id="post-<?php the_ID(); ?>" <?php wc_product_class('post tf_clearfix'); ?>>

				<?php do_action( 'tbp_before_shop_loop_item' ); ?>

				<?php
					if($isAPP===true){
					self::retrieve_template('partials/advanched-archive.php', $fields_args);
					}
					else{
					self::retrieve_template('wc/loop/simple-archive.php', $fields_args);
					}
				?>

				<?php do_action( 'tbp_after_shop_loop_item' ); ?>

		    </<?php echo $item_tag; ?>>

			<?php if ( $fields_args['display'] === 'slider' ) : ?>
					</div>
				</div><!-- .swiper-slide -->
			<?php endif; ?>

		    <?php endwhile; ?>

		<?php if ( $fields_args['display'] === 'slider' ) : ?>
				</div><!-- .swiper-wrapper -->
			</div><!-- .themify_builder_slider -->
		<?php endif; ?>

	    </<?php echo $wrap_tag; ?>>

    <?php
	    wp_reset_postdata();
	    $ThemifyBuilder->in_the_loop = $isLoop;
	    if ( $fields_args['display'] === 'grid' && $fields_args['pagination'] === 'yes') {
		    self::retrieve_template('partials/pagination.php', array(
			    'pagination_option'=>$fields_args['pagination_option'],
			    'next_link'=>$fields_args['next_link'],
			    'prev_link'=>$fields_args['prev_link'],
			    'query'=>$the_query
		    ));
	    }
        ?>
	<?php else:?>
	    <?php echo $fields_args['no_found'];?>
	<?php endif; ?>
        <!-- /<?php echo $mod_name?> module -->
    </div>
<?php endif; ?>
