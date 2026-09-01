<?php
/**
 * Plugin Name: Agentur Basis — Betriebsmodul
 * Description: Funktionen, die nicht ins Theme gehören, weil sie den Themewechsel überleben müssen: Anbindungen, Rollen, Bildverarbeitung, Cronjobs.
 * Version: 0.1.0
 * Requires PHP: 8.1
 *
 * WARUM MU-PLUGIN UND NICHT THEME
 *
 * Ein Theme darf abgeschaltet werden. Eine Google-Anbindung, ein Cronjob und
 * eine Rollenverteilung dürfen das nicht — sonst hängt beim Themewechsel
 * plötzlich ein Zeitplan in der Luft oder der Inhaber verliert seine Rechte.
 * Deshalb liegt hier alles, was Betrieb ist, und im Theme nur, was Aussehen
 * ist. Die Trennung ist auch die Voraussetzung dafür, dass dasselbe Modul
 * bei jedem Kunden läuft, während die Themes sich unterscheiden.
 *
 * WARUM DIESE DATEI SO KURZ IST
 *
 * WordPress lädt aus mu-plugins/ ausschließlich PHP-Dateien, die direkt im
 * Ordner liegen — Unterordner werden nicht durchsucht. Diese Datei ist
 * deshalb nur der Einstieg; die Module liegen daneben im gleichnamigen
 * Unterordner und werden hier von Hand eingebunden.
 *
 * @package agentur-basis-mu
 */

defined( 'ABSPATH' ) || exit;

define( 'AGENTUR_MU_VERSION', '0.1.0' );
define( 'AGENTUR_MU_PFAD', __DIR__ . '/agentur-basis-mu' );

/**
 * Module in fester Reihenfolge. Keine Glob-Schleife: eine neue Datei im
 * Ordner soll nicht ungefragt in jeder Kundeninstallation aktiv werden.
 */
$agentur_mu_module = array(
	// angaben.php zuerst: rollen.php vergibt das Recht, das dort definiert ist.
	'angaben.php',
	'rollen.php',
	'meta.php',
	'bewertungen.php',
);

foreach ( $agentur_mu_module as $agentur_mu_modul ) {
	$agentur_mu_datei = AGENTUR_MU_PFAD . '/' . $agentur_mu_modul;

	if ( is_readable( $agentur_mu_datei ) ) {
		require_once $agentur_mu_datei;
		continue;
	}

	// Kein stiller Ausfall: ein fehlendes Modul fällt sonst erst auf, wenn
	// ein Kunde eine leere Stelle auf seiner Seite meldet.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf( '[agentur-mu] Modul nicht lesbar: %s', $agentur_mu_modul ) );
	}
}

unset( $agentur_mu_module, $agentur_mu_modul, $agentur_mu_datei );
