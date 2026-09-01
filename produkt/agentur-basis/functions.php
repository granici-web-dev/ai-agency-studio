<?php
/**
 * Agentur Basis — Theme-Funktionen.
 *
 * Grundsatz: Dieses Theme bestimmt das Aussehen. Alles, was eine Seite
 * *betreibt* — Rollenrechte, Buchung, Rechtstexte, Sicherheit — gehört in
 * das begleitende Must-Use-Plugin und nicht hierher. Wer Rollen im Theme
 * ändert, verliert sie beim Themewechsel, und niemand merkt es.
 *
 * @package agentur-basis
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const AGENTUR_BASIS_VERSION = '0.1.0';

/**
 * Grundeinstellungen.
 */
function agentur_basis_setup(): void {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );

	load_theme_textdomain( 'agentur-basis', get_template_directory() . '/languages' );

	/*
	 * Bildgrößen des Rasters. Der Zuschnitt ist fest, damit ein hochkant
	 * geknipstes Porträt und ein Querformat im selben Muster gleich sitzen.
	 */
	add_image_size( 'basis-portrait', 800, 800, true );   // 1:1, Mitarbeiterkarte
	add_image_size( 'basis-quer', 1600, 1000, true );     // 8:5, Leistungsabschnitt
	add_image_size( 'basis-breit', 2400, 1200, true );    // 2:1, Hero
}
add_action( 'after_setup_theme', 'agentur_basis_setup' );

/**
 * Stylesheet des Themes.
 */
function agentur_basis_styles(): void {
	wp_enqueue_style(
		'agentur-basis',
		get_stylesheet_uri(),
		array(),
		AGENTUR_BASIS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'agentur_basis_styles' );

/**
 * Die beiden Schriftschnitte des ersten Bildschirms vorladen.
 *
 * Ohne das erscheint die Überschrift erst nach dem Nachladen der Schrift —
 * das schlägt direkt auf den LCP durch, und der steht in §6 als Zusage.
 * Nur latin: Deutsch braucht latin-ext nicht, und wer einen türkischen oder
 * polnischen Mitarbeiternamen setzt, lädt die Ergänzung ohnehin nach.
 */
function agentur_basis_schriften_vorladen(): void {
	$schriften = array(
		'/assets/fonts/archivo-400-latin.woff2',
		'/assets/fonts/archivo-display-latin.woff2',
	);

	foreach ( $schriften as $pfad ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_template_directory_uri() . $pfad )
		);
	}
}
add_action( 'wp_head', 'agentur_basis_schriften_vorladen', 1 );

/**
 * Musterkategorien.
 *
 * Die Branche steckt in der Kategorie, nicht in einem eigenen Theme:
 * ein Friseur sieht in der Auswahl nur seine Abschnitte.
 */
function agentur_basis_musterkategorien(): void {
	$kategorien = array(
		'agentur-seitenaufbau' => __( 'Seitenaufbau', 'agentur-basis' ),
		'agentur-friseur'   => __( 'Friseur', 'agentur-basis' ),
	);

	foreach ( $kategorien as $slug => $titel ) {
		register_block_pattern_category( $slug, array( 'label' => $titel ) );
	}
}
add_action( 'init', 'agentur_basis_musterkategorien' );

/**
 * Ballast abwerfen.
 *
 * Emoji-Skript und die oEmbed-Erkennung kosten Anfragen und Bytes auf jeder
 * Seite und leisten für einen Salon nichts. Die Bilderkennung für Emojis
 * lädt zudem von einem fremden Host — das allein reicht als Grund (§2.1).
 */
function agentur_basis_ballast_abwerfen(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
}
add_action( 'init', 'agentur_basis_ballast_abwerfen' );

/**
 * Keine Emoji-Ersetzung in Beitragsinhalten.
 */
add_filter( 'emoji_svg_url', '__return_false' );


/* --- Bilder des Themes: Größen und Ladeverhalten beim Rendern -----------
 *
 * Diese Bilder liegen im Theme, nicht in der Mediathek. Deshalb kann
 * WordPress ihnen weder srcset noch Maße noch eine Ladestrategie geben —
 * `wp_filter_content_tags()` kennt nur Anhänge. Also macht es das Theme,
 * und zwar an derselben Stelle im Ablauf: beim Rendern.
 *
 * WARUM NICHT IM MUSTER. Von Hand ins Markup geschriebene Attribute weichen
 * von dem ab, was der Bildblock beim Speichern erzeugt. Die Blockprüfung im
 * Editor erklärt den Block dann für ungültig — was niemandem auffällt, weil
 * WordPress ungültige Blöcke stillschweigend wörtlich behält. Bis jemand den
 * Editor aufmacht und eine Warnung sieht, die er nicht einordnen kann.
 *
 * Das Verzeichnis ist die einzige Stelle, an der Maße stehen. Ein Bild
 * auszutauschen heißt: Datei ersetzen, hier die Maße nachziehen. Falsche
 * Maße sind kein Schönheitsfehler, sondern Layout-Sprünge beim Laden (CLS).
 */

/**
 * Was das Theme über seine eigenen Bilder weiß.
 *
 * `lcp` markiert das eine Bild über dem Falz: es wird sofort geladen und
 * bevorzugt. Genau eines — zwei bevorzugte Bilder sind keines.
 */
function agentur_basis_bildverzeichnis(): array {
	return array(
		'interieur.webp' => array(
			'breite' => 1600,
			'hoehe'  => 1200,
			'sizes'  => '(max-width: 781px) 92vw, 46vw',
			'lcp'    => true,
			'fassungen' => array( 'interieur-480.webp' => 480, 'interieur-900.webp' => 900, 'interieur.webp' => 1600 ),
		),
		'pflege.webp'    => array( 'breite' => 1600, 'hoehe' => 1000 ),
		'haar.webp'      => array( 'breite' => 1600, 'hoehe' => 1000 ),
		'bart.webp'      => array( 'breite' => 1600, 'hoehe' => 1000 ),
		'farbe.webp'     => array( 'breite' => 1600, 'hoehe' => 1000 ),
	);
}

add_filter( 'render_block', 'agentur_basis_bild_attribute', 10, 2 );

/**
 * Ergänzt Maße, srcset, sizes und Ladeverhalten an Bildern des Themes.
 */
function agentur_basis_bild_attribute( string $inhalt, array $block ): string {
	if ( 'core/image' !== ( $block['blockName'] ?? '' ) || ! str_contains( $inhalt, '/assets/img/demo/' ) ) {
		return $inhalt;
	}

	$verzeichnis = agentur_basis_bildverzeichnis();
	$basis       = get_template_directory_uri() . '/assets/img/demo';

	return (string) preg_replace_callback(
		'/<img\b[^>]*>/i',
		static function ( array $treffer ) use ( $verzeichnis, $basis ): string {
			$tag = $treffer[0];

			if ( ! preg_match( '#/assets/img/demo/([^"\'\s>]+)#', $tag, $datei ) ) {
				return $tag;
			}

			$angaben = $verzeichnis[ $datei[1] ] ?? null;

			if ( null === $angaben ) {
				return $tag;
			}

			$neu = array(
				'width'  => (string) $angaben['breite'],
				'height' => (string) $angaben['hoehe'],
			);

			if ( ! empty( $angaben['fassungen'] ) ) {
				$teile = array();

				foreach ( $angaben['fassungen'] as $name => $breite ) {
					$teile[] = "$basis/$name {$breite}w";
				}

				$neu['srcset'] = implode( ', ', $teile );
				$neu['sizes']  = (string) ( $angaben['sizes'] ?? '100vw' );
			}

			// Genau ein Bild wird bevorzugt geladen. Ein Bild über dem Falz
			// verzögert lazy geladen den LCP, statt ihn zu verbessern.
			if ( ! empty( $angaben['lcp'] ) ) {
				$neu['loading']       = 'eager';
				$neu['fetchpriority'] = 'high';
				$neu['decoding']      = 'async';
			} else {
				$neu['loading']  = 'lazy';
				$neu['decoding'] = 'async';
			}

			$zusatz = '';

			foreach ( $neu as $name => $wert ) {
				// Was schon im Markup steht, bleibt stehen — sonst überschriebe
				// diese Funktion eine bewusste Ausnahme im Muster.
				if ( ! preg_match( '/\s' . preg_quote( $name, '/' ) . '\s*=/i', $tag ) ) {
					$zusatz .= sprintf( ' %s="%s"', $name, esc_attr( $wert ) );
				}
			}

			return '' === $zusatz ? $tag : preg_replace( '/<img\b/i', '<img' . $zusatz, $tag, 1 );
		},
		$inhalt
	);
}


/**
 * URL einer Theme-Datei mit Versionsstempel.
 *
 * Ohne den Stempel hält der Browser eine ausgetauschte Datei fest — beim
 * Wechsel auf die helle Fläche blieb der schwarze Porträt-Platzhalter
 * sichtbar, obwohl die Datei längst hell war. Im Test ist das Verwirrung,
 * beim Kunden ist es ein Bild, das nach dem Austausch wochenlang das alte
 * bleibt und niemand versteht, warum.
 *
 * Der Stempel ist die Änderungszeit der Datei: kein Pflegeaufwand, und er
 * ändert sich genau dann, wenn sich die Datei ändert.
 */
function agentur_basis_datei( string $pfad ): string {
	$uri  = get_theme_file_uri( $pfad );
	$voll = get_theme_file_path( $pfad );

	if ( ! is_readable( $voll ) ) {
		return $uri;
	}

	return add_query_arg( 'v', (string) filemtime( $voll ), $uri );
}
