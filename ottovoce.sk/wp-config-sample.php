<?php
/** 
 * Zakladna konfiguracia pre WordPress.
 *
 * Tento subor ma nasledujuce konfiguracie: nastavenia MySQL, predpony tabulky,
 * tajne kluce, jazyk WordPress, a ABSPATH. Mozete sa dozvediet viac informacii
 * navstivenim {@link http://codex.wordpress.org/Editing_wp-config.php Uprava
 * wp-config.php} Codex Stranky. Nastavenia MySQL mozete zuskat z vasho hostingu.
 *
 * Tento subor je pouzity vytvaracim skriptom pre wp-config.php pocas
 * instalacie. Nemali by ste ho pouzivat na stranke, staci nakopirovat jeho obsah
 * do "wp-config.php" a vyplnit hodnoty.
 *
 * @package WordPress
 */

// ** Nastavenia MySQL - Tieto informacie mozete ziskat od vasho hostingu ** //
/** Meno databazy pre WordPress */
define('DB_NAME', 'database_name_here');

/** Uzivatel databazy MySQL */
define('DB_USER', 'username_here');

/** Heslo databazy MySQL */
define('DB_PASSWORD', 'password_here');

/** Umiestnenie databazy MySQL */
define('DB_HOST', 'localhost');

/** Kodvanie databazy pouzivane pri tvorbe databazovych tabuliek. */
define('DB_CHARSET', 'utf8');

/** Databazova kolekcia. Nemente, pokial si nieste isty. */
define('DB_COLLATE', '');

/**#@+
 * Autorizacia unikatnych klucov.
 *
 * Zmente tieto na rozne unikatne frazy! 
 * Mozete ich vytvarat pomocou {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Tieto mozete zmenit kedykolvek to uznate za vhodne, aby ste znehodnotili vsetky existujuce cookies. Toto donuti vsetkych uzivatelov sa znova prihlasit.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

/**#@-*/

/**
 * Predpona databazovej tabulky WordPress.
 *
 * Mozete mat viacero instalacii v jednej databaze tym ak kazdej date unikatnu
 * predponu. Len cisla, pismena a podtrhnutia, prosim!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* To je vsetko, prestante upravovat! Vesele blogovanie. */

/** Absolutna cesta WordPress k priecinku WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Nastavenia premennych WordPress a vkladanych suborov. */
require_once(ABSPATH . 'wp-settings.php');
