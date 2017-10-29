<?php


/* for wordpress-mu-domain-mapping plugin */
define( 'SUNRISE', 'on' );

// load credentials

require_once(__DIR__.'/../etc/credentials/basic/pages/.wordpress.php');

// ** MySQL-Einstellungen ** //

/**
 * Ersetze datenbankname_hier_einfuegen
 * mit dem Namen der Datenbank, die du verwenden möchtest.
 */
define('DB_NAME', getenv('WP_DB_NAME'));

/**
 * Ersetze benutzername_hier_einfuegen
 * mit deinem MySQL-Datenbank-Benutzernamen.
 */
define('DB_USER', getenv('WP_DB_USER'));

/**
 * Ersetze passwort_hier_einfuegen mit deinem MySQL-Passwort.
 */
define('DB_PASSWORD', getenv('WP_DB_PASSWORD'));

/**
 * Ersetze localhost mit der MySQL-Serveradresse.
 */
define('DB_HOST', getenv('WP_DB_HOST'));

/**
 * Der Datenbankzeichensatz, der beim Erstellen der
 * Datenbanktabellen verwendet werden soll
 */
define('DB_CHARSET', getenv('WP_DB_CHARSET'));

/**
 * Der Collate-Type sollte nicht geändert werden.
 */
define('DB_COLLATE', getenv('WP_DB_COLLATE'));

/**#@+
 * Sicherheitsschlüssel
 *
 * Ändere jeden untenstehenden Platzhaltertext in eine beliebige,
 * möglichst einmalig genutzte Zeichenkette.
 * Auf der Seite {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * kannst du dir alle Schlüssel generieren lassen.
 * Du kannst die Schlüssel jederzeit wieder ändern, alle angemeldeten
 * Benutzer müssen sich danach erneut anmelden.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Füge hier deine Zeichenkette ein');
define('SECURE_AUTH_KEY',  'Füge hier deine Zeichenkette ein');
define('LOGGED_IN_KEY',    'Füge hier deine Zeichenkette ein');
define('NONCE_KEY',        'Füge hier deine Zeichenkette ein');
define('AUTH_SALT',        'Füge hier deine Zeichenkette ein');
define('SECURE_AUTH_SALT', 'Füge hier deine Zeichenkette ein');
define('LOGGED_IN_SALT',   'Füge hier deine Zeichenkette ein');
define('NONCE_SALT',       'Füge hier deine Zeichenkette ein');

/**#@-*/

/**
 * WordPress Datenbanktabellen-Präfix
 *
 * Wenn du verschiedene Präfixe benutzt, kannst du innerhalb einer Datenbank
 * verschiedene WordPress-Installationen betreiben.
 * Bitte verwende nur Zahlen, Buchstaben und Unterstriche!
 */
$table_prefix  = 'wp_';

/**
 * Für Entwickler: Der WordPress-Debug-Modus.
 *
 * Setze den Wert auf „true“, um bei der Entwicklung Warnungen und Fehler-Meldungen angezeigt zu bekommen.
 * Plugin- und Theme-Entwicklern wird nachdrücklich empfohlen, WP_DEBUG
 * in ihrer Entwicklungsumgebung zu verwenden.
 *
 * Besuche den Codex, um mehr Informationen über andere Konstanten zu finden,
 * die zum Debuggen genutzt werden können.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false);
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

if ( WP_DEBUG ) {
	error_reporting( E_ALL );
	if ( WP_DEBUG_DISPLAY )
		@ini_set( 'display_errors', 1 );
	elseif ( null !== WP_DEBUG_DISPLAY )
		@ini_set( 'display_errors', 0 );
	if ( WP_DEBUG_LOG ) {
		@ini_set( 'log_errors', 1 );
		@ini_set( 'error_log', __DIR__.'/../var/logs/wp-debug.log' );
	}
}


define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/';
if (getenv('WP_DOMAIN_CURRENT_SITE_OVERRIDE')) {
	define( 'DOMAIN_CURRENT_SITE', getenv('WP_DOMAIN_CURRENT_SITE_OVERRIDE') );
} else {
	define( 'DOMAIN_CURRENT_SITE', getenv('WP_DOMAIN_CURRENT_SITE') );
}
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
define( 'PATH_CURRENT_SITE', '/');
/* That's all, stop editing! Happy blogging. */

/** Der absolute Pfad zum WordPress-Verzeichnis. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
	
	
// DISABLE CRON JOB ON-REQUEST
define('DISABLE_WP_CRON', true);
// FORCE LESS COMPILATION
define('WP_LESS_COMPILATION', 'always');
// Use oyejorge less compiler implementation
define('WP_LESS_COMPILER','less.php');
// disable automatic updates of core, plugins, themes and translation files
define( 'AUTOMATIC_UPDATER_DISABLED', true );
	
	
	
	/** Definiert WordPress-Variablen und fügt Dateien ein.  */
require_once(ABSPATH . 'wp-settings.php');
