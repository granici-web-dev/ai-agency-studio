<?php
/**
 * Title: Preisliste
 * Slug: agentur-basis/preisliste
 * Categories: agentur-seitenaufbau, agentur-friseur
 * Description: Band mit Bezeichnung, darunter die Preise über die volle Breite auf ruhigem Grund.
 * Keywords: preise, leistungen, liste, band
 * Viewport Width: 1400
 *
 * Nach dem Umbau auf Bühnen und Bänder (2026-08-17) liegt hier die ruhige
 * Fläche zwischen zwei Bühnen. Vorher war das eine Zweispaltung mit
 * Einleitungstext links und Liste rechts — derselbe Takt wie in vier
 * anderen Abschnitten.
 *
 * Jetzt: ein Band benennt den Abschnitt, darunter läuft die Liste über die
 * volle Breite. Die Einleitung steht als ein Satz im Band und nicht mehr
 * als halbe Spalte, die neben der Liste Leerraum erzeugt.
 *
 * Eine Liste statt drei Kacheln: dieselbe Fläche zeigt zwölf Positionen
 * statt drei, und wer einen Friseur sucht, sucht die Preisliste.
 *
 * PANGV: Endpreise inklusive Umsatzsteuer, deshalb der Hinweis unter der
 * Liste. „ab"-Preise sind zulässig, solange erkennbar ist, wovon der
 * Endpreis abhängt — deswegen steht das dort und nicht im Kleingedruckten.
 *
 * ACHTUNG VOR DEM LIVEGANG: Die Preise sind Platzhalter und müssen vom
 * Inhaber bestätigt werden. Eine falsche Preisangabe ist teurer als jeder
 * Gestaltungsfehler auf dieser Seite.
 *
 * @package agentur-basis
 */

if ( function_exists( 'agentur_preise_nach_gruppen' ) ) {
	$gruppen = agentur_preise_nach_gruppen();
} else {
	$gruppen = array(
		'Damen'  => array(
			array( 'leistung' => 'Waschen, Schneiden, Föhnen', 'preis' => 'ab 39 €' ),
			array( 'leistung' => 'Ansatzfarbe', 'preis' => 'ab 45 €' ),
			array( 'leistung' => 'Strähnen in Folientechnik', 'preis' => 'ab 65 €' ),
			array( 'leistung' => 'Hochsteckfrisur', 'preis' => 'ab 55 €' ),
		),
		'Herren' => array(
			array( 'leistung' => 'Waschen, Schneiden, Styling', 'preis' => 'ab 24 €' ),
			array( 'leistung' => 'Maschinenschnitt', 'preis' => '16 €' ),
			array( 'leistung' => 'Bart schneiden und formen', 'preis' => 'ab 14 €' ),
		),
		'Kinder' => array(
			array( 'leistung' => 'bis 6 Jahre', 'preis' => '12 €' ),
			array( 'leistung' => '7 bis 12 Jahre', 'preis' => '16 €' ),
		),
	);
}

$vorspann = function_exists( 'agentur_angabe' )
	? agentur_angabe( 'preise_vorspann' )
	: 'Was am Ende zu zahlen ist, sagen wir vor dem ersten Schnitt — nicht danach.';

// Ohne Preise keine Preisliste. Eine Überschrift über einer leeren Fläche
// sieht nach einer kaputten Seite aus.
if ( empty( $gruppen ) ) {
	return;
}
?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","anchor":"leistungen","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div id="leistungen" class="wp-block-group alignfull" style="padding-bottom:var(--wp--preset--spacing--70)">

	<!-- wp:group {"className":"band","layout":{"type":"default"}} -->
	<div class="wp-block-group band">
		<!-- wp:heading {"level":2,"className":"band-name"} -->
		<h2 class="wp-block-heading band-name">Preise</h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"band-satz"} -->
		<p class="band-satz"><?php echo esc_html( $vorspann ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"preistafel","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"default"}} -->
	<div class="wp-block-group preistafel" style="margin-top:var(--wp--preset--spacing--60)">

		<?php foreach ( $gruppen as $gruppe => $positionen ) : ?>

			<?php if ( '' !== (string) $gruppe ) : ?>
		<!-- wp:heading {"level":3,"className":"preisgruppe"} -->
		<h3 class="wp-block-heading preisgruppe"><?php echo esc_html( $gruppe ); ?></h3>
		<!-- /wp:heading -->
			<?php endif; ?>

			<?php foreach ( $positionen as $zeile ) : ?>
		<!-- wp:group {"className":"preiszeile","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
		<div class="wp-block-group preiszeile">
			<!-- wp:paragraph -->
			<p><?php echo esc_html( $zeile['leistung'] ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"preis"} -->
			<p class="preis"><?php echo esc_html( $zeile['preis'] ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
			<?php endforeach; ?>

		<?php endforeach; ?>

	</div>
	<!-- /wp:group -->

	<!-- wp:paragraph {"className":"gedaempft","fontSize":"small","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
	<p class="gedaempft has-small-font-size" style="margin-top:var(--wp--preset--spacing--50)">Alle Preise inklusive Mehrwertsteuer. Bei „ab“-Preisen richtet sich der Endpreis nach Haarlänge und Aufwand; wir nennen ihn vorher.</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
