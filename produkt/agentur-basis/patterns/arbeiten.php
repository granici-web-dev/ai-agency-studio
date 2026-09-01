<?php
/**
 * Title: Unsere Arbeiten — Galerie
 * Slug: agentur-basis/arbeiten
 * Categories: agentur-seitenaufbau, agentur-friseur
 * Description: Dunkle Bildwand mit den Ergebnissen. Ein Hauptstück, dazu kleinere Aufnahmen.
 * Keywords: arbeiten, galerie, ergebnisse, vorher nachher, bilder
 * Viewport Width: 1400
 *
 * Der Abschnitt, den ein Friseur eigentlich am nötigsten hat: was dabei
 * herauskommt. Bis 2026-08-17 gab es ihn nicht — die Seite erzählte Preise,
 * Leute und Meinungen, aber zeigte kein einziges Ergebnis.
 *
 * DUNKEL, und zwar aus zwei Gründen. Erstens geht damit der Wechsel über
 * die ganze Seite auf: Bühne, Fläche, Bühne, Fläche, Wand, Fläche, Bühne.
 * Zweitens werden Bilder auf Schwarz als Galerie gelesen und nicht als
 * Kachelreihe — dasselbe Verfahren wie an einer Ausstellungswand.
 *
 * EIN HAUPTSTÜCK. Vier gleich große Kacheln sind eine Kachelreihe und
 * behaupten, alle vier Aufnahmen seien gleich gut. Das erste Bild ist
 * deshalb doppelt so groß; die Reihenfolge im Muster ist die Rangfolge.
 *
 * ── Vor dem Livegang ────────────────────────────────────────────────────
 *
 * DAS SIND STIMMUNGSBILDER, KEINE ARBEITEN. Sie stammen aus derselben
 * Platzhaltersammlung wie die übrigen Bilder der Seite. Ein Friseursalon,
 * der unter „Unsere Arbeiten" Aufnahmen zeigt, die nicht seine sind, wirbt
 * mit fremder Leistung — das ist irreführend im Sinne des § 5 UWG, und es
 * fällt spätestens dem ersten Gast auf, der das Ergebnis wiedererkennen
 * will. Deshalb steht das Etikett sichtbar über der Wand.
 *
 * Sind Kundinnen oder Kunden erkennbar abgebildet, braucht es deren
 * schriftliche Einwilligung (§ 22 KUG) — bei Vorher-Nachher-Aufnahmen
 * ausdrücklich für diesen Zweck. Ein „ja" im Salon reicht nicht.
 *
 * @package agentur-basis
 */

$verzeichnis = get_template_directory_uri() . '/assets/img/demo';

/*
 * Reihenfolge ist Rangfolge: das erste Bild ist das Hauptstück. Später
 * ersetzt der Inhaber die Dateien; bis dahin sind es die Demobilder.
 */
$arbeiten = array(
	array( 'datei' => 'farbe.webp',  'text' => 'Strähnen in Folientechnik', 'alt' => 'Strähnen werden in Folie gelegt' ),
	array( 'datei' => 'haar.webp',   'text' => 'Damenschnitt',              'alt' => 'Ein Damenschnitt wird geföhnt' ),
	array( 'datei' => 'bart.webp',   'text' => 'Bart in Form',              'alt' => 'Ein Bart wird konturiert' ),
	array( 'datei' => 'pflege.webp', 'text' => 'Pflege danach',             'alt' => 'Pflegeprodukte auf der Ablage' ),
	array( 'datei' => 'interieur.webp', 'text' => 'Im Salon',               'alt' => 'Blick in den Salon' ),
);

/*
 * Fünf Bilder, nicht vier: das Hauptstück belegt zwei Spalten und zwei
 * Zeilen, die vier kleineren füllen die restlichen zwei Spalten in zwei
 * Zeilen. Mit vier Bildern bliebe unten rechts ein Loch.
 */
?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","anchor":"arbeiten","className":"auf-buehne","backgroundColor":"buehne","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div id="arbeiten" class="wp-block-group alignfull auf-buehne has-buehne-background-color has-background" style="padding-bottom:var(--wp--preset--spacing--70)">

	<!-- wp:group {"className":"band","layout":{"type":"default"}} -->
	<div class="wp-block-group band">
		<!-- wp:heading {"level":2,"className":"band-name"} -->
		<h2 class="wp-block-heading band-name">Unsere Arbeiten</h2>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"band-satz"} -->
		<p class="band-satz">Beispielbilder — die echten Arbeiten aus dem Salon folgen.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:html -->
	<div class="arbeiten-gitter">
		<?php foreach ( $arbeiten as $stueck ) : ?>
		<figure>
			<img src="<?php echo esc_url( "$verzeichnis/{$stueck['datei']}" ); ?>" alt="<?php echo esc_attr( $stueck['alt'] ); ?>" loading="lazy" decoding="async" width="1600" height="1000"/>
			<figcaption><?php echo esc_html( $stueck['text'] ); ?></figcaption>
		</figure>
		<?php endforeach; ?>
	</div>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->
