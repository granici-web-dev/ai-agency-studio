<?php
/**
 * Prüft das Bewertungsmodul ohne echten API-Schlüssel.
 *
 * Die Wege, die im Betrieb schiefgehen, laufen nie im Normalfall mit: ein
 * Fehler bei Google, ein Bestand über der 30-Tage-Grenze, eine Antwort mit
 * übersetztem statt originalem Text. Genau die werden hier durchgespielt.
 *
 * Aufruf:
 *   docker compose run --rm wpcli eval-file /opt/skripte/bewertungen-pruefen.php
 *
 * @package agentur-basis-mu
 */

/*
 * Statisch gezählt, nicht global: `wp eval-file` führt den Inhalt innerhalb
 * einer Funktion aus, deshalb ist ein `$fehler = 0;` hier lokal und ein
 * `global $fehler;` in der Zählfunktion eine andere Variable. Der Lauf hätte
 * „bestanden" gemeldet, obwohl Prüfungen danebengingen — in rolle-pruefen.php
 * ist genau das passiert.
 */
function pruefe( string $was, ?bool $erfuellt = null, string $gesehen = '' ): int {
	static $fehler = 0;

	if ( null === $erfuellt ) {
		return $fehler;
	}

	if ( $erfuellt ) {
		WP_CLI::log( '  ok    ' . $was );

		return $fehler;
	}

	++$fehler;
	WP_CLI::log( '  FEHLT ' . $was . ( '' !== $gesehen ? ' — gesehen: ' . $gesehen : '' ) );

	return $fehler;
}

// Zustand sichern, damit die Demoseite hinterher aussieht wie vorher.
$sicherung_manuell = get_option( AGENTUR_BEWERTUNGEN_MANUELL, array() );
$sicherung_bestand = get_option( AGENTUR_BEWERTUNGEN_OPTION, array() );
$sicherung_ort     = get_option( AGENTUR_BEWERTUNGEN_ORT, '' );


WP_CLI::log( "\n1 · Ohne Einrichtung läuft nichts hinaus" );

delete_option( AGENTUR_BEWERTUNGEN_ORT );
$ergebnis = agentur_bewertungen_abgleich();
pruefe( 'Abgleich verweigert die Arbeit', is_wp_error( $ergebnis ) );
pruefe(
	'und nennt den Grund',
	is_wp_error( $ergebnis ) && 'agentur_bewertungen_nicht_eingerichtet' === $ergebnis->get_error_code(),
	is_wp_error( $ergebnis ) ? $ergebnis->get_error_code() : 'kein Fehler'
);
pruefe( 'kein Zeitplan eingetragen', false === wp_next_scheduled( AGENTUR_BEWERTUNGEN_TERMIN ) );


WP_CLI::log( "\n2 · Namen werden gekürzt" );

pruefe( 'Vor- und Nachname → „Maria S."', 'Maria S.' === agentur_bewertungen_name( 'Maria Schneider' ), agentur_bewertungen_name( 'Maria Schneider' ) );
pruefe( 'Doppelname → letzter Teil abgekürzt', 'Ali D.' === agentur_bewertungen_name( 'Ali Can Demir' ), agentur_bewertungen_name( 'Ali Can Demir' ) );
pruefe( 'einzelner Name bleibt', 'Sina' === agentur_bewertungen_name( 'Sina' ), agentur_bewertungen_name( 'Sina' ) );
pruefe( 'Umlaut überlebt die Kürzung', 'Jörg Ö.' === agentur_bewertungen_name( 'Jörg Öztürk' ), agentur_bewertungen_name( 'Jörg Öztürk' ) );
pruefe( 'leerer Name bleibt leer', '' === agentur_bewertungen_name( '  ' ) );

add_filter( 'agentur_bewertungen_namen_kuerzen', '__return_false' );
pruefe( 'Filter schaltet die Kürzung ab', 'Maria Schneider' === agentur_bewertungen_name( 'Maria Schneider' ), agentur_bewertungen_name( 'Maria Schneider' ) );
remove_filter( 'agentur_bewertungen_namen_kuerzen', '__return_false' );


WP_CLI::log( "\n3 · Antwort von Google auswerten" );

// Nachbau einer echten Antwort: eine übersetzte Bewertung mit Original,
// eine ohne Text, eine schlechte. Die schlechte muss durchkommen.
$antwort = array(
	array(
		'text'              => array( 'text' => 'Great haircut, very friendly.' ),
		'originalText'      => array( 'text' => 'Toller Haarschnitt, sehr freundlich.' ),
		'authorAttribution' => array(
			'displayName' => 'Maria Schneider',
			'photoUri'    => 'https://lh3.googleusercontent.com/a/irgendwas',
		),
		'rating'            => 5,
		'relativePublishTimeDescription' => 'vor 2 Monaten',
	),
	array(
		'authorAttribution' => array( 'displayName' => 'Ohne Text' ),
		'rating'            => 4,
	),
	array(
		'text'              => array( 'text' => 'Musste 40 Minuten warten.' ),
		'authorAttribution' => array( 'displayName' => 'Kai Berger' ),
		'rating'            => 2,
		'relativePublishTimeDescription' => 'vor einer Woche',
	),
);

$gelesen = agentur_bewertungen_stimmen_lesen( $antwort );

pruefe( 'Bewertung ohne Text fällt raus', 2 === count( $gelesen ), (string) count( $gelesen ) );
pruefe( 'Original schlägt die Übersetzung', 'Toller Haarschnitt, sehr freundlich.' === ( $gelesen[0]['text'] ?? '' ), $gelesen[0]['text'] ?? '—' );
pruefe( 'Name gekürzt', 'Maria S.' === ( $gelesen[0]['name'] ?? '' ), $gelesen[0]['name'] ?? '—' );
pruefe( 'Zeitangabe übernommen', 'vor 2 Monaten' === ( $gelesen[0]['zeit'] ?? '' ), $gelesen[0]['zeit'] ?? '—' );
pruefe( 'schlechte Bewertung bleibt drin', 2 === ( $gelesen[1]['note'] ?? 0 ), (string) ( $gelesen[1]['note'] ?? 0 ) );
pruefe( 'Reihenfolge unverändert', 'Kai B.' === ( $gelesen[1]['name'] ?? '' ), $gelesen[1]['name'] ?? '—' );
pruefe( 'kein Feld für ein Profilbild', ! isset( $gelesen[0]['bild'] ) );

$viele = array_fill( 0, 9, array( 'text' => array( 'text' => 'Kurz.' ), 'authorAttribution' => array( 'displayName' => 'A B' ) ) );
pruefe( 'nie mehr als fünf', 5 === count( agentur_bewertungen_stimmen_lesen( $viele ) ), (string) count( agentur_bewertungen_stimmen_lesen( $viele ) ) );
pruefe( 'Unsinn statt Liste ergibt leer', array() === agentur_bewertungen_stimmen_lesen( 'kaputt' ) );


WP_CLI::log( "\n4 · Die 30-Tage-Grenze räumt auf" );

update_option(
	AGENTUR_BEWERTUNGEN_OPTION,
	array(
		'abgerufen_am' => time() - 40 * DAY_IN_SECONDS,
		'note'         => 5.0,
		'anzahl'       => 999,
		'stimmen'      => array( array( 'text' => 'zu alt', 'name' => 'X' ) ),
	),
	false
);

pruefe( 'zu alter Bestand gilt nicht mehr', null === agentur_bewertungen_bestand() );
pruefe( 'und liegt nicht mehr in der Datenbank', array() === get_option( AGENTUR_BEWERTUNGEN_OPTION, array() ) );

update_option( AGENTUR_BEWERTUNGEN_MANUELL, array( 'note' => 4.5, 'anzahl' => 234, 'stand' => time(), 'stimmen' => array( array( 'text' => 'a', 'name' => 'B C' ) ) ), false );
$daten = agentur_bewertungen();
pruefe( 'Seite fällt auf die Handarbeit zurück', 'manuell' === $daten['quelle'], $daten['quelle'] );
pruefe( 'und zeigt weiter eine Note', '4,5' === $daten['note'], $daten['note'] );

update_option( AGENTUR_BEWERTUNGEN_OPTION, array( 'abgerufen_am' => time() - 3 * DAY_IN_SECONDS, 'note' => 4.7, 'anzahl' => 240, 'stimmen' => array() ), false );
pruefe( 'frischer Bestand bleibt', null !== agentur_bewertungen_bestand() );
pruefe( 'und schlägt die Handarbeit', '4,7' === agentur_bewertungen()['note'], agentur_bewertungen()['note'] );


WP_CLI::log( "\n5 · Ein Fehler löscht nicht den letzten guten Stand" );

update_option( AGENTUR_BEWERTUNGEN_ORT, 'ChIJ-erfunden-und-ungueltig' );
$vorher = get_option( AGENTUR_BEWERTUNGEN_OPTION );

// Ohne Schlüssel bricht der Abgleich schon vor dem Aufruf ab — deshalb
// direkt die Fehlerbehandlung prüfen.
agentur_bewertungen_fehler_merken( 'Places API antwortete mit Status 403.' );
$nachher = get_option( AGENTUR_BEWERTUNGEN_OPTION );

pruefe( 'Note steht noch', 4.7 === (float) $nachher['note'], (string) $nachher['note'] );
pruefe( 'Fehler ist vermerkt', str_contains( (string) $nachher['fehler'], '403' ), (string) $nachher['fehler'] );
pruefe( 'Fehlerzeitpunkt ist vermerkt', ! empty( $nachher['fehler_am'] ) );
pruefe( 'kein Schlüssel im Vermerk', ! str_contains( wp_json_encode( $nachher ), 'Api-Key' ) );


WP_CLI::log( "\n6 · Pflichtangabe nach § 5b Abs. 3 UWG" );

$g = agentur_bewertungen_hinweis( 'google', 234, 5 );
$m = agentur_bewertungen_hinweis( 'manuell', 234, 3 );

pruefe( 'bei der API: Google wählt aus', str_contains( $g, 'die Auswahl trifft Google' ) );
pruefe( 'bei Handarbeit: wir wählen aus', str_contains( $m, 'Ausgewählte Bewertungen' ) );
pruefe( 'beide nennen die Gesamtzahl', str_contains( $g, '234' ) && str_contains( $m, '234' ) );
pruefe( 'beide sagen, wer prüft', str_contains( $g, 'prüft Google; wir prüfen es nicht' ) && str_contains( $m, 'prüft Google; wir prüfen es nicht' ) );
pruefe( 'ohne Daten kein Hinweis', '' === agentur_bewertungen_hinweis( 'leer', 0, 0 ) );


// Aufräumen.
update_option( AGENTUR_BEWERTUNGEN_MANUELL, $sicherung_manuell, false );
update_option( AGENTUR_BEWERTUNGEN_OPTION, $sicherung_bestand, false );
update_option( AGENTUR_BEWERTUNGEN_ORT, $sicherung_ort, false );

WP_CLI::log( '' );

$fehler = pruefe( '' );

if ( $fehler > 0 ) {
	WP_CLI::error( sprintf( '%d Prüfung(en) fehlgeschlagen.', $fehler ) );
}

WP_CLI::success( 'Alle Prüfungen bestanden.' );
