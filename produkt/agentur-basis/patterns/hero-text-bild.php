<?php
/**
 * Title: Hero — Aussage links, Bild rechts
 * Slug: agentur-basis/hero-text-bild
 * Categories: agentur-seitenaufbau, agentur-friseur
 * Description: Erster Bildschirm mit einem Satz, einem Verweis und einem großen Bild. Kein Karussell.
 * Keywords: hero, start, kopf
 * Viewport Width: 1400
 *
 * Zwei bewusste Abweichungen von der Vorlage (cgbarbershop.com):
 *
 * 1. Kein Karussell. Ein Slider lädt mehrere große Bilder für den ersten
 *    Bildschirm und verschiebt den LCP — die Lighthouse-Zusage aus §6 ist
 *    schriftlich gegeben, ein Karussell kostet sie.
 * 2. Der Titel steht in der Schildschrift und in der größten Stufe. Bis
 *    2026-08-17 war er kleiner und leichter als die Abschnittsüberschriften
 *    darunter — eine umgedrehte Hierarchie, die die Seite als Rauschen
 *    lesbar machte. Wer den Seitentitel setzt, setzt die größte Stufe.
 *
 * @package agentur-basis
 */

$verzeichnis = get_template_directory_uri() . '/assets/img/demo';

/*
 * Betriebsangaben kommen aus dem Betriebsmodul, damit Telefonnummer, Zeiten
 * und Adresse an allen vier Stellen dieselben sind. Vorher stand die Nummer
 * dreimal im Theme; wer eine änderte, hinterließ zwei falsche.
 *
 * Ohne das Modul greifen die Rückfallwerte — das Theme muss allein laufen.
 */
$angabe = static function ( string $feld, string $rueckfall ): string {
	return function_exists( 'agentur_angabe' ) ? agentur_angabe( $feld, $rueckfall ) : $rueckfall;
};

$ueberschrift    = $angabe( 'hero_ueberschrift', 'Für Damen, Herren und Kinder — mitten in Troisdorf' );
$zeiten_kurz     = $angabe( 'zeiten_kurz', 'Mo – Sa  09:00 – 20:00' );
$adresse_kurz    = $angabe( 'adresse_kurz', 'Frankfurter Str. 11, Troisdorf' );
$telefon_anzeige = $angabe( 'telefon', '0178 5184291' );
$telefon_wahl    = $angabe( 'telefon_wahl', '+491785184291' );
$bild        = esc_url( "$verzeichnis/interieur.webp" );

/*
 * Das Hero-Bild ist der LCP der Startseite. Ohne srcset lud auch ein Telefon
 * die 1600er Fassung — messbar: LCP 2,3 s statt 1,5 s, Lighthouse 98 statt 100.
 *
 * SRCSET, SIZES UND FETCHPRIORITY STEHEN NICHT MEHR HIER, sondern werden beim
 * Rendern ergänzt (functions.php, agentur_bild_attribute). Der Grund ist keine
 * Ordnungsliebe: von Hand ergänzte Attribute weichen von dem ab, was der
 * Bildblock beim Speichern erzeugt, und die Blockprüfung im Editor erklärt den
 * Block dann für ungültig. Genau so lag es hier.
 *
 * Zur Rendezeit ist es außerdem der Weg, den WordPress selbst geht: für Bilder
 * aus der Mediathek ergänzt `wp_filter_content_tags()` dieselben Attribute.
 * Diese Bilder liegen im Theme und nicht in der Mediathek, also macht es das
 * Theme.
 */
?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns are-vertically-aligned-center">

		<!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%">

			<!-- wp:heading {"level":1,"fontSize":"xxx-large"} -->
			<h1 class="wp-block-heading has-xxx-large-font-size"><?php echo esc_html( $ueberschrift ); ?></h1>
			<!-- /wp:heading -->

			<?php
			/*
			 * Die drei Angaben, wegen derer jemand sucht, stehen über dem Falz.
			 * Vorher erschien die Telefonnummer erstmals bei 77 % der Scrolltiefe —
			 * bei einer Seite, deren eigener Brief die Szene an der Bushaltestelle
			 * beschreibt, war das der teuerste Fehler der Seite.
			 *
			 * PHP-Kommentar, nicht HTML: ein <!-- --> innerhalb der Blockmarkierungen
			 * steht nicht in dem, was der Block beim Speichern erzeugt. Die Blockprüfung
			 * im Editor schlägt dann fehl, und der Block gilt als „ungültiger Inhalt".
			 */
			?>
			<!-- wp:group {"className":"statusleiste","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
			<div class="wp-block-group statusleiste" style="margin-top:var(--wp--preset--spacing--40)">
				<!-- wp:paragraph -->
				<p><?php echo esc_html( str_replace( '  ', "\u{00A0}\u{00A0}", $zeiten_kurz ) ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph -->
				<p><?php echo esc_html( $adresse_kurz ); ?></p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph -->
				<p><a href="tel:<?php echo esc_attr( $telefon_wahl ); ?>"><?php echo esc_html( $telefon_anzeige ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:paragraph {"className":"strich-verweis","style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
			<p class="strich-verweis" style="margin-top:var(--wp--preset--spacing--30)"><a href="#leistungen">Preise ansehen</a></p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">

			<!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"basis-quer","className":"ist-zugeschnitten"} -->
			<figure class="wp-block-image size-basis-quer ist-zugeschnitten"><img src="<?php echo $bild; ?>" alt="Blick in den Salon: Bedienplätze vor den Spiegeln, dahinter die Waschplätze" style="aspect-ratio:4/3;object-fit:cover"/></figure>
			<!-- /wp:image -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
