<?php
/**
 * Title: Bühne — eine Leistung
 * Slug: agentur-basis/leistung-zickzack
 * Categories: agentur-seitenaufbau, agentur-friseur
 * Description: Bildbühne für eine einzelne Leistung. Mehrfach einsetzbar, je Leistung eine Bühne.
 * Keywords: leistung, bühne, detail
 * Viewport Width: 1400
 *
 * Bis 2026-08-17 war das ein Zickzack: Text links, Bild rechts, beim
 * nächsten Einsatz getauscht. Der Wechsel war Gestaltung, aber er war
 * derselbe Takt wie im Auftakt, in der Mannschaft und im Kontakt — vier
 * Abschnitte, ein Muster, und die Seite las sich wie ein Prospekt.
 *
 * Jetzt eine Bühne: das Bild trägt, der Text steht darauf. Wer eine zweite
 * Leistung zeigen will, setzt eine zweite Bühne — aber mit einem Band
 * dazwischen. Zwei Bühnen ohne Band nehmen einander die Wirkung.
 *
 * Die Überschriftenebene bleibt bei jedem Einsatz h2. Der Wechsel ist
 * Gestaltung und keine Information; sonst zerfällt die Gliederung für
 * Screenreader.
 *
 * @package agentur-basis
 */

$bild = esc_url( get_template_directory_uri() . '/assets/img/demo/farbe.webp' );
?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","className":"buehne buehne-halb","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull buehne buehne-halb">

	<!-- wp:image {"className":"buehne-bild"} -->
	<figure class="wp-block-image buehne-bild"><img src="<?php echo $bild; ?>" alt="Strähnen werden in Folie gelegt"/></figure>
	<!-- /wp:image -->

	<!-- wp:heading {"level":2,"className":"buehne-titel","fontSize":"xx-large"} -->
	<h2 class="wp-block-heading buehne-titel has-xx-large-font-size">Farbe und Strähnen</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"buehne-satz","fontSize":"large","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
	<p class="buehne-satz has-large-font-size" style="margin-top:var(--wp--preset--spacing--40)">Ansatz, Strähnen, Farbwechsel — vorher sehen wir uns Haar und Hautton an und sagen ehrlich, was hält und was nicht. Wer zum ersten Mal färbt, bekommt eine Strähne zur Probe.</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"className":"strich-verweis","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
	<p class="strich-verweis" style="margin-top:var(--wp--preset--spacing--50)"><a href="#termin">Termin buchen</a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
