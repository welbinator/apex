<?php

class Tbp_Dynamic_Content {

	private static $items = null;

	/**
	 * Name of the option that stores Dynamic Content settings
	 *
	 * @type string
	 */
	private static $field_name = '__dc__';

	public static function run() {
		add_filter( 'tf_builder_row', array( __CLASS__, 'tf_builder_row' ) );
		add_filter( 'themify_builder_module_render_vars', array( __CLASS__, 'themify_builder_module_render_vars' ) );
	    if ( Themify_Builder_Model::is_frontend_editor_page()) {
			add_action( 'themify_builder_frontend_enqueue', array( __CLASS__, 'admin_enqueue' ) );
			add_action( 'themify_builder_admin_enqueue', array( __CLASS__, 'admin_enqueue' ), 15 );
			add_action( 'wp_ajax_tpb_get_dynamic_content_fields', array( __CLASS__, 'options' ) );
			add_action( 'wp_ajax_tpb_get_dynamic_content_preview', array( __CLASS__, 'preview' ) );
	    }
	    add_action( 'themify_builder_background_styling', array( __CLASS__, 'background_styling' ), 10, 4 );
	}

	private static function register_items() {
		if ( self::$items !== null ) {
			return;
		}

		$items = self::$items = array();
		$base_path = TBP_DIR . 'includes/dynamic-content/';
		$files = scandir ( $base_path );
		foreach ($files as $file) {
			if ( $file !== '.' && $file !== '..' && $file !== '.svn' ) {
				include_once $base_path . $file;
				$name = pathinfo( $file, PATHINFO_FILENAME );
				$items[ $name ] = "Tbp_Dynamic_Item_{$name}";
			}
		}
		$files = $file = null;
		$items = apply_filters( 'tbp_dynamic_items', $items );
		foreach ( $items as $id => $class ) {
		    $instance = new $class();
		    /* add this item only if is_available() */
		    if ( $instance->is_available() === true ) {
			    self::$items[ $id ] = $instance;
		    } else {
				$instance = null;
		    }
		}
	}

	public static function get( $id = null ) {
		return $id === null ? self::$items : ( isset( self::$items[ $id ] ) ? self::$items[ $id ] : false );
	}

	/**
	 * Returns an assoc array
	 *
	 * @return array
	 */
	public static function get_list() {
		$list = array();
		foreach ( self::$items as $id => $instance ) {
			$list[ $id ] = array(
				'type' => $instance->get_type(),
			);
		}

		return $list;
	}

	/**
	 * Adds inline styles for styling the background image of Builder components
	 *
	 * hooked to "themify_builder_background_styling"
	 */
	public static function background_styling( $builder_id, $settings, $order_id, $type ) {
		if ( ! isset( $settings[ 'styling' ][ self::$field_name ] ) || $settings[ 'styling' ][ self::$field_name ]==='{}' ) {
			return;
		}
		$dc = is_string($settings[ 'styling' ][ self::$field_name ])?json_decode( $settings[ 'styling' ][ self::$field_name ], true ):$settings[ 'styling' ][ self::$field_name ];
		
		if ( ! is_array( $dc ) ) {
			return;
		}

		self::register_items();

		static $cacheIDs=array();
		if ( $type==='row' || $type==='column' || $type==='subrow' ) {
			$element_id = isset($settings['element_id'])?'tb_'.$settings['element_id']:$order_id;
			if(!isset($cacheIDs[$type])){
				$bg_fields=array('background_image'=>'');
				$inner_select='>div.';
				$inner_select.=$type==='column'?'tb-column-inner':$type.'_inner';
				$bg_fields['background_image_inner']=array($inner_select);
				if($type==='column' && Themify_Builder::$frontedit_active===true){
				    $bg_fields['background_image_inner'][]= '>.tb_holder';
				}
			}
			else{
				$cacheIDs[$type]=$bg_fields;
			}
			$type_selector='.module_' . $type;
			
		} else {
			$mod_name = $settings['mod_name'];
			
			$element_id=$order_id;
			if(!isset($cacheIDs[$mod_name])){
			    $module = Themify_Builder_Model::$modules[ $mod_name ];
			    $styling = $module->get_form_settings( true );
			    $module=null;
			    $bg_fields = self::get_background_image_fields( $styling );
			    $styling=null;
			    $cacheIDs[$mod_name]=$bg_fields;
			}
			else{
			    $bg_fields =$cacheIDs[$mod_name];
			}
			$type_selector='.module-' . $mod_name;
		}
		if ( empty( $bg_fields )) {
			return;
		}
		$intersect = array_intersect_key($dc,$bg_fields);
		if(empty($intersect)){
			return;
		}
		$dc=null;
		$styles = '';
		$base='';
		if(Tbp_Utils::$isLoop===true){
		    if(class_exists('TB_Advanced_Posts_Module') && TB_Advanced_Posts_Module::$builder_id!==null){
				$builder_id = str_replace( 'tb_', '', TB_Advanced_Posts_Module::$builder_id );
		    }
		    elseif(class_exists('TB_Advanced_Products_Module') && TB_Advanced_Products_Module::$builder_id!==null){
				$builder_id = str_replace( 'tb_', '', TB_Advanced_Products_Module::$builder_id );
		    }
		}
		else{
		    $base='.themify_builder';
		}
		$base.='.themify_builder_content-'.$builder_id;
		foreach ( $intersect as $key => $options ) {
			if ( $value = self::get_value( $options ) ) {
				$selector = $base;
				if(Tbp_Utils::$isLoop===true){
				    $selector.=' .post-'.get_the_ID();
				}
				$selector.=" {$type_selector}.{$element_id}";
				if(is_string($bg_fields[ $key ])){
				    $selector.=$bg_fields[ $key ];
				}
				else{
				    $selector.=implode(',',$bg_fields[ $key ]);
				}
			    $styles.= $selector . '{ background-image:url("' . $value . '") }';
			
			}
		}
		if ( $styles!=='' ) {
		    echo '<style class="tbp_dc_styles">' . $styles . '</style>';
		}
	}

	/**
	 * Loops through a component styling definition to find all background-image fields
	 *
	 * @return array
	 */
	private static function get_background_image_fields( array $array ) {
		$iterator  = new RecursiveArrayIterator( $array );
		$recursive = new RecursiveIteratorIterator( $iterator, RecursiveIteratorIterator::SELF_FIRST );
		$list = array();
		foreach ( $recursive as  $value ) {
			if ( isset( $value['prop'], $value['id'] ) && !isset($value['ishover']) && $value['prop'] === 'background-image' && ($value['label']==='bg' ||  $value['label']==='b_i')  && ($value['type']==='image' ||  $value['type']==='imageGradient')) {
				$list[ $value['id'] ] = $value['selector'];
			}
		}
		return $list;
	}

	/**
	 * Loop through a row
	 *
	 * @return array
	 */
	public static function tf_builder_row( $row ) {
		if ( ! empty( $row['cols'] ) ) {
			foreach ( $row['cols'] as $column_index => $column ) {
				if ( ! empty( $column['modules'] ) ) {
					foreach ( $column['modules'] as $module_index => $module ) {
						/* subrows */
						if ( ! empty( $module['cols'] ) ) {
							foreach ( $module['cols'] as $sub_column_index => $sub_column ) {
								if ( ! empty( $sub_column['modules'] ) ) {
									foreach ( $sub_column['modules'] as $sub_column_module_index => $sub_column_module ) {
										$replace = self::do_replace( $row['cols'][ $column_index ]['modules'][ $module_index ]['cols'][ $sub_column_index ]['modules'][ $sub_column_module_index ] );
										if ( $replace === '__disable_module__' ) {
											unset( $row['cols'][ $column_index ]['modules'][ $module_index ]['cols'][ $sub_column_index ]['modules'][ $sub_column_module_index ] );
										} else if ( $replace === '__disable_row__' ) {
											// hide entire row
											return array();
										} else if ( $replace === '__disable_subrow__' ) {
											// hide subrow
											unset( $row['cols'][ $column_index ]['modules'][ $module_index ] );
										} else {
											$row['cols'][ $column_index ]['modules'][ $module_index ]['cols'][ $sub_column_index ]['modules'][ $sub_column_module_index ] = $replace;
										}
									}
								}
							}
						} else {
							$replace = self::do_replace( $row['cols'][ $column_index ]['modules'][ $module_index ], true );
							if ( $replace === '__disable_module__' ) {
								/* hide the module */
								unset( $row['cols'][ $column_index ]['modules'][ $module_index ] );
							} else if ( $replace === '__disable_row__' ) {
								/* hide the entire row */
								return array();
							} else {
								$row['cols'][ $column_index ]['modules'][ $module_index ] = $replace;
							}
						}
					}
				}
			}
		}

		return $row;
	}

	/**
	 * Filter module settings in preview
	 *
	 * @return array
	 */
	public static function themify_builder_module_render_vars( $vars ) {
		if ( Themify_Builder::$frontedit_active === true ) {
			$vars = self::do_replace( $vars );
		}

		return $vars;
	}

	/**
	 * Parse DC settings and replace the module settings in $vars with their values.
	 *
	 * @return array|string
	 */
	public static function do_replace( $vars, $ignore_disable_subrow_condition = false ) {
		if ( ! isset( $vars['mod_settings'][ self::$field_name ] ) || $vars['mod_settings'][ self::$field_name ]==='{}' )
			return $vars;
		$fields = is_string($vars['mod_settings'][ self::$field_name ])?json_decode( $vars['mod_settings'][ self::$field_name ], true ):$vars['mod_settings'][ self::$field_name ];
	
		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return $vars;
		}

		self::register_items();

		foreach ( $fields as $key => $options ) {
			if ( ! isset( $options['item'] ) || isset( $options['repeatable'] ) ) {
			    if ( isset( $vars['mod_settings'][ $key ] ) && is_array( $vars['mod_settings'][ $key ] ) ) {
					unset( $options['repeatable'], $options['o'] );
					// loop through repeatable items
					if ( ! empty( $options ) && is_array( $options ) ) {
						foreach ( $options as $i => $items ) {
							if ( ! empty( $items ) ) {
								foreach ( $items as $field_name => $field_options ) {
									if ( isset( $field_options['item'] ) ) {
										$value = self::get_value( $field_options );
										if ( in_array( $value, array( '__disable_module__', '__disable_row__' ) ) ) {
											return $value;
										} else if ( '__disable_subrow__' === $value ) {
											if ( $ignore_disable_subrow_condition ) {
												// replace field with empty string
												$vars['mod_settings'][ $key ][ $i ][ $field_name ] = '';
											} else {
												// flag the subrow to be removed
												return '__disable_subrow__';
											}
										} else {
											$vars['mod_settings'][ $key ][ $i ][ $field_name ] = $value;
										}
									}
								}
							}
						}
					}
			    }
			} else {
				$value = self::get_value( $options );
				if ( in_array( $value, array( '__disable_module__', '__disable_row__' ) ) ) {
					// special flags, return the string instead of replacing
					return $value;
				} else if ( '__disable_subrow__' === $value ) {
					if ( $ignore_disable_subrow_condition ) {
						// replace field with empty string
						$vars['mod_settings'][ $key ] = '';
					} else {
						// flag the subrow to be removed
						return '__disable_subrow__';
					}
				} else {
					$vars['mod_settings'][ $key ] = $value;
				}
			}
		}

		return $vars;
	}

	/**
	 * Get value from saved DC settings
	 *
	 * Calls Tbp_Dynamic_Content::get_value for $options['item']
	 */
	private static function get_value( $options ) {
		if ( isset( $options['item'] ) && ( $item = self::get( $options['item'] ) ) ) {
			unset( $options['item'] );

			$value = $item->get_value( $options );

			if ( empty( $value ) ) {
				if ( ! empty( $options['condition'] ) && ! Themify_Builder::$frontedit_active ) {
					if ( $options['condition'] === 'hide_module' ) {
						return '__disable_module__'; // flag to remove the module
					} else if ( $options['condition'] === 'hide_row' ) {
						return '__disable_row__'; // flag to remove the row
					} else if ( $options['condition'] === 'hide_subrow' ) {
						return '__disable_subrow__'; // flag to remove the subrow
					}
				}
			} else {
				// general formatting for various field types
				if ( isset( $options['text_before'] ) ) {
					$value = $options['text_before'] . $value;
				}
				if ( isset( $options['text_after'] ) ) {
					$value .= $options['text_after'];
				}
				if ( ! empty( $options['uri_scheme'] ) ) {
					$value = $options['uri_scheme'] . ':' . $value;
				}
			}

			return $value;
		}

		return null;
	}

	public static function admin_enqueue() {
	    self::register_items();

		$v = Tbp::get_version();
	    wp_enqueue_script( 'tbp-dynamic-content', themify_enque(TBP_URL . 'admin/js/tbp-dynamic-content.js') , array( 'themify-builder-app-js' ), $v, true );
	    wp_localize_script( 'tbp-dynamic-content', 'tbpDynamic',
		    array(
			    'items' => self::get_list(),
			    'field_name' => self::$field_name,
			    'v'=>$v,
			    'd_label'=>__('Dynamic','tbp'),
			    'emptyVal'=>__('Empty Value','tbp'),
			    'placeholder_image' => TBP_URL . 'admin/img/template-placeholder.png',
			    'excludes' =>self::get_option_excludes()
		    )
	    );
	}

	/**
	 * list of option IDs that will not have DC enabled on them
	 *
	 * @return array
	 */
	private static function get_option_excludes() {
		return array(
			'item_title_field',
			'placeholder',
			'button_t',
			'custom_url',
			'fallback_i',
			'prev_label',
			'next_label',
			'custom_link',
			'cat',
			'tag',
			'sku',
			'sep',
		);
	}

	/**
	 * Generate preview value
	 *
	 * Hooked to "wp_ajax_tpb_get_dynamic_content_preview"
	 */
	public static function preview() {
		check_ajax_referer( 'tb_load_nonce', 'tb_load_nonce' );
		Themify_Builder::$frontedit_active = true;
		// before rendering the dynamic value, first set up the WP Loop
		Tbp_Utils::$isLoop=true;
		if ( isset( $_POST['pid'] )) {
		    $post_id = (int) $_POST['pid'];
		    if ( $post_object = get_post( $post_id ) ) {
			    setup_postdata( $GLOBALS['post'] =& $post_object );
		    }
		}
		$options = ! empty( $_POST['values'] )? json_decode( stripslashes_deep( $_POST['values'] ), true ) : array();
		if ( isset( $options['item'] ) ) {
			self::register_items();
		    $value = array( 'value' => self::get_value( $options ) );
		} else {
			$value = array( 'error' => __( 'Invalid value.', 'tbp' ) );
		}
		die( json_encode( $value ) );
	}
	
	public static function options() {
		check_ajax_referer('tb_load_nonce', 'tb_load_nonce');

		self::register_items();

		$items_list = $items_settings = array();
		$categories = array(
			'disabled' => '',
			'general' => __( 'General', 'tbp' ),
			'post' => __( 'Post', 'tbp' ),
			'wc' => __( 'WooCommerce', 'tbp' ),
			'advanced' => __( 'Advanced', 'tbp' ),
			'ptb' => __( 'Themify Post Type Builder', 'tbp' ),
			'acf' => __( 'Advanced Custom Fields', 'tbp' ),
		);
		$items = self::get();
		$items_list['empty'] = array( 'options' => array( '' => '' ) );
		foreach ( $items as $id => $class ) {
			$cat_id = $class->get_category();
			if ( ! isset( $items_list[ $cat_id ] ) ) {
			    $items_list[ $cat_id ] = array(
					'label' => $categories[ $cat_id ],
					'options' => array()
			    );
			}
			$items_list[ $cat_id ]['options'][ $id ] = $class->get_label();

			if ( $options = $class->get_options() ) {
				$items_settings[ $id ] = array(
					'type' => 'group',
					'options' =>  $options,
					'wrap_class' => 'field_' . $id,
				);
			}
		}
		$data = array();
		foreach($categories as $k=>$v){
			if(isset($items_list[$k])){
				$data[$k]=$items_list[$k];
			}
		}
		$items=$categories=$items_list=null;
		$items_settings['general_text'] = array(
			'type' => 'group',
			'options' => array(
				array(
					'label' => __( 'Text Before', 'tbp' ),
					'id' => 'text_before',
					'type' => 'text'
				),
				array(
					'label' => __( 'Text After', 'tbp' ),
					'id' => 'text_after',
					'type' => 'text'
				),
			),
			'wrap_class' => 'field_general_text field_general_textarea field_general_wp_editor'
		);
		$items_settings['general_url'] = array(
			'type' => 'group',
			'options' => array(
				array(
					'label' => __( 'URI Scheme', 'tbp' ),
					'id' => 'uri_scheme',
					'type' => 'select',
					'options' => array(
						'0' => __( 'None', 'tbp' ),
						'tel' => __( 'Telephone', 'tbp' ),
						'mailto' => __( 'Email', 'tbp' ),
						'sms' => __( 'Text Message', 'tbp' ),
						'fax' => __( 'Fax', 'tbp' ),
					)
				),
			),
			'wrap_class' => 'field_general_url',
		);
		$items_settings['general_condition'] = array(
			'type' => 'group',
			'options' => array(
				array(
					'type' => 'separator',
					'label' => __('Display Condition', 'tbp')
				),
				array(
					'label' => __( 'When Empty:', 'tbp' ),
					'id' => 'condition',
					'type' => 'select',
					'options' => array(
						'' => '',
						'hide_module' => __( 'Hide the module', 'tbp' ),
						'hide_subrow' => __( 'Hide the subrow', 'tbp' ),
						'hide_row' => __( 'Hide the row', 'tbp' ),
					)
				),
			),
			'wrap_class' => 'tbp_dynamic_content_condition field_general_text field_general_textarea field_general_wp_editor field_general_url field_general_image',
		);
		$options = array(
			array(
				'id' => 'item',
				'type' => 'select',
				'options' => $data,
				'control' => false,
				'optgroup' => true
			),
			array(
				'type' => 'group',
				'options' => $items_settings,
				'wrap_class' => 'field_settings'
			),
		);
		die( json_encode( $options ) );
	}
}

class Tbp_Dynamic_Item {

	/**
	 * Returns true if this item is available.
	 *
	 * @return bool
	 */
	public function is_available() {
		return true;
	}
	/**
	 * Returns an array of Builder field types this item applies to.
	 *
	 * @return array
	 */
	public function get_type() {
		return array();
	}

	/**
	 * Returns the category this item belongs to
	 *
	 * @return string
	 */
	public function get_category() {
		return '';
	}

	public function get_label() {
		return '';
	}

	public function get_value( $args = array() ) {
		return null;
	}

	public function get_options() {
		return array();
	}
}