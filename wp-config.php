<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'apexbran__wp_morax' );
// define( 'DB_NAME', 'local' );
/** MySQL database username */
define( 'DB_USER', 'apexbran_redesign_j90dj98jd9' );
// define( 'DB_USER', 'root' );
/** MySQL database password */
define( 'DB_PASSWORD', 'mug#U8*gB0XP' );
// define( 'DB_PASSWORD', 'root' );
/** MySQL hostname */
// define( 'DB_HOST', '67.227.174.185' );
define( 'DB_HOST', 'localhost' );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Q/Jljkx8DsQvoJgpXFFAjgpT9X4zPIMeUTaxa6EumakPjrMCDRlqp6XInIo2YMN5JH2I0QsFWjnKjhu8c1Y1Yg==');
define('SECURE_AUTH_KEY',  'AJoB7tlKqQO5lONtMVkdo4jLd34ARZJa9yf8vag4NzxWyTICcS02YWar4+X95yLIyf21TlT+VATN9S1NWkJAaA==');
define('LOGGED_IN_KEY',    'ne+JK3iioE6nvC658HXDRlOE+HM2i3I13K5sn696COLBW7clKyiaAZhfjOen7zz3PhQmicqzCHJlBIqBibD8iA==');
define('NONCE_KEY',        'ue7Wcl1cVTVNn098vyul8LDGYH8KiSd6MBU5+lazseVO04KmeZ5PvhEbkBXowATyEaeHSQIwQO1EKn/WOi0+wg==');
define('AUTH_SALT',        'Xe2cdi5J/6YW82uv4EG3CAMXEmMxLwEWwK6Ll90jkafPxm5BicCvNxD/xCQovntX4SpBA53CuG8jS9wOjpG05Q==');
define('SECURE_AUTH_SALT', 'zNOLUr77+81g63OH15SRhATmKrmRXt4Xb5jjeAZG/fLqLTFQ9q8QLIBsfWQD0h+Q6AcYUrcRPfhjIbBYmh5qtw==');
define('LOGGED_IN_SALT',   'dwyWFP2ewQMpC2CitddJcJuj7lllV/Iszfn4S0uDNrO7Q0gV81PX3TK/LCouTHf5YS9x+Y46fvYxrCRGzA2spg==');
define('NONCE_SALT',       'sygf3vBsuSaxfoUPO1WULTaghIXuNjnVu8FbMSAhmGCS/fifhmRepFexjb8XkyAJ3EfyT/9dye8TcjX4JYBagw==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
