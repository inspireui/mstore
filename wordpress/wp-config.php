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
define('DB_NAME', 'fluxstore-demo');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'PEwe]~,bC,o/G.XfjU}V{67=wY-Z@e~Dhc?R:tJFEGso0@E ya34Y  GO FM0N&3');
define('SECURE_AUTH_KEY',  'C]@ZtVml2+B/2C3cX:}WdbGYbyDebN|b+!j*s-AIB^!0FBb4dq*WNygTUCIzDNcC');
define('LOGGED_IN_KEY',    ']sDn*6JB4~F;7|fiZ+4YXXsRQjQk-6;EK{0~`YcNcvY}nooOTTG(R>s66YDt0&Zq');
define('NONCE_KEY',        '5,w+XW[ 4%HzP$ ~] ;A{z-*J +w-]7#?KSGn6=m~.K8W85;vu*e[`_`/SPW{LVT');
define('AUTH_SALT',        '&NKpPTW/WFG=+|U)6W+J/sBIZt@7m!NsNP>gHYw^Pn~9|*Mm9T9J=4dCU|HMU{Mc');
define('SECURE_AUTH_SALT', '5u=jY2z<]s/,rQ$8$0KdK&6|;-<Pv+T,@/i_z&<G|h NnrNl|/r_`VSMN&k8-YMA');
define('LOGGED_IN_SALT',   '[2-G3AvW<Ci<CQF^CN98>&[R^[g )V}0vl!e-c4H48c}@[?q?(9$p.R+Tz><i1_:');
define('NONCE_SALT',       'dpZsAJ1cz6k),I&)kdebC&WSaVV>.4 hG1x!0i~(7%DH=Tgt;ws/Ac[`Y04#Cw4:');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
