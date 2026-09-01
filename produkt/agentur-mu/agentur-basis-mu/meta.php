<?php
/**
 * Meta-Description und Open Graph — ohne SEO-Plugin.
 *
 * ── Warum kein Yoast, Rank Math oder Vergleichbares ─────────────────────
 *
 * Für eine Seite mit fünf Unterseiten holt man sich mit einem SEO-Plugin
 * mehrere Megabyte Code, ein Dashboard voller Werbung für die Bezahlfassung,
 * regelmäßige Sicherheitslücken und bei einigen Anbietern eine Anbindung an
 * fremde Server. Gebraucht werden davon vier Zeilen im <head>. WordPress
 * setzt Titel und Canonical selbst; es fehlt genau die Beschreibung und
 * Open Graph. Das ist dieses Modul.
 *
 * ── Was hier entschieden ist ────────────────────────────────────────────
 *
 * KEINE ERFUNDENE BESCHREIBUNG. Wenn keine gepflegt ist und sich auch keine
 * aus dem Inhalt gewinnen lässt, wird nichts ausgegeben. Eine automatisch
 * zusammengestückelte Beschreibung ist schlechter als keine: Google
 * verwendet sie ohnehin nicht, aber sie steht in der Vorschau, wenn jemand
 * die Seite teilt.
 *
 * DIE STARTSEITE BRAUCHT HANDARBEIT. Ihr Inhalt besteht in einem Blocktheme
 * nur aus Musterverweisen (`<!-- wp:pattern … /-->`); daraus lässt sich
 * kein Satz gewinnen. Deshalb steht sie in einer eigenen Option.
 *
 * OPEN GRAPH TEILT SICH DIE BESCHREIBUNG. §6 der CLAUDE.md verlangt OG-Tags.
 * Sie getrennt zu pflegen hieße, denselben Satz zweimal zu schreiben und
 * beim zweiten Mal zu vergessen.
 *
 * OG-BILD ALS JPEG, NICHT WEBP. Der Crawler von Facebook liest kein WebP.
 * Das Bild wird nie im Seitenaufruf geladen, nur von Crawlern — sein
 * Gewicht zählt für Lighthouse nicht.
 *
 * KEIN TWITTER-EIGENES BILD-TAG. `twitter:card` genügt; Titel, Beschreibung
 * und Bild zieht X aus den OG-Angaben.
 *
 * @package agentur-basis-mu
 */

defined( 'ABSPATH' ) || exit;

const AGENTUR_META_SCHLUESSEL = '_agentur_beschreibung';
const AGENTUR_META_START      = 'agentur_meta_start';

/**
 * Google schneidet die Beschreibung je nach Gerät zwischen etwa 120 und 160
 * Zeichen ab. Was danach steht, liest niemand — deshalb gehört die Aussage
 * nach vorn und die Höflichkeit ans Ende.
 */
const AGENTUR_META_LAENGE_MAX = 158;
const AGENTUR_META_LAENGE_MIN = 70;


/* ── Feld am Beitrag ─────────────────────────────────────────────────── */

add_action( 'init', 'agentur_meta_feld_anmelden' );

/**
 * Meldet das Feld für alle öffentlichen Inhaltstypen an.
 *
 * `show_in_rest` ist die Bedingung dafür, dass der Blockeditor es überhaupt
 * sehen kann — ohne diese Zeile speichert das Seitenpanel ins Leere, ohne
 * eine Fehlermeldung zu zeigen.
 */
function agentur_meta_feld_anmelden(): void {
	foreach ( get_post_types( array( 'public' => true ), 'names' ) as $typ ) {
		register_post_meta(
			$typ,
			AGENTUR_META_SCHLUESSEL,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'sanitize_callback' => 'agentur_meta_saeubern',
				'auth_callback'     => static fn( $erlaubt, $feld, $beitrag ) => current_user_can( 'edit_post', $beitrag ),
			)
		);
	}
}

/**
 * Räumt einen eingegebenen Text auf.
 *
 * Zeilenumbrüche und Anführungszeichen zerlegen sonst das Attribut im
 * <head>. Gekürzt wird hier **nicht** — wer 300 Zeichen eintippt, soll das
 * in der Prüfung sehen und selbst kürzen, statt es stillschweigend
 * abgeschnitten zu bekommen.
 */
function agentur_meta_saeubern( $wert ): string {
	$wert = is_string( $wert ) ? $wert : '';
	$wert = wp_strip_all_tags( $wert );
	$wert = preg_replace( '/\s+/u', ' ', $wert );

	return trim( (string) $wert );
}


/* ── Ermitteln ───────────────────────────────────────────────────────── */

/**
 * Die Beschreibung für die gerade angezeigte Seite.
 *
 * Reihenfolge, von Hand nach automatisch:
 *
 *  1. das gepflegte Feld am Beitrag
 *  2. bei der Startseite die eigene Option
 *  3. der Textauszug
 *  4. aus dem Inhalt gewonnen — nur wenn dabei ganze Sätze herauskommen
 *  5. nichts
 *
 * Der Untertitel der Website steht bewusst **nicht** in der Kette. Er ist
 * drei Wörter lang und wiederholt bloß den Titel; als Beschreibung ist er
 * eine verschenkte Zeile im Suchergebnis.
 */
function agentur_meta_beschreibung(): string {
	$text = agentur_meta_beschreibung_roh();

	// Platzhalter wie {telefon} zuerst auflösen, dann kürzen — sonst zählt
	// die Kürzung den Platzhalter statt der Nummer und schneidet an der
	// falschen Stelle. Ohne die Auflösung wäre die Beschreibung die eine
	// Stelle, an der die alte Telefonnummer stehen bleibt, nachdem der
	// Inhaber sie unter „Ihre Angaben" geändert hat.
	if ( function_exists( 'agentur_platzhalter' ) ) {
		$text = agentur_platzhalter( $text );
	}

	// Beim Ausgeben wird gekürzt, beim Speichern nicht. Der Unterschied ist
	// gewollt: im Editor soll der volle Text stehen bleiben und der Zähler
	// warnen, damit ein Mensch ihn kürzt. Im <head> hat ein 300-Zeichen-Satz
	// nichts verloren — Google schneidet ihn ohnehin ab.
	return agentur_meta_kuerzen( $text );
}

/**
 * Die ungekürzte Beschreibung. Siehe agentur_meta_beschreibung().
 */
function agentur_meta_beschreibung_roh(): string {
	if ( is_front_page() ) {
		$start = agentur_meta_saeubern( get_option( AGENTUR_META_START, '' ) );

		// Ein gepflegtes Feld an der Startseite schlägt die Option — sonst
		// ließe sich die Startseite als Einzige nicht im Editor pflegen.
		$eigen = agentur_meta_am_beitrag();

		return '' !== $eigen ? $eigen : $start;
	}

	if ( is_singular() ) {
		$eigen = agentur_meta_am_beitrag();

		if ( '' !== $eigen ) {
			return $eigen;
		}

		return agentur_meta_aus_inhalt( get_post() );
	}

	// Archive, Suche, 404: keine Beschreibung. Sie gehören ohnehin nicht in
	// den Index, und ein erfundener Satz hilft dort niemandem.
	return '';
}

/**
 * Das von Hand gepflegte Feld, falls gesetzt.
 */
function agentur_meta_am_beitrag( ?int $id = null ): string {
	$id = $id ?? get_queried_object_id();

	if ( ! $id ) {
		return '';
	}

	return agentur_meta_saeubern( (string) get_post_meta( $id, AGENTUR_META_SCHLUESSEL, true ) );
}

/**
 * Versucht, aus Auszug oder Inhalt einen brauchbaren Satz zu gewinnen.
 *
 * „Brauchbar" ist streng gemeint. Ein Blocktheme-Inhalt besteht oft nur aus
 * Musterverweisen; was dann übrig bleibt, sind Bruchstücke. Deshalb die
 * Mindestlänge: lieber gar keine Beschreibung als ein halber Satz.
 */
function agentur_meta_aus_inhalt( ?WP_Post $beitrag ): string {
	if ( ! $beitrag instanceof WP_Post ) {
		return '';
	}

	$auszug = agentur_meta_saeubern( (string) $beitrag->post_excerpt );

	if ( '' !== $auszug ) {
		return agentur_meta_kuerzen( $auszug );
	}

	// Musterverweise entfernen, bevor der Rest gerendert wird — sonst steht
	// die halbe Startseite im Attribut.
	$inhalt = preg_replace( '/<!--\s*wp:pattern.*?-->/s', '', $beitrag->post_content );
	$inhalt = excerpt_remove_blocks( (string) $inhalt );
	$inhalt = agentur_meta_saeubern( wp_strip_all_tags( (string) $inhalt ) );

	if ( mb_strlen( $inhalt ) < AGENTUR_META_LAENGE_MIN ) {
		return '';
	}

	return agentur_meta_kuerzen( $inhalt );
}

/**
 * Kürzt auf die Höchstlänge, aber an einer Wortgrenze.
 *
 * Mitten im Wort abzuschneiden sieht im Suchergebnis nach kaputter Software
 * aus — und genau das ist der erste Eindruck, den die Seite verkaufen soll.
 */
function agentur_meta_kuerzen( string $text, int $max = AGENTUR_META_LAENGE_MAX ): string {
	if ( mb_strlen( $text ) <= $max ) {
		return $text;
	}

	$schnitt = mb_substr( $text, 0, $max );
	$luecke  = mb_strrpos( $schnitt, ' ' );

	if ( false !== $luecke && $luecke > $max * 0.6 ) {
		$schnitt = mb_substr( $schnitt, 0, $luecke );
	}

	return rtrim( $schnitt, " \t\n\r\0\x0B,;:–-" ) . '…';
}


/* ── Ausgabe ─────────────────────────────────────────────────────────── */

// Priorität 2, damit die Angaben oben im <head> stehen und ein Crawler sie
// findet, der nur die ersten Kilobyte liest.
add_action( 'wp_head', 'agentur_meta_ausgeben', 2 );

/**
 * Schreibt Beschreibung und Open Graph in den <head>.
 *
 * Kein einziger externer Aufruf, kein Skript, kein Stylesheet — nur Text.
 */
function agentur_meta_ausgeben(): void {
	if ( ! is_singular() && ! is_front_page() && ! is_home() ) {
		return;
	}

	$beschreibung = agentur_meta_beschreibung();
	$titel        = agentur_meta_titel();

	if ( '' !== $beschreibung ) {
		printf(
			'<meta name="description" content="%s" />' . "\n",
			esc_attr( $beschreibung )
		);
	}

	printf(
		'<meta property="og:type" content="%s" />' . "\n",
		esc_attr( is_singular( 'post' ) ? 'article' : 'website' )
	);
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $titel ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( agentur_meta_adresse() ) );
	// get_locale(), nicht get_bloginfo('language'). Letzteres liefert den
	// Wert für das lang-Attribut — hier schlicht „de". Open Graph erwartet
	// Sprache_REGION; bei einem ungültigen Wert fällt Facebook kommentarlos
	// auf en_US zurück, und die geteilte Seite sieht englisch aus.
	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( get_locale() ) );

	if ( '' !== $beschreibung ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $beschreibung ) );
	}

	$bild = agentur_meta_bild();

	if ( ! empty( $bild['url'] ) ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $bild['url'] ) );

		if ( ! empty( $bild['breite'] ) && ! empty( $bild['hoehe'] ) ) {
			printf( '<meta property="og:image:width" content="%d" />' . "\n", (int) $bild['breite'] );
			printf( '<meta property="og:image:height" content="%d" />' . "\n", (int) $bild['hoehe'] );
		}

		if ( ! empty( $bild['text'] ) ) {
			printf( '<meta property="og:image:alt" content="%s" />' . "\n", esc_attr( $bild['text'] ) );
		}

		// Ohne Bild wäre eine große Karte eine große leere Fläche.
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	}
}

/**
 * Der Titel für die Vorschau beim Teilen.
 *
 * `wp_get_document_title()` liefert den Titel samt Websitenamen — beim
 * Teilen steht der Name schon in `og:site_name`, doppelt gemoppelt liest
 * sich schlecht. Deshalb auf der Startseite Name plus Untertitel, sonst
 * nur die Überschrift der Seite.
 */
function agentur_meta_titel(): string {
	if ( is_front_page() ) {
		$untertitel = trim( (string) get_bloginfo( 'description' ) );

		return '' !== $untertitel
			? get_bloginfo( 'name' ) . ' — ' . $untertitel
			: get_bloginfo( 'name' );
	}

	if ( is_singular() ) {
		return wp_strip_all_tags( get_the_title( get_queried_object_id() ) );
	}

	return get_bloginfo( 'name' );
}

/**
 * Die kanonische Adresse der aktuellen Seite.
 */
function agentur_meta_adresse(): string {
	if ( is_front_page() ) {
		return home_url( '/' );
	}

	$verweis = is_singular() ? get_permalink( get_queried_object_id() ) : '';

	return is_string( $verweis ) && '' !== $verweis ? $verweis : home_url( '/' );
}

/**
 * Das Vorschaubild.
 *
 *  1. Beitragsbild der Seite — das ist der Weg, den der Inhaber selbst geht
 *  2. Website-Logo aus den Einstellungen
 *  3. Rückfall aus dem Theme
 *
 * @return array{url:string,breite:int,hoehe:int,text:string}
 */
function agentur_meta_bild(): array {
	$leer = array( 'url' => '', 'breite' => 0, 'hoehe' => 0, 'text' => '' );

	$anhang = 0;

	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		$anhang = (int) get_post_thumbnail_id( get_queried_object_id() );
	} elseif ( get_theme_mod( 'custom_logo' ) ) {
		$anhang = (int) get_theme_mod( 'custom_logo' );
	}

	if ( $anhang ) {
		$daten = wp_get_attachment_image_src( $anhang, 'full' );

		if ( is_array( $daten ) && ! empty( $daten[0] ) ) {
			return array(
				'url'    => (string) $daten[0],
				'breite' => (int) ( $daten[1] ?? 0 ),
				'hoehe'  => (int) ( $daten[2] ?? 0 ),
				'text'   => (string) get_post_meta( $anhang, '_wp_attachment_image_alt', true ),
			);
		}
	}

	/**
	 * Rückfallbild, wenn weder Beitragsbild noch Logo gesetzt sind.
	 *
	 * Als JPEG, weil der Crawler von Facebook kein WebP liest. 1200×630 ist
	 * das Verhältnis, das alle Netze ohne Beschnitt anzeigen.
	 *
	 * @param array $bild Pfad, Maße und Alternativtext.
	 */
	$rueckfall = apply_filters(
		'agentur_meta_rueckfallbild',
		array(
			'datei'  => 'assets/img/demo/og-standard.jpg',
			'breite' => 1200,
			'hoehe'  => 630,
			'text'   => '',
		)
	);

	$pfad = get_theme_file_path( (string) $rueckfall['datei'] );

	if ( ! is_readable( $pfad ) ) {
		return $leer;
	}

	return array(
		'url'    => get_theme_file_uri( (string) $rueckfall['datei'] ),
		'breite' => (int) $rueckfall['breite'],
		'hoehe'  => (int) $rueckfall['hoehe'],
		'text'   => (string) $rueckfall['text'],
	);
}


/* ── Feld im Blockeditor ─────────────────────────────────────────────── */

add_action( 'enqueue_block_editor_assets', 'agentur_meta_editor_laden' );

/**
 * Lädt das Panel für die Seitenleiste.
 *
 * Bewusst reines JavaScript ohne Bauschritt. Ein Build-Prozess für ein
 * einzelnes Textfeld hieße: node_modules im Repository, eine Kette von
 * Abhängigkeiten zu pflegen und ein Artefakt, das niemand mehr bauen kann,
 * wenn in zwei Jahren jemand eine Zeile ändern will.
 */
function agentur_meta_editor_laden(): void {
	$datei = AGENTUR_MU_PFAD . '/editor/meta-panel.js';

	if ( ! is_readable( $datei ) ) {
		return;
	}

	wp_enqueue_script(
		'agentur-meta-panel',
		// WPMU_PLUGIN_URL statt plugins_url(): für mu-plugins ist das der
		// direkte Weg, plugins_url() braucht dafür einen Umweg über eine
		// Datei, die gar nicht die eigene ist.
		WPMU_PLUGIN_URL . '/agentur-basis-mu/editor/meta-panel.js',
		array( 'wp-plugins', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n' ),
		(string) filemtime( $datei ),
		true
	);

	wp_add_inline_script(
		'agentur-meta-panel',
		sprintf(
			'window.agenturMeta = %s;',
			wp_json_encode(
				array(
					'schluessel' => AGENTUR_META_SCHLUESSEL,
					'max'        => AGENTUR_META_LAENGE_MAX,
					'min'        => AGENTUR_META_LAENGE_MIN,
					// Damit der Zähler die aufgelöste Länge zeigt und nicht die
					// des Platzhalters. „{telefon}" sind 9 Zeichen, die Nummer
					// zwölf — ein Zähler, der auf Grün steht und trotzdem
					// abgeschnitten wird, ist schlimmer als keiner.
					'werte'      => agentur_meta_platzhalterwerte(),
				)
			)
		),
		'before'
	);
}


/**
 * Die auflösbaren Platzhalter mit ihren aktuellen Werten.
 *
 * Leer, wenn das Angaben-Modul fehlt — dann kennt der Editor keine
 * Platzhalter, und der Zähler zählt schlicht die Zeichen.
 */
function agentur_meta_platzhalterwerte(): array {
	if ( ! function_exists( 'agentur_platzhalter_liste' ) ) {
		return array();
	}

	$werte = array();

	foreach ( agentur_platzhalter_liste() as $feld ) {
		$werte[ $feld ] = agentur_angabe( $feld );
	}

	return $werte;
}


/* ── Kommandozeile ───────────────────────────────────────────────────── */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Meta-Beschreibungen setzen und prüfen.
	 */
	class Agentur_Meta_Befehle {

		/**
		 * Setzt die Beschreibung der Startseite.
		 *
		 * ## OPTIONEN
		 *
		 * <text>
		 * : Der Satz. Höchstens 158 Zeichen, sonst schneidet Google ab.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-meta start "Friseur in Troisdorf für Damen, Herren und Kinder."
		 */
		public function start( array $args ): void {
			$text = agentur_meta_saeubern( $args[0] ?? '' );

			if ( '' === $text ) {
				WP_CLI::error( 'Kein Text angegeben.' );
			}

			update_option( AGENTUR_META_START, $text, true );

			$laenge = mb_strlen( $text );
			WP_CLI::success( sprintf( 'Gesetzt, %d Zeichen.', $laenge ) );

			if ( $laenge > AGENTUR_META_LAENGE_MAX ) {
				WP_CLI::warning( sprintf( 'Google schneidet ab etwa %d Zeichen ab.', AGENTUR_META_LAENGE_MAX ) );
			}
		}

		/**
		 * Setzt die Beschreibung einer einzelnen Seite.
		 *
		 * ## OPTIONEN
		 *
		 * <id>
		 * : Beitrags- oder Seiten-ID.
		 *
		 * <text>
		 * : Der Satz.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-meta seite 5 "Impressum von Friseur Sunshine …"
		 */
		public function seite( array $args ): void {
			$id   = absint( $args[0] ?? 0 );
			$text = agentur_meta_saeubern( $args[1] ?? '' );

			if ( ! $id || ! get_post( $id ) ) {
				WP_CLI::error( 'Keine Seite mit dieser ID.' );
			}

			update_post_meta( $id, AGENTUR_META_SCHLUESSEL, $text );
			WP_CLI::success( sprintf( '„%s": %d Zeichen.', get_the_title( $id ), mb_strlen( $text ) ) );
		}

		/**
		 * Listet alle öffentlichen Seiten mit ihrer Beschreibung und den Befunden.
		 *
		 * Der Befehl, der vor der Auslieferung läuft. Er findet die Seite, die
		 * jemand angelegt und dabei die Beschreibung vergessen hat — das ist
		 * der Normalfall, nicht die Ausnahme.
		 *
		 * ## BEISPIELE
		 *
		 *     wp agentur-meta pruefen
		 */
		public function pruefen(): void {
			$seiten = get_posts(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order title',
					'order'          => 'ASC',
				)
			);

			$zeilen  = array();
			$mangel  = 0;
			$startid = (int) get_option( 'page_on_front' );

			foreach ( $seiten as $seite ) {
				$text = agentur_meta_am_beitrag( $seite->ID );
				$quelle = 'Feld';

				if ( '' === $text && $seite->ID === $startid ) {
					$text   = agentur_meta_saeubern( get_option( AGENTUR_META_START, '' ) );
					$quelle = 'Option';
				}

				if ( '' === $text ) {
					$text   = agentur_meta_aus_inhalt( $seite );
					$quelle = '' !== $text ? 'Inhalt' : '—';
				}

				// Gezählt wird die aufgelöste Länge — sonst steht „122 ok" für
				// einen Satz, der mit eingesetzter Telefonnummer 148 Zeichen
				// hat und abgeschnitten wird.
				if ( function_exists( 'agentur_platzhalter' ) ) {
					$text = agentur_platzhalter( $text );
				}

				$laenge = mb_strlen( $text );

				$befund = 'ok';

				if ( '' === $text ) {
					$befund = 'FEHLT';
					++$mangel;
				} elseif ( $laenge > AGENTUR_META_LAENGE_MAX ) {
					$befund = 'zu lang';
					++$mangel;
				} elseif ( $laenge < AGENTUR_META_LAENGE_MIN ) {
					$befund = 'sehr kurz';
					++$mangel;
				}

				$zeilen[] = array(
					'ID'      => $seite->ID,
					'Seite'   => get_the_title( $seite ),
					'Quelle'  => $quelle,
					'Zeichen' => $laenge,
					'Befund'  => $befund,
					'Text'    => mb_substr( $text, 0, 60 ),
				);
			}

			WP_CLI\Utils\format_items( 'table', $zeilen, array( 'ID', 'Seite', 'Quelle', 'Zeichen', 'Befund', 'Text' ) );

			if ( $mangel > 0 ) {
				WP_CLI::warning( sprintf( '%d Seite(n) mit Befund. Vor der Auslieferung beheben (CLAUDE.md §6).', $mangel ) );

				return;
			}

			WP_CLI::success( 'Alle Seiten haben eine brauchbare Beschreibung.' );
		}
	}

	WP_CLI::add_command( 'agentur-meta', 'Agentur_Meta_Befehle' );
}
