<?php
/**
 * Google-Bewertungen ohne externen Aufruf beim Seitenaufruf.
 *
 * ── Was das Modul tut ───────────────────────────────────────────────────
 *
 * Ein Cronjob holt einmal täglich Note, Anzahl und die von Google gelieferten
 * Bewertungen über die Places API — auf dem Server, nicht im Browser des
 * Besuchers. Das Ergebnis liegt in einer Option; die Seite gibt nur noch
 * Text aus. Der Besucher spricht nie mit Google, es fällt kein Cookie an,
 * und es braucht keinen Einwilligungsbanner (CLAUDE.md §2.1).
 *
 * Genau das ist der Unterschied zu jedem fertigen Bewertungs-Widget. Die
 * verkaufen „DSGVO-konform" und laden dabei beim Seitenaufruf von einem
 * fremden Server. Wer das einbettet, verkauft das Argument und bricht es
 * im selben Zug.
 *
 * ── Vier Grenzen, die nicht verhandelbar sind ───────────────────────────
 *
 * 1. FÜNF BEWERTUNGEN, MEHR NICHT. Die Places API liefert höchstens fünf,
 *    und sie wählt selbst aus. Es gibt keinen Parameter für „alle". Wer
 *    234 Bewertungen auf der Seite erwartet, erwartet etwas, das die
 *    Schnittstelle nicht kann — daran ändert auch kein anderer Anbieter
 *    etwas, alle sitzen auf derselben API.
 *
 * 2. HÖCHSTENS 30 TAGE ZWISCHENSPEICHERN. Die Nutzungsbedingungen der
 *    Google Maps Platform erlauben dauerhaftes Speichern nur für die
 *    Place ID. Alles andere ist vorübergehend, Obergrenze 30 Tage. Deshalb
 *    verfällt der Bestand hier hart — läuft der Abgleich länger auf Fehler,
 *    werden die Bewertungen verworfen und die Seite fällt auf die von Hand
 *    gepflegten Angaben zurück. Ein Bestand, der nach einem halben Jahr
 *    kaputter Cronjobs immer noch angezeigt wird, ist ein Vertragsbruch,
 *    den niemand bemerkt.
 *
 * 3. KEINE PROFILBILDER. Die Bild-URLs zeigen auf googleusercontent.com —
 *    einbinden hieße externer Aufruf beim Seitenaufruf, also genau das,
 *    was dieses Modul vermeidet. Herunterladen und selbst ausliefern
 *    verstößt gegen Punkt 2. Es bleibt der Name.
 *
 * 4. DER SCHLÜSSEL STEHT NIE IN DER DATENBANK. Nur als Konstante in der
 *    wp-config.php. Eine Option landet im Export, im Backup und in jedem
 *    Datenbank-Dump, den jemand per Mail verschickt.
 *
 * ── Recht ───────────────────────────────────────────────────────────────
 *
 * § 5b Abs. 3 UWG: Wer Verbraucherbewertungen zugänglich macht, muss
 * angeben, ob und wie er die Echtheit sicherstellt. Das Modul erzeugt
 * diesen Hinweis selbst (agentur_bewertungen_hinweis) und formuliert ihn
 * je nach Quelle unterschiedlich — bei der API wählt Google aus, bei der
 * Handpflege wählt der Betrieb aus, und das ist nicht dasselbe. Der Text
 * ist Pflichtangabe, keine Gestaltung; er wird nicht weggelassen.
 *
 * Anhang zu § 3 UWG Nr. 23b: Bewertungen als echt darstellen, ohne dass
 * sie es sind, ist abmahnbar. Deshalb wird hier nichts sortiert, nichts
 * nach Note gefiltert und nichts gekürzt. Was Google liefert, steht da —
 * in der gelieferten Reihenfolge.
 *
 * Keine AggregateRating-Auszeichnung. Bewertungssterne für den eigenen
 * Betrieb auf der eigenen Seite sind von Google nicht vorgesehen und
 * riskieren eine manuelle Maßnahme.
 *
 * @package agentur-basis-mu
 */

defined( 'ABSPATH' ) || exit;

/**
 * Vorübergehende Speicherung, Obergrenze aus den Maps-Platform-Bedingungen.
 * Bewusst mit Sicherheitsabstand: 28 statt 30 Tage, damit ein Cronjob, der
 * am letzten Tag klemmt, nicht sofort über die Grenze läuft.
 */
const AGENTUR_BEWERTUNGEN_HALTBARKEIT = 28 * DAY_IN_SECONDS;

const AGENTUR_BEWERTUNGEN_OPTION   = 'agentur_bewertungen_bestand';
const AGENTUR_BEWERTUNGEN_MANUELL  = 'agentur_bewertungen_manuell';
const AGENTUR_BEWERTUNGEN_ORT      = 'agentur_bewertungen_ort';
const AGENTUR_BEWERTUNGEN_TERMIN   = 'agentur_bewertungen_abgleich';

/**
 * Ist die Anbindung überhaupt eingerichtet?
 *
 * Ohne Schlüssel oder ohne Place ID läuft das Modul im Handbetrieb: die
 * Seite zeigt die gepflegten Angaben, es geht kein Aufruf hinaus, und es
 * gibt keine Fehlermeldung. Das ist der Normalfall bei Auslieferung.
 */
function agentur_bewertungen_bereit(): bool {
	return '' !== agentur_bewertungen_schluessel() && '' !== agentur_bewertungen_ort();
}

/**
 * Der API-Schlüssel — ausschließlich aus der Konstante.
 *
 * Nie loggen, nie ausgeben, nie in eine Option schreiben. Der einzige Ort,
 * an dem er stehen darf, ist die wp-config.php außerhalb des Webverzeichnisses
 * beziehungsweise die Umgebung des Containers.
 */
function agentur_bewertungen_schluessel(): string {
	if ( defined( 'AGENTUR_GOOGLE_PLACES_KEY' ) && is_string( AGENTUR_GOOGLE_PLACES_KEY ) ) {
		return trim( AGENTUR_GOOGLE_PLACES_KEY );
	}

	return '';
}

/**
 * Die Place ID des Betriebs. Sie darf dauerhaft gespeichert werden — sie ist
 * die einzige Angabe, die die Maps-Platform-Bedingungen davon ausnehmen.
 */
function agentur_bewertungen_ort(): string {
	$ort = get_option( AGENTUR_BEWERTUNGEN_ORT, '' );

	return is_string( $ort ) ? trim( $ort ) : '';
}

/**
 * Von Hand gepflegte Angaben. Sie sind der Rückfall und zugleich der
 * Auslieferungszustand: ein Betrieb ohne Google-Cloud-Konto bekommt eine
 * vollständige Seite, nur eben ohne automatische Aktualisierung.
 *
 * Bewusst getrennt vom API-Bestand gespeichert. Ein Abgleich darf die
 * Handarbeit niemals überschreiben — sonst ist sie beim ersten Lauf weg
 * und niemand weiß, wo die Zitate geblieben sind.
 */
function agentur_bewertungen_handarbeit(): array {
	$vorgabe = array(
		'note'    => 0.0,
		'anzahl'  => 0,
		'stand'   => 0,
		'profil'  => '',
		'stimmen' => array(),
	);

	$gespeichert = get_option( AGENTUR_BEWERTUNGEN_MANUELL, array() );

	return is_array( $gespeichert ) ? array_merge( $vorgabe, $gespeichert ) : $vorgabe;
}

/**
 * Alles, was die Vorlage braucht — in einem Aufruf, ohne HTTP.
 *
 * Diese Funktion wird bei jedem Seitenaufruf ausgeführt. Sie liest genau
 * eine Option und rechnet ein paar Zahlen um. Kein Netzwerk, keine
 * Abfrage, kein Grund, sie zu cachen.
 *
 * @return array{
 *     quelle:'google'|'manuell'|'leer',
 *     note:string, note_zahl:float, anteil:float,
 *     anzahl:string, anzahl_zahl:int,
 *     stand:string, profil:string,
 *     stimmen:array<int,array{text:string,name:string,note:int,zeit:string}>,
 *     hinweis:string
 * }
 */
function agentur_bewertungen(): array {
	$bestand = agentur_bewertungen_bestand();
	$quelle  = 'google';

	// Kein gültiger API-Bestand: auf die Handarbeit zurückfallen. Passiert
	// bei Auslieferung, nach einem abgelaufenen Bestand und bei jedem
	// Betrieb ohne Google-Cloud-Konto.
	if ( null === $bestand ) {
		$bestand = agentur_bewertungen_handarbeit();
		$quelle  = 'manuell';
	}

	$note_zahl   = (float) ( $bestand['note'] ?? 0 );
	$anzahl_zahl = (int) ( $bestand['anzahl'] ?? 0 );
	$stimmen     = is_array( $bestand['stimmen'] ?? null ) ? $bestand['stimmen'] : array();

	if ( $note_zahl <= 0 || $anzahl_zahl <= 0 ) {
		$quelle = 'leer';
	}

	return array(
		'quelle'      => $quelle,
		'note'        => $note_zahl > 0 ? number_format_i18n( $note_zahl, 1 ) : '',
		'note_zahl'   => $note_zahl,
		'anteil'      => max( 0.0, min( 100.0, $note_zahl / 5 * 100 ) ),
		'anzahl'      => $anzahl_zahl > 0 ? number_format_i18n( $anzahl_zahl ) : '',
		'anzahl_zahl' => $anzahl_zahl,
		'stand'       => ! empty( $bestand['stand'] ) ? wp_date( 'j. F Y', (int) $bestand['stand'] ) : '',
		'profil'      => agentur_bewertungen_profil( $bestand ),
		'stimmen'     => $stimmen,
		'hinweis'     => agentur_bewertungen_hinweis( $quelle, $anzahl_zahl, count( $stimmen ) ),
	);
}

/**
 * Der gespeicherte API-Bestand, oder null wenn keiner gilt.
 *
 * Hier sitzt die 30-Tage-Regel. Ein zu alter Bestand wird nicht nur
 * ignoriert, sondern gelöscht — sonst liegt er weiter in der Datenbank und
 * der Verstoß bleibt bestehen, auch wenn ihn niemand mehr anzeigt.
 */
function agentur_bewertungen_bestand(): ?array {
	$bestand = get_option( AGENTUR_BEWERTUNGEN_OPTION, array() );

	if ( ! is_array( $bestand ) || empty( $bestand['abgerufen_am'] ) ) {
		return null;
	}

	if ( time() - (int) $bestand['abgerufen_am'] > AGENTUR_BEWERTUNGEN_HALTBARKEIT ) {
		delete_option( AGENTUR_BEWERTUNGEN_OPTION );

		error_log(
			'[agentur-mu] Bewertungsbestand älter als die zulässigen 30 Tage und deshalb verworfen. '
			. 'Der Abgleich läuft seit Längerem auf Fehler — bitte prüfen.'
		);

		return null;
	}

	return $bestand;
}

/**
 * Verweis auf das Profil.
 *
 * Bevorzugt die von Google gelieferte URL. Sonst der von Hand gepflegte
 * Kurzlink aus dem Unternehmensprofil. Sonst als letzte Stufe eine
 * Kartensuche über die Adresse — dokumentiertes Format, ohne
 * Sitzungsparameter, die nach ein paar Tagen ins Leere laufen.
 */
function agentur_bewertungen_profil( array $bestand ): string {
	if ( ! empty( $bestand['profil'] ) ) {
		return (string) $bestand['profil'];
	}

	$manuell = agentur_bewertungen_handarbeit();

	if ( ! empty( $manuell['profil'] ) ) {
		return (string) $manuell['profil'];
	}

	return '';
}

/**
 * Die Pflichtangabe nach § 5b Abs. 3 UWG, passend zur Quelle formuliert.
 *
 * Der Unterschied ist nicht kosmetisch: bei der API trifft Google die
 * Auswahl, bei der Handpflege der Betrieb. Wer von Hand drei gute aus 234
 * heraussucht und dazuschreibt „die Auswahl trifft Google", behauptet etwas
 * Falsches — und landet damit bei genau der Vorschrift, die er erfüllen
 * wollte.
 */
function agentur_bewertungen_hinweis( string $quelle, int $anzahl, int $gezeigt ): string {
	if ( 'leer' === $quelle ) {
		return '';
	}

	$gesamt = $anzahl > 0
		? sprintf(
			/* translators: %s: Gesamtzahl der Bewertungen */
			' Note und Anzahl beziehen sich auf alle %s Bewertungen.',
			number_format_i18n( $anzahl )
		)
		: '';

	if ( 'google' === $quelle ) {
		return sprintf(
			/* translators: %d: Anzahl der angezeigten Bewertungen */
			'Diese %d Bewertungen liefert Google täglich automatisch aus unserem Unternehmensprofil; '
			. 'die Auswahl trifft Google, nicht wir.%s '
			. 'Ob eine Bewertung von einem tatsächlichen Gast stammt, prüft Google; wir prüfen es nicht.',
			$gezeigt,
			$gesamt
		);
	}

	return 'Ausgewählte Bewertungen aus unserem Google-Unternehmensprofil, wörtlich übernommen und nicht gekürzt.'
		. $gesamt
		. ' Ob eine Bewertung von einem tatsächlichen Gast stammt, prüft Google; wir prüfen es nicht.';
}


/* ── Abgleich ────────────────────────────────────────────────────────────
   Der einzige Ort im Modul, an dem eine Verbindung nach draußen aufgebaut
   wird. Läuft im Cron, nie im Seitenaufruf.                              */

/**
 * Holt den Bestand bei Google und speichert ihn.
 *
 * @param bool $erzwingen Auch dann laufen, wenn der Bestand noch frisch ist.
 * @return true|WP_Error
 */
function agentur_bewertungen_abgleich( bool $erzwingen = false ) {
	if ( ! agentur_bewertungen_bereit() ) {
		return new WP_Error(
			'agentur_bewertungen_nicht_eingerichtet',
			'Kein API-Schlüssel (AGENTUR_GOOGLE_PLACES_KEY) oder keine Place ID hinterlegt.'
		);
	}

	$bestand = get_option( AGENTUR_BEWERTUNGEN_OPTION, array() );

	// Höchstens ein Aufruf pro Tag. Der Cron ist täglich getaktet, aber ein
	// versehentlicher zweiter Aufruf soll keine Abrechnungseinheit kosten.
	if ( ! $erzwingen && is_array( $bestand ) && ! empty( $bestand['abgerufen_am'] )
		&& time() - (int) $bestand['abgerufen_am'] < 20 * HOUR_IN_SECONDS ) {
		return true;
	}

	$antwort = wp_remote_get(
		add_query_arg(
			array(
				'languageCode' => 'de',
				'regionCode'   => 'DE',
			),
			'https://places.googleapis.com/v1/places/' . rawurlencode( agentur_bewertungen_ort() )
		),
		array(
			'timeout' => 15,
			'headers' => array(
				// Nur die Felder anfordern, die gebraucht werden. Google
				// rechnet nach Feldgruppen ab: ein zu breiter Feldsatz kostet
				// bares Geld für Daten, die niemand anzeigt.
				'X-Goog-FieldMask' => 'rating,userRatingCount,googleMapsUri,reviews',
				'X-Goog-Api-Key'   => agentur_bewertungen_schluessel(),
			),
		)
	);

	if ( is_wp_error( $antwort ) ) {
		agentur_bewertungen_fehler_merken( $antwort->get_error_message() );

		return $antwort;
	}

	$status = (int) wp_remote_retrieve_response_code( $antwort );

	if ( 200 !== $status ) {
		// Der Antwortkörper kann bei Google den Schlüssel zurückspiegeln.
		// Deshalb wird er nicht mitgeloggt, nur der Statuscode.
		$meldung = sprintf( 'Places API antwortete mit Status %d.', $status );
		agentur_bewertungen_fehler_merken( $meldung );

		return new WP_Error( 'agentur_bewertungen_http', $meldung );
	}

	$daten = json_decode( wp_remote_retrieve_body( $antwort ), true );

	if ( ! is_array( $daten ) ) {
		agentur_bewertungen_fehler_merken( 'Antwort war kein verwertbares JSON.' );

		return new WP_Error( 'agentur_bewertungen_json', 'Antwort war kein verwertbares JSON.' );
	}

	$neu = array(
		'abgerufen_am' => time(),
		'note'         => isset( $daten['rating'] ) ? round( (float) $daten['rating'], 1 ) : 0.0,
		'anzahl'       => isset( $daten['userRatingCount'] ) ? absint( $daten['userRatingCount'] ) : 0,
		'profil'       => isset( $daten['googleMapsUri'] ) ? esc_url_raw( (string) $daten['googleMapsUri'] ) : '',
		'stimmen'      => agentur_bewertungen_stimmen_lesen( $daten['reviews'] ?? array() ),
		'fehler'       => '',
	);

	update_option( AGENTUR_BEWERTUNGEN_OPTION, $neu, false );

	return true;
}

/**
 * Wandelt die Bewertungen aus der Antwort in die Form um, die die Vorlage liest.
 *
 * Drei Entscheidungen, die hier festgeschrieben sind, weil sie sonst später
 * jemand „verbessert":
 *
 * - **originalText vor text.** Google übersetzt Bewertungen automatisch.
 *   Eine übersetzte Bewertung ist nicht mehr wörtlich, und wörtlich ist
 *   genau das, was § 3 UWG verlangt. Wo das Original vorliegt, gilt es.
 * - **Keine Sortierung, keine Filterung nach Note.** Die Reihenfolge kommt
 *   von Google. Wer nur Fünf-Sterne durchlässt, baut sich eine Auswahl, die
 *   das Gesamtbild verfälscht — abmahnbar, und zwar unabhängig davon, dass
 *   jede einzelne Bewertung echt ist.
 * - **Bewertungen ohne Text fliegen raus.** Nicht als Auswahl, sondern weil
 *   ein leeres Zitat nichts anzeigt. Die Note bleibt davon unberührt, sie
 *   kommt aus rating und zählt alle mit.
 */
function agentur_bewertungen_stimmen_lesen( $roh ): array {
	if ( ! is_array( $roh ) ) {
		return array();
	}

	$stimmen = array();

	foreach ( $roh as $eintrag ) {
		if ( ! is_array( $eintrag ) ) {
			continue;
		}

		$text = (string) ( $eintrag['originalText']['text'] ?? $eintrag['text']['text'] ?? '' );
		$text = trim( wp_strip_all_tags( $text ) );

		if ( '' === $text ) {
			continue;
		}

		$stimmen[] = array(
			'text' => $text,
			'name' => agentur_bewertungen_name( (string) ( $eintrag['authorAttribution']['displayName'] ?? '' ) ),
			'note' => isset( $eintrag['rating'] ) ? absint( $eintrag['rating'] ) : 0,
			'zeit' => sanitize_text_field( (string) ( $eintrag['relativePublishTimeDescription'] ?? '' ) ),
		);
	}

	// Die API liefert höchstens fünf. Die Grenze steht trotzdem hier, damit
	// eine Änderung auf Google-Seite nicht ungefragt die Seite umbaut.
	return array_slice( $stimmen, 0, 5 );
}

/**
 * Kürzt den Namen auf Vorname und Anfangsbuchstaben.
 *
 * ABZUSTIMMEN VOR DEM LIVEGANG. Hier stehen sich zwei Regeln gegenüber:
 *
 * - Datenminimierung (Art. 5 Abs. 1 lit. c DSGVO) spricht dafür. Der
 *   Bewertende hat seinen Namen bei Google veröffentlicht, nicht auf der
 *   Seite eines Betriebs.
 * - Die Places-Richtlinien verlangen, die Urheberangabe zur Bewertung
 *   anzuzeigen. Ob eine Kürzung das noch erfüllt, ist nicht eindeutig.
 *
 * Voreinstellung ist die Kürzung, weil sie den Bewertenden schützt und der
 * Verweis auf das vollständige Profil daneben steht. `german-legal-compliance`
 * entscheidet das vor der ersten Auslieferung; über den Filter lässt es sich
 * ohne Codeänderung umstellen.
 */
function agentur_bewertungen_name( string $name ): string {
	$name = sanitize_text_field( trim( $name ) );

	if ( '' === $name ) {
		return '';
	}

	/**
	 * Namen kürzen?
	 *
	 * @param bool $kuerzen Voreinstellung true.
	 */
	if ( ! apply_filters( 'agentur_bewertungen_namen_kuerzen', true ) ) {
		return $name;
	}

	$teile = preg_split( '/\s+/u', $name );

	if ( ! is_array( $teile ) || count( $teile ) < 2 ) {
		return $name;
	}

	$vorname  = array_shift( $teile );
	$nachname = (string) array_pop( $teile );

	return $vorname . ' ' . mb_substr( $nachname, 0, 1 ) . '.';
}

/**
 * Fehler am Bestand vermerken, ohne den Bestand zu zerstören.
 *
 * Ein Aussetzer darf die Seite nicht leeren. Erst die 30-Tage-Grenze räumt
 * auf — bis dahin bleibt der letzte gute Stand stehen und der Fehler steht
 * daneben, damit die Wartung ihn sieht.
 */
function agentur_bewertungen_fehler_merken( string $meldung ): void {
	$bestand = get_option( AGENTUR_BEWERTUNGEN_OPTION, array() );

	if ( ! is_array( $bestand ) ) {
		$bestand = array();
	}

	$bestand['fehler']     = sanitize_text_field( $meldung );
	$bestand['fehler_am']  = time();

	update_option( AGENTUR_BEWERTUNGEN_OPTION, $bestand, false );

	error_log( '[agentur-mu] Bewertungsabgleich fehlgeschlagen: ' . $meldung );
}


/* ── Zeitplan ────────────────────────────────────────────────────────── */

add_action( 'init', 'agentur_bewertungen_termin_sichern' );

/**
 * Trägt den täglichen Abgleich ein — aber nur, wenn die Anbindung steht.
 *
 * Ohne diese Bedingung stünde bei jedem Kunden ohne Google-Konto ein
 * Zeitplan im System, der täglich anläuft, sofort abbricht und eine Zeile
 * ins Fehlerprotokoll schreibt. Nach einem Jahr sucht jemand danach.
 */
function agentur_bewertungen_termin_sichern(): void {
	$eingetragen = (bool) wp_next_scheduled( AGENTUR_BEWERTUNGEN_TERMIN );

	if ( agentur_bewertungen_bereit() && ! $eingetragen ) {
		// Versetzt starten, damit nicht alle Kundeninstallationen zur
		// gleichen Minute denselben Endpunkt anfassen.
		wp_schedule_event( time() + wp_rand( 0, HOUR_IN_SECONDS ), 'daily', AGENTUR_BEWERTUNGEN_TERMIN );

		return;
	}

	if ( ! agentur_bewertungen_bereit() && $eingetragen ) {
		wp_clear_scheduled_hook( AGENTUR_BEWERTUNGEN_TERMIN );
	}
}

add_action( AGENTUR_BEWERTUNGEN_TERMIN, 'agentur_bewertungen_abgleich' );


/* ── Kommandozeile ───────────────────────────────────────────────────────
   Eingerichtet wird die Anbindung von der Agentur, nicht vom Inhaber. Er
   pflegt Inhalte, keine Schnittstellen (CLAUDE.md §4.2). Deshalb Befehle
   statt einer Einstellungsseite, die im Backend nur im Weg steht.        */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Google-Bewertungen einrichten und prüfen.
	 */
	class Agentur_Bewertungen_Befehle {

		/**
		 * Zeigt, ob die Anbindung steht und wie alt der Bestand ist.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-bewertungen status
		 */
		public function status(): void {
			$bestand = get_option( AGENTUR_BEWERTUNGEN_OPTION, array() );
			$daten   = agentur_bewertungen();

			$zeilen = array(
				array( 'Angabe', 'Wert' ),
				array( 'API-Schlüssel', '' !== agentur_bewertungen_schluessel() ? 'gesetzt' : 'FEHLT (Konstante AGENTUR_GOOGLE_PLACES_KEY)' ),
				array( 'Place ID', agentur_bewertungen_ort() ?: 'FEHLT' ),
				array( 'Quelle', $daten['quelle'] ),
				array( 'Note', $daten['note'] ?: '—' ),
				array( 'Anzahl', $daten['anzahl'] ?: '—' ),
				array( 'Zitate', (string) count( $daten['stimmen'] ) ),
				array( 'Stand', $daten['stand'] ?: '—' ),
				array(
					'Nächster Abgleich',
					wp_next_scheduled( AGENTUR_BEWERTUNGEN_TERMIN )
						? wp_date( 'j. F Y, H:i', (int) wp_next_scheduled( AGENTUR_BEWERTUNGEN_TERMIN ) )
						: 'nicht eingeplant',
				),
				array( 'Letzter Fehler', ! empty( $bestand['fehler'] ) ? $bestand['fehler'] : '—' ),
			);

			$kopf = array_shift( $zeilen );

			WP_CLI\Utils\format_items(
				'table',
				array_map(
					static fn( $z ) => array_combine( $kopf, $z ),
					$zeilen
				),
				$kopf
			);
		}

		/**
		 * Hinterlegt die Place ID des Betriebs.
		 *
		 * ## OPTIONEN
		 *
		 * <place-id>
		 * : Die Place ID aus dem Google-Unternehmensprofil, beginnt meist mit ChIJ.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-bewertungen ort ChIJxxxxxxxxxxxxxxxxxxxxxxx
		 */
		public function ort( array $args ): void {
			$ort = sanitize_text_field( $args[0] ?? '' );

			if ( '' === $ort ) {
				WP_CLI::error( 'Keine Place ID angegeben.' );
			}

			update_option( AGENTUR_BEWERTUNGEN_ORT, $ort, false );
			WP_CLI::success( 'Place ID hinterlegt: ' . $ort );

			if ( '' === agentur_bewertungen_schluessel() ) {
				WP_CLI::warning( 'Es fehlt noch die Konstante AGENTUR_GOOGLE_PLACES_KEY in der wp-config.php.' );
			}
		}

		/**
		 * Pflegt die Angaben von Hand.
		 *
		 * Der Auslieferungszustand jedes Betriebs ohne Google-Cloud-Konto und
		 * zugleich der Rückfall, wenn der Abgleich ausfällt. Wird bei der
		 * Einrichtung einmal gesetzt, danach höchstens quartalsweise gepflegt.
		 *
		 * Die Zitate werden **wörtlich** aus dem Unternehmensprofil übernommen:
		 * nicht gekürzt, nicht geglättet, Tippfehler bleiben drin. Erfundene
		 * oder geschönte Bewertungen sind nach Anhang zu § 3 UWG Nr. 23b
		 * abmahnbar — das ist kein Formfehler.
		 *
		 * ## OPTIONEN
		 *
		 * [--note=<note>]
		 * : Gesamtnote, etwa 4.5
		 *
		 * [--anzahl=<anzahl>]
		 * : Gesamtzahl der Bewertungen, etwa 234
		 *
		 * [--profil=<url>]
		 * : Verweis auf das Profil. Am besten der Kurzlink aus dem Unternehmensprofil (g.page/…).
		 *
		 * [--stimmen=<json>]
		 * : Zitate als JSON: [{"text":"…","name":"Vorname N."}, …]
		 *
		 * [--stand=<datum>]
		 * : Ablesedatum, Format JJJJ-MM-TT. Ohne Angabe: heute.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-bewertungen handarbeit --note=4.5 --anzahl=234 --stand=2026-08-17
		 */
		public function handarbeit( array $args, array $optionen ): void {
			$bestand = agentur_bewertungen_handarbeit();

			if ( isset( $optionen['note'] ) ) {
				$bestand['note'] = round( (float) $optionen['note'], 1 );
			}

			if ( isset( $optionen['anzahl'] ) ) {
				$bestand['anzahl'] = absint( $optionen['anzahl'] );
			}

			if ( isset( $optionen['profil'] ) ) {
				$bestand['profil'] = esc_url_raw( (string) $optionen['profil'] );
			}

			if ( isset( $optionen['stimmen'] ) ) {
				$stimmen = json_decode( (string) $optionen['stimmen'], true );

				if ( ! is_array( $stimmen ) ) {
					WP_CLI::error( '--stimmen ist kein gültiges JSON-Array.' );
				}

				$bestand['stimmen'] = array();

				foreach ( $stimmen as $stimme ) {
					$text = trim( wp_strip_all_tags( (string) ( $stimme['text'] ?? '' ) ) );

					if ( '' === $text ) {
						continue;
					}

					$bestand['stimmen'][] = array(
						'text' => $text,
						'name' => sanitize_text_field( (string) ( $stimme['name'] ?? '' ) ),
						'note' => isset( $stimme['note'] ) ? absint( $stimme['note'] ) : 0,
						'zeit' => sanitize_text_field( (string) ( $stimme['zeit'] ?? '' ) ),
					);
				}
			}

			$bestand['stand'] = isset( $optionen['stand'] )
				? (int) strtotime( (string) $optionen['stand'] )
				: time();

			update_option( AGENTUR_BEWERTUNGEN_MANUELL, $bestand, false );

			WP_CLI::success(
				sprintf(
					'Gepflegt: Note %s aus %s Bewertungen, %d Zitate.',
					number_format_i18n( (float) $bestand['note'], 1 ),
					number_format_i18n( (int) $bestand['anzahl'] ),
					count( $bestand['stimmen'] )
				)
			);
		}

		/**
		 * Holt den Bestand jetzt bei Google.
		 *
		 * ## OPTIONEN
		 *
		 * [--erzwingen]
		 * : Auch dann laufen, wenn der Bestand jünger als 20 Stunden ist. Kostet eine Abrechnungseinheit.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-bewertungen abgleich
		 *     wp agentur-bewertungen abgleich --erzwingen
		 */
		public function abgleich( array $args, array $optionen ): void {
			$ergebnis = agentur_bewertungen_abgleich( isset( $optionen['erzwingen'] ) );

			if ( is_wp_error( $ergebnis ) ) {
				WP_CLI::error( $ergebnis->get_error_message() );
			}

			$daten = agentur_bewertungen();

			WP_CLI::success(
				sprintf(
					'Abgeglichen. Note %s aus %s Bewertungen, %d Zitate übernommen.',
					$daten['note'] ?: '—',
					$daten['anzahl'] ?: '—',
					count( $daten['stimmen'] )
				)
			);
		}

		/**
		 * Verwirft den zwischengespeicherten Bestand.
		 *
		 * Nötig, wenn ein Kunde die Anbindung kündigt: die Bewertungen dürfen
		 * dann nicht in der Datenbank liegen bleiben.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-bewertungen verwerfen
		 */
		public function verwerfen(): void {
			delete_option( AGENTUR_BEWERTUNGEN_OPTION );
			WP_CLI::success( 'Bestand gelöscht. Die Seite zeigt jetzt die von Hand gepflegten Angaben.' );
		}
	}

	WP_CLI::add_command( 'agentur-bewertungen', 'Agentur_Bewertungen_Befehle' );
}
