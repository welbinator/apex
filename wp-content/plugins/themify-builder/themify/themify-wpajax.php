<?php
/***************************************************************************
 *
 * 	----------------------------------------------------------------------
 * 						DO NOT EDIT THIS FILE
 *	----------------------------------------------------------------------
 * 
 *  				     Copyright (C) Themify
 * 
 *	----------------------------------------------------------------------
 *
 ***************************************************************************/

defined( 'ABSPATH' ) || exit;

// Initialize actions
$themify_ajax_actions = array(
	'plupload',
	'delete_preset',
	'get_404_pages',
	'remove_video',
	'save',
	'reset_styling',
	'reset_settings',
	'pull',
	'add_link_field',
	'media_lib_browse',
	'import_sample_content',
	'erase_sample_content',
	'import_sample_actions',
	'notice_dismiss',
	'clear_all_webp',
	'clear_all_menu',
	'clear_all_concate',
	'clear_all_html',
    'search_autocomplete'
);
foreach($themify_ajax_actions as $action){
	add_action('wp_ajax_themify_' . $action, 'themify_' . $action);
}

//Show 404 page in autocomplete
function themify_get_404_pages(){
    if(!empty($_POST['term'])){
        $args = array(
                'sort_order' => 'asc',
                'sort_column' => 'post_title',
                'post_type' => 'page',
                's'=>  sanitize_text_field($_POST['term']),
		'no_found_rows'=>true,
		'ignore_sticky_posts'=>true,
		'cache_results'=>false,
		'update_post_term_cache'=>false,
		'update_post_meta_cache'=>false,
                'post_status' => 'publish',
                'posts_per_page' => 15
        );
        add_filter( 'posts_search', 'themify_posts_where', 10, 2 );
        $terms = new WP_Query($args);
        $items = array();
        if($terms->have_posts()){
            while ($terms->have_posts()){
                $terms->the_post();
                $items[] = array('value'=>  get_the_ID(),'label'=>  get_the_title());
            }
        }
        echo wp_json_encode($items);
    }
    wp_die();
}

//Search only by post title
function themify_posts_where($search,$wp_query ){       
    if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
        global $wpdb;

        $q = $wp_query->query_vars;
        $n = ! empty( $q['exact'] ) ? '' : '%';

        $search = array();
        $search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $wpdb->esc_like( implode(' ',$q['search_terms']) ) . $n );

        if ( ! is_user_logged_in() )
            $search[] = "$wpdb->posts.post_password = ''";

        $search = ' AND ' . implode( ' AND ', $search );
    }
    return $search;
}
/**
 * AJAX - Plupload execution routines
 * @since 1.2.2
 * @package themify
 */
function themify_plupload() {
    $imgid = $_POST['imgid'];
    ! empty( $_POST[ '_ajax_nonce' ] ) && check_ajax_referer($imgid . 'themify-plupload');
	/** Check whether this image should be set as a preset. @var String */
	$haspreset = isset( $_POST['haspreset'] )? $_POST['haspreset'] : '';
	/** Decide whether to send this image to Media. @var String */
	$add_to_media_library = isset( $_POST['tomedia'] ) ? $_POST['tomedia'] : false;
	/** If post ID is set, uploaded image will be attached to it. @var String */
	$postid = isset( $_POST['topost'] )? $_POST['topost'] : '';
 
    /** Handle file upload storing file|url|type. @var Array */
    $file = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'themify_plupload'));
	
	// if $file returns error, return it and exit the function
	if ( isset( $file['error'] ) && ! empty( $file['error'] ) ) {
		echo json_encode($file);
		exit;
	}

	//let's see if it's an image, a zip file or something else
	$ext = explode('/', $file['type']);
	
	// Import routines
	if( 'zip' == $ext[1] || 'rar' == $ext[1] || 'plain' == $ext[1] ){
		
		$url = wp_nonce_url('admin.php?page=themify');

		if (false === ($creds = request_filesystem_credentials($url) ) ) {
			return true;
		}
		if ( ! WP_Filesystem($creds) ) {
			request_filesystem_credentials($url, '', true);
			return true;
		}
		
		global $wp_filesystem;
		
		if( 'zip' == $ext[1] || 'rar' == $ext[1] ) {
			unzip_file($file['file'], THEME_DIR);
			if( $wp_filesystem->exists( THEME_DIR . '/data_export.txt' ) ){
				$data = $wp_filesystem->get_contents( THEME_DIR . '/data_export.txt' );
				themify_set_data( unserialize( $data ) );
				$wp_filesystem->delete(THEME_DIR . '/data_export.txt');
				$wp_filesystem->delete($file['file']);
			} else {
				_e('Data could not be loaded', 'themify');
			}
		} else {
			if( $wp_filesystem->exists( $file['file'] ) ){
				$data = $wp_filesystem->get_contents( $file['file'] );
				themify_set_data( unserialize( $data ) );
				$wp_filesystem->delete($file['file']);
			} else {
				_e('Data could not be loaded', 'themify');
			}
		}
		
	} else {
		//Image Upload routines
		if( 'tomedia' == $add_to_media_library ){
			
			// Insert into Media Library
			// Set up options array to add this file as an attachment
	        $attachment = array(
	            'post_mime_type' => sanitize_mime_type($file['type']),
	            'post_title' => str_replace('-', ' ', sanitize_file_name(pathinfo($file['file'], PATHINFO_FILENAME))),
	            'post_status' => 'inherit'
	        );
			
			if( $postid ){
				$attach_id = wp_insert_attachment( $attachment, $file['file'], $postid );
			} else {
				$attach_id = wp_insert_attachment( $attachment, $file['file'] );
			}
			$file['id'] = $attach_id;

			// Common attachment procedures
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		    $attach_data = wp_generate_attachment_metadata( $attach_id, $file['file'] );
		    wp_update_attachment_metadata($attach_id, $attach_data);
			
			if( $postid ) {
				
				$full = wp_get_attachment_image_src( $attach_id, 'full' );

				update_post_meta($postid, $_POST['fields'], $full[0]);
				update_post_meta($postid, '_'.$_POST['fields'] . '_attach_id', $attach_id);				
			}

			$thumb = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
			
			//Return URL for the image field in meta box
			$file['thumb'] = $thumb[0];
		}
		/**
		 * Presets like backgrounds and such
		 */
		if( 'haspreset' == $haspreset ){
			// For the sake of predictability, we're not adding this to Media.
			$presets = get_option('themify_background_presets');
			$presets[ $file['file'] ] = $file['url'];
			$_key='themify_background_presets';
			delete_option($_key);
			add_option($_key,$presets, '', false );
			
			/*$presets_attach_id = get_option('themify_background_presets_attach_id');
			$presets_attach_id[ $file['file'] ] = $attach_id;
			update_option('themify_background_presets_attach_id', $presets_attach_id);*/
		}
		
	}
	$file['type'] = $ext[1];
	// send the uploaded file url in response
	echo json_encode($file);
    exit;
}

/**
 * AJAX - Delete preset image
 * @since 1.2.2
 * @package themify
 */
function themify_delete_preset(){
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	
	if( isset($_POST['file']) ){
		$file = $_POST['file'];
		$presets = get_option('themify_background_presets');
		
		if(is_file(THEME_DIR . '/uploads/bg/' . $file)){
			// It's one of the presets budled with the theme
			unlink(THEME_DIR . '/uploads/bg/' . $file);
			echo 'Deleted ' . THEME_DIR . '/uploads/bg/' . $file;
		} else {
			// It's one of the presets uploaded by user to media
			$presets_attach_id = get_option('themify_background_presets_attach_id');
			//wp_delete_attachment($presets_attach_id[stripslashes($file)], true);
			@ unlink(stripslashes($file));
			unset($presets_attach_id[stripslashes($file)]);
			$_key='themify_background_presets_attach_id';
			delete_option($_key);
			add_option($_key,$presets_attach_id, '', false );
		}
		unset($presets[ stripslashes($file) ]);
		$_key='themify_background_presets';
		delete_option($_key);
		add_option($_key,$presets, '', false );
	}
	die();
}


/**
 * AJAX - Remove image assigned in Themify custom panel. Clears post_image and _thumbnail_id field.
 * @since 1.7.4
 * @package themify
 */
function themify_remove_video() {
	check_ajax_referer( 'themify-custom-panel', 'nonce' );
	if ( isset( $_POST['postid'] ) && isset( $_POST['customfield'] ) ) {
		update_post_meta( $_POST['postid'], $_POST['customfield'], '' );
	} else {
		_e( 'Missing vars: post ID and custom field.', 'themify' );
	}
	die();
}

/**
 * AJAX - Save user settings
 * @since 1.1.3
 * @package themify
 */
function themify_save(){
	$previous_data = themify_get_data();

	check_ajax_referer( 'ajax-nonce', 'nonce' );
	$temp = apply_filters( 'themify_save_data', themify_normalize_save_data( $_POST['data'] ), $previous_data );
	unset($temp['tmp_cache_network']);
	themify_set_data( $temp );
	_e('Your settings were saved', 'themify');

	if (
		Themify_Enqueue_Assets::$mobileMenuActive !== intval( $temp['setting-mobile_menu_trigger_point'] )
		|| ( isset( $previous_data['skin'] ) && $previous_data['skin'] !== $temp['skin'])
        || ( isset( $previous_data['setting-header_design'] ) && $previous_data['setting-header_design'] !== $temp['setting-header_design'])
        || ( isset( $previous_data['setting-exclude_menu_navigation'] ) && $previous_data['setting-exclude_menu_navigation'] !== $temp['setting-exclude_menu_navigation'])
	) {
		Themify_Enqueue_Assets::clearConcateCss();
	}

	if ( class_exists( 'Themify_Builder_Stylesheet' ) ) {
		$breakpoints=themify_get_breakpoints('all',true);
		foreach ( $breakpoints as $bp=>$v ) {
			if ( isset( $previous_data["setting-customizer_responsive_design_{$bp}"] ) && $previous_data["setting-customizer_responsive_design_{$bp}"] !== $temp["setting-customizer_responsive_design_{$bp}"] ) {
				Themify_Builder_Stylesheet::regenerate_css_files();
				break;
			}
		}
	}
	unset($previous_data);
	if(themify_get_server()==='nginx'){
		if(empty($temp['setting-webp'])){
			Themify_Enqueue_Assets::removeWebp();
		}
	}
	else{
		Themify_Enqueue_Assets::rewrite_htaccess((!empty($temp['setting-dev-mode'])?true:empty($temp['setting-cache_gzip'])),empty($temp['setting-webp']));
	}
	TFCache::remove_cache();
	if(empty($temp['setting-dev-mode'])){
		TFCache::create_config($temp);
	}
	else{
		TFCache::disable_cache();
	}
    TFCache::clear_3rd_plugins_cache();
	wp_die();
}

function themify_normalize_save_data($data){
    $data = explode('&', $data);
    $temp = array();
    foreach($data as $a){
	    $v = explode('=', $a);
	    $temp[$v[0]] = urldecode( str_replace('+',' ',preg_replace_callback('/%([0-9a-f]{2})/i', 'themify_save_replace_cb', urlencode($v[1]))) );
    }
    return $temp;
}

/**
 * Replace callback for preg_replace_callback used in themify_save().
 * 
 * @since 2.2.5
 * 
 * @param array $matches 0 complete match 1 first match enclosed in (...)
 * 
 * @return string One character specified by ascii.
 */
function themify_save_replace_cb( $matches ) {
	// "chr(hexdec('\\1'))"
	return chr( hexdec( $matches[1] ) );
}

/**
 * AJAX - Reset Styling
 * @since 1.1.3
 * @package themify
 */
function themify_reset_styling(){
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	$temp_data = themify_normalize_save_data($_POST['data']);
	$temp = array();
	foreach($temp_data as $key => $val){
		if(strpos($key, 'styling') === false){
			$temp[$key] = $val;
		}
	}
	print_r(themify_set_data($temp));
	delete_option( 'themify_has_styling_data' );
	die();
}

/**
 * AJAX - Reset Settings
 * @since 1.1.3
 * @package themify
 */
function themify_reset_settings(){
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	$temp_data = themify_normalize_save_data($_POST['data']);
	$temp = array();
	foreach($temp_data as $key => $val){
		// Don't reset if it's not a setting or the # of social links or a social link or the Hook Contents
		if(strpos($key, 'setting') === false || strpos($key, 'hooks') || strpos($key, 'link_field_ids') || strpos($key, 'themify-link') || strpos($key, 'twitter_settings') || strpos($key, 'custom_css')){
			$temp[$key] = $val;
		}
	}
        $temp['setting-script_minification'] = 'disable';
	print_r(themify_set_data($temp));
	die();
}

/**
 * Export Settings to zip file and prompt to download
 * NOTE: This function is not called through AJAX but it is kept here for consistency. 
 * @since 1.1.3
 * @package themify
 */
function themify_export() {
	if ( isset( $_GET['export'] ) && 'themify' === $_GET['export'] ) {
		check_admin_referer( 'themify_export_nonce' );
		$theme = wp_get_theme();
		$theme_name = $theme->display('Name');

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		global $wp_filesystem;

		if(class_exists('ZipArchive')){
			$theme_name_lc = strtolower($theme_name);
			$datafile = 'data_export.txt';
			$wp_filesystem->put_contents( $datafile, serialize( themify_get_data() ) );
			$files_to_zip = array(
				get_template_directory() . '/custom-modules.php',
				get_template_directory() . '/custom-functions.php',
				get_template_directory() . '/custom-config.php',
				get_template_directory() . '/custom_style.css',
				$datafile
			);
			//print_r($files_to_zip);
			$file = $theme_name . '_themify_export_' . date('Y_m_d') . '.zip';
			$result = themify_create_zip( $files_to_zip, $file, true );
		}
		if(isset($result) && $result){
			if ( ( isset( $file ) ) && ( $wp_filesystem->exists( $file ) ) ) {
				ob_start();
				header('Pragma:public');
				header('Expires:0');
				header('Content-type:application/force-download');
				header('Content-Disposition:attachment; filename="' . $file . '"');
				header('Content-Transfer-Encoding:Binary'); 
				header('Content-length:'.filesize($file));
				header('Connection:close');
				ob_clean();
				flush();
				echo $wp_filesystem->get_contents( $file );
				$wp_filesystem->delete( $datafile );
				$wp_filesystem->delete( $file );
				exit();
			} else {
				return false;
			}
		} else {
			if ( ini_get( 'zlib.output_compression' ) ) {
				/**
				 * Turn off output buffer compression for proper zip download.
				 * @since 2.0.2
				 */
				ini_set( 'zlib.output_compression', 'Off' );
			}
			ob_start();
			header('Content-Type: application/force-download');
			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: private',false);
			header('Content-Disposition: attachment; filename="'.$theme_name.'_themify_export_'.date("Y_m_d").'.txt"');
			header('Content-Transfer-Encoding: binary');
			ob_clean();
			flush();
			echo serialize(themify_get_data());
			exit();
		}
	}
	return false;
}
add_action('after_setup_theme', 'themify_export', 10);

/**
 * Pull data for inspection
 * @since 1.1.3
 * @package themify
 */
function themify_pull(){
	print_r(themify_get_data());
	die();
}

function themify_add_link_field(){
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	
	if( isset($_POST['fid']) ) {
		$hash = $_POST['fid'];
		$type = isset( $_POST['type'] )? $_POST['type'] : 'image-icon';
		echo themify_add_link_template( 'themify-link-'.$hash, array(), true, $type);
		exit();
	}
}

/**
 * Set image from wp library
 * @since 1.2.9
 * @package themify
 */
function themify_media_lib_browse() {
	if ( ! wp_verify_nonce( $_POST['media_lib_nonce'], 'media_lib_nonce' ) ) die(-1);

	$file = array();
	$postid = $_POST['post_id'];
	$attach_id = $_POST['attach_id'];

	$full = wp_get_attachment_image_src( $attach_id, 'full' );
	if( $_POST['featured'] ){
		//Set the featured image for the post
		set_post_thumbnail($postid, $attach_id);
	}
	update_post_meta($postid, $_POST['field_name'], $full[0]);
	update_post_meta($postid, '_'.$_POST['field_name'] . '_attach_id', $attach_id);

	$thumb = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
				
	//Return URL for the image field in meta box
	$file['thumb'] = $thumb[0];

	echo json_encode($file);

	exit();
}
/**
 * Get the path to import.php file
 *
 * @return string|WP_Error
 */
function themify_get_sample_content_file() {
	// importing demo content for an skin,regular old demo import
	$resource_file = isset( $_POST['skin'] ) ? THEME_DIR . '/skins/' . sanitize_text_field( $_POST['skin'] ) . '/import' : THEME_DIR . '/sample/import';

	/**
	 * if an extracted import file exists in the theme, use that instead
	 * this is useful in case some server issue causes problem with the unzip
	 */
	if ( is_file( $resource_file . '.php' ) ) {
		return $resource_file . '.php';
	}

	$resource_file .= '.zip';
	$cache_dir = themify_get_cache_dir();
	$extract_file = $cache_dir['path'] . 'import.php';

	if ( is_file( $extract_file ) ) {
		unlink( $extract_file );
	}
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	WP_Filesystem();
	$result = unzip_file( $resource_file, $cache_dir['path'] );
	if ( is_wp_error( $result ) ) {
		return $result;
	}

	return is_file( $extract_file ) ? $extract_file : $resource_file;
}

/**
 * Imports sample contents to replicate the demo site.
 *
 * @since 1.7.6
 */
function themify_import_sample_content() {

	$file = themify_get_sample_content_file();
	if ( is_wp_error( $file ) ) {
		wp_send_json_error( array(
			'error' => sprintf( __( 'Failed to get the sample file. Error: %s', 'themify' ), $file->get_error_message() ),
		) );
	}

	/* these constants are used in the import.php file */
	define( 'ERASEDEMO', false );
	define( 'IMPORT_IMAGES', isset( $_POST['import_images'] ) && sanitize_text_field( $_POST['import_images'] ) === 'yes' );

	do_action( 'themify_before_demo_import' );

	include THEMIFY_DIR . '/themify-import-functions.php';

	if ( file_exists( $file ) ) {
		include_once( $file );
		themify_do_demo_import();
	}
	do_action( 'themify_after_demo_import' );

	wp_send_json_success( array(
		'actions' => Themify_Import_Helper::get_import_actions(),
	) );
}

function themify_import_sample_actions() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	$action = sanitize_text_field( $_POST['_action'] );

	switch( $action ) {
		case 'post_thumb' :
			$post_id = (int) $_POST['data']['id'];
			$url = sanitize_text_field( $_POST['data']['thumb'] );

			$fetch = tf_fetch_remote_file( $url, $post_id );
			if ( is_wp_error( $fetch ) ) {
				wp_send_json_error( array( 'error' => $fetch->get_error_message() ) );
			}

			set_post_thumbnail( $post_id, $fetch );
			wp_send_json_success();
			break;

		case 'term_thumb' :
			$id = (int) $_POST['data']['id'];
			$url = sanitize_text_field( $_POST['data']['thumb'] );

			$fetch = tf_fetch_remote_file( $url );
			if ( is_wp_error( $fetch ) ) {
				wp_send_json_error( array( 'error' => $fetch->get_error_message() ) );
			}

			update_term_meta( $id, 'thumbnail_id', $fetch );
			wp_send_json_success();
			break;

		case 'gallery_field' :
			$post_id = (int) $_POST['data']['id'];
			$fields = $_POST['data']['fields'];

			foreach( $fields as $field_id => $values ) {
				$ids = array();
				foreach ( $values as $url ) {
					$fetch = tf_fetch_remote_file( $url, $post_id );
					if ( ! is_wp_error( $fetch ) ) {
						$ids[] = $fetch;
					}
				}

				// if field already exists, try just plopping the new IDs in the existing shortcode
				if ( ( $previous_value = get_post_meta( $post_id, $field_id, true ) ) && preg_match( '/ids=/', $previous_value ) ) {
					$new_value = preg_replace( '/ids=[\'"](.*?)[\'"]/', 'ids="' . implode( ',', $ids ) . '"', $previous_value );
				} else {
					$new_value = '[gallery ids="' . implode( ',', $ids ) . '"]';
				}
				update_post_meta( $post_id, $field_id, $new_value );
			}

			wp_send_json_success();
			break;

		case 'product_gallery' :
			$post_id = (int) $_POST['data']['id'];
			$images = $_POST['data']['images'];

			$ids = array();
			foreach ( $images as $url ) {
				$fetch = tf_fetch_remote_file( $url, $post_id );
				if ( ! is_wp_error( $fetch ) ) {
					$ids[] = $fetch;
				}
			}
			update_post_meta( $post_id, '_product_image_gallery', implode( ',', $ids ) );

			wp_send_json_success();
			break;
	}
}


/**
 * Download an image from external URL and returns the file
 *
 * @param $post_id Attachments may be associated with a parent post or page.
 *
 * @return WP_Error|int ID of created attachment, or WP_Error
 */
function tf_fetch_remote_file( $url, $post_id = null ) {
	// extract the file name and extension from the url
	$file_name = basename( $url );

	// get placeholder file in the upload dir with a unique, sanitized filename
	$upload = wp_upload_bits( $file_name, 0, '' );
	if ( $upload['error'] )
		return new WP_Error( 'upload_dir_error', $upload['error'] );

	// fetch the remote url and write it to the placeholder file
	$remote_response = wp_safe_remote_get( $url, array(
		'timeout' => 300,
		'stream' => true,
		'filename' => $upload['file'],
	) );

	$headers = wp_remote_retrieve_headers( $remote_response );

	// request failed
	if ( ! $headers ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', __('Remote server did not respond', 'themify') );
	}

	$remote_response_code = wp_remote_retrieve_response_code( $remote_response );

	// make sure the fetch was successful
	if ( $remote_response_code != '200' ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'themify'), esc_html( $remote_response_code ), get_status_header_desc( $remote_response_code ) ) );
	}

	$filesize = filesize( $upload['file'] );

	if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
		/* note: this is intentionally disabled, if gZip is enabled, $headers['content-length'] and $filesize do not necessarily match */
	}

	if ( 0 == $filesize ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'themify') );
	}

	$post = array(
		'post_title' => '',
		'post_content' => '',
		'post_status' => 'inherit',
	);
	if ( $info = wp_check_filetype( $upload['file'] ) )
		$post['post_mime_type'] = $info['type'];
	else
		return new WP_Error( 'mime_type_error', __('Invalid file type', 'themify') );

	$attach_id = wp_insert_attachment( $post, $upload['file'], $post_id );
	wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $upload['file'] ) );

	return $attach_id;
}

/**
 * Cleans up the sample content installed.
 *
 * @since 1.7.6
 */
function themify_erase_sample_content() {
	$file = themify_get_sample_content_file();
	if ( is_wp_error( $file ) ) {
		wp_send_json_error( array(
			'error' => sprintf( __( 'Failed to get the sample file. Error: %s', 'themify' ), $file->get_error_message() ),
		) );
	}

	define( 'ERASEDEMO', true );

	do_action( 'themify_before_demo_erase' );

	include THEMIFY_DIR . '/themify-import-functions.php';

	if ( file_exists( $file ) ) {
		include_once( $file );
		themify_do_demo_import();
	}
	do_action( 'themify_after_demo_erase' );

	wp_send_json_success();
}

/**
 * Hide the import notice on the Themify screen.
 *
 * @since 1.8.2
 */
function themify_notice_dismiss() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );
	if ( !empty( $_POST['notice'] )) {
	    $_key='themify_' . sanitize_text_field( $_POST['notice'] ) . '_notice';
	    delete_option($_key);
	    add_option($_key,0, '', false );
	}
	die('1');
}

function themify_clear_all_webp(){
	check_ajax_referer('ajax-nonce', 'nonce');
	wp_send_json_success(Themify_Enqueue_Assets::removeWebp());
}

function themify_clear_all_concate(){
	check_ajax_referer('ajax-nonce', 'nonce');
	Themify_Enqueue_Assets::clearConcateCss();
    TFCache::clear_3rd_plugins_cache();
	die('1');
}

function themify_clear_all_menu(){
	check_ajax_referer('ajax-nonce', 'nonce');
	TFCache::remove_cache();
	themify_clear_menu_cache();
	die('1');
}

function themify_clear_all_html(){
    check_ajax_referer('ajax-nonce', 'nonce');
	$type='blog';
	if(is_multisite()){
		$data = themify_normalize_save_data($_POST['data']);
		if(!empty($data['tmp_cache_network'])){
			$type='all';
		}
		$data=null;
	}
    TFCache::remove_cache($type);
    die('1');
}
add_action('wp_ajax_nopriv_themify_search_autocomplete','themify_search_autocomplete');
function themify_search_autocomplete(){
    if(!empty($_POST['s'])){
        $s  = sanitize_text_field($_POST['s']);
        if(!empty($s)){
            global $query,$found_types;
            if(!empty($_POST['post_type'])){
                $post_types  = array(sanitize_text_field($_POST['post_type']));
            }else{
                if(true===themify_is_woocommerce_active() && 'product' === themify_get( 'setting-search_post_type','all',true )){
                    $post_types = array('product');
                }else{
                    $post_types = Themify_Builder_Model::get_post_types();
                    unset($post_types['attachment']);
                    $post_types=array_keys($post_types);
                }
            }
            $query_args = array(
                'post_type'=>$post_types,
                'post_status'=>'publish',
                'posts_per_page'=>22,
                's'=>$s
            );
            if(!empty($_POST['term'])){
                Themify_Builder_Model::parseTermsQuery( $query_args, urldecode($_POST['term']), $_POST['tax'] );
            }
            $query_args = apply_filters('themify_search_args',$query_args);
            wp_reset_postdata();
            $query = new WP_Query( $query_args );
            $found_types=array();
            while ( $query->have_posts() ){
                $query->the_post();
                $post_type = get_post_type();
                if (($key = array_search($post_type, $query_args['post_type'])) !== false) {
                    unset($query_args['post_type'][$key]);
                    $found_types[]=$post_type;
                }
                if(empty($query_args['post_type'])){
                    break;
                }
            }
            $query->rewind_posts();

            ob_start();
            include( THEMIFY_DIR.'/includes/search-box-result.php' );
            ob_end_flush();
        }
    }
    wp_die();
}