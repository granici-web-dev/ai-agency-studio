<?php
/**
 * Title: Termin, Öffnungszeiten und Anfahrt
 * Slug: agentur-basis/kontakt-zeiten
 * Categories: agentur-seitenaufbau, agentur-friseur
 * Description: Schlussbühne. Die Telefonnummer ist die Schrift — darunter der Satz, darunter Zeiten und Anfahrt.
 * Keywords: kontakt, termin, öffnungszeiten, anfahrt, adresse, anrufen
 * Viewport Width: 1400
 *
 * ── Umbau am 2026-08-17, auf Ablehnung des Founders ────────────────────
 *
 * Der Abschnitt war eine Überschrift, ein verrutschter Satz, ein kleines
 * gelbes Feld und darunter drei Spalten. Vier Befunde, alle im Browser
 * nachgemessen, und alle vier hingen an derselben Ursache — der Abschnitt
 * wusste nicht, was er will:
 *
 *  1. **Die Handlung war das kleinste Element.** Die Überschrift „Termin?
 *     Rufen Sie einfach an." lief auf 68 px, die Nummer, um die es geht,
 *     auf 36 px in einem Feld von 190 px Breite. Der Satz über dem Knopf
 *     war größer als der Knopf. Jetzt ist **die Nummer die Schrift**: sie
 *     läuft über die volle Breite und ist das größte Element der Seite.
 *     Die Aufforderung steht als kleine Zeile darüber.
 *
 *  2. **Der Satz stand mittig im Nichts.** `.vorspann` hatte eine
 *     max-width, und die constrained-Layoutregel zentriert jedes direkte
 *     Kind mit max-width per `margin-inline: auto !important`. Vierter
 *     Fall derselben Falle (Pflichthinweis, Bühnenbild, Bühnentitel).
 *     Deshalb liegt der ganze Inhalt jetzt in einer Gruppe im normalen
 *     Fluss; dort greift die Regel nicht.
 *
 *  3. **Die rechte Hälfte war leer.** Nichts lief über die halbe Breite
 *     hinaus, also stand rechts von jeder Zeile totes Feld.
 *
 *  4. **Zeiten und Adresse standen zweimal untereinander.** Drei Spalten
 *     hier, und keine 100 px darunter dieselben Angaben noch einmal im
 *     Fuß. Die dritte Spalte „Ohne Termin — Sie sind ebenso willkommen"
 *     war dabei Füllmaterial in Spaltenform. Der Hinweis ist jetzt ein
 *     Halbsatz im Fließtext, und aus drei Spalten sind zwei geworden:
 *     genau die beiden, auf die das Menü zeigt.
 *
 * ── Was hier nicht wegdarf ─────────────────────────────────────────────
 *
 * `#termin` ist das Ziel **jedes** Knopfes der Seite (Kopfzeile, Auftakt,
 * Leistung, Mannschaft). `#zeiten` und `#kontakt` sind die Ziele zweier
 * Menüpunkte. Wer hier umbaut, lässt diese drei Anker stehen, sonst
 * laufen sechs Verweise ins Leere.
 *
 * Keine eingebettete Karte. Ein Google-Maps-iframe lädt beim Seitenaufruf
 * von einem fremden Server, setzt Cookies und braucht deshalb eine
 * Einwilligung vor dem Laden — für eine Wegbeschreibung ein absurder
 * Preis (§2.1). Der Verweis öffnet die Karten-App erst auf Klick.
 *
 * Kein Bild dahinter. Auftakt und Leistung tragen die Bilder; eine dritte
 * Bildbühne wäre die dritte Wiederholung. Und kein gelber Grund: die
 * Vorlage hält Gelb für die Handlung frei, und die Handlung ist hier die
 * Nummer. Wenn die ganze Fläche gelb ist, zeichnet Gelb nichts mehr aus.
 *
 * @package agentur-basis
 */

// An einer Stelle gepflegt, damit Anzeige und Wählverweis nicht auseinander
// laufen — der Fehler fällt erst auf, wenn ein Kunde vergeblich anruft.
// Quelle ist das Betriebsmodul; ohne es greifen die Rückfallwerte.
$angabe = static function ( string $feld, string $rueckfall ): string {
	return function_exists( 'agentur_angabe' ) ? agentur_angabe( $feld, $rueckfall ) : $rueckfall;
};

$ueberschrift    = $angabe( 'termin_ueberschrift', 'Termin vereinbaren' );
$vorspann        = $angabe(
	'termin_vorspann',
	'Rufen Sie einfach an und sagen Sie, bei wem Sie möchten — oder lassen Sie sich '
	. 'einteilen. Ohne Termin sind Sie ebenso willkommen; dann kann es aber Wartezeit geben.'
);
$telefon_anzeige = $angabe( 'telefon', '0178 5184291' );
$telefon_wahl    = $angabe( 'telefon_wahl', '+491785184291' );
$strasse         = $angabe( 'strasse', 'Frankfurter Str. 11' );
$ort             = $angabe( 'plz_ort', '53840 Troisdorf' );
$zeiten_tage     = $angabe( 'zeiten_tage', 'Montag bis Samstag' );
$zeiten_uhrzeit  = $angabe( 'zeiten_uhrzeit', '09:00 – 20:00 Uhr' );
$zeiten_zusatz   = $angabe( 'zeiten_zusatz', 'Sonntag geschlossen' );
$route           = 'https://www.openstreetmap.org/search?query=' . rawurlencode( "$strasse, $ort" );
?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","anchor":"termin","className":"auf-buehne schlussbuehne","backgroundColor":"buehne","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div id="termin" class="wp-block-group alignfull auf-buehne schlussbuehne has-buehne-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--60)">

	<?php
	/*
	 * Diese Gruppe liegt im normalen Fluss („default"), nicht in constrained.
	 * Damit dürfen ihre Kinder eine max-width tragen, ohne zentriert zu
	 * werden — siehe Befund 2 im Kopf dieser Datei. Wer sie entfernt,
	 * schiebt den Satz wieder in die Mitte.
	 *
	 * PHP-Kommentar, nicht HTML: ein <!-- --> in der Blockmarkierung lässt
	 * die Blockprüfung im Editor fehlschlagen.
	 */
	?>
	<!-- wp:group {"className":"handlung","layout":{"type":"default"}} -->
	<div class="wp-block-group handlung">

		<!-- wp:heading {"level":2,"className":"etikett"} -->
		<h2 class="wp-block-heading etikett"><?php echo esc_html( $ueberschrift ); ?></h2>
		<!-- /wp:heading -->

		<?php
		/*
		 * Die Nummer als Verweis, nicht als Schmuck: auf dem Telefon wählt
		 * ein Antippen, am Rechner öffnet es die Telefonie-App. Der Zusatz
		 * „anrufen" steht nur für Screenreader da, damit der Verweisname
		 * eine Handlung nennt und nicht bloß eine Ziffernfolge — den
		 * sichtbaren Text enthält er weiterhin (WCAG 2.5.3).
		 */
		?>
		<!-- wp:html -->
		<a class="ruf" href="tel:<?php echo esc_attr( $telefon_wahl ); ?>"><span class="ruf-nummer"><?php echo esc_html( $telefon_anzeige ); ?></span><span class="screen-reader-text"> anrufen</span></a>
		<!-- /wp:html -->

		<!-- wp:paragraph {"className":"ruf-satz","fontSize":"large"} -->
		<p class="ruf-satz has-large-font-size"><?php echo esc_html( $vorspann ); ?></p>
		<!-- /wp:paragraph -->

		<?php
		/*
		 * Zwei Felder, nicht drei — und beide tragen einen Anker, auf den
		 * das Menü zeigt. Was darüber hinaus zu Kontakt und Zeiten zu sagen
		 * ist, steht im Fuß direkt darunter; hier stünde es zum zweiten Mal.
		 */
		?>
		<!-- wp:group {"className":"handlung-fuss","layout":{"type":"default"}} -->
		<div class="wp-block-group handlung-fuss">

			<!-- wp:group {"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:heading {"level":3,"className":"etikett","anchor":"zeiten"} -->
				<h3 id="zeiten" class="wp-block-heading etikett">Öffnungszeiten</h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"className":"wert"} -->
				<p class="wert"><?php echo esc_html( $zeiten_tage ); ?><br><?php echo esc_html( $zeiten_uhrzeit ); ?></p>
				<!-- /wp:paragraph -->
				<?php if ( '' !== $zeiten_zusatz ) : ?>
				<!-- wp:paragraph {"className":"gedaempft"} -->
				<p class="gedaempft"><?php echo esc_html( $zeiten_zusatz ); ?></p>
				<!-- /wp:paragraph -->
				<?php endif; ?>
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:heading {"level":3,"className":"etikett","anchor":"kontakt"} -->
				<h3 id="kontakt" class="wp-block-heading etikett">Anfahrt</h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph {"className":"wert"} -->
				<p class="wert"><?php echo esc_html( $strasse ); ?><br><?php echo esc_html( $ort ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"className":"strich-verweis"} -->
				<p class="strich-verweis"><a href="<?php echo esc_url( $route ); ?>" rel="noopener nofollow" target="_blank">Route öffnen<span class="screen-reader-text"> (öffnet in einem neuen Fenster)</span></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
