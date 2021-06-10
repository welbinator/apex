<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Gallery Grid
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-gallery-grid.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
Themify_Builder_Model::load_module_self_style($args['mod_name'],'grid');
$pagination = $args['settings']['gallery_pagination'] && $args['settings']['gallery_per_page'] > 0;
$disable = Themify_Builder_Model::is_img_php_disabled();

if ( ! empty( $args['settings']['lightbox'] ) ) {
	$enable_lightbox = 'n' !== $args['settings']['lightbox'];
} else {
	$enable_lightbox = 'disable' !== themify_builder_get( 'setting-page_builder_gallery_lightbox', 'builder_lightbox' );
}

if ($pagination) {
    $total = count($args['settings']['gallery_images']);
    if ($total <= $args['settings']['gallery_per_page']) {
        $pagination = false;
    } else {
        $is_current_gallery = !empty($_GET['tb_gallery']) ? $args['module_ID'] === $_GET['tb_gallery'] : true;
        $current = isset($_GET['builder_gallery']) && $is_current_gallery ? $_GET['builder_gallery'] : 1;
        $offset = $args['settings']['gallery_per_page'] * ( $current - 1 );
        $args['settings']['gallery_images'] = array_slice($args['settings']['gallery_images'], $offset, $args['settings']['gallery_per_page'], true);
    }
}
$columns = $args['columns'];
$responsive_cols = !empty($args['settings']['t_columns']) ? ' gallery-t-columns-'.$args['settings']['t_columns']:'';
$responsive_cols .= !empty($args['settings']['m_columns']) ? ' gallery-m-columns-'.$args['settings']['m_columns']:'';
?>
<div class="module-gallery-grid gallery-columns-<?php echo $columns.$responsive_cols;?><?php if($args['settings']['layout_masonry'] === 'masonry'):?> gallery-masonry<?php endif;?> tf_clear">
	<?php foreach ($args['settings']['gallery_images'] as $image) :
		$caption = !empty($image->post_excerpt) ? $image->post_excerpt : '';
		$title = $image->post_title;
		?>
		<dl class="gallery-item">
			<dt class="gallery-icon">
			<?php
			if ($args['settings']['link_opt'] === 'file') {
				$link = wp_get_attachment_image_src($image->ID, $args['settings']['link_image_size']);
				$link = $link[0];
			} elseif ('none' === $args['settings']['link_opt']) {
				$link = '';
			} else {
				$link = get_attachment_link($image->ID);
			}
			$link_before = '' !== $link ? sprintf(
				'<a data-title="%s" title="%s" href="%s" data-rel="%s" %s>',
				esc_attr( $args['settings']['lightbox_title'] ),
				esc_attr( $caption ),
				esc_url( $link ),
				$args['module_ID'],
				( $enable_lightbox ? 'class="themify_lightbox"' : '' )
			) : '';
			$link_before = apply_filters('themify_builder_image_link_before', $link_before, $image, $args['settings']);
			$link_after = '' !== $link ? '</a>' : '';
			$img = $disable===true?wp_get_attachment_image($image->ID, $args['settings']['image_size_gallery']):themify_get_image(array('src'=>$image->ID,'w'=>$args['settings']['thumb_w_gallery'],'h'=>$args['settings']['thumb_h_gallery']));
			echo!empty($img) ? $link_before . $img . $link_after : '';
			?>
			</dt>
			<dd<?php if (($args['settings']['gallery_image_title'] === 'yes' && $title!=='' ) || ( $args['settings']['gallery_exclude_caption'] !== 'yes' && $caption!=='' )) : ?> class="wp-caption-text gallery-caption"<?php endif; ?>>
				<?php if ($args['settings']['gallery_image_title'] === 'yes' && $title!=='') : ?>
					<strong class="themify_image_title tf_block"><?php echo $title ?></strong>
				<?php endif; ?>
				<?php if ($args['settings']['gallery_exclude_caption'] !== 'yes' && $caption!=='') : ?>
					<span class="themify_image_caption"><?php echo $caption ?></span>
				<?php endif; ?>
			</dd>
		</dl>
	<?php endforeach; // end loop  ?>
</div>
<?php
if ($pagination) :
    echo Themify_Builder_Component_Base::get_pagination('','','tb_gallery='.$args['module_ID'].'&builder_gallery',0,ceil($total / $args['settings']['gallery_per_page']),$current);
endif;
