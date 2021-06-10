<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Module Name: Cart Icon
 * Description: 
 */

class TB_Cart_Icon_Module extends Themify_Builder_Component_Module {

    function __construct() {
		parent::__construct(array(
		    'name' => __('Cart Icon', 'tbp'),
		    'slug' => 'cart-icon',
		    'category' => array('site')
		));
    }
    
    public function get_assets() {
	return array(
	    'ver'=>Tbp::get_version(),
	    'css'=>TBP_WC_CSS_MODULES.$this->slug.'.css'
	);
    }
    
    public function get_icon(){
	return 'shopping-cart';
    }

    public function get_options() {
		return array(
			array(
				'id' => 'icon',
				'type' => 'icon',
				'label' => __('Icon', 'tbp')
			),
			array(
				'id' => 'style',
				'label' => __( 'Style', 'tbp'),
				'type' => 'select',
				'options' => array(
					'slide' => __( 'Slide', 'tbp'),
					'dropdown' => __( 'Drop Down', 'tbp')
				)
			),
			array(
				'id' => 'bubble',
				'label' => __('Bubble', 'tbp'),
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name'=>'on','value' =>'show'),
					'off' => array('name'=>'off', 'value' =>'')
				)
			),
			array(
				'id' => 'sub_total',
				'label' => __('Sub Total', 'tbp'),
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name'=>'on','value' =>'show'),
					'off' => array('name'=>'off', 'value' =>'')
				)
			),
			array(
				'id' => 'alignment',
				'label' => __( 'Alignment', 'tbp'),
				'type' => 'icon_radio',
				'aligment2' => true
			),
		);
	}

	public function get_styling() {
		$general = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
						self::get_image('', 'b_i','bg_c','b_r','b_p')
						)
					),
					'h' => array(
						'options' => array(
							self::get_image('', 'b_i','bg_c','b_r','b_p', 'h')
						)
					)
				))
			)),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_font_family('', 'f_f_g'),
						self::get_color_type(array('',' .tbp_shop_cart_icon:before'),'', 'f_c_t_g',  'f_c_g', 'f_g_c_g'),
						self::get_font_size(array('', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'f_s_g', ''),
						self::get_line_height('', 'l_h_g'),
						self::get_letter_spacing('', 'l_s_g'),
						self::get_text_transform('', 't_t_g'),
						self::get_font_style('', 'f_st_g', 'f_w_g'),
						self::get_text_decoration('', 't_d_r_g'),
						self::get_text_shadow('','t_sh_g','h'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_font_family('', 'f_f_g_h'),
						self::get_color_type(array('',' .tbp_shop_cart_icon:before'),'', 'f_c_t_g_h',  'f_c_g_h', 'f_g_c_g_h'),
						self::get_font_size('', 'f_s_g', '', 'h'),
						self::get_line_height('', 'l_h_g', 'h'),
						self::get_letter_spacing('', 'l_s_g', 'h'),
						self::get_text_transform('', 't_t_g', 'h'),
						self::get_font_style('', 'f_st_g', 'f_w_g', 'h'),
						self::get_text_decoration('', 't_d_r_g', 'h'),
						self::get_text_shadow('','t_sh_g','h'),
					)
					)
				))
			)),
			// Link
			self::get_expand('l', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' a', 'l_c'),
						self::get_text_decoration(' a', 't_d_l')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' a', 'l_c',null, null, 'hover'),
						self::get_text_decoration(' a', 't_d_l', 'h')
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
						self::get_padding('', 'p')
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding('', 'p', 'h')
						)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
						self::get_margin('', 'm')
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin('', 'm', 'h')
						)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
						self::get_border('', 'b')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border('', 'b', 'h')
						)
					)
				))
			)),
			// Filter
			self::get_expand('f_l',
				array(
					self::get_tab(array(
						'n' => array(
							'options' => count($a = self::get_blend('','fl'))>2 ? array($a) : $a
						),
						'h' => array(
							'options' => count($a = self::get_blend('','fl_h','h'))>2 ? array($a + array('ishover'=>true)) : $a
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
							self::get_border_radius('', 'r_c')
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius('', 'r_c', 'h')
							)
						)
					))
			)),
			// Shadow
			self::get_expand('sh', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
							self::get_box_shadow('', 'sh')
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow('', 'sh', 'h')
							)
						)
					))
			)),
			// Position
			self::get_expand('po', array( self::get_css_position())),
			// Display
			self::get_expand('disp', self::get_display())
		);

		$sub_total = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' .woocommerce-Price-amount', 'b_c_c_s_t', 'bg_c', 'background-color')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .woocommerce-Price-amount', 'b_c_c_s_t', 'bg_c', 'background-color', 'h')
					)
					)
				))
			)),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_font_family(' .woocommerce-Price-amount', 'f_f_c_s_t'),
						self::get_color(' .woocommerce-Price-amount', 'f_c_c_s_t'),
						self::get_font_size(' .woocommerce-Price-amount', 'f_s_c_s_t'),
						self::get_letter_spacing(' .woocommerce-Price-amount', 'l_s_c_s_t'),
						self::get_line_height(' .woocommerce-Price-amount', 'l_h_c_s_t'),
						self::get_font_style(' .woocommerce-Price-amount', 'f_st_c_s_t', 'f_w_c_s_t'),
						self::get_text_shadow(' .woocommerce-Price-amount', 't_sh_c_s_t'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_font_family(' .woocommerce-Price-amount', 'f_f_c_s_t', 'h'),
						self::get_color(' .woocommerce-Price-amount', 'f_c_c_s_t', null,null, 'h'),
						self::get_font_size(' .woocommerce-Price-amount', 'f_s_c_s_t', 'h'),
						self::get_letter_spacing(' .woocommerce-Price-amount', 'l_s_c_s_t', 'h'),
						self::get_line_height(' .woocommerce-Price-amount', 'l_h_c_s_t', 'h'),
						self::get_font_style(' .woocommerce-Price-amount', 'f_st_c_s_t', 'f_w_c_s_t', 'h'),
						self::get_text_shadow(' .woocommerce-Price-amount', 't_sh_c_s_t', 'h'),
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding(' .woocommerce-Price-amount', 'p_c_s_t')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding(' .woocommerce-Price-amount', 'p_c_s_t', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' .woocommerce-Price-amount', 'm_c_s_t'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' .woocommerce-Price-amount', 'm_c_s_t', 'h'),
					)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border(' .woocommerce-Price-amount', 'b_c_s_t')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border(' .woocommerce-Price-amount', 'b_c_s_t', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .woocommerce-Price-amount', 'r_c_c_s_t')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .woocommerce-Price-amount', 'r_c_c_s_t', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .woocommerce-Price-amount', 'sh_c_s_t')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .woocommerce-Price-amount', 'sh_c_s_t', 'h')
						)
					)
				))
			)),
		);

		$icon = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' .tbp_shop_cart_icon', 'b_c_c_i_i', 'bg_c', 'background-color')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .tbp_shop_cart_icon', 'b_c_c_i_i', 'bg_c', 'background-color', 'h')
					)
					)
				))
			)),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(array(' .tbp_shop_cart_icon',' .tbp_shop_cart_icon:before'), 'f_c_c_i_i'),
						self::get_font_size(' .tbp_shop_cart_icon', 'f_s_c_i_i'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(array(' .tbp_shop_cart_icon',' .tbp_shop_cart_icon:before'), 'f_c_c_i_i', null,null, 'h'),
						self::get_font_size(' .tbp_shop_cart_icon', 'f_s_c_i_i', '', 'h'),
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding(' .tbp_shop_cart_icon', 'p_c_i_i')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding(' .tbp_shop_cart_icon', 'p_c_i_i', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' .tbp_shop_cart_icon', 'm_c_i_i'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' .tbp_shop_cart_icon', 'm_c_i_i', 'h'),
					)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border(' .tbp_shop_cart_icon', 'b_c_i_i')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border(' .tbp_shop_cart_icon', 'b_c_i_i', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tbp_shop_cart_icon', 'r_c_c_i_i')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tbp_shop_cart_icon', 'r_c_c_i_i', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tbp_shop_cart_icon', 'sh_c_i_i')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tbp_shop_cart_icon', 'sh_c_i_i', 'h')
						)
					)
				))
			)),
		);

		$bubble = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' .tbp_cart_count', 'b_c_c_i_b', 'bg_c', 'background-color')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .tbp_cart_count', 'b_c_c_i_b', 'bg_c', 'background-color', 'h')
					)
					)
				))
			)),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_font_family(' .tbp_cart_count', 'f_f_c_i_b'),
						self::get_color(' .tbp_cart_count', 'f_c_c_i_b'),
						self::get_font_size(' .tbp_cart_count', 'f_s_c_i_b'),
						self::get_letter_spacing(' .tbp_cart_count', 'l_s_c_i_b'),
						self::get_line_height(' .tbp_cart_count', 'l_h_c_i_b'),
						self::get_font_style(' .tbp_cart_count', 'f_st_c_i_b', 'f_w_c_i_b'),
						self::get_text_shadow(' .tbp_cart_count', 't_sh_c_i_b'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_font_family(' .tbp_cart_count', 'f_f_c_i_b','h'),
						self::get_color(' .tbp_cart_count', 'f_c_c_i_b', null,null, 'h'),
						self::get_font_size(' .tbp_cart_count', 'f_s_c_i_b', '', 'h'),
						self::get_letter_spacing(' .tbp_cart_count', 'l_s_c_i_b', 'h'),
						self::get_line_height(' .tbp_cart_count', 'l_h_c_i_b', 'h'),
						self::get_font_style(' .tbp_cart_count', 'f_st_c_i_b', 'f_w_c_i_b', 'h'),
						self::get_text_shadow(' .tbp_cart_count', 't_sh_c_i_b','h'),
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding(' .tbp_cart_count', 'p_c_i_b')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding(' .tbp_cart_count', 'p_c_i_b', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' .tbp_cart_count', 'm_c_i_b'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' .tbp_cart_count', 'm_c_i_b', 'h'),
					)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border(' .tbp_cart_count', 'b_c_i_b')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border(' .tbp_cart_count', 'b_c_i_b', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tbp_cart_count', 'r_c_c_i_b')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tbp_cart_count', 'r_c_c_i_b', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tbp_cart_count', 'sh_c_i_b')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tbp_cart_count', 'sh_c_i_b', 'h')
						)
					)
				))
			)),
		);

		$cart_panel = array(
			// Background
			self::get_expand('Panel', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_image(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_list'), 'b_i','bg_c','b_r','b_p'),
						self::get_padding(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_list'), 'p_c_p'),
						self::get_margin(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_list'), 'm_c_p'),
						self::get_border(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_wrap'), 'b_c_p'),
						self::get_border_radius(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_list'), 'r_c_c_p'),
						self::get_box_shadow(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_wrap'), 'sh_c_p')
					)
					),
					'h' => array(
					'options' => array(
						self::get_image(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_list'), 'b_i','bg_c','b_r','b_p', 'h'),
						self::get_padding(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_list'), 'p_c_p', 'h'),
						self::get_margin(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_list'), 'm_c_p', 'h'),
						self::get_border(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_wrap'), 'b_c_p', 'h'),
						self::get_border_radius(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_list'), 'r_c_c_p', 'h'),
						self::get_box_shadow(array(' .tbp_slide_cart', '.tbp_cart_icon_style_dropdown .tbp_cart_wrap'), 'sh_c_p', 'h')
					)
					)
				))
			)),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_font_family(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'f_f_c_p'),
						self::get_color(' .tbp_cart_wrap', 'f_c_c_p'),
						self::get_font_size(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'f_s_c_p'),
						self::get_letter_spacing(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'l_s_c_p'),
						self::get_line_height(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'l_h_c_p'),
						self::get_font_style(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'f_st_c_p', 'f_w_c_p'),
						self::get_text_shadow(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 't_sh_c_p'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_font_family(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'f_f_c_p','h'),
						self::get_color(' .tbp_cart_wrap', 'f_c_c_p', null,null, 'h'),
						self::get_font_size(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'f_s_c_p', '', 'h'),
						self::get_letter_spacing(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'l_s_c_p', 'h'),
						self::get_line_height(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'l_h_c_p', 'h'),
						self::get_font_style(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 'f_st_c_p', 'f_w_c_p', 'h'),
						self::get_text_shadow(array(' .tbp_cart_wrap', ' .tbp_cart_wrap .tbp_cart_product .tbp_cart_title'), 't_sh_c_p','h'),
					)
					)
				))
			)),
			// Link
			self::get_expand('l', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' .tbp_cart_wrap .tbp_cart_list a', 'c_c_p_l'),
						self::get_font_style(' .tbp_cart_wrap .tbp_cart_list a', 'f_st_c_p_l', 'f_w_c_p_l'),
						self::get_text_decoration(' .tbp_cart_wrap .tbp_cart_list a', 't_d_l')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .tbp_cart_wrap .tbp_cart_list a', 'c_c_p_l_h',null, null, 'hover'),
						self::get_font_style(' .tbp_cart_wrap .tbp_cart_list a', 'f_st_c_p_l', 'f_w_c_p_l', 'h'),
						self::get_text_decoration(' .tbp_cart_wrap .tbp_cart_list a', 't_d_l', 'h')
					)
					)
				))
			)),
			// Cart Panel Button
			self::get_expand(__('Button', 'tbp'), array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' .tbp_cart_wrap button', 'bc_c_c_p_b', 'bg_c', 'background-color'),
						self::get_color(' .tbp_cart_wrap button', 'c_c_p_b'),
						self::get_font_family(' .tbp_cart_wrap button', 'f_f_c_p_b'),
						self::get_font_size(' .tbp_cart_wrap button', 'f_s_c_p_b'),
						self::get_letter_spacing(' .tbp_cart_wrap button', 'l_s_c_p_b'),
						self::get_line_height(' .tbp_cart_wrap button', 'l_h_c_p_b'),
						self::get_font_style(' .tbp_cart_wrap button', 'f_st_c_p_b', 'f_w_c_p_b'),
						self::get_text_shadow(' .tbp_cart_wrap button', 't_sh_c_p_b'),
						self::get_padding(' .tbp_cart_wrap button', 'p_c_p_b'),
						self::get_margin(' .tbp_cart_wrap button', 'm_c_p_b'),
						self::get_border(' .tbp_cart_wrap button', 'b_c_p_b'),
						self::get_border_radius(' .tbp_cart_wrap button', 'r_c_c_p_b'),
						self::get_box_shadow(' .tbp_cart_wrap button', 'sh_c_p_b')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .tbp_cart_wrap button:hover', 'bc_c_c_p_b_h', 'bg_c', 'background-color', null, 'h'),
						self::get_color(' .tbp_cart_wrap button', 'c_c_p_b_h',null, null, 'h'),
						self::get_font_family(' .tbp_cart_wrap button', 'f_f_c_p_b','h'),
						self::get_font_size(' .tbp_cart_wrap button', 'f_s_c_p_b', '', 'h'),
						self::get_letter_spacing(' .tbp_cart_wrap button', 'l_s_c_p_b', 'h'),
						self::get_line_height(' .tbp_cart_wrap button', 'l_h_c_p_b', 'h'),
						self::get_font_style(' .tbp_cart_wrap button', 'f_st_c_p_b', 'f_w_c_p', 'h'),
						self::get_text_shadow(' .tbp_cart_wrap button', 't_sh_c_p_b','h'),
						self::get_padding(' .tbp_cart_wrap button', 'p_c_p_b', 'h'),
						self::get_margin(' .tbp_cart_wrap button', 'm_c_p_b', 'h'),
						self::get_border(' .tbp_cart_wrap button', 'b_c_p_b', 'h'),
						self::get_border_radius(' .tbp_cart_wrap button', 'r_c_c_p_b', 'h'),
						self::get_box_shadow(' .tbp_cart_wrap button', 'sh_c_p_b', 'h')
					)
					)
				)),
			))
		);

		return array(
			'type' => 'tabs',
			'options' => array(
				'g' => array(
					'options' => $general
				),
				's_t' => array(
					'label' => __('Sub Total', 'tbp'),
					'options' => $sub_total
				),
				's_i' => array(
					'label' => __('Icon', 'tbp'),
					'options' => $icon
				),
				'b_b' => array(
					'label' => __('Bubble', 'tbp'),
					'options' => $bubble
				),
				'c_p' => array(
					'label' => __('Cart Panel', 'tbp'),
					'options' => $cart_panel
				)
			)
		);
	}

	public function get_live_default() {
		return array(
			'icon' => 'ti-shopping-cart'
		);
	}

	public function get_visual_type() {
		return 'ajax';
    }

    public function get_category() {
		return array( 'product' );
	}

}

if ( themify_is_woocommerce_active() ) {
	Themify_Builder_Model::register_module('TB_Cart_Icon_Module');
}
