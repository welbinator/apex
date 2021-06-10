<?php
/**
 * Generate reports of various system variables and configs, useful for debugging.
 * This only available to administrators.
 *
 * @package Themify
 */
class Themify_System_Status {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	public static function admin_menu() {
		$parent = is_plugin_active( 'themify-builder/themify-builder.php' ) ? 'themify-builder' : 'themify';
		add_submenu_page ( $parent, __( 'System Status', 'themify' ), __( 'System Status', 'themify' ), 'manage_options', 'tf-status', array( __CLASS__, 'admin' ) );
	}

	public static function admin() {
		$server_info = isset( $_SERVER['SERVER_SOFTWARE'] ) ? self::sanitize_deep( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		?>
<div class="wrap">
	<h1><?php _e( 'System Status', 'themify' ); ?></h1>
	<table class="tf_status_table widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3"><h2><?php esc_html_e( 'Server environment', 'themify' ); ?></h2></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Server info', 'themify' ); ?>:</th>
				<th><?php echo esc_html( $server_info ); ?></th>
			</tr>
			<tr>
				<td><?php esc_html_e( 'PHP version', 'themify' ); ?>:</td>
				<td>
					<?php
					echo phpversion();
					if ( version_compare( phpversion(), '7.2', '>=' ) ) {
						//
					} else {
						echo '<span class="dashicons dashicons-warning"></span> ' . __( 'We recommend using PHP version 7.2 or above for greater performance and security. Please contact your web hosting provider.', 'themify' );
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Debug Mode', 'themify' ); ?>:</td>
				<td><?php echo ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? __( 'Enabled', 'themify' ) : __( 'Disabled', 'themify' ); ?></td>
			</tr>
			<?php if ( function_exists( 'ini_get' ) ) : ?>
				<tr>
					<td><?php esc_html_e( 'PHP post max size', 'themify' ); ?>:</td>
					<td><?php echo esc_html( size_format( self::let_to_num( ini_get( 'post_max_size' ) ) ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP time limit', 'themify' ); ?>:</td>
					<td><?php echo esc_html( (int) ini_get( 'max_execution_time' ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP memory limit', 'themify' ); ?>:</td>
					<td><?php echo esc_html( size_format( self::get_memory_limit() ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'PHP max input vars', 'themify' ); ?>:</td>
					<td><?php echo esc_html( (int) ini_get( 'max_input_vars' ) ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'cURL version', 'themify' ); ?>:</td>
					<td><?php echo esc_html( self::get_curl_version() ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'SUHOSIN installed', 'themify' ); ?>:</td>
					<td><?php echo extension_loaded( 'suhosin' ) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
				</tr>
			<?php endif; ?>

			<?php
			$database_version = self::get_server_database_version();
			if ( $database_version['number'] ) :
				?>
				<tr>
					<td><?php esc_html_e( 'MySQL version', 'themify' ); ?>:</td>
					<td>
						<?php
						if ( version_compare( $database_version['number'], '5.6', '<' ) && ! strstr( $database_version['string'], 'MariaDB' ) ) {
							/* Translators: %1$s: MySQL version, %2$s: Recommended MySQL version. */
							echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( '%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'themify' ), esc_html( $database_version['string'] ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . esc_html__( 'WordPress requirements', 'themify' ) . '</a>' ) . '</mark>';
						} else {
							echo '' . esc_html( $database_version['string'] ) . '';
						}
						?>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<td><?php esc_html_e( 'Max upload size', 'themify' ); ?>:</td>
				<td><?php echo esc_html( size_format( wp_max_upload_size() ) ); ?></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'fsockopen/cURL', 'themify' ); ?>:</td>
				<td>
					<?php
					if ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) {
						echo '<span class="dashicons dashicons-yes"></span>';
					} else {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Your server does not have fsockopen or cURL enabled - some features that require connecting to external web services may not work. Contact your hosting provider.', 'themify' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'GZip', 'themify' ); ?>:</td>
				<td>
					<?php
					if ( is_callable( 'gzopen' ) ) {
						echo '<span class="dashicons dashicons-yes"></span>';
					} else {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '<a href="%s">GZIP</a> is recommended for better performance.', 'themify' ), 'https://php.net/manual/en/zlib.installation.php' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Multibyte string', 'themify' ); ?>:</td>
				<td>
					<?php
					if ( extension_loaded( 'mbstring' ) ) {
						echo '<span class="dashicons dashicons-yes"></span>';
					} else {
						/* Translators: %s: classname and link. */
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( esc_html__( 'Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'themify' ), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>' ) . '</mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Uploads folder', 'themify' ); ?>:</td>
				<td>
					<?php
					$dir = themify_upload_dir();
					echo '<strong>' . __( 'Base Dir ', 'themify' ) . '</strong>: ' . $dir['basedir'] . ' - ' . '<strong>' . __( 'Base URL ', 'themify' ) . '</strong>: ' . $dir['baseurl'];
					?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Concate CSS folder', 'themify' ); ?>:</td>
				<td>
					<?php
					$dir = Themify_Enqueue_Assets::getCurrentVersionFolder();
					echo $dir . ' - ';
					if ( Themify_Filesystem::is_writable( $dir ) ) {
						echo '<span class="dashicons dashicons-yes"></span>';
					} else {
						echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Uploads folder is not writeable, your CSS may not display correctly.', 'themify' ) . '</mark>';
					}
					?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
		<?php
	}

	public static function get_memory_limit() {
		$wp_memory_limit = self::let_to_num( WP_MEMORY_LIMIT );
		if ( function_exists( 'memory_get_usage' ) ) {
			$wp_memory_limit = max( $wp_memory_limit, self::let_to_num( @ini_get( 'memory_limit' ) ) );
		}

		return $wp_memory_limit;
	}

	public static function get_curl_version() {
		$curl_version = '';
		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
			$curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
		} elseif ( extension_loaded( 'curl' ) ) {
			$curl_version = __( 'cURL installed but unable to retrieve version.', 'themify' );
		}
		return $curl_version;
	}

	/**
	 * Retrieves the MySQL server version. Based on $wpdb.
	 *
	 * @return array Vesion information.
	 */
	public static function get_server_database_version() {
		global $wpdb;

		if ( empty( $wpdb->is_mysql ) ) {
			return array(
				'string' => '',
				'number' => '',
			);
		}

		if ( $wpdb->use_mysqli ) {
			$server_info = mysqli_get_server_info( $wpdb->dbh );
		} else {
			$server_info = mysql_get_server_info( $wpdb->dbh );
		}

		return array(
			'string' => $server_info,
			'number' => preg_replace( '/([^\d.]+).*/', '', $server_info ),
		);
	}

	/**
	 * Notation to numbers.
	 *
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 *
	 * @param  string $size Size value.
	 * @return int
	 */
	public static function let_to_num( $size ) {
		$l   = substr( $size, -1 );
		$ret = (int) substr( $size, 0, -1 );
		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
				// No break.
			case 'T':
				$ret *= 1024;
				// No break.
			case 'G':
				$ret *= 1024;
				// No break.
			case 'M':
				$ret *= 1024;
				// No break.
			case 'K':
				$ret *= 1024;
				// No break.
		}
		return $ret;
	}

	/**
	 * Applies sanitize_ function on multidimensional array
	 *
	 * @return mixed
	 */
	public static function sanitize_deep( $value ) {
		if ( is_array( $value ) ) { 
			return array_map( 'wc_clean', $value ); 
		} else { 
			return is_scalar( $value ) ? sanitize_text_field( $value ) : $value; 
		}
	}
}