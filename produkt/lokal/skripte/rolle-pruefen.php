<?php
/**
 * Prüft die Rolle „Inhaber", indem sie versucht, die Seite kaputtzumachen.
 *
 * Eine Liste von Berechtigungen zu vergleichen beweist nichts — entscheidend
 * ist, was beim tatsächlichen Schreibvorgang passiert. Deshalb wird hier als
 * Inhaber angemeldet und dann gelöscht, umbenannt, zurückgestellt und
 * fremder Code eingefügt.
 *
 * DIE ZERSTÖRENDEN PRÜFUNGEN LAUFEN AUF EINER WEGWERFSEITE, nicht auf dem
 * echten Impressum. Der erste Prüflauf hat die echte Seite gelöscht, weil die
 * Sperre nicht griff — ein Test, der bei einem Befund die Daten mitnimmt, ist
 * schlechter als keiner. Das echte Impressum wird nur gelesen.
 *
 * Aufruf:
 *   docker compose run --rm wpcli eval-file /opt/skripte/rolle-pruefen.php
 *
 * @package agentur-basis-mu
 */

/*
 * ACHTUNG, hier lag ein Fehler im Prüfwerkzeug selbst:
 *
 * `wp eval-file` führt den Inhalt innerhalb einer Funktion aus. Was hier wie
 * oberste Ebene aussieht, ist deshalb **lokaler** Gültigkeitsbereich — ein
 * `$fehler = 0;` an dieser Stelle und ein `global $fehler;` in der Zählfunktion
 * sind zwei verschiedene Variablen. Ergebnis: der Lauf meldete „alle Prüfungen
 * bestanden", während zwei danebengegangen waren.
 *
 * Ein Test, der bei einem Befund Erfolg meldet, ist schlimmer als gar keiner.
 * Deshalb zählt jetzt eine statische Variable in der Funktion selbst.
 */
function pruefe( string $was, ?bool $erfuellt = null, ?string $gesehen = null ): int {
	static $fehler = 0;

	if ( null === $erfuellt ) {
		return $fehler;
	}

	if ( $erfuellt ) {
		WP_CLI::log( '  ok    ' . $was );

		return $fehler;
	}

	++$fehler;
	WP_CLI::log( '  FEHLT ' . $was . ( null !== $gesehen && '' !== $gesehen ? ' — gesehen: ' . $gesehen : '' ) );

	return $fehler;
}

$echte_pflichtseiten = agentur_pflichtseiten();

if ( empty( $echte_pflichtseiten ) ) {
	WP_CLI::error( 'Keine Pflichtseiten eingetragen — erst `wp agentur-rolle pflichtseiten` laufen lassen.' );
}

// Wegwerfseite, die für die Dauer der Prüfung als Pflichtseite gilt.
$opfer = wp_insert_post(
	array(
		'post_type'   => 'page',
		'post_title'  => 'Prüfseite Pflichtseitenschutz',
		'post_name'   => 'pruefseite-schutz',
		'post_status' => 'publish',
	)
);

update_option( AGENTUR_PFLICHTSEITEN, array_merge( $echte_pflichtseiten, array( $opfer ) ), true );

// Prüfbenutzer anlegen und als dieser arbeiten.
$anmeldename = 'pruef-inhaber';
$benutzer    = get_user_by( 'login', $anmeldename );

if ( ! $benutzer ) {
	$benutzer = get_user_by(
		'id',
		wp_insert_user(
			array(
				'user_login' => $anmeldename,
				'user_pass'  => wp_generate_password( 32 ),
				'user_email' => 'pruef-inhaber@example.invalid',
				'role'       => AGENTUR_ROLLE,
			)
		)
	);
}

$benutzer->set_role( AGENTUR_ROLLE );
wp_set_current_user( $benutzer->ID );


WP_CLI::log( "\n1 · Was der Inhaber gar nicht erst darf" );

foreach ( array(
	'switch_themes'      => 'Theme wechseln',
	'edit_theme_options' => 'Site-Editor öffnen',
	'activate_plugins'   => 'Plugin aktivieren',
	'install_plugins'    => 'Plugin installieren',
	'edit_users'         => 'Benutzer bearbeiten',
	'create_users'       => 'Benutzer anlegen',
	'manage_options'     => 'Einstellungen ändern',
	'update_core'        => 'WordPress aktualisieren',
	'edit_files'         => 'Dateien bearbeiten',
	'export'             => 'Inhalte exportieren',
	'unfiltered_html'    => 'beliebiges HTML einfügen',
) as $recht => $was ) {
	pruefe( $was, ! current_user_can( $recht ) );
}


WP_CLI::log( "\n2 · Was er darf — sonst wäre die Rolle wertlos" );

pruefe( 'Seiten bearbeiten', current_user_can( 'edit_pages' ) );
pruefe( 'Seiten veröffentlichen', current_user_can( 'publish_pages' ) );
pruefe( 'Bilder hochladen', current_user_can( 'upload_files' ) );

foreach ( $echte_pflichtseiten as $id ) {
	pruefe( sprintf( 'Pflichtseite %d bearbeiten', $id ), current_user_can( 'edit_post', $id ) );
	pruefe( sprintf( 'Pflichtseite %d NICHT löschen', $id ), ! current_user_can( 'delete_post', $id ) );
}


WP_CLI::log( "\n3 · Eine Pflichtseite lässt sich nicht wegräumen" );

pruefe( 'Löschrecht entzogen', ! current_user_can( 'delete_post', $opfer ) );

// Beides absichtlich als Direktaufruf: wp_trash_post() und wp_delete_post()
// prüfen selbst keine Berechtigungen. Genau hier fiel die erste Fassung durch.
wp_trash_post( $opfer );
pruefe( 'Papierkorb greift nicht', 'publish' === get_post_status( $opfer ), (string) get_post_status( $opfer ) );

wp_delete_post( $opfer, true );
pruefe( 'endgültiges Löschen greift nicht', get_post( $opfer ) instanceof WP_Post );

if ( ! get_post( $opfer ) instanceof WP_Post ) {
	WP_CLI::warning( 'Die Wegwerfseite ist weg — die folgenden Abschnitte werden übersprungen.' );
} else {

	WP_CLI::log( "\n4 · Permalink und Veröffentlichung bleiben stehen" );

	wp_update_post(
		array(
			'ID'         => $opfer,
			'post_title' => 'Umbenannt vom Inhaber',
			'post_name'  => 'umbenannt-vom-inhaber',
		)
	);
	$nachher = get_post( $opfer );

	pruefe( 'Permalink unverändert', 'pruefseite-schutz' === $nachher->post_name, $nachher->post_name );
	pruefe( 'Titel darf sich ändern', 'Umbenannt vom Inhaber' === $nachher->post_title, $nachher->post_title );

	wp_update_post( array( 'ID' => $opfer, 'post_status' => 'draft' ) );
	pruefe( 'bleibt veröffentlicht', 'publish' === get_post_status( $opfer ), (string) get_post_status( $opfer ) );
}


WP_CLI::log( "\n5 · Fremder Code wird beim Speichern entfernt (§2.1)" );

$gift = '<p>Vorher</p>'
	. '<script>fetch("https://beispiel.invalid/sammeln")</script>'
	. '<iframe src="https://www.google.com/maps/embed?pb=1"></iframe>'
	. '<p onclick="alert(1)">Nachher</p>';

$probe = wp_insert_post(
	array(
		'post_type'    => 'page',
		'post_title'   => 'Prüfseite Einbindungen',
		'post_status'  => 'draft',
		'post_content' => $gift,
	)
);

$gespeichert = get_post( $probe )->post_content;

pruefe( 'kein <script>', ! str_contains( $gespeichert, '<script' ), mb_substr( $gespeichert, 0, 70 ) );
pruefe( 'kein <iframe>', ! str_contains( $gespeichert, '<iframe' ) );
pruefe( 'kein onclick', ! str_contains( $gespeichert, 'onclick' ) );
pruefe( 'Text bleibt erhalten', str_contains( $gespeichert, 'Vorher' ) && str_contains( $gespeichert, 'Nachher' ) );


WP_CLI::log( "\n6 · Gewöhnliche Seiten darf er löschen" );

pruefe( 'Löschen erlaubt', current_user_can( 'delete_post', $probe ) );
wp_delete_post( $probe, true );
pruefe( 'Seite ist weg', ! ( get_post( $probe ) instanceof WP_Post ) );


WP_CLI::log( "\n7 · Der Administrator kommt weiterhin durch" );

$verwalter = get_users( array( 'role' => 'administrator', 'number' => 1 ) );

if ( $verwalter && get_post( $opfer ) instanceof WP_Post ) {
	wp_set_current_user( $verwalter[0]->ID );

	pruefe( 'darf die Pflichtseite löschen', current_user_can( 'delete_post', $opfer ) );

	wp_update_post( array( 'ID' => $opfer, 'post_name' => 'vom-admin-umbenannt' ) );
	pruefe( 'darf den Permalink ändern', 'vom-admin-umbenannt' === get_post_field( 'post_name', $opfer ), get_post_field( 'post_name', $opfer ) );

	wp_delete_post( $opfer, true );
	pruefe( 'darf sie tatsächlich löschen', ! ( get_post( $opfer ) instanceof WP_Post ) );
} else {
	WP_CLI::warning( 'Abschnitt 7 übersprungen.' );
}


WP_CLI::log( "\n8 · Die Startseite ist zu, die Angaben sind offen" );

wp_set_current_user( $benutzer->ID );

$start = agentur_startseite_id();

if ( $start > 0 ) {
	pruefe( 'Startseite nicht bearbeitbar', ! current_user_can( 'edit_post', $start ) );
	pruefe( 'Startseite nicht löschbar', ! current_user_can( 'delete_post', $start ) );

	// Direktaufruf: auch hier prüft wp_update_post() selbst nichts. Der Inhalt
	// muss trotzdem stehen bleiben, sonst friert die Seite doch ein.
	$vorher = get_post( $start )->post_content;
	wp_update_post( array( 'ID' => $start, 'post_content' => '<p>vom Inhaber überschrieben</p>' ) );
	$nachher = get_post( $start )->post_content;

	if ( $vorher !== $nachher ) {
		// Zurückdrehen, bevor irgendetwas anderes darauf aufbaut.
		wp_update_post( array( 'ID' => $start, 'post_content' => $vorher ) );
	}

	pruefe( 'Musterverweise bleiben unangetastet', str_contains( get_post( $start )->post_content, 'wp:pattern' ) );
} else {
	WP_CLI::warning( 'Keine Seite als Startseite eingestellt.' );
}

pruefe( '„Ihre Angaben" ist erlaubt', current_user_can( AGENTUR_ANGABEN_RECHT ) );
pruefe( 'Telefonnummer lesbar', '' !== agentur_angabe( 'telefon' ) );
pruefe( 'Wählverweis abgeleitet', str_starts_with( agentur_angabe( 'telefon_wahl' ), '+' ), agentur_angabe( 'telefon_wahl' ) );
pruefe( 'Preisliste gefüllt', count( agentur_angabe_liste( 'preise' ) ) > 0 );
pruefe( 'Mannschaft gefüllt', count( agentur_angabe_liste( 'team' ) ) > 0 );

// Die Nummer steht an vier Stellen im Theme. Genau dafür gibt es das Modul.
$nummer = '0' . wp_rand( 1000, 9999 ) . ' ' . wp_rand( 100000, 999999 );
$vorher = get_option( AGENTUR_ANGABEN_OPTION, array() );
update_option( AGENTUR_ANGABEN_OPTION, array_merge( is_array( $vorher ) ? $vorher : array(), array( 'telefon' => $nummer ) ), true );

/*
 * NICHT home_url() nehmen. Das ist `http://localhost:8080` — aus dem
 * wpcli-Container zeigt „localhost" auf den Container selbst, nicht auf den
 * Webserver. Der Abruf lief ins Leere, der Zähler stand auf 0, und die Prüfung
 * „alte Nummer nirgends mehr" ging bei leerem Körper als bestanden durch.
 * Ein falsches Bestanden ist teurer als ein Durchfall.
 */
$basis = getenv( 'AGENTUR_PRUEF_URL' ) ?: 'http://wordpress/';
$antwort = wp_remote_get( $basis, array( 'timeout' => 20 ) );
$seite   = wp_remote_retrieve_body( $antwort );

pruefe( 'Startseite überhaupt abrufbar', mb_strlen( $seite ) > 2000, mb_strlen( $seite ) . ' Byte' );
pruefe( 'eine Änderung schlägt überall durch', substr_count( $seite, $nummer ) >= 3, substr_count( $seite, $nummer ) . '×' );
pruefe( 'Wählverweis mitgezogen', str_contains( $seite, 'tel:' . agentur_telefon_wahl( $nummer ) ) );
pruefe( 'alte Nummer nirgends mehr', '' !== $seite && ! str_contains( $seite, '0178 5184291' ) );

update_option( AGENTUR_ANGABEN_OPTION, $vorher, true );

wp_set_current_user( 0 );


WP_CLI::log( "\n9 · Die echten Pflichtseiten stehen unverändert" );

foreach ( $echte_pflichtseiten as $id ) {
	$seite = get_post( $id );

	pruefe(
		sprintf( 'Seite %d unversehrt', $id ),
		$seite instanceof WP_Post && 'publish' === $seite->post_status,
		$seite instanceof WP_Post ? $seite->post_status : 'GELÖSCHT'
	);
}

foreach ( array( 'impressum', 'datenschutz' ) as $kennung ) {
	pruefe( sprintf( 'Fußverweis /%s trägt', $kennung ), get_page_by_path( $kennung ) instanceof WP_Post );
}


// Aufräumen: Zustand wiederherstellen, egal wie der Lauf ausging.
wp_set_current_user( 0 );
update_option( AGENTUR_PFLICHTSEITEN, $echte_pflichtseiten, true );

if ( get_post( $opfer ) instanceof WP_Post ) {
	wp_delete_post( $opfer, true );
}

wp_delete_user( $benutzer->ID );

WP_CLI::log( '' );

$fehler = pruefe( '' );

if ( $fehler > 0 ) {
	WP_CLI::error( sprintf( '%d Prüfung(en) fehlgeschlagen.', $fehler ) );
}

WP_CLI::success( 'Alle Prüfungen bestanden.' );
