<?php
/*
 * The base configuration of the WordPress jQuery.com setup.
 */

/*
 * jQuery.com settings
 */
$port = getenv('JQUERY_WP_HTTP_PORT');
define( 'JQUERY_STAGING_FORMAT', $port ? 'local.%s:' . $port : 'local.%s' );

define( 'JQUERY_STAGING', true );
require_once __DIR__ . '/wp-content/sites.php' ;
define( 'JQUERY_LIVE_SITE', jquery_site_extract( $_SERVER['HTTP_HOST'] ?? 'jquery.com' ) );

// WordPress debugging mode (enables PHP E_NOTICE and WordPress notices)
define( 'WP_DEBUG', (bool) JQUERY_STAGING );

/*
 * Database Settings
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress_' . strtr( JQUERY_LIVE_SITE, [ '.' => '_' ]));

/** MySQL database username */
define('DB_USER', getenv('WORDPRESS_DB_USER'));

/** MySQL database password */
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD'));

/** MySQL hostname */
define('DB_HOST', getenv('WORDPRESS_DB_HOST'));

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/*
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * Use https://api.wordpress.org/secret-key/1.1/salt/
 */
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

/*
 * WordPress Database Table prefix.
 */
$table_prefix  = 'wp_';


/* That's all, stop editing! Happy blogging. */

/** Sets up WordPress vars and included files. */
require_once __DIR__ . '/wp-settings.php' ;

// Auto-create an admin account for local development
//
// https://codex.wordpress.org/Plugin_API/Action_Reference
//
// The 'init' and 'wp_loaded' hooks would be great hooks for this purpose
// if this code was in any other file (e.g. theme or plugin). Here in
// wp-config.php, add_action is either undefined (before wp-settings.php),
// or it's too later as those those hooks *just* fired at the end of
// the wp-settings.php file. So, instead, just call it directly.
function jquery_dev_autocreate_dev_admin() {
	$username = 'dev';
	$password = 'dev';
	$email = 'dev@localhost';
	if ( !username_exists( $username ) ) {
		wp_insert_user( [
			'user_login' => $username,
			'user_pass' => $password,
			'user_email' => $email,
			'role' => 'administrator',
		] );
	}
}
jquery_dev_autocreate_dev_admin();
