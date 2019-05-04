<?php
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
define( 'DB_NAME', 'i5077419_wp2' );

/** MySQL database username */
define( 'DB_USER', 'i5077419_wp2' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Z.c9oEq1tzNgEyojJgi34' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'MNiqGxLfAHENB2QJeZGjmJFfHYm2d8PhvAPx1oTWlOfrZVx9qcASu6Ta7RMNF19Y');
define('SECURE_AUTH_KEY',  'MPf6cbMuTezBGXdgwylVefzL0Q0WdvVRgciXH9bv4Fn8buCcwFQNicXisUKjTW5D');
define('LOGGED_IN_KEY',    'A8eAbcIHZaDS9bU6705XJEVOayY0GXzS3K4EE46JH3kkRPz8YGF13YkCEB2WqbXO');
define('NONCE_KEY',        '9mieVdaqg4Ob2jJspK0s8nmEA7NSp8UZRbeywtY1MigSTmxgfbJXBtI0dJGkNVhL');
define('AUTH_SALT',        'jbkKoQbiHyIJffc8URNQVHEb2ZY9kyj1m8RzKbChS5uYWqv3R0mbMBoeqAzSx1EC');
define('SECURE_AUTH_SALT', 'txYUC3zQ58MXE6oUltfTJ82M0OIob6qoNKXYQfD5c5emYOmrsozMBX9Eju6wAvAx');
define('LOGGED_IN_SALT',   'mCLhQ4QfMt8tdmzELIWRbYZ0Rqbjk6OTLCoQUR5eTkpWj4XxKch1fB7RIno6Tx1x');
define('NONCE_SALT',       'H6Crwwczo8Q918sVz6v4PWLyh0pgWWWgAyUT9h7CMNp5twQKSYQvT3wxInMXuYBl');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
