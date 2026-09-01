<?php
/**
 * Title: Mannschaft — Karten mit Terminknopf
 * Slug: agentur-basis/team-karten
 * Categories: agentur-seitenaufbau, agentur-friseur
 * Description: Die Mitarbeiter mit Porträt, Angaben und einem eigenen Terminknopf je Person.
 * Keywords: team, mitarbeiter, mannschaft, buchen
 * Viewport Width: 1400
 *
 * Der Terminknopf steht auf der Karte, nicht erst im Buchungsschritt. Das ist
 * die bessere Hälfte aus zwei Vorlagen: cgbarbershop.com bucht direkt beim
 * Mitarbeiter, das Altegio-Widget lässt erst wählen und dann suchen. Wer
 * seine Bedienung kennt, will sie hier anklicken.
 *
 * Die Porträts bleiben farbig — anders als die Arbeits- und Stimmungsbilder,
 * die getont werden. So macht es auch die Vorlage, und es ist richtig:
 * Gesichter sollen echt wirken, nicht inszeniert.
 *
 * Hinweis für die Übergabe: Fotos von Mitarbeiterinnen und Mitarbeitern brauchen deren Einwilligung
 * (§ 22 KUG). Das steht in der Handover-Dokumentation, nicht nur im Gespräch.
 *
 * @package agentur-basis
 */

$portrait = esc_url( agentur_basis_datei( 'assets/img/platzhalter-portrait.svg' ) );

/*
 * Die Mannschaft pflegt der Inhaber unter „Ihre Angaben" — dort ändert sich
 * am häufigsten etwas, und jede Einstellung oder Kündigung würde sonst einen
 * Auftrag an die Agentur bedeuten. Ohne das Modul greift die Liste unten.
 */
if ( function_exists( 'agentur_angabe_liste' ) ) {
	$leute = agentur_angabe_liste( 'team' );
} else {
	$leute = array(
		array( 'name' => 'Aylin', 'erfahrung' => '14 Jahre Erfahrung', 'sprachen' => 'Deutsch, Türkisch', 'schwerpunkt' => 'Farbe, Strähnen, Langhaar' ),
		array( 'name' => 'Marek', 'erfahrung' => '11 Jahre Erfahrung', 'sprachen' => 'Deutsch, Polnisch', 'schwerpunkt' => 'Herrenschnitt, Bart' ),
		array( 'name' => 'Deniz', 'erfahrung' => '7 Jahre Erfahrung', 'sprachen' => 'Deutsch, Türkisch, Englisch', 'schwerpunkt' => 'Schnitt und Beratung, Kinder' ),
	);
}

$etikett = function_exists( 'agentur_angabe' ) ? agentur_angabe( 'team_etikett' ) : 'Fotos folgen';

// Ohne Personen kein Abschnitt „Wer Sie bedient".
if ( empty( $leute ) ) {
	return;
}
?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","anchor":"team","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div id="team" class="wp-block-group alignfull" style="padding-bottom:var(--wp--preset--spacing--70)">

	<!-- wp:group {"className":"band","layout":{"type":"default"}} -->
	<div class="wp-block-group band">
		<!-- wp:heading {"level":2,"className":"band-name"} -->
		<h2 class="wp-block-heading band-name">Wer Sie bedient</h2>
		<!-- /wp:heading -->
		<?php if ( '' !== $etikett ) : ?>
		<!-- wp:paragraph {"className":"band-satz"} -->
		<p class="band-satz"><?php echo esc_html( $etikett ); ?></p>
		<!-- /wp:paragraph -->
		<?php endif; ?>
	</div>
	<!-- /wp:group -->

	<?php
	/*
	 * Attribut und Inline-Style müssen denselben Wert tragen. Hier stand im
	 * Attribut spacing|40 und im style spacing|50 — gerendert wurde 50, im
	 * Editor erschien der Block als ungültig. Solche Abweichungen entstehen
	 * beim Nachjustieren von Hand und fallen nirgends auf, weil die Seite
	 * richtig aussieht.
	 */
	?>
	<!-- wp:columns {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"blockGap":{"top":"var:preset|spacing|50","left":"var:preset|spacing|40"}}}} -->
	<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--50)">

		<?php foreach ( $leute as $person ) : ?>
		<!-- wp:column -->
		<div class="wp-block-column">

			<!-- wp:image {"aspectRatio":"1/1","scale":"cover","sizeSlug":"basis-portrait","className":"ist-zugeschnitten"} -->
			<figure class="wp-block-image size-basis-portrait ist-zugeschnitten"><img src="<?php echo $portrait; ?>" alt="Porträt von <?php echo esc_attr( $person['name'] ); ?>" style="aspect-ratio:1/1;object-fit:cover"/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"person-name","fontSize":"x-large"} -->
			<h3 class="wp-block-heading person-name has-x-large-font-size"><?php echo esc_html( $person['name'] ); ?></h3>
			<!-- /wp:heading -->

			<?php
			// Leere Angaben erzeugen sonst Leerzeilen unter dem Namen.
			$zeilen = array_filter( array( $person['erfahrung'] ?? '', $person['sprachen'] ?? '', $person['schwerpunkt'] ?? '' ) );
			?>
			<?php if ( ! empty( $zeilen ) ) : ?>
			<!-- wp:paragraph {"className":"person-daten"} -->
			<p class="person-daten"><?php echo implode( '<br>', array_map( 'esc_html', $zeilen ) ); ?></p>
			<!-- /wp:paragraph -->
			<?php endif; ?>

			<!-- wp:buttons {"className":"person-aktion"} -->
			<div class="wp-block-buttons person-aktion"><!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#termin">Termin bei <?php echo esc_html( $person['name'] ); ?></a></div>
			<!-- /wp:button --></div>
			<!-- /wp:buttons -->

		</div>
		<!-- /wp:column -->
		<?php endforeach; ?>

	</div>
	<!-- /wp:columns -->

	<!-- wp:paragraph {"className":"nachsatz","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"padding":{"top":"var:preset|spacing|30"}}}} -->
	<p class="nachsatz" style="margin-top:var(--wp--preset--spacing--50);padding-top:var(--wp--preset--spacing--30)">Sie wissen noch nicht, zu wem? <a href="#termin">Termin buchen, egal bei wem</a> — wir teilen Sie ein, dann geht es meist schneller.</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
