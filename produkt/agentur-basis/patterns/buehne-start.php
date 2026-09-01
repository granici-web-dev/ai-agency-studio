<?php
/**
 * Title: Bühne — Auftakt
 * Slug: agentur-basis/buehne-start
 * Categories: agentur-seitenaufbau, agentur-friseur
 * Description: Bildbühne über die volle Höhe, eine Aussage, ein Knopf. Darunter die drei Angaben, wegen derer jemand sucht.
 * Keywords: bühne, hero, auftakt, start
 * Viewport Width: 1400
 *
 * Der Auftakt nach lamborghini.com: ein Bild über die volle Höhe, eine
 * Aussage in Versalien, ein Knopf. Kein Text-neben-Bild mehr — das war
 * derselbe Takt wie in allen anderen Abschnitten und deshalb kein Auftakt.
 *
 * EINE ABWEICHUNG VON DER VORLAGE, und zwar mit Absicht:
 *
 *   Lamborghini schickt den Preis auf den dritten Bildschirm. Ein
 *   Sportwagen wird nicht nach dem Preis gesucht, ein Friseur schon.
 *   Deshalb steht die Statusleiste — Zeiten, Adresse, Nummer — hier unten
 *   auf der Bühne und nicht erst weiter unten auf der Seite. Wer die
 *   Bühne übernimmt und diese Zeile wegnimmt, nimmt der Seite ihren Zweck.
 *
 * DER SCHLEIER IST KEINE DEKORATION. Ohne ihn hängt die Lesbarkeit am
 * Foto, und beim nächsten Bild des Inhabers steht helle Schrift auf einer
 * hellen Wand. Er liegt in style.css auf `.buehne::after`, nicht hier —
 * sonst müsste jede neue Bühne daran denken.
 *
 * @package agentur-basis
 */

$verzeichnis = get_template_directory_uri() . '/assets/img/demo';
$bild        = esc_url( "$verzeichnis/interieur.webp" );

$angabe = static function ( string $feld, string $rueckfall ): string {
	return function_exists( 'agentur_angabe' ) ? agentur_angabe( $feld, $rueckfall ) : $rueckfall;
};

$ueberschrift    = $angabe( 'hero_ueberschrift', 'Für Damen, Herren und Kinder — mitten in Troisdorf' );
$zeiten_kurz     = $angabe( 'zeiten_kurz', 'Mo – Sa  09:00 – 20:00' );
$adresse_kurz    = $angabe( 'adresse_kurz', 'Frankfurter Str. 11, Troisdorf' );
$telefon_anzeige = $angabe( 'telefon', '0178 5184291' );
$telefon_wahl    = $angabe( 'telefon_wahl', '+491785184291' );
?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","className":"buehne","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull buehne">

	<!-- wp:image {"className":"buehne-bild"} -->
	<figure class="wp-block-image buehne-bild"><img src="<?php echo $bild; ?>" alt="Blick in den Salon: Bedienplätze vor den Spiegeln, dahinter die Waschplätze"/></figure>
	<!-- /wp:image -->

	<!-- wp:heading {"level":1,"className":"buehne-titel","fontSize":"xxx-large"} -->
	<h1 class="wp-block-heading buehne-titel has-xxx-large-font-size"><?php echo esc_html( $ueberschrift ); ?></h1>
	<!-- /wp:heading -->

	<?php
	/*
	 * PHP-Kommentar, kein HTML: ein <!-- --> in der Blockmarkierung lässt
	 * die Blockprüfung im Editor scheitern (siehe README, „Blockgültigkeit").
	 */
	?>
	<!-- wp:group {"className":"statusleiste","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
	<div class="wp-block-group statusleiste" style="margin-top:var(--wp--preset--spacing--50)">
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

	<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--50)"><!-- wp:button -->
	<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#termin">Termin buchen</a></div>
	<!-- /wp:button --></div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
