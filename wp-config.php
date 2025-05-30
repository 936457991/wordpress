<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dblggtsqa4u1ju' );

/** Database username */
define( 'DB_USER', 'un8vqsabbiikq' );

/** Database password */
define( 'DB_PASSWORD', 'pjsxbbfe77j8' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'RJD@%k1JxxvHw#C0}dBt[nP wtVUl|B*)H2[E4pwZmXj6G&VFPueiUbvJGkQh:H=' );
define( 'SECURE_AUTH_KEY',   '0DQ(:Rdiug50jRC!tqbbkjur}5hP|Tb)>/V,P.C#Nq{H{KD{(Hq$&s((TK7B5{o.' );
define( 'LOGGED_IN_KEY',     'PsjYK@Q6E{An`[r89lPjXAnO_)v06aWCY^jy;yf=s$B@f_&-.hpc(ySFY_7Sz~}j' );
define( 'NONCE_KEY',         'u%A&m&)5YghAeG>S-30ZOAIxf%Dz|a+-kZQ7wov4M(KW7A_LJrBy$O(qxtG}U<Td' );
define( 'AUTH_SALT',         'm~cl}Lc|aU93R:j]h(P?wfv|_nj3!54Cyq_:857*iZC!VDnFXTPzM+gN4UJ(~vJd' );
define( 'SECURE_AUTH_SALT',  ' `!d}.o?a;&fb`[WArr$b~*Erd%L{GAU4l2vgC#C%EsKmM- :1|@T<^H>+ g&o3_' );
define( 'LOGGED_IN_SALT',    '_1iFH&.7mx,IG8 #:Pi.%=HxpEO&h/9x D)UW@UCEf.o57nR|}@$:s>>`W&_Epf#' );
define( 'NONCE_SALT',        's^dS>k:WCS(u|Em[v0<J~?W]b5KDpc$9;#*L:T-3U$*FwL#*i]Dlw:P%.n|@20V_' );
define( 'WP_CACHE_KEY_SALT', '_C!&`K?@c#}T{Ke5NW`HuL1maK(>yRK{f+ZA0lw;b0GlSEmjnOp42y N~@(raxe0' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'obb_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
@include_once('/var/lib/sec/wp-settings-pre.php'); // Added by SiteGround WordPress management system
require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system
