<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Module Name: Search Form
 * Description: 
 */

class TB_Search_Form_Module extends Themify_Builder_Component_Module {

    function __construct() {
	parent::__construct(array(
	    'name' => __('Search Form', 'tbp'),
	    'slug' => 'search-form',
	    'category' => array('site')
	));
    }
    
    public function get_assets() {
	return array(
	    'ver'=>Tbp::get_version(),
	    'css'=>TBP_CSS_MODULES.$this->slug.'.css'
	);
    }
    
    public function get_icon(){
	return 'search';
    }

    public function get_options() {
		return array(
            array(
                'id'      => 'style',
                'type'    => 'radio',
                'label'   => __( 'Search Style', 'tbp'),
                'options' => array(
                    array( 'name' => __( 'Search form', 'tbp'), 'value' => 'form' ),
                    array( 'name' => __( 'Overlay', 'tbp'), 'value' => 'overlay' )
                ),
                'option_js' => true
            ),
            array(
                'id'      => 'autocomplete',
                'type'    => 'toggle_switch',
                'label'   => __( 'Ajax autocomplete', 'tbp'),
                'options' => array(
                    'off' => array( 'value' =>  'dis', 'name' => 'off' ),
                    'on' => array( 'value' => 'en', 'name' => 'on' ),
                ),
                'wrap_class' => 'tb_group_element_form'
            ),
		    array(
				'id' => 'placeholder',
				'type' => 'text',
				'label' => __('Placeholder', 'tbp'),
                'wrap_class' => 'tb_group_element_form'
			),
			array(
				'type' => 'query_posts',
				'id' => 'post_type',
				'all' => true,
				'exclude' => false
			),
			array(
				'id'      => 'button',
				'type'    => 'toggle_switch',
				'label'   => __( 'Button', 'tbp'),
				'options'   => array(
					'on'  => array( 'name' => 'yes', 'value' => 's' ),
					'off' => array( 'name' => 'no', 'value' => 'hi' ),
				),
				'binding' => array(
					'checked' => array( 'show' => array(  'icon', 'button_t') ),
					'not_checked' => array( 'hide' => array( 'icon', 'button_t' ) ),
				),
                'wrap_class' => 'tb_group_element_form'
			),
			array(
				'id' => 'icon',
				'type' => 'select',
				'label' => '',
				'options' => array(
					'icon' => __('Icon', 'tbp'),
					'text' => __('Text', 'tbp'),
				),
				'binding' => array(
					'icon' => array( 'hide' => 'button_t'),
					'text' => array( 'show' =>  'button_t' ),
				),
                'wrap_class' => 'tb_group_element_form'
			),
			array(
				'id' => 'button_t',
				'type' => 'text',
				'label' => '',
                'wrap_class' => 'tb_group_element_form'
			),
			array(
				'id'      => 'button_icon',
				'type'    => 'toggle_switch',
				'label'   => __( 'Search Icon', 'tbp'),
				'options'   => array(
					'on'  => array( 'name' => 'yes', 'value' => 's' ),
					'off' => array( 'name' => 'no', 'value' => 'hi' ),
				),
                'wrap_class' => 'tb_group_element_form'
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
						self::get_font_family('.module .tbp_searchform', 'f_f_g'),
						self::get_color_type('.module .tbp_searchform','', 'f_c_t_g',  'f_c_g', 'f_g_c_g'),
						self::get_font_size('.module .tbp_searchform', 'f_s_g', ''),
						self::get_line_height('.module .tbp_searchform', 'l_h_g'),
						self::get_letter_spacing('.module .tbp_searchform', 'l_s_g'),
						self::get_text_align('.module .tbp_searchform', 't_a_g'),
						self::get_text_transform('.module .tbp_searchform', 't_t_g'),
						self::get_font_style('.module .tbp_searchform', 'f_st_g', 'f_w_g'),
						self::get_text_decoration('.module .tbp_searchform', 't_d_r_g'),
						self::get_text_shadow('.module .tbp_searchform','t_sh_g','h'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_font_family('.module .tbp_searchform', 'f_f_g_h'),
						self::get_color_type('.module .tbp_searchform','', 'f_c_t_g_h',  'f_c_g_h', 'f_g_c_g_h'),
						self::get_font_size('.module .tbp_searchform', 'f_s_g', '', 'h'),
						self::get_line_height('.module .tbp_searchform', 'l_h_g', 'h'),
						self::get_letter_spacing('.module .tbp_searchform', 'l_s_g', 'h'),
						self::get_text_align('.module .tbp_searchform', 't_a_g', 'h'),
						self::get_text_transform('.module .tbp_searchform', 't_t_g', 'h'),
						self::get_font_style('.module .tbp_searchform', 'f_st_g', 'f_w_g', 'h'),
						self::get_text_decoration('.module .tbp_searchform', 't_d_r_g', 'h'),
						self::get_text_shadow('.module .tbp_searchform','t_sh_g','h'),
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
			! method_exists( $this, 'get_max_height' ) ? array() :
			// Height & Min Height
			self::get_expand('ht', array(
				self::get_height('', 'g_h'),
				self::get_min_height('', 'g_m_h'),
				self::get_max_height('', 'g_m_h')
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

		$inputs = array(
		    self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
				       self::get_color(' .tbp_searchform input', 'b_c_i', 'bg_c', 'background-color'),
				   )
			       ),
			       'h' => array(
				   'options' => array(
				         self::get_color(' .tbp_searchform input', 'b_c_i', 'bg_c', 'background-color','h'),
				   )
			       )
			   ))
		    )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'f_f_i'),
				    self::get_color(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'), 'f_c_i'),
				    self::get_font_size(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'f_s_i'),
				    self::get_line_height(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'l_h_i'),
					self::get_text_transform(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'t_tf_i'),
					self::get_text_shadow(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'t_sh_i'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'f_f_i','h'),
				    self::get_color(array(' .tbp_searchform input:hover', ' .tbp_searchform input:hover::placeholder'), 'f_c_i_h',null,null,''),
				    self::get_font_size(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'f_s_i','','h'),
				    self::get_line_height(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'l_h_i', 'h'),
					self::get_text_transform(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'t_tf_i','h'),
					self::get_text_shadow(array(' .tbp_searchform input', ' .tbp_searchform input::placeholder'),'t_sh_i','h'),
				)
			    )
			))
		    )),
		    // Border
		    self::get_expand('b', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_border(' .tbp_searchform input','in_b')
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_border(' .tbp_searchform input','in_b','h')
				)
			    )
			))
		    )),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .tbp_searchform input', 'in_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .tbp_searchform input', 'in_p', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' .tbp_searchform input', 'in_m')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' .tbp_searchform input', 'in_m', 'h')
					)
					)
				))
			)),
			// Width
			self::get_expand('w', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_width(' .tbp_searchform input', 'in_w')
						)
					),
					'h' => array(
						'options' => array(
							self::get_width(' .tbp_searchform input', 'in_w', 'h')
						)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tbp_searchform input', 'in_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tbp_searchform input', 'in_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tbp_searchform input', 'in_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tbp_searchform input', 'in_b_sh', 'h')
						)
					)
				))
			)),
		);
		
		$search_button = array(
		    
		    self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
				       self::get_color(' .tbp_searchform button', 'b_c_s', 'bg_c', 'background-color')
				   )
			       ),
			       'h' => array(
				   'options' => array(
				        self::get_color(' .tbp_searchform button', 'b_c_s', 'bg_c', 'background-color','h')
				   )
			       )
			   ))
		    )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(' .tbp_searchform button' ,'f_f_s'),
				    self::get_color( ' .tbp_searchform button', 'f_c_s'),
				    self::get_font_size( ' .tbp_searchform button','f_s_s'),
				    self::get_line_height( ' .tbp_searchform button','l_h_s'),
					self::get_text_transform(' .tbp_searchform button' ,'t_tf_s'),
					self::get_text_shadow(' .tbp_searchform button' ,'t_sh_s'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(' .tbp_searchform button' ,'f_f_s','h'),
				    self::get_color( ' .tbp_searchform button', 'f_c_s',null,null,'h'),
				    self::get_font_size( ' .tbp_searchform button','f_s_s','','h'),
				    self::get_line_height( ' .tbp_searchform button','l_h_s', 'h'),
					self::get_text_transform(' .tbp_searchform button' ,'t_tf_s','h'),
					self::get_text_shadow(' .tbp_searchform button' ,'t_sh_s','h'),
				)
			    )
			))
		    )),
		    // Border
		    self::get_expand('b', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_border(' .tbp_searchform button','b_s')
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_border(' .tbp_searchform button','b_s','h')
				)
			    )
			))
		    )),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .tbp_searchform button', 'p_sd')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .tbp_searchform button', 'p_sd', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .tbp_searchform button', 'm_sd')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .tbp_searchform button', 'm_sd', 'h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tbp_searchform button', 'r_c_sd')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tbp_searchform button', 'r_c_sd', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tbp_searchform button', 's_sd')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tbp_searchform button', 's_sd', 'h')
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
				'i' => array(
					'label' => __('Search Input', 'tbp'),
					'options' => $inputs
				),
				's_b' => array(
					'label' => __('Button', 'builder-contact'),
					'options' => $search_button
				)
			)
		);
	}

	public function get_live_default() {
		return array(
			'placeholder' => __( 'Search', 'tbp'),
			'post_type' => 'any',
			'icon' => 'icon',
			'button' => 'yes',
			'button_t' => __( 'Search', 'tbp')
		);
	}


	public function get_visual_type() {
		return 'ajax';
    }

    public function get_category() {
		return array( 'general' );
	}

}

Themify_Builder_Model::register_module('TB_Search_Form_Module');
