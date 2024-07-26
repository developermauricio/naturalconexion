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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'natural2022' );

/** MySQL database username */
define( 'DB_USER', 'forge' );

/** MySQL database password */
define( 'DB_PASSWORD', 'aTjdorw2H8ymMaFijWuH' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '?u4(D#y3_-kxpq#%<`cefoEL]ijU]~/rV5In`.tzfR)o7~pGZw[]iJ-tK^4ZaO.0' );
define( 'SECURE_AUTH_KEY',  ':O1,@@7{F]^y8J##[iN/Q8X{J<x&fe&`,BJunD,:YiR3.AD}8ZTLiB9kE6 G.I z' );
define( 'LOGGED_IN_KEY',    'R1kGlvQRmg/+O-:j0O{o3*2k-yM^=%7d@[#pNGul7g)wq5Y4xkHV/ufX(F]9o~c@' );
define( 'NONCE_KEY',        '} ~XjAj;|+2/m;^p&{vG8M[i%oC[i(BC*m+Rv#%:~@ganWP+U?0f-W$#2sliNB-N' );
define( 'AUTH_SALT',        'xcbb_1Nf$0uiffj!s@.CykM-i#[tbv$t$bh},C!jbkJw5 F69V9uc%V=0|_Pd3t<' );
define( 'SECURE_AUTH_SALT', 'o_hu?WC?-`p4BGeSh0!i<TOb]*Ta%>,<uBCU6|z!TXt`ZxBU5L:(SqFi/z@_8tW_' );
define( 'LOGGED_IN_SALT',   '_CzBFaehcd=soCUVjn@x?%BT|c8;f?U^0U8-RLHy9IiKit8#fylROtc-:!i>1ArG' );
define( 'NONCE_SALT',       '@*k_^Xma_[7k^=-_Urho~`F,2H0c1unqL1Ba*|7n:KQySi6fl-0CZt;jM0@<-kc0' );

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
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
//define( 'WP_DEBUG', true );

define( 'WP_MEMORY_LIMIT', '256M' );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
