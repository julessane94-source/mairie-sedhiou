<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'yex:>S9X|&eAimcrigUxB,&Wko7}!tBvJ3w&(58LZ~77[gY55IC_h*^L0j>o?af<' );
define( 'SECURE_AUTH_KEY',  'Puf2QTpE<&SyC>-yLO2Hn+rR:&O5g]S-@;5?c*{J9D.W%ijZ_a;{bdiGVe,Zh`8$' );
define( 'LOGGED_IN_KEY',    'l%9@DEH,guGKK$IO#|+Pe.< [Qx1v*+3XijQ,*Rl/2k::,#Ykv02Mw`TdS/8s{gy' );
define( 'NONCE_KEY',        '$o ^]=^$.z)^zZUn:o`I!9|bnb>cQbe=smj:JDaQi5;RX`ZV+Ku-Xr&=4+?$*1f/' );
define( 'AUTH_SALT',        'U,UjCBjZ,zsX|G +jjhB73/VjxUcoVE=[]zKokFI/$n9phCuaX5dSBOSCcPtrPCI' );
define( 'SECURE_AUTH_SALT', ']r,}{B>*cPOsL{bbI=;sKusN_,X$j[}2Y6n%=T3b0Nq)mDq@p_V&QETc@9}[2!OH' );
define( 'LOGGED_IN_SALT',   '3J#OF3F]`MWG$CT4I$D6-&L?aDH2l.62P4WRY|,c_]w9oDmI;:p%U1E>Pn;gj0X:' );
define( 'NONCE_SALT',       '4! 6&VavJQ,i=||MI-I%Q`kfo@&eS^{VZWv5ou>4[y7grz!QmcHvU9,Ei7w^_y&3' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
