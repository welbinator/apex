<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://themify.me/
 * @since      1.0.0
 *
 * @package    Tbp
 * @subpackage Tbp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tbp
 * @subpackage Tbp/includes
 * @author     Themify <themify@themify.me>
 */
final class Tbp {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected static $plugin_name='tbp';
	
	
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected static $version;

	protected static $active_theme;

	private function __construct() {

	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public static function run() {
	    if (class_exists( 'Themify_Builder' ) ) {
		    self::$version = current(get_file_data( TBP_DIR.'themify-builder-pro.php', array( 'Version') ));
		    self::register_cpt();//should work on init
		    self::load_dependencies();
			self::load_active_theme();

		    $is_ajax = Tbp_Utils::isAjax();
		    $is_admin = $is_ajax===true || is_admin();
		    if ( $is_ajax===true || $is_admin===true || Tbp_Utils::isRest() ) {
				Tbp_Admin::run();
		    }
		    if($is_ajax===true || $is_admin===false){
		       Tbp_Public::run();
		    }
		    if(did_action('themify_builder_setup_modules')>0){
				self::init();
		    }
		    else{
				add_action('themify_builder_setup_modules',array(__CLASS__,'init'));
		    }

			self::plugins_compatibility();
	    }
	}

	/**
	 * Set up plugin's action hooks
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::register_module();
		Tbp_Dynamic_Content::run();
		Tbp_Dynamic_Query::run();
		if(is_admin()){
		    Tbp_Import_Demo::run();
		}	
	}

	private static function register_module() {
		Themify_Builder_Model::register_directory( 'templates', TBP_DIR . 'templates' );
		Themify_Builder_Model::register_directory( 'modules', TBP_DIR . 'modules' );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tbp_Loader. Orchestrates the hooks of the plugin.
	 * - Tbp_i18n. Defines internationalization functionality.
	 * - Tbp_Admin. Defines all hooks for the admin area.
	 * - Tbp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private static function load_dependencies() {
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */

		/**
		 * The class responsible for various functions.
		 */
		require_once TBP_DIR. 'includes/class-tbp-utils.php';
		
		
		/**
		 * Handles Dynamic Content feature.
		 */
		require_once TBP_DIR. 'includes/class-tbp-dynamic-content.php';

		require_once TBP_DIR. 'includes/class-tbp-dynamic-query.php';

		require_once TBP_DIR. 'includes/tbp-maps-pro-integration.php';

		if(is_admin()){
		    /**
		     * The class responsible for pointer functions.
		     */
		    require_once TBP_DIR. 'admin/class-tbp-import-demo.php';
		    require_once TBP_DIR. 'admin/class-tbp-pointers.php';
		}
		/**
		 * The class responsible for themes functions.
		 */
		require_once TBP_DIR. 'includes/class-tbp-themes.php';

		/**
		 * The class responsible for templates functions.
		 */
		require_once TBP_DIR. 'includes/class-tbp-templates.php';
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once TBP_DIR. 'admin/class-tbp-admin.php';
		
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once TBP_DIR. 'public/class-tbp-public.php';

	}

	private static  function load_active_theme() {
		$theme = Tbp_Utils::get_active_theme();

		if ( $theme ) {
			self::$active_theme = $theme;
		} else {
			$theme = new stdClass();
			$theme->post_name = '';
			$theme->ID = null;
			self::$active_theme = $theme;
		}
	}



	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public static function get_version() {
		return self::$version;
	}
	
	public static function get_active_theme(){
	    return self::$active_theme;
	}
	
	
	public static function register_cpt(){
	    register_post_type('tbp_theme',
		    apply_filters( 'tbp_register_post_type_tbp_theme', array(
			    'labels' => array(
				    'name'               => __( 'Themes', 'tbp' ),
				    'singular_name'      => __( 'Theme', 'tbp' ),
				    'menu_name'          => _x( 'Themes', 'admin menu', 'tbp' ),
				    'name_admin_bar'     => _x( 'Theme', 'add new on admin bar', 'tbp' ),
				    'add_new'            => _x( 'Add New', 'theme', 'tbp' ),
				    'add_new_item'       => __( 'Add New Theme', 'tbp' ),
				    'new_item'           => __( 'New Theme', 'tbp' ),
				    'edit_item'          => __( 'Edit Theme', 'tbp' ),
				    'view_item'          => __( 'View Theme', 'tbp' ),
				    'all_items'          => __( 'All Themes', 'tbp' ),
				    'search_items'       => __( 'Search Themes', 'tbp' ),
				    'parent_item_colon'  => __( 'Parent Themes:', 'tbp' ),
				    'not_found'          => __( 'No themes found.', 'tbp' ),
				    'not_found_in_trash' => __( 'No themes found in Trash.', 'tbp' )
			    ),
			    'public'              => false,
			    'exclude_from_search' => true,
			    'publicly_queryable'  => false,
			    'show_ui'             => true,
			    'show_in_menu'        => false,
			    'query_var'           => true,
			    'rewrite'             => array( 'slug' => 'tbp-theme' ),
			    'capability_type'     => 'post',
			    'has_archive'         => true,
			    'hierarchical'        => false,
			    'menu_position'       => null,
			    'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
			    'can_export'          => true,
			    'show_in_rest'=> true
		    ))
	    );

	    register_post_type( 'tbp_template',
		    apply_filters( 'tbp_register_post_type_tbp_template', array(
			    'labels' => array(
				    'name'               => __( 'Templates', 'tbp' ),
				    'singular_name'      => __( 'Template', 'tbp' ),
				    'menu_name'          => _x( 'Templates', 'admin menu', 'tbp' ),
				    'name_admin_bar'     => _x( 'Template', 'add new on admin bar', 'tbp' ),
				    'add_new'            => _x( 'Add New', 'template', 'tbp' ),
				    'add_new_item'       => __( 'Add New Template', 'tbp' ),
				    'new_item'           => __( 'New Template', 'tbp' ),
				    'edit_item'          => __( 'Edit Template', 'tbp' ),
				    'view_item'          => __( 'View Template', 'tbp' ),
				    'all_items'          => __( 'All Templates', 'tbp' ),
				    'search_items'       => __( 'Search Templates', 'tbp' ),
				    'parent_item_colon'  => __( 'Parent Templates:', 'tbp' ),
				    'not_found'          => __( 'No templates found.', 'tbp' ),
				    'not_found_in_trash' => __( 'No templates found in Trash.', 'tbp' )
			    ),
			    'public'              => false,
			    'exclude_from_search' => true,
			    'publicly_queryable'  => current_user_can( 'manage_options' ),
			    'show_ui'             => true,
			    'show_in_menu'        => false,
			    'show_in_admin_bar'   => true,
			    'query_var'           => true,
			    'rewrite'             => array( 'slug' => 'tbp-template' ),
			    'capability_type'     => 'post',
			    'has_archive'         => false,
			    'hierarchical'        => false,
			    'menu_position'       => null,
			    'supports'            => array( 'title', 'thumbnail','revisions' ),
			    'can_export'          => true,
			    'show_in_rest'=> true
		    ))
	    );
	}

	/**
	 * Load compatibility patches for Pro plugin
	 */
	private static function plugins_compatibility(){
		$plugins = array(
			'wooVariationSwatches' => 'woo-variation-swatches-pro/woo-variation-swatches-pro.php',
		);
		foreach ( $plugins as $plugin => $active_check ) {
			if ( Themify_Builder_Model::is_plugin_active( $active_check ) ) {
				include( TBP_DIR . 'includes/plugin-compat/' . $plugin . '.php' );
				$classname = "Themify_Builder_Plugin_Compat_{$plugin}";
				$classname::init();
			}
		}
		unset( $plugins );
	}
}
