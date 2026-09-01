<?php
/**
 * „Ihre Angaben" — benannte Felder statt einer editierbaren Startseite.
 *
 * ── Warum es das gibt ───────────────────────────────────────────────────
 *
 * Die Startseite besteht aus Musterverweisen. Öffnet der Inhaber sie im
 * Editor und speichert, schreibt WordPress die Muster als vollständiges
 * Markup aus: gemessen **358 Byte werden 26 439 Byte**. Ab diesem Moment
 * folgt seine Startseite nicht mehr den Mustern des Themes — jede spätere
 * Korrektur im Theme erreicht diesen Kunden nicht mehr, und niemand merkt
 * es, weil die Seite ja weiter richtig aussieht.
 *
 * Also wird die Startseite für ihn geschlossen (siehe rollen.php) und er
 * bekommt stattdessen die Angaben, die sich tatsächlich ändern: Telefon,
 * Zeiten, Adresse, Preise, Mannschaft.
 *
 * ── Der zweite Grund, der ebenso schwer wiegt ───────────────────────────
 *
 * Dieselbe Angabe stand an mehreren Stellen im Theme. Die Telefonnummer
 * **dreimal**: im Hero, im Kontaktabschnitt und im Fuß. Die Öffnungszeiten
 * in drei Schreibweisen. Wer eine davon ändert, hinterlässt zwei falsche —
 * und die falsche steht dann im Fuß, wo niemand hinsieht, bis ein Kunde
 * vergeblich anruft.
 *
 * Hier ist jede Angabe **einmal** hinterlegt. Muster und Vorlagenteile
 * lesen sie.
 *
 * ── Abgeleitete Felder ──────────────────────────────────────────────────
 *
 * Der Inhaber pflegt Straße, PLZ und Ort. Daraus entstehen „53840 Troisdorf",
 * „Frankfurter Str. 11, Troisdorf" und die vollständige Anschrift. Und aus
 * der angezeigten Telefonnummer entsteht der Wählverweis (`tel:+49…`).
 *
 * Das ist kein Komfort, sondern Fehlervermeidung: ein zweites Feld für den
 * Wählverweis wäre das Feld, das als Erstes veraltet — und ein falscher
 * `tel:`-Verweis fällt niemandem auf, der die Nummer daneben lesen kann.
 *
 * ── Vorlagenteile ───────────────────────────────────────────────────────
 *
 * `parts/footer.html` ist statisches HTML und kann kein PHP aufrufen. Dafür
 * gibt es den Block `agentur/angabe`, der serverseitig rendert. Muster in
 * PHP rufen `agentur_angabe()` direkt auf.
 *
 * @package agentur-basis-mu
 */

defined( 'ABSPATH' ) || exit;

const AGENTUR_ANGABEN_OPTION = 'agentur_angaben';
const AGENTUR_ANGABEN_RECHT  = 'agentur_angaben_bearbeiten';


/* ── Feldverzeichnis ─────────────────────────────────────────────────────
   Eine einzige Stelle. Ein neues Feld ist ein Eintrag hier, kein neues
   Formular — sonst wächst die Oberfläche schneller als die Sorgfalt.     */

/**
 * Alle Felder, gruppiert wie im Formular.
 *
 * `wo` steht in der Oberfläche unter dem Feld. Ohne diese Angabe tippt
 * jemand einen Text ein und weiß nicht, wo er landet — dann wird nichts
 * gepflegt, aus Sorge, etwas kaputtzumachen.
 */
function agentur_angaben_verzeichnis(): array {
	$verzeichnis = array(

		'betrieb' => array(
			'titel'  => 'Betrieb',
			'felder' => array(
				'name'    => array(
					'titel'   => 'Name des Betriebs',
					'typ'     => 'text',
					'vorgabe' => 'Friseur Sunshine',
					'wo'      => 'Im Fuß und in der Vorschau, wenn jemand die Seite teilt.',
				),
				'strasse' => array(
					'titel'   => 'Straße und Hausnummer',
					'typ'     => 'text',
					'vorgabe' => 'Frankfurter Str. 11',
					'wo'      => 'Im Kopf der Startseite, im Kontaktabschnitt und im Fuß.',
				),
				'plz'     => array(
					'titel'   => 'Postleitzahl',
					'typ'     => 'text',
					'vorgabe' => '53840',
					'wo'      => 'Im Kontaktabschnitt und im Fuß.',
				),
				'ort'     => array(
					'titel'   => 'Ort',
					'typ'     => 'text',
					'vorgabe' => 'Troisdorf',
					'wo'      => 'Im Kopf der Startseite, im Kontaktabschnitt und im Fuß.',
				),
				'telefon' => array(
					'titel'   => 'Telefonnummer',
					'typ'     => 'tel',
					'vorgabe' => '0178 5184291',
					'wo'      => 'Dreimal auf der Startseite und im Fuß. Der Wählverweis für '
						. 'Mobiltelefone wird daraus berechnet — bitte kein zweites Mal eintragen.',
				),
				'email'   => array(
					'titel'   => 'E-Mail-Adresse',
					'typ'     => 'email',
					'vorgabe' => '',
					'wo'      => 'Nur im Fuß, und nur wenn hier etwas steht.',
				),
			),
		),

		'zeiten' => array(
			'titel'   => 'Öffnungszeiten',
			'hinweis' => 'Die Zeiten stehen an zwei Stellen in zwei Schreibweisen: '
				. 'oben kurz, weiter unten ausgeschrieben. Deshalb zwei Felder — bitte '
				. 'beide ändern, sonst widersprechen sie sich.',
			'felder'  => array(
				'zeiten_kurz'    => array(
					'titel'   => 'Kurzform',
					'typ'     => 'text',
					'vorgabe' => 'Mo – Sa  09:00 – 20:00',
					'wo'      => 'Ganz oben auf der Startseite, in der Zeile neben der Adresse.',
				),
				'zeiten_tage'    => array(
					'titel'   => 'Tage, ausgeschrieben',
					'typ'     => 'text',
					'vorgabe' => 'Montag bis Samstag',
					'wo'      => 'Im Kontaktabschnitt und im Fuß.',
				),
				'zeiten_uhrzeit' => array(
					'titel'   => 'Uhrzeit, ausgeschrieben',
					'typ'     => 'text',
					'vorgabe' => '09:00 – 20:00 Uhr',
					'wo'      => 'Im Kontaktabschnitt und im Fuß.',
				),
				'zeiten_zusatz'  => array(
					'titel'   => 'Zusatz',
					'typ'     => 'text',
					'vorgabe' => 'Sonntag geschlossen',
					'wo'      => 'Kleingedruckt unter den Zeiten. Leer lassen blendet die Zeile aus.',
				),
			),
		),

		'texte' => array(
			'titel'  => 'Texte auf der Startseite',
			'felder' => array(
				'hero_ueberschrift'  => array(
					'titel'   => 'Große Überschrift ganz oben',
					'typ'     => 'text',
					'vorgabe' => 'Für Damen, Herren und Kinder — mitten in Troisdorf',
					'wo'      => 'Das Erste, was jemand liest. Auch das, was Google als Überschrift wertet.',
				),
				'preise_vorspann'    => array(
					'titel'   => 'Einleitung neben der Preisliste',
					'typ'     => 'textarea',
					'vorgabe' => 'Was am Ende zu zahlen ist, sagen wir vor dem ersten Schnitt — nicht danach. '
						. 'Wer unsicher ist, kommt zur Beratung vorbei, die kostet nichts.',
					'wo'      => 'Links neben den Preisen.',
				),
				'team_etikett'       => array(
					'titel'   => 'Kleiner Hinweis über der Mannschaft',
					'typ'     => 'text',
					'vorgabe' => 'Fotos folgen',
					'wo'      => 'Über den Porträts. Leer lassen blendet ihn aus — bitte tun, sobald die Fotos da sind.',
				),
				'termin_ueberschrift' => array(
					'titel'   => 'Kleine Zeile über der Telefonnummer',
					'typ'     => 'text',
					'vorgabe' => 'Termin vereinbaren',
					'wo'      => 'Im schwarzen Abschnitt am Ende der Startseite, über der großen Nummer. '
						. 'Bitte kurz halten — die Zeile läuft in Großbuchstaben.',
				),
				'termin_vorspann'    => array(
					'titel'   => 'Satz unter der Telefonnummer',
					'typ'     => 'textarea',
					'vorgabe' => 'Rufen Sie einfach an und sagen Sie, bei wem Sie möchten — oder lassen Sie sich '
						. 'einteilen. Ohne Termin sind Sie ebenso willkommen; dann kann es aber Wartezeit geben.',
					'wo'      => 'Direkt unter der großen Telefonnummer am Ende der Startseite.',
				),
				'footer_beschreibung' => array(
					'titel'   => 'Satz im Fuß',
					'typ'     => 'textarea',
					'vorgabe' => 'Schnitt, Farbe und Pflege für Damen, Herren und Kinder — seit Jahren an der '
						. 'Frankfurter Straße in Troisdorf.',
					'wo'      => 'Ganz unten, links neben den Öffnungszeiten.',
				),
			),
		),

		'preise' => array(
			'titel'   => 'Preisliste',
			'hinweis' => 'Alle Preise sind Endpreise inklusive Mehrwertsteuer — das schreibt die '
				. 'Preisangabenverordnung vor. Bei „ab"-Preisen muss erkennbar sein, wovon der '
				. 'Endpreis abhängt; der Satz darunter erledigt das. Bitte keine Nettopreise eintragen.',
			'liste'   => array(
				'schluessel' => 'preise',
				'spalten'    => array(
					'gruppe'   => array( 'titel' => 'Gruppe', 'breite' => '18%' ),
					'leistung' => array( 'titel' => 'Leistung', 'breite' => '52%' ),
					'preis'    => array( 'titel' => 'Preis', 'breite' => '20%' ),
				),
				'vorgabe'    => array(
					array( 'gruppe' => 'Damen', 'leistung' => 'Waschen, Schneiden, Föhnen', 'preis' => 'ab 39 €' ),
					array( 'gruppe' => 'Damen', 'leistung' => 'Ansatzfarbe', 'preis' => 'ab 45 €' ),
					array( 'gruppe' => 'Damen', 'leistung' => 'Strähnen in Folientechnik', 'preis' => 'ab 65 €' ),
					array( 'gruppe' => 'Damen', 'leistung' => 'Hochsteckfrisur', 'preis' => 'ab 55 €' ),
					array( 'gruppe' => 'Herren', 'leistung' => 'Waschen, Schneiden, Styling', 'preis' => 'ab 24 €' ),
					array( 'gruppe' => 'Herren', 'leistung' => 'Maschinenschnitt', 'preis' => '16 €' ),
					array( 'gruppe' => 'Herren', 'leistung' => 'Bart schneiden und formen', 'preis' => 'ab 14 €' ),
					array( 'gruppe' => 'Kinder', 'leistung' => 'bis 6 Jahre', 'preis' => '12 €' ),
					array( 'gruppe' => 'Kinder', 'leistung' => '7 bis 12 Jahre', 'preis' => '16 €' ),
				),
			),
		),

		'team' => array(
			'titel'   => 'Mannschaft',
			'hinweis' => 'Die Reihenfolge hier ist die Reihenfolge auf der Seite. '
				. 'Für Fotos bitte melden — dafür braucht es die schriftliche Einwilligung '
				. 'der abgebildeten Person (§ 22 KUG).',
			'liste'   => array(
				'schluessel' => 'team',
				'spalten'    => array(
					'name'       => array( 'titel' => 'Name', 'breite' => '18%' ),
					'erfahrung'  => array( 'titel' => 'Erfahrung', 'breite' => '20%' ),
					'sprachen'   => array( 'titel' => 'Sprachen', 'breite' => '26%' ),
					'schwerpunkt' => array( 'titel' => 'Schwerpunkt', 'breite' => '26%' ),
				),
				'vorgabe'    => array(
					array( 'name' => 'Aylin', 'erfahrung' => '14 Jahre Erfahrung', 'sprachen' => 'Deutsch, Türkisch', 'schwerpunkt' => 'Farbe, Strähnen, Langhaar' ),
					array( 'name' => 'Marek', 'erfahrung' => '11 Jahre Erfahrung', 'sprachen' => 'Deutsch, Polnisch', 'schwerpunkt' => 'Herrenschnitt, Bart' ),
					array( 'name' => 'Deniz', 'erfahrung' => '7 Jahre Erfahrung', 'sprachen' => 'Deutsch, Türkisch, Englisch', 'schwerpunkt' => 'Schnitt und Beratung, Kinder' ),
				),
			),
		),
	);

	/**
	 * Verzeichnis der pflegbaren Angaben.
	 *
	 * Der Haken, über den eine andere Branche eigene Felder bekommt, ohne
	 * dass diese Datei je Kunde verzweigt.
	 *
	 * @param array $verzeichnis Gruppen mit Feldern.
	 */
	return apply_filters( 'agentur_angaben_verzeichnis', $verzeichnis );
}

/**
 * Flache Sicht auf alle Einzelfelder: Schlüssel → Beschreibung.
 */
function agentur_angaben_felder(): array {
	$felder = array();

	foreach ( agentur_angaben_verzeichnis() as $gruppe ) {
		foreach ( $gruppe['felder'] ?? array() as $schluessel => $feld ) {
			$felder[ $schluessel ] = $feld;
		}
	}

	return $felder;
}


/* ── Lesen ───────────────────────────────────────────────────────────── */

/**
 * Eine Angabe, mit Rückfall auf die Vorgabe aus dem Verzeichnis.
 *
 * Wird bei jedem Seitenaufruf mehrfach gerufen. Deshalb ein statischer
 * Zwischenspeicher: eine Option, nicht zwölf.
 */
function agentur_angabe( string $schluessel, string $rueckfall = '' ): string {
	static $gespeichert = null;

	if ( null === $gespeichert ) {
		$roh         = get_option( AGENTUR_ANGABEN_OPTION, array() );
		$gespeichert = is_array( $roh ) ? $roh : array();
	}

	// Abgeleitete Felder zuerst: sie stehen nie in der Option.
	$abgeleitet = agentur_angabe_abgeleitet( $schluessel, $gespeichert );

	if ( null !== $abgeleitet ) {
		return $abgeleitet;
	}

	if ( isset( $gespeichert[ $schluessel ] ) && is_string( $gespeichert[ $schluessel ] ) && '' !== $gespeichert[ $schluessel ] ) {
		return $gespeichert[ $schluessel ];
	}

	$felder = agentur_angaben_felder();

	if ( isset( $felder[ $schluessel ] ) ) {
		// Ein leer gespeichertes Feld ist eine Entscheidung, keine Lücke:
		// „Zusatz leer lassen blendet die Zeile aus" steht so im Formular.
		if ( array_key_exists( $schluessel, $gespeichert ) ) {
			return '';
		}

		return (string) ( $felder[ $schluessel ]['vorgabe'] ?? '' );
	}

	return $rueckfall;
}

/**
 * Felder, die aus anderen berechnet werden.
 *
 * @return string|null null, wenn der Schlüssel nicht abgeleitet ist.
 */
function agentur_angabe_abgeleitet( string $schluessel, array $gespeichert ): ?string {
	$hole = static function ( string $s ) {
		return agentur_angabe( $s );
	};

	switch ( $schluessel ) {
		case 'plz_ort':
			return trim( $hole( 'plz' ) . ' ' . $hole( 'ort' ) );

		case 'adresse_kurz':
			$ort = $hole( 'ort' );

			return '' !== $ort ? $hole( 'strasse' ) . ', ' . $ort : $hole( 'strasse' );

		case 'telefon_wahl':
			return agentur_telefon_wahl( $hole( 'telefon' ) );
	}

	return null;
}

/**
 * Macht aus einer angezeigten Nummer den Wählverweis.
 *
 * „0178 5184291" wird zu „+491785184291". Ein Mobiltelefon wählt beides,
 * aber die internationale Schreibweise funktioniert auch, wenn der Anrufer
 * gerade im Ausland ist — und das ist bei einer Grenzregion kein Randfall.
 */
function agentur_telefon_wahl( string $anzeige ): string {
	$ziffern = preg_replace( '/[^\d+]/', '', $anzeige );

	if ( ! is_string( $ziffern ) || '' === $ziffern ) {
		return '';
	}

	/**
	 * Ländervorwahl für Nummern in nationaler Schreibweise.
	 *
	 * @param string $vorwahl Voreinstellung „+49".
	 */
	$vorwahl = (string) apply_filters( 'agentur_telefon_vorwahl', '+49' );

	if ( str_starts_with( $ziffern, '+' ) ) {
		return $ziffern;
	}

	if ( str_starts_with( $ziffern, '00' ) ) {
		return '+' . substr( $ziffern, 2 );
	}

	if ( str_starts_with( $ziffern, '0' ) ) {
		return $vorwahl . substr( $ziffern, 1 );
	}

	return $ziffern;
}

/**
 * Ersetzt Platzhalter der Form `{telefon}` durch die gepflegte Angabe.
 *
 * Für Fließtexte, in denen eine Angabe vorkommt — allen voran die
 * Beschreibung für Google: „Termin telefonisch unter {telefon}". Ohne das
 * wäre die Meta-Description die eine Stelle, an der die alte Nummer stehen
 * bliebe, nachdem der Inhaber sie unter „Ihre Angaben" geändert hat. Genau
 * dieser Fall ist beim Prüfen aufgefallen: die Nummer war auf der Seite
 * dreimal neu und im Suchergebnis noch zweimal alt.
 *
 * Unbekannte Platzhalter bleiben stehen. Sie wortlos zu löschen würde aus
 * einem Tippfehler eine Lücke im Satz machen, die niemand mehr zuordnet.
 */
function agentur_platzhalter( string $text ): string {
	if ( ! str_contains( $text, '{' ) ) {
		return $text;
	}

	$erlaubt = array_merge(
		array_keys( agentur_angaben_felder() ),
		array( 'plz_ort', 'adresse_kurz', 'telefon_wahl' )
	);

	return (string) preg_replace_callback(
		'/\{([a-z_]+)\}/',
		static function ( array $treffer ) use ( $erlaubt ): string {
			return in_array( $treffer[1], $erlaubt, true )
				? agentur_angabe( $treffer[1] )
				: $treffer[0];
		},
		$text
	);
}

/**
 * Die Platzhalter, die in Fließtexten erlaubt sind — für die Oberfläche.
 */
function agentur_platzhalter_liste(): array {
	return array_merge(
		array_keys( agentur_angaben_felder() ),
		array( 'plz_ort', 'adresse_kurz', 'telefon_wahl' )
	);
}

/**
 * Eine gepflegte Liste (Preise, Mannschaft) als Zeilen.
 *
 * @return array<int,array<string,string>>
 */
function agentur_angabe_liste( string $schluessel ): array {
	$gespeichert = get_option( AGENTUR_ANGABEN_OPTION, array() );
	$listen      = is_array( $gespeichert ) && isset( $gespeichert['listen'] ) && is_array( $gespeichert['listen'] )
		? $gespeichert['listen']
		: array();

	if ( isset( $listen[ $schluessel ] ) && is_array( $listen[ $schluessel ] ) ) {
		return $listen[ $schluessel ];
	}

	foreach ( agentur_angaben_verzeichnis() as $gruppe ) {
		if ( ( $gruppe['liste']['schluessel'] ?? '' ) === $schluessel ) {
			return (array) ( $gruppe['liste']['vorgabe'] ?? array() );
		}
	}

	return array();
}

/**
 * Die Preisliste nach Gruppen, in der eingetragenen Reihenfolge.
 *
 * Die Reihenfolge der Gruppen ergibt sich aus dem ersten Auftreten. Damit
 * entscheidet der Inhaber sie durch Sortieren der Zeilen und braucht kein
 * zweites Feld dafür.
 *
 * @return array<string,array<int,array{leistung:string,preis:string}>>
 */
function agentur_preise_nach_gruppen(): array {
	$gruppen = array();

	foreach ( agentur_angabe_liste( 'preise' ) as $zeile ) {
		$name     = trim( (string) ( $zeile['gruppe'] ?? '' ) );
		$leistung = trim( (string) ( $zeile['leistung'] ?? '' ) );

		if ( '' === $leistung ) {
			continue;
		}

		$gruppen[ $name ][] = array(
			'leistung' => $leistung,
			'preis'    => trim( (string) ( $zeile['preis'] ?? '' ) ),
		);
	}

	return $gruppen;
}


/* ── Block für Vorlagenteile ─────────────────────────────────────────── */

add_action( 'init', 'agentur_angaben_block_anmelden' );

/**
 * Meldet `agentur/angabe` an.
 *
 * Für `parts/footer.html`: statisches HTML kann kein PHP aufrufen, und die
 * Angaben sollen trotzdem aus derselben Quelle kommen wie überall sonst.
 *
 * Bewusst ohne block.json und ohne Editor-Oberfläche: der Block gehört in
 * die Vorlage, nicht in den Werkzeugkasten des Inhabers. Er soll ihn nicht
 * einfügen können, er soll ihn nicht einmal sehen.
 */
function agentur_angaben_block_anmelden(): void {
	register_block_type(
		'agentur/angabe',
		array(
			'api_version'     => 3,
			'attributes'      => array(
				'felder'    => array( 'type' => 'array', 'default' => array() ),
				'als'       => array( 'type' => 'string', 'default' => 'absatz' ),
				'className' => array( 'type' => 'string', 'default' => '' ),
			),
			'render_callback' => 'agentur_angaben_block_rendern',
		)
	);
}

/**
 * Rendert den Block.
 *
 * `als`:
 *   absatz   — ein <p>, mehrere Felder mit <br> untereinander
 *   telefon  — ein <p> mit Wählverweis
 *   text     — nur der Text, ohne Hülle
 *
 * Leere Felder fallen heraus. Bleibt nichts übrig, wird nichts ausgegeben —
 * ein leeres <p> im Fuß erzeugt eine Lücke, die aussieht wie ein Fehler.
 */
function agentur_angaben_block_rendern( array $attribute ): string {
	$felder = array_filter( array_map( 'strval', (array) ( $attribute['felder'] ?? array() ) ) );
	$als    = (string) ( $attribute['als'] ?? 'absatz' );
	$klasse = sanitize_html_class( (string) ( $attribute['className'] ?? '' ) );

	if ( 'telefon' === $als ) {
		$anzeige = agentur_angabe( 'telefon' );

		if ( '' === $anzeige ) {
			return '';
		}

		return sprintf(
			'<p%s><a href="tel:%s">%s</a></p>',
			$klasse ? ' class="' . esc_attr( $klasse ) . '"' : '',
			esc_attr( agentur_angabe( 'telefon_wahl' ) ),
			esc_html( $anzeige )
		);
	}

	$werte = array();

	foreach ( $felder as $feld ) {
		$wert = agentur_angabe( $feld );

		if ( '' !== $wert ) {
			$werte[] = esc_html( $wert );
		}
	}

	if ( empty( $werte ) ) {
		return '';
	}

	$text = implode( '<br>', $werte );

	if ( 'text' === $als ) {
		return $text;
	}

	return sprintf( '<p%s>%s</p>', $klasse ? ' class="' . esc_attr( $klasse ) . '"' : '', $text );
}


/* ── Formular im Backend ─────────────────────────────────────────────── */

add_action( 'admin_menu', 'agentur_angaben_menue' );

/**
 * Eigener Menüpunkt statt eines Untermenüs unter „Einstellungen".
 *
 * Der Inhaber hat kein `manage_options` und sieht „Einstellungen" gar nicht.
 * Und es sind auch keine Einstellungen, sondern sein Inhalt.
 */
function agentur_angaben_menue(): void {
	add_menu_page(
		'Ihre Angaben',
		'Ihre Angaben',
		AGENTUR_ANGABEN_RECHT,
		'agentur-angaben',
		'agentur_angaben_seite',
		'dashicons-store',
		21
	);
}

add_action( 'admin_enqueue_scripts', 'agentur_angaben_admin_dateien' );

function agentur_angaben_admin_dateien( string $haken ): void {
	if ( 'toplevel_page_agentur-angaben' !== $haken ) {
		return;
	}

	$js = AGENTUR_MU_PFAD . '/admin/angaben.js';

	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'agentur-angaben',
			WPMU_PLUGIN_URL . '/agentur-basis-mu/admin/angaben.js',
			array(),
			(string) filemtime( $js ),
			true
		);
	}
}

/**
 * Zeichnet das Formular.
 */
function agentur_angaben_seite(): void {
	if ( ! current_user_can( AGENTUR_ANGABEN_RECHT ) ) {
		wp_die( 'Keine Berechtigung.' );
	}

	?>
	<div class="wrap">
		<h1>Ihre Angaben</h1>
		<p style="max-width:52em">Alles, was hier steht, erscheint auf Ihrer Website — jede Angabe
			nur einmal eingetragen und überall gleich. Unter jedem Feld steht, wo es auftaucht.</p>

		<?php if ( isset( $_GET['gespeichert'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p>Gespeichert. Die Website zeigt die Änderung sofort.</p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="agentur_angaben_speichern">
			<?php wp_nonce_field( 'agentur_angaben_speichern' ); ?>

			<?php foreach ( agentur_angaben_verzeichnis() as $kennung => $gruppe ) : ?>
				<h2 style="margin-top:2em"><?php echo esc_html( $gruppe['titel'] ); ?></h2>

				<?php if ( ! empty( $gruppe['hinweis'] ) ) : ?>
					<p style="max-width:52em;color:#50575e"><?php echo esc_html( $gruppe['hinweis'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $gruppe['liste'] ) ) : ?>
					<?php agentur_angaben_liste_zeichnen( $gruppe['liste'] ); ?>
				<?php else : ?>
					<table class="form-table" role="presentation"><tbody>
					<?php foreach ( $gruppe['felder'] as $schluessel => $feld ) : ?>
						<tr>
							<th scope="row">
								<label for="feld-<?php echo esc_attr( $schluessel ); ?>"><?php echo esc_html( $feld['titel'] ); ?></label>
							</th>
							<td>
								<?php if ( 'textarea' === $feld['typ'] ) : ?>
									<textarea id="feld-<?php echo esc_attr( $schluessel ); ?>"
										name="angaben[<?php echo esc_attr( $schluessel ); ?>]"
										rows="3" class="large-text"><?php echo esc_textarea( agentur_angabe( $schluessel ) ); ?></textarea>
								<?php else : ?>
									<input type="<?php echo esc_attr( 'email' === $feld['typ'] ? 'email' : ( 'tel' === $feld['typ'] ? 'tel' : 'text' ) ); ?>"
										id="feld-<?php echo esc_attr( $schluessel ); ?>"
										name="angaben[<?php echo esc_attr( $schluessel ); ?>]"
										value="<?php echo esc_attr( agentur_angabe( $schluessel ) ); ?>"
										class="regular-text">
								<?php endif; ?>

								<?php if ( ! empty( $feld['wo'] ) ) : ?>
									<p class="description" style="max-width:44em"><?php echo esc_html( $feld['wo'] ); ?></p>
								<?php endif; ?>

								<?php if ( 'telefon' === $schluessel ) : ?>
									<p class="description">Wählverweis daraus berechnet:
										<code><?php echo esc_html( agentur_angabe( 'telefon_wahl' ) ); ?></code></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody></table>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php submit_button( 'Angaben speichern' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Zeichnet eine Liste mit Zeilen zum Hinzufügen und Entfernen.
 */
function agentur_angaben_liste_zeichnen( array $liste ): void {
	$schluessel = $liste['schluessel'];
	$spalten    = $liste['spalten'];
	$zeilen     = agentur_angabe_liste( $schluessel );

	?>
	<table class="widefat striped agentur-liste" data-liste="<?php echo esc_attr( $schluessel ); ?>" style="max-width:60em">
		<thead>
			<tr>
				<?php foreach ( $spalten as $spalte ) : ?>
					<th style="width:<?php echo esc_attr( $spalte['breite'] ); ?>"><?php echo esc_html( $spalte['titel'] ); ?></th>
				<?php endforeach; ?>
				<th style="width:4em"><span class="screen-reader-text">Entfernen</span></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( array_values( $zeilen ) as $i => $zeile ) : ?>
				<tr>
					<?php foreach ( $spalten as $name => $spalte ) : ?>
						<td><input type="text" style="width:100%"
							name="listen[<?php echo esc_attr( $schluessel ); ?>][<?php echo (int) $i; ?>][<?php echo esc_attr( $name ); ?>]"
							value="<?php echo esc_attr( (string) ( $zeile[ $name ] ?? '' ) ); ?>"
							aria-label="<?php echo esc_attr( $spalte['titel'] ); ?>"></td>
					<?php endforeach; ?>
					<td><button type="button" class="button-link agentur-zeile-weg" aria-label="Zeile entfernen">Entfernen</button></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<template class="agentur-zeilenvorlage" data-liste="<?php echo esc_attr( $schluessel ); ?>">
		<tr>
			<?php foreach ( $spalten as $name => $spalte ) : ?>
				<td><input type="text" style="width:100%"
					name="listen[<?php echo esc_attr( $schluessel ); ?>][__NR__][<?php echo esc_attr( $name ); ?>]"
					value="" aria-label="<?php echo esc_attr( $spalte['titel'] ); ?>"></td>
			<?php endforeach; ?>
			<td><button type="button" class="button-link agentur-zeile-weg" aria-label="Zeile entfernen">Entfernen</button></td>
		</tr>
	</template>

	<p><button type="button" class="button agentur-zeile-neu" data-liste="<?php echo esc_attr( $schluessel ); ?>">Zeile hinzufügen</button></p>
	<?php
}

add_action( 'admin_post_agentur_angaben_speichern', 'agentur_angaben_speichern' );

/**
 * Nimmt das Formular entgegen.
 */
function agentur_angaben_speichern(): void {
	if ( ! current_user_can( AGENTUR_ANGABEN_RECHT ) ) {
		wp_die( 'Keine Berechtigung.' );
	}

	check_admin_referer( 'agentur_angaben_speichern' );

	$felder    = agentur_angaben_felder();
	$eingang   = isset( $_POST['angaben'] ) && is_array( $_POST['angaben'] ) ? wp_unslash( $_POST['angaben'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$gespeicht = array();

	// Nur bekannte Felder. Ein untergeschobenes Feld landet nicht in der Option.
	foreach ( $felder as $schluessel => $feld ) {
		$wert = isset( $eingang[ $schluessel ] ) ? (string) $eingang[ $schluessel ] : '';

		$gespeicht[ $schluessel ] = match ( $feld['typ'] ) {
			'email'    => sanitize_email( $wert ),
			'textarea' => sanitize_textarea_field( $wert ),
			default    => sanitize_text_field( $wert ),
		};
	}

	$listen        = array();
	$listeneingang = isset( $_POST['listen'] ) && is_array( $_POST['listen'] ) ? wp_unslash( $_POST['listen'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	foreach ( agentur_angaben_verzeichnis() as $gruppe ) {
		if ( empty( $gruppe['liste'] ) ) {
			continue;
		}

		$kennung = $gruppe['liste']['schluessel'];
		$spalten = array_keys( $gruppe['liste']['spalten'] );
		$roh     = is_array( $listeneingang[ $kennung ] ?? null ) ? $listeneingang[ $kennung ] : array();

		foreach ( $roh as $zeile ) {
			if ( ! is_array( $zeile ) ) {
				continue;
			}

			$sauber = array();

			foreach ( $spalten as $spalte ) {
				$sauber[ $spalte ] = sanitize_text_field( (string) ( $zeile[ $spalte ] ?? '' ) );
			}

			// Vollständig leere Zeilen fliegen raus — sonst sammeln sich
			// Leerzeilen an, die auf der Seite als Lücken erscheinen.
			if ( '' !== implode( '', $sauber ) ) {
				$listen[ $kennung ][] = $sauber;
			}
		}
	}

	$gespeicht['listen'] = $listen;

	update_option( AGENTUR_ANGABEN_OPTION, $gespeicht, true );

	wp_safe_redirect( add_query_arg( 'gespeichert', '1', admin_url( 'admin.php?page=agentur-angaben' ) ) );
	exit;
}


/* ── Kommandozeile ───────────────────────────────────────────────────── */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Angaben des Betriebs lesen und setzen.
	 */
	class Agentur_Angaben_Befehle {

		/**
		 * Zeigt alle Angaben, auch die abgeleiteten.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-angaben zeigen
		 */
		public function zeigen(): void {
			$zeilen = array();

			foreach ( agentur_angaben_verzeichnis() as $gruppe ) {
				foreach ( $gruppe['felder'] ?? array() as $schluessel => $feld ) {
					$zeilen[] = array(
						'Gruppe' => $gruppe['titel'],
						'Feld'   => $schluessel,
						'Wert'   => mb_substr( agentur_angabe( $schluessel ), 0, 58 ),
					);
				}

				if ( ! empty( $gruppe['liste'] ) ) {
					$zeilen[] = array(
						'Gruppe' => $gruppe['titel'],
						'Feld'   => $gruppe['liste']['schluessel'] . ' (Liste)',
						'Wert'   => sprintf( '%d Zeilen', count( agentur_angabe_liste( $gruppe['liste']['schluessel'] ) ) ),
					);
				}
			}

			foreach ( array( 'plz_ort', 'adresse_kurz', 'telefon_wahl' ) as $schluessel ) {
				$zeilen[] = array( 'Gruppe' => 'abgeleitet', 'Feld' => $schluessel, 'Wert' => agentur_angabe( $schluessel ) );
			}

			WP_CLI\Utils\format_items( 'table', $zeilen, array( 'Gruppe', 'Feld', 'Wert' ) );
		}

		/**
		 * Setzt eine einzelne Angabe.
		 *
		 * ## OPTIONEN
		 *
		 * <feld>
		 * : Schlüssel des Feldes, siehe `zeigen`.
		 *
		 * <wert>
		 * : Der neue Wert.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-angaben setzen telefon "02241 123456"
		 */
		public function setzen( array $args ): void {
			$feld = (string) ( $args[0] ?? '' );
			$wert = (string) ( $args[1] ?? '' );

			$felder = agentur_angaben_felder();

			if ( ! isset( $felder[ $feld ] ) ) {
				WP_CLI::error( sprintf( 'Unbekanntes Feld „%s". `wp agentur-angaben zeigen` listet alle.', $feld ) );
			}

			$gespeichert = get_option( AGENTUR_ANGABEN_OPTION, array() );
			$gespeichert = is_array( $gespeichert ) ? $gespeichert : array();

			$gespeichert[ $feld ] = match ( $felder[ $feld ]['typ'] ) {
				'email'    => sanitize_email( $wert ),
				'textarea' => sanitize_textarea_field( $wert ),
				default    => sanitize_text_field( $wert ),
			};

			update_option( AGENTUR_ANGABEN_OPTION, $gespeichert, true );

			WP_CLI::success( sprintf( '%s = %s', $feld, $gespeichert[ $feld ] ) );

			if ( 'telefon' === $feld ) {
				WP_CLI::log( 'Wählverweis daraus: ' . agentur_telefon_wahl( $gespeichert[ $feld ] ) );
			}
		}
	}

	WP_CLI::add_command( 'agentur-angaben', 'Agentur_Angaben_Befehle' );
}
