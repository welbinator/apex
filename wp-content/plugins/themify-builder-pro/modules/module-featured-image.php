<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Module Name: Featured Image
 * Description: 
 */

class TB_Featured_Image_Module extends Themify_Builder_Component_Module {

    function __construct() {
		parent::__construct(array(
		    'name' => __('Featured Image', 'tbp'),
		    'slug' => 'featured-image',
		    'category' => array('single')
		));
    }

	public function get_assets() {
	    if(!defined('THEMIFY_BUILDER_CSS_MODULES')){
		return false;
	    }
	    return array(
		    'css'=>THEMIFY_BUILDER_CSS_MODULES.'image.css'
	    );
	}

    public function get_icon(){
	return 'image';
    }

    public function get_options() {
        return array(
            array(
                'id' => 'image_w',
                'type' => 'number',
                'label' => __('Image Width', 'tbp')
            ),
            array(
                'id' => 'auto_fullwidth',
                'type' => 'checkbox',
                'label' => '',
                'options' => array(array('name' => '1', 'value' => __('Auto fullwidth image', 'tbp'))),
                'wrap_class' => 'auto_fullwidth'
            ),
            array(
                'id' => 'image_h',
                'type' => 'number',
                'label' => __('Image Height', 'tbp')
            ),
            array(
                'id' => 'appearance_image',
                'type' => 'checkbox',
                'label' => __('Appearance', 'tbp'),
                'img_appearance'=>true
            ),
            array(
                'type'=>'advacned_link'
            ),
            array(
                'type'=> 'fallback'
            ),
            array(
                'id' => 'caption',
                'label' => __('Image Caption', 'tbp'),
                'type' => 'toggle_switch',
                'options'   => array(
                    'on'  => array( 'name' => 'yes', 'value' => 'en' ),
                    'off' => array( 'name' => 'no', 'value' => 'dis' ),
                ),
                'binding' => array(
                    'yes' => array('show' => array('caption_layout')),
                    'no' => array('hide' => array('caption_layout','caption_on_overlay'))
                )
            ),
            array(
                'id' => 'caption_layout',
                'type' => 'layout',
                'label' => __('Caption Layout', 'tbp'),
                'mode' => 'sprite',
                'options' => array(
                    array('img' => 'image_top', 'value' => 'image-top', 'label' => __('Image Top', 'tbp')),
                    array('img' => 'image_overlay', 'value' => 'image-overlay', 'label' => __('Partial Overlay', 'tbp')),
                    array('img' => 'image_centered_overlay', 'value' => 'image-full-overlay', 'label' => __('Full Overlay', 'tbp'))
                ),
                'binding' => array(
                    'image-top' => array(
                        'hide' => array('caption_on_overlay')
                    ),
                    'image-overlay' => array(
                        'show' => array('caption_on_overlay')
                    ),
                    'image-full-overlay' => array(
                        'show' => array('caption_on_overlay')
                    )
                )
            ),
            array(
                'id' => 'caption_on_overlay',
                'type' => 'checkbox',
                'label' => '',
                'options' => array(
                    array('name' => 'yes', 'value' => __('Show caption overlay on hover only', 'tbp'))
                )
            ),
            array('type' => 'tbp_custom_css')
        );
    }

	public function get_styling() {
		$general = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_image('.module img', 'b_i','bg_c','b_r','b_p')
					)
					),
					'h' => array(
					'options' => array(
						self::get_image('.module img', 'b_i','bg_c','b_r','b_p', 'h')
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding('.module img', 'p')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding('.module img', 'p', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin('.module img', 'm')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin('.module img', 'm', 'h')
					)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border('.module img', 'b')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border('.module img', 'b', 'h')
					)
					)
				))
			)),
			// Filter
			self::get_expand('f_l',
				array(
					self::get_tab(array(
						'n' => array(
							'options' => count($a = self::get_blend(' img'))>2 ? array($a) : $a
						),
						'h' => array(
							'options' => count($a = self::get_blend(' img','bl_m_h','h'))>2 ? array($a + array('ishover'=>true)) : $a
						)
					))
				)
			),
			// Width
			self::get_expand('w', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_width('', 'w')
						)
					),
					'h' => array(
						'options' => array(
							self::get_width('', 'w', 'h')
						)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius('.module img', 'r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius('.module img', 'r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow('.module img', 'sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow('.module img', 'sh', 'h')
						)
					)
				))
			)),
			// Position
			self::get_expand('po', array( self::get_css_position())),
			// Display
			self::get_expand('disp', self::get_display())
		);

		$featured_caption = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
					   self::get_color(' .image-content', 'c_b_c', 'bg_c', 'background-color')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .image-content', 'c_b_c', 'bg_c', 'background-color','h')
					)
					)
				))
			)),
			// Background
			self::get_expand(__('Caption Overlay', 'tbp'), array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
					self::get_color(array('.image-overlay .image-content',  '.image-full-overlay .image-content::before', '.image-card-layout .image-content'), 'b_c_c', __('Overlay', 'tbp'), 'background-color'),

					)
					),
					'h' => array(
					'options' => array(
						self::get_color(array('.image-overlay:hover .image-content', '.image-full-overlay:hover .image-content::before', '.image-card-layout:hover .image-content'), 'b_c_c_h', __('Overlay', 'tbp'), 'background-color'),
						self::get_color(array('.image-overlay:hover .image-title', '.image-overlay:hover .image-caption', '.image-full-overlay:hover .image-title',  '.image-full-overlay:hover .image-caption','.image-card-layout:hover .image-content', '.image-card-layout:hover .image-title'), 'f_c_c_h', __('Overlay Font Color', 'tbp'))
					)
					)
				))
			)),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_font_family('.module .image-caption', 'font_family_caption'),
						self::get_color('.module .image-caption', 'font_color_caption'),
						self::get_font_size('.module .image-caption', 'font_size_caption'),
						self::get_line_height('.module  .image-caption', 'line_height_caption'),
						self::get_text_shadow('.module .image-caption', 't_sh_c'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_font_family('.module .image-caption', 'f_f_c', 'h'),
						 self::get_color(array('.module:hover .image-caption', '.module:hover .image-title'), 'f_c_c_h', NULL, NULL, ''),
						self::get_font_size('.module .image-caption', 'f_s_c', '', 'h'),
						self::get_line_height('.module .image-caption', 'l_h_c', 'h'),
						self::get_text_shadow('.module .image-caption', 't_sh_c','h'),
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding(' .image-content','c_p')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding(' .image-content','c_p','h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
					   self::get_margin(' .image-content', 'c_m')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' .image-content', 'c_m','h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .image-content', 'c_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .image-content', 'c_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .image-content', 'c_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .image-content', 'c_sh', 'h')
						)
					)
				))
			))
		);

		return array(
			'type' => 'tabs',
			'options' => array(
				'g' => array(
					'options' => $general
				),
				'c' => array(
					'label' => __('Image Caption', 'tbp'),
					'options' => $featured_caption
				)
			)
		);
	}

	public function get_live_default() {
		return array(
			'lightbox_w_unit' => '%',
			'lightbox_h_unit' => '%',
			'fallback_s' => 'no'
		);
	}

	public function get_visual_type() {
		return 'ajax';
    }

    public function get_category() {
		return array( 'single', 'archive', 'page' );
	}

}

Themify_Builder_Model::register_module('TB_Featured_Image_Module');
