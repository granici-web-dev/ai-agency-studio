<?php
/**
 * Rolle „Inhaber" — pflegen dürfen, zerstören nicht.
 *
 * ── Der Ausgangspunkt ───────────────────────────────────────────────────
 *
 * Der Inhaber bekommt **keinen Administrator-Zugang**. Nicht aus Misstrauen,
 * sondern weil ein Administratorkonto bei einem Betrieb mit fünf Angestellten
 * und einem gemeinsamen Passwort die wahrscheinlichste Art ist, wie eine
 * Kundenseite kaputtgeht: ein Plugin „das ein Bekannter empfohlen hat", ein
 * Theme-Wechsel zum Ausprobieren, ein gelöschtes Impressum.
 *
 * Die eingebaute Rolle `editor` kann bereits keine Themes wechseln, keine
 * Plugins aktivieren, keine Benutzer anlegen und keine Einstellungen ändern —
 * das ist WordPress-Standard und muss nicht nachgebaut werden. Was fehlt,
 * sind drei Dinge, die WordPress von sich aus nicht kennt:
 *
 * ── 1. Kein unfiltered_html ─────────────────────────────────────────────
 *
 * Ein Editor darf im Standard beliebiges HTML einfügen — auch <script> und
 * <iframe>. Damit ist die Zusage aus §2.1 („kein externer Aufruf beim
 * Seitenaufruf") an genau einer Stelle einklebbar: der Inhaber fügt das
 * Google-Maps-iframe ein, das ihm jemand empfohlen hat, und die Seite lädt
 * beim Aufruf von einem fremden Server. Ohne Einwilligung, ohne dass es
 * jemand merkt, und wir haben es verkauft.
 *
 * Ohne diese Berechtigung räumt WordPress solche Einbindungen beim Speichern
 * selbst weg. Das ist der wirksamste Schutz im ganzen Modul.
 *
 * ── 2. Pflichtseiten sind unlöschbar ────────────────────────────────────
 *
 * Impressum und Datenschutzerklärung müssen von jeder Seite erreichbar sein
 * (§2.1). Die Verweise im Fuß stehen fest auf `/impressum` und
 * `/datenschutz`. Ein Editor darf im Standard Seiten löschen, in den
 * Papierkorb legen, auf Entwurf zurückstellen **und den Permalink ändern** —
 * jedes davon bricht die Verweise, und keines davon meldet WordPress.
 *
 * Ein 404 auf das Impressum ist keine Kleinigkeit: das ist genau der Zustand,
 * für den abgemahnt wird.
 *
 * ── 3. Kein Site-Editor ─────────────────────────────────────────────────
 *
 * `edit_theme_options` wird bewusst **nicht** vergeben. Damit bliebe der
 * Site-Editor offen, und dort lassen sich Vorlagenteile bearbeiten — auch
 * der Fuß mit den Pflichtverweisen. Ein Schutz, der die Seite bewacht, aber
 * die Vorlage offenlässt, bewacht nichts.
 *
 * Der Preis dafür steht in der README: der Inhaber kann Menü, Logo und
 * Vorlagen nicht selbst ändern. Das ist gewollt und gehört ins
 * Übergabegespräch, nicht in eine Fußnote.
 *
 * ── Was hier NICHT steht ────────────────────────────────────────────────
 *
 * Keine Umleitungen auf `admin_init`, die Plugin- oder Theme-Seiten
 * abfangen. Die Berechtigungen decken das ab; eine zweite Prüfung darüber
 * sieht nach Sicherheit aus, prüft aber nichts, was nicht schon geprüft ist,
 * und verdeckt beim nächsten Fehler die eigentliche Ursache.
 *
 * `DISALLOW_FILE_EDIT` gehört in die wp-config.php jeder Kundeninstallation,
 * nicht hierher — der Theme- und Plugin-Editor ist eine Server-Einstellung.
 *
 * @package agentur-basis-mu
 */

defined( 'ABSPATH' ) || exit;

const AGENTUR_ROLLE            = 'inhaber';
const AGENTUR_ROLLE_STAND      = 'agentur_rollen_stand';
const AGENTUR_PFLICHTSEITEN    = 'agentur_pflichtseiten';

/**
 * Wird erhöht, wenn sich die Berechtigungen ändern. Die Rolle liegt in der
 * Datenbank; ohne diesen Vergleich würde eine geänderte Liste bei
 * bestehenden Installationen nie ankommen — und mit einem Schreibvorgang bei
 * jedem Seitenaufruf wäre es genauso falsch.
 */
const AGENTUR_ROLLE_VERSION = 3;


/* ── Rolle ───────────────────────────────────────────────────────────── */

add_action( 'init', 'agentur_rolle_sichern' );

/**
 * Legt die Rolle an oder bringt sie auf den aktuellen Stand.
 */
function agentur_rolle_sichern(): void {
	if ( (int) get_option( AGENTUR_ROLLE_STAND, 0 ) === AGENTUR_ROLLE_VERSION ) {
		return;
	}

	// Der Stand wird nur gestempelt, wenn wirklich alles geschrieben wurde.
	//
	// Vorher stempelte diese Funktion unbedingt. Fehlte in dem Moment das
	// Angaben-Modul, bekam die Rolle das Recht dafür nicht — und weil der
	// Stand trotzdem als erledigt galt, blieb sie **dauerhaft** kaputt. Beim
	// Prüfen erschien das als „403" auf einer Seite, die es geben müsste.
	// Bei einem Kunden wäre es ein Inhaber, der seine Angaben nie öffnen kann.
	if ( agentur_rolle_schreiben() ) {
		update_option( AGENTUR_ROLLE_STAND, AGENTUR_ROLLE_VERSION, true );
	}
}

/**
 * Schreibt die Rolle aus den Berechtigungen des Editors.
 *
 * Abgeleitet statt abgeschrieben: WordPress ändert die Ausstattung der
 * eingebauten Rollen zwischen Hauptversionen. Eine hier eingefrorene Liste
 * würde stillschweigend veralten.
 */
function agentur_rolle_schreiben(): bool {
	$editor = get_role( 'editor' );

	if ( ! $editor ) {
		error_log( '[agentur-mu] Rolle „editor" nicht gefunden — Rolle „Inhaber" nicht angelegt.' );

		return false;
	}

	if ( ! defined( 'AGENTUR_ANGABEN_RECHT' ) ) {
		error_log(
			'[agentur-mu] angaben.php ist nicht geladen — die Rolle „Inhaber" bekäme das Recht auf '
			. '„Ihre Angaben" nicht. Es wird nichts geschrieben, damit der Zustand beim nächsten '
			. 'Aufruf erneut versucht wird, statt dauerhaft falsch zu bleiben.'
		);

		return false;
	}

	$rechte = $editor->capabilities;

	foreach ( agentur_rolle_entzogen() as $recht ) {
		unset( $rechte[ $recht ] );
	}

	// Die Startseite ist für ihn geschlossen; dafür pflegt er „Ihre Angaben".
	// Das Recht ist eigen, weil es keine eingebaute Entsprechung gibt: es ist
	// weder eine Einstellung noch ein Beitrag.
	$rechte[ AGENTUR_ANGABEN_RECHT ] = true;

	// Ohne diese Zeile sieht der Administrator den Menüpunkt nicht — und
	// wundert sich, warum er die Angaben des Kunden nicht ändern kann.
	$verwalter = get_role( 'administrator' );

	if ( $verwalter && ! $verwalter->has_cap( AGENTUR_ANGABEN_RECHT ) ) {
		$verwalter->add_cap( AGENTUR_ANGABEN_RECHT );
	}

	// Erst entfernen, dann neu anlegen: add_role() tut bei einer bestehenden
	// Rolle gar nichts und meldet das auch nicht.
	remove_role( AGENTUR_ROLLE );
	add_role( AGENTUR_ROLLE, 'Inhaber', $rechte );

	return get_role( AGENTUR_ROLLE ) instanceof WP_Role;
}

/**
 * Berechtigungen, die der Inhaber gegenüber dem Editor **nicht** hat.
 */
function agentur_rolle_entzogen(): array {
	/**
	 * @param string[] $rechte Liste der entzogenen Berechtigungen.
	 */
	return apply_filters(
		'agentur_rolle_entzogen',
		array(
			// Siehe Kopf dieser Datei: hier hängt §2.1 dran.
			'unfiltered_html',
			// Verwaltet die veraltete Linkliste. Der Menüpunkt verwirrt nur.
			'manage_links',
		)
	);
}


/* ── Pflichtseiten ───────────────────────────────────────────────────── */

/**
 * Die IDs der rechtlich vorgeschriebenen Seiten.
 *
 * Nach ID, nicht nach Titel: den Titel darf der Inhaber ändern („Impressum
 * & Kontakt"), und die Seite bleibt trotzdem dieselbe.
 */
function agentur_pflichtseiten(): array {
	$ids = get_option( AGENTUR_PFLICHTSEITEN, array() );

	return is_array( $ids ) ? array_map( 'absint', $ids ) : array();
}

function agentur_ist_pflichtseite( int $id ): bool {
	return $id > 0 && in_array( $id, agentur_pflichtseiten(), true );
}

/**
 * Wer darf über eine Pflichtseite hinweg entscheiden?
 *
 * Die Agentur, nicht der Inhaber. Ein Administrator muss eine Seite
 * umbauen können — sonst ließe sich ein Fehler nur noch über die Datenbank
 * beheben.
 */
function agentur_pflichtseiten_frei( ?int $benutzer = null ): bool {
	return $benutzer
		? user_can( $benutzer, 'manage_options' )
		: current_user_can( 'manage_options' );
}

/* ── Startseite ──────────────────────────────────────────────────────────
   Sie ist aus Musterverweisen gebaut. Öffnet der Inhaber sie im Editor und
   speichert, schreibt WordPress die Muster als volles Markup aus — gemessen
   wurden 358 Byte, die zu 26 439 Byte werden. Ab dann folgt seine Startseite
   nicht mehr dem Theme, und jede spätere Korrektur erreicht ihn nicht mehr.

   Deshalb ist sie für ihn zu. Was sich daran ändert, ändert er unter
   „Ihre Angaben" (angaben.php).                                          */

/**
 * Die ID der Seite, die als Startseite eingestellt ist.
 */
function agentur_startseite_id(): int {
	return 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;
}

add_filter( 'map_meta_cap', 'agentur_startseite_sperre', 10, 4 );

/**
 * Nimmt dem Inhaber das Recht, die Startseite zu bearbeiten oder zu löschen.
 */
function agentur_startseite_sperre( array $rechte, string $recht, int $benutzer, array $args ): array {
	if ( ! in_array( $recht, array( 'edit_post', 'edit_page', 'delete_post', 'delete_page' ), true ) ) {
		return $rechte;
	}

	$id = isset( $args[0] ) ? absint( $args[0] ) : 0;

	if ( 0 === $id || $id !== agentur_startseite_id() || agentur_pflichtseiten_frei( $benutzer ) ) {
		return $rechte;
	}

	return array( 'do_not_allow' );
}

add_filter( 'display_post_states', 'agentur_startseite_kennzeichnen', 10, 2 );

/**
 * Sagt in der Seitenübersicht, wohin man stattdessen geht.
 *
 * Ein fehlender „Bearbeiten"-Verweis ohne Erklärung ist ein Anruf. Ein
 * fehlender Verweis mit dem Namen des richtigen Menüpunkts daneben ist eine
 * Anleitung.
 */
function agentur_startseite_kennzeichnen( array $zustaende, $beitrag ): array {
	if ( ! $beitrag instanceof WP_Post || $beitrag->ID !== agentur_startseite_id() ) {
		return $zustaende;
	}

	if ( ! current_user_can( 'edit_post', $beitrag->ID ) ) {
		$zustaende['agentur_startseite'] = 'Wird über „Ihre Angaben" gepflegt';
	}

	return $zustaende;
}

add_filter( 'map_meta_cap', 'agentur_pflichtseiten_loeschsperre', 10, 4 );

/**
 * Nimmt das Recht, eine Pflichtseite zu löschen oder in den Papierkorb zu legen.
 *
 * Über `map_meta_cap` statt über eine Prüfung im Admin: so greift die Sperre
 * überall dort, wo WordPress **Berechtigungen prüft** — im Backend, in der
 * REST-Schnittstelle, im Papierkorb-Verweis der Übersicht.
 *
 * DAS REICHT ABER NICHT, und das war der wichtigste Befund beim Prüfen:
 * `wp_trash_post()` und `wp_delete_post()` prüfen **selbst keine
 * Berechtigungen**. Sie sind die untere Ebene; geprüft wird eine Ebene
 * darüber. Wer die Funktionen direkt aufruft — ein Plugin, ein Skript, ein
 * Import — löscht das Impressum trotz dieser Sperre. Im ersten Prüflauf ist
 * genau das passiert: die Seite war weg.
 *
 * Deshalb steht darunter eine zweite Sperre in den Funktionen selbst.
 */
function agentur_pflichtseiten_loeschsperre( array $rechte, string $recht, int $benutzer, array $args ): array {
	if ( 'delete_post' !== $recht && 'delete_page' !== $recht ) {
		return $rechte;
	}

	$id = isset( $args[0] ) ? absint( $args[0] ) : 0;

	if ( ! agentur_ist_pflichtseite( $id ) || agentur_pflichtseiten_frei( $benutzer ) ) {
		return $rechte;
	}

	return array( 'do_not_allow' );
}

add_filter( 'pre_trash_post', 'agentur_pflichtseiten_papierkorbsperre', 10, 2 );
add_filter( 'pre_delete_post', 'agentur_pflichtseiten_papierkorbsperre', 10, 2 );

/**
 * Die zweite Sperre, direkt in wp_trash_post() und wp_delete_post().
 *
 * Beide Filter halten die Funktion an, sobald sie etwas anderes als null
 * zurückbekommen. Das ist die einzige Stelle, an der ein Löschvorgang
 * aufzuhalten ist, der die Berechtigungsprüfung nie durchlaufen hat.
 *
 * ANGEMELDETER BENUTZER ALS BEDINGUNG. Läuft gar kein Benutzer — WP-CLI,
 * Cron, ein Wartungsskript —, greift die Sperre nicht. Wer die Kommandozeile
 * hat, hat den Server; ihn hier auszusperren würde nur die eigene Wartung
 * behindern und keinen Angreifer aufhalten. Gesperrt wird der Weg, der im
 * Alltag begangen wird: jemand ist angemeldet und darf es nicht.
 *
 * @param mixed $abbruch null, wenn nichts eingreift.
 * @param mixed $beitrag Der betroffene Beitrag.
 * @return mixed false hält den Vorgang an, null lässt ihn laufen.
 */
function agentur_pflichtseiten_papierkorbsperre( $abbruch, $beitrag ) {
	if ( null !== $abbruch ) {
		return $abbruch;
	}

	$id = $beitrag instanceof WP_Post ? $beitrag->ID : absint( $beitrag );

	if ( ! agentur_ist_pflichtseite( $id ) ) {
		return $abbruch;
	}

	if ( 0 === get_current_user_id() || agentur_pflichtseiten_frei() ) {
		return $abbruch;
	}

	error_log(
		sprintf(
			'[agentur-mu] Löschversuch an der Pflichtseite %d durch Benutzer %d abgewiesen.',
			$id,
			get_current_user_id()
		)
	);

	return false;
}

add_filter( 'wp_insert_post_data', 'agentur_pflichtseiten_festhalten', 10, 2 );

/**
 * Hält Permalink und Veröffentlichungsstatus einer Pflichtseite fest.
 *
 * Der Permalink ist der eigentliche Punkt. Die Verweise im Fuß stehen fest
 * auf `/impressum` und `/datenschutz`; ändert jemand den Titel, schlägt
 * WordPress einen neuen Permalink vor, und der Fuß zeigt ins Leere — ohne
 * Fehlermeldung, ohne dass es beim Speichern auffällt. Gefunden wird das
 * erst von jemandem, der abmahnt.
 */
function agentur_pflichtseiten_festhalten( array $daten, array $eingang ): array {
	$id = isset( $eingang['ID'] ) ? absint( $eingang['ID'] ) : 0;

	if ( ! agentur_ist_pflichtseite( $id ) || agentur_pflichtseiten_frei() ) {
		return $daten;
	}

	$alt = get_post( $id );

	if ( ! $alt instanceof WP_Post ) {
		return $daten;
	}

	$daten['post_name'] = $alt->post_name;

	// Auto-Entwürfe und Revisionen laufen hier ebenfalls durch; die dürfen
	// nicht auf „publish" gezwungen werden.
	if ( 'publish' === $alt->post_status && in_array( $daten['post_status'], array( 'draft', 'pending', 'private' ), true ) ) {
		$daten['post_status'] = 'publish';
	}

	return $daten;
}

add_filter( 'display_post_states', 'agentur_pflichtseiten_kennzeichnen', 10, 2 );

/**
 * Kennzeichnet die Seiten in der Übersicht.
 *
 * Damit niemand vergeblich sucht, warum der Papierkorb-Verweis fehlt. Ein
 * gesperrter Knopf ohne Begründung erzeugt einen Anruf.
 */
function agentur_pflichtseiten_kennzeichnen( array $zustaende, $beitrag ): array {
	if ( $beitrag instanceof WP_Post && agentur_ist_pflichtseite( $beitrag->ID ) ) {
		$zustaende['agentur_pflicht'] = 'Gesetzlich vorgeschrieben';
	}

	return $zustaende;
}

add_action( 'edit_form_top', 'agentur_pflichtseiten_hinweis' );
add_action( 'enqueue_block_editor_assets', 'agentur_pflichtseiten_hinweis_block' );

/**
 * Hinweis im klassischen Editor.
 */
function agentur_pflichtseiten_hinweis( $beitrag ): void {
	if ( ! $beitrag instanceof WP_Post || ! agentur_ist_pflichtseite( $beitrag->ID ) ) {
		return;
	}

	printf(
		'<div class="notice notice-info"><p>%s</p></div>',
		esc_html( agentur_pflichtseiten_text() )
	);
}

/**
 * Derselbe Hinweis im Blockeditor.
 */
function agentur_pflichtseiten_hinweis_block(): void {
	$id = 0;

	if ( function_exists( 'get_current_screen' ) ) {
		global $post;
		$id = $post instanceof WP_Post ? $post->ID : 0;
	}

	if ( ! agentur_ist_pflichtseite( (int) $id ) ) {
		return;
	}

	wp_add_inline_script(
		'wp-notices',
		sprintf(
			'wp.data.dispatch("core/notices").createNotice("info",%s,{isDismissible:false,id:"agentur-pflichtseite"});',
			wp_json_encode( agentur_pflichtseiten_text() )
		),
		'after'
	);
}

function agentur_pflichtseiten_text(): string {
	return 'Diese Seite ist gesetzlich vorgeschrieben. Der Text lässt sich ändern, '
		. 'die Adresse der Seite und die Veröffentlichung nicht — der Verweis im Fuß '
		. 'muss auf jeder Seite funktionieren. Löschen ist gesperrt. '
		. 'Bei inhaltlichen Änderungen bitte vorher Rücksprache halten.';
}


/* ── Übersichtsseite für den Inhaber ─────────────────────────────────── */

add_action( 'wp_dashboard_setup', 'agentur_rolle_startbildschirm' );

/**
 * Ersetzt die Standardkacheln durch eine kurze Anleitung.
 *
 * Der Standard-Startbildschirm zeigt WordPress-Neuigkeiten, Aktivitäten und
 * einen „Willkommen"-Kasten voller Verweise, die diese Rolle nicht öffnen
 * darf. Für jemanden, der zweimal im Jahr ein Foto tauscht, ist das eine
 * Wand aus Fremdwörtern.
 */
function agentur_rolle_startbildschirm(): void {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wp_meta_boxes;

	$wp_meta_boxes['dashboard']['normal']['core']   = array();
	$wp_meta_boxes['dashboard']['side']['core']     = array();
	$wp_meta_boxes['dashboard']['normal']['high']   = array();

	remove_action( 'welcome_panel', 'wp_welcome_panel' );

	wp_add_dashboard_widget( 'agentur_hilfe', 'Ihre Website', 'agentur_rolle_startbildschirm_inhalt' );
}

/**
 * Inhalt der Kachel. Bewusst kurz und ohne Fachbegriffe.
 */
function agentur_rolle_startbildschirm_inhalt(): void {
	$betreuung = agentur_saeubern_text( (string) get_option( 'agentur_betreuung_kontakt', '' ) );

	?>
	<p><strong>Was Sie hier selbst ändern können:</strong></p>
	<ul style="list-style:disc;padding-left:1.5em">
		<li>Texte auf Ihren Seiten unter <em>Seiten</em></li>
		<li>Bilder austauschen unter <em>Medien</em></li>
		<li>Die Beschreibung, die bei Google unter dem Titel steht — im Feld
			<em>Beschreibung für Google</em> rechts neben der Seite</li>
	</ul>
	<p><strong>Was wir für Sie machen:</strong> Preisliste und Öffnungszeiten im Seitenaufbau,
		Aktualisierungen, Sicherungen, Störungen. Das ist Teil der Betreuung — bitte melden Sie sich
		einfach, statt es selbst zu versuchen.</p>
	<p><strong>Impressum und Datenschutzerklärung</strong> sind gegen Löschen gesperrt. Beide müssen
		von jeder Seite erreichbar sein; ohne sie droht eine Abmahnung.</p>
	<?php

	if ( '' !== $betreuung ) {
		printf( '<p><strong>Ihre Ansprechperson:</strong> %s</p>', esc_html( $betreuung ) );
	}
}

/**
 * Kleiner Helfer, weil das Meta-Modul denselben Bedarf hat.
 */
function agentur_saeubern_text( string $wert ): string {
	return function_exists( 'agentur_meta_saeubern' )
		? agentur_meta_saeubern( $wert )
		: trim( wp_strip_all_tags( $wert ) );
}

add_action( 'admin_menu', 'agentur_rolle_menue_aufraeumen', 999 );

/**
 * Nimmt Menüpunkte weg, die für diese Rolle ins Leere führen.
 *
 * „Werkzeuge" ist der einzige echte Fall: die Seite ist für einen Editor
 * vollständig leer — geprüft, das Untermenü hat null Einträge. Ein Menüpunkt,
 * der auf eine leere Seite führt, erzeugt genau einen Anruf pro Kunde.
 *
 * Design, Plugins, Benutzer und Einstellungen stehen hier bewusst NICHT.
 * Die blendet WordPress schon anhand der Berechtigungen aus; sie hier noch
 * einmal zu entfernen sähe nach Sicherheit aus, wäre aber keine — und beim
 * nächsten Fehler wüsste niemand mehr, welche der beiden Ebenen greift.
 */
function agentur_rolle_menue_aufraeumen(): void {
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}

	remove_menu_page( 'tools.php' );
}

add_filter( 'admin_footer_text', 'agentur_rolle_fusszeile' );

/**
 * Ersetzt „Danke für dein Vertrauen in WordPress" durch etwas Brauchbares.
 */
function agentur_rolle_fusszeile( $text ) {
	if ( current_user_can( 'manage_options' ) ) {
		return $text;
	}

	$betreuung = agentur_saeubern_text( (string) get_option( 'agentur_betreuung_kontakt', '' ) );

	return '' !== $betreuung
		? esc_html( 'Fragen zur Website? ' . $betreuung )
		: esc_html( 'Fragen zur Website? Bitte bei Ihrer Betreuung melden.' );
}


/* ── Kommandozeile ───────────────────────────────────────────────────── */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Rolle „Inhaber" und die Pflichtseiten verwalten.
	 */
	class Agentur_Rollen_Befehle {

		/**
		 * Legt die Rolle neu an.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-rolle anlegen
		 */
		public function anlegen(): void {
			agentur_rolle_schreiben();
			update_option( AGENTUR_ROLLE_STAND, AGENTUR_ROLLE_VERSION, true );

			$rolle = get_role( AGENTUR_ROLLE );

			if ( ! $rolle ) {
				WP_CLI::error( 'Rolle konnte nicht angelegt werden.' );
			}

			WP_CLI::success( sprintf( 'Rolle „Inhaber" angelegt, %d Berechtigungen.', count( $rolle->capabilities ) ) );
			WP_CLI::log( 'Entzogen gegenüber Editor: ' . implode( ', ', agentur_rolle_entzogen() ) );
		}

		/**
		 * Weist einem Benutzer die Rolle zu.
		 *
		 * ## OPTIONEN
		 *
		 * <benutzer>
		 * : Benutzer-ID, Anmeldename oder E-Mail-Adresse.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-rolle zuweisen inhaber@beispiel.de
		 */
		public function zuweisen( array $args ): void {
			$benutzer = get_user_by( 'id', absint( $args[0] ?? 0 ) )
				?: get_user_by( 'login', (string) ( $args[0] ?? '' ) )
				?: get_user_by( 'email', (string) ( $args[0] ?? '' ) );

			if ( ! $benutzer ) {
				WP_CLI::error( 'Benutzer nicht gefunden.' );
			}

			if ( user_can( $benutzer, 'manage_options' ) ) {
				WP_CLI::warning( 'Das ist ein Administrator. Die Rolle ersetzt seine bisherige.' );
			}

			$benutzer->set_role( AGENTUR_ROLLE );
			WP_CLI::success( sprintf( '%s ist jetzt Inhaber.', $benutzer->user_login ) );
		}

		/**
		 * Trägt die Pflichtseiten ein.
		 *
		 * Ohne Angabe werden Impressum und Datenschutzerklärung anhand ihres
		 * Permalinks gesucht. Die Verweise im Fuß des Themes stehen fest auf
		 * `/impressum` und `/datenschutz`.
		 *
		 * ## OPTIONEN
		 *
		 * [<id>...]
		 * : Seiten-IDs. Ohne Angabe wird gesucht.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-rolle pflichtseiten
		 *     wp agentur-rolle pflichtseiten 5 6
		 */
		public function pflichtseiten( array $args ): void {
			$ids = array_filter( array_map( 'absint', $args ) );

			if ( empty( $ids ) ) {
				foreach ( array( 'impressum', 'datenschutz' ) as $kennung ) {
					$seite = get_page_by_path( $kennung );

					if ( $seite instanceof WP_Post ) {
						$ids[] = $seite->ID;
						continue;
					}

					WP_CLI::warning( sprintf( 'Keine Seite mit dem Permalink „%s" gefunden.', $kennung ) );
				}
			}

			if ( empty( $ids ) ) {
				WP_CLI::error( 'Keine Pflichtseiten eingetragen.' );
			}

			update_option( AGENTUR_PFLICHTSEITEN, array_values( array_unique( $ids ) ), true );

			foreach ( $ids as $id ) {
				WP_CLI::log( sprintf( '  %d  %s  (/%s)', $id, get_the_title( $id ), get_post_field( 'post_name', $id ) ) );
			}

			WP_CLI::success( sprintf( '%d Pflichtseite(n) geschützt.', count( $ids ) ) );
		}

		/**
		 * Prüft die Einrichtung.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-rolle pruefen
		 */
		public function pruefen(): void {
			$rolle  = get_role( AGENTUR_ROLLE );
			$mangel = 0;

			$zeilen = array();

			$zeilen[] = array(
				'Prüfung' => 'Rolle „Inhaber" vorhanden',
				'Befund'  => $rolle ? 'ja' : 'FEHLT',
			);

			foreach ( agentur_rolle_entzogen() as $recht ) {
				$hat = $rolle && ! empty( $rolle->capabilities[ $recht ] );

				$zeilen[] = array(
					'Prüfung' => sprintf( '%s entzogen', $recht ),
					'Befund'  => $hat ? 'NEIN — noch vorhanden' : 'ja',
				);

				if ( $hat ) {
					++$mangel;
				}
			}

			foreach ( array( 'switch_themes', 'edit_theme_options', 'activate_plugins', 'edit_users', 'manage_options', 'install_plugins', 'update_core' ) as $recht ) {
				$hat = $rolle && ! empty( $rolle->capabilities[ $recht ] );

				$zeilen[] = array(
					'Prüfung' => sprintf( 'kein %s', $recht ),
					'Befund'  => $hat ? 'NEIN — vorhanden!' : 'ja',
				);

				if ( $hat ) {
					++$mangel;
				}
			}

			$seiten = agentur_pflichtseiten();

			$zeilen[] = array(
				'Prüfung' => 'Pflichtseiten eingetragen',
				'Befund'  => $seiten ? implode( ', ', $seiten ) : 'FEHLT',
			);

			if ( ! $seiten ) {
				++$mangel;
			}

			foreach ( $seiten as $id ) {
				$vorhanden = get_post( $id ) instanceof WP_Post;
				$name      = $vorhanden ? get_post_field( 'post_name', $id ) : '';

				$zeilen[] = array(
					'Prüfung' => sprintf( 'Seite %d erreichbar', $id ),
					'Befund'  => $vorhanden ? sprintf( '/%s', $name ) : 'FEHLT — Seite gelöscht!',
				);

				if ( ! $vorhanden ) {
					++$mangel;
				}
			}

			// Der Fuß verweist fest; stimmen die Permalinks nicht, zeigt er ins Leere.
			foreach ( array( 'impressum', 'datenschutz' ) as $kennung ) {
				$seite = get_page_by_path( $kennung );

				$zeilen[] = array(
					'Prüfung' => sprintf( 'Fußverweis /%s', $kennung ),
					'Befund'  => $seite && 'publish' === $seite->post_status ? 'ja' : 'BRICHT',
				);

				if ( ! $seite || 'publish' !== $seite->post_status ) {
					++$mangel;
				}
			}

			WP_CLI\Utils\format_items( 'table', $zeilen, array( 'Prüfung', 'Befund' ) );

			if ( $mangel > 0 ) {
				WP_CLI::error( sprintf( '%d Befund(e). Vor der Übergabe beheben.', $mangel ) );
			}

			WP_CLI::success( 'Rolle und Pflichtseiten in Ordnung.' );
		}
	}

	WP_CLI::add_command( 'agentur-rolle', 'Agentur_Rollen_Befehle' );
}
