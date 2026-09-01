<?php
/**
 * Title: Google-Bewertungen
 * Slug: agentur-basis/bewertungen
 * Categories: agentur-seitenaufbau, agentur-friseur
 * Description: Note, Anzahl und drei wörtliche Zitate. Ohne Google-Widget, ohne externen Aufruf.
 * Keywords: bewertungen, rezensionen, google, sterne, referenzen
 * Viewport Width: 1400
 *
 * WOHER DIE ZAHLEN KOMMEN. Aus dem Betriebsmodul, wenn es installiert ist:
 * `agentur_bewertungen()` liest eine Option, die ein Cronjob täglich auf dem
 * Server mit der Places API abgleicht. Fehlt das Modul, läuft die Vorlage mit
 * den Werten weiter, die unten eingetragen sind. Regeln, Grenzen und Befehle
 * stehen in `produkt/agentur-mu/README.md`.
 *
 * KEIN GOOGLE-WIDGET. Das offizielle Bewertungs-Widget und die Places-API im
 * Browser laden beim Seitenaufruf von fremden Servern, setzen Cookies und
 * brauchen deshalb eine Einwilligung vor dem Laden (§2.1). Dieselbe Rechnung
 * wie bei der eingebetteten Karte, und sie geht genauso wenig auf: für eine
 * Handvoll Sätze zahlt man mit einem Consent-Banner und der Zusage „keine
 * externen Aufrufe". Deshalb der Umweg über den Server; der Verweis auf das
 * Profil öffnet erst auf Klick.
 *
 * ACHTUNG VOR DEM LIVEGANG — das ist kein Gestaltungsdetail:
 *
 *   Erfundene oder geschönte Bewertungen sind unzulässig. Seit 2022 steht der
 *   Fall ausdrücklich im Anhang zu § 3 UWG: Bewertungen als echt darzustellen,
 *   ohne dass sie es sind, ist eine unlautere geschäftliche Handlung. Das ist
 *   kein Formfehler, sondern abmahnbar.
 *
 *   Note und Anzahl sind abgelesen, die Zitate nicht: die Rezensionsliste
 *   lädt bei Google erst hinter der Einwilligung nach. Deshalb stehen dort
 *   weiterhin [[Platzhalter]] und keine plausibel klingenden Sätze. Sie
 *   verschwinden entweder mit der Anbindung oder mit
 *   `wp agentur-bewertungen handarbeit` — nicht durch Danebenschreiben.
 *
 * HINWEISPFLICHT, § 5b Abs. 3 UWG. Wer Verbraucherbewertungen zugänglich
 * macht, muss angeben, **ob und wie** er sicherstellt, dass sie von echten
 * Kunden stammen. Das gilt auch für übernommene Google-Bewertungen und auch
 * dann, wenn man selbst gar nichts prüft — dann ist genau das die Angabe.
 * Den Satz formuliert das Modul, weil er von der Quelle abhängt: bei der API
 * wählt Google aus, bei der Handpflege der Betrieb, und das ist nicht
 * dasselbe. Er steht fest im Muster und ist nicht wegzuklicken.
 *
 * KEINE STRUCTURED DATA. AggregateRating-Auszeichnung für den eigenen Betrieb
 * auf der eigenen Seite ist von Google ausdrücklich nicht vorgesehen und kann
 * zu einer manuellen Maßnahme führen. Die Sterne hier sind Gestaltung, keine
 * Auszeichnung für die Suchmaschine.
 *
 * @package agentur-basis
 */

/*
 * Die Angaben kommen aus dem Betriebsmodul (mu-plugin), wenn es installiert
 * ist: es gleicht täglich mit der Places API ab und legt das Ergebnis in
 * einer Option ab. Hier wird nur gelesen, nie abgerufen — kein HTTP im
 * Seitenaufbau.
 *
 * Fehlt das Modul, läuft die Vorlage mit den Werten unten weiter. Das ist
 * kein Notbehelf, sondern der Auslieferungszustand für jeden Betrieb ohne
 * Google-Cloud-Konto: eine vollständige Seite, nur ohne Automatik. Ein
 * Theme, das ohne sein Plugin eine leere Fläche zeigt, ist ein Theme, das
 * beim ersten Themewechsel die Kundenseite zerlegt.
 */
if ( function_exists( 'agentur_bewertungen' ) ) {
	$daten = agentur_bewertungen();
} else {
	// Am 2026-08-17 aus der Rezensionsübersicht von Google abgelesen. Note und
	// Anzahl bewegen sich, deshalb steht der Stand daneben — wer sie ein Jahr
	// später ungeprüft stehen lässt, wirbt mit Zahlen, die nicht mehr stimmen.
	$daten = array(
		'note'      => '4,5',
		'note_zahl' => 4.5,
		'anzahl'    => '234',
		'anteil'    => 90.0,
		'stand'     => '17. August 2026',
		// Dokumentiertes, sitzungsunabhängiges Kartenformat statt einer Such-URL
		// mit Sitzungsparametern. Besser wäre der Kurzlink aus dem
		// Unternehmensprofil des Inhabers (g.page/…) — beim Kunden zu erfragen.
		'profil'    => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'Barber SUNSHINE, Frankfurter Str. 11, 53840 Troisdorf' ),
		'stimmen'   => array(
			array( 'text' => '[[Zitat aus einer echten Google-Bewertung — zwei bis drei Zeilen, wörtlich übernommen.]]', 'name' => '[[Vorname N.]]' ),
			array( 'text' => '[[Zweites Zitat. Am glaubwürdigsten sind die, die etwas Konkretes benennen.]]', 'name' => '[[Vorname N.]]' ),
			array( 'text' => '[[Drittes Zitat. Wörtlich lassen, auch wenn ein Tippfehler drin ist.]]', 'name' => '[[Vorname N.]]' ),
		),
		'hinweis'   => 'Ausgewählte Bewertungen aus unserem Google-Unternehmensprofil, wörtlich übernommen und nicht gekürzt.'
			. ' Note und Anzahl beziehen sich auf alle 234 Bewertungen.'
			. ' Ob eine Bewertung von einem tatsächlichen Gast stammt, prüft Google; wir prüfen es nicht.',
	);
}

$note    = $daten['note'];
$anzahl  = $daten['anzahl'];
$stand   = $daten['stand'];
$profil  = $daten['profil'];
$anteil  = $daten['anteil'];
$stimmen = $daten['stimmen'];
$hinweis = $daten['hinweis'];
$kennung = 'sterne-' . wp_unique_id();

// Ohne Note gibt es nichts anzuzeigen. Ein Abschnitt mit leeren Sternen und
// „0 Bewertungen" ist schlechter als gar keiner — er sagt dem Besucher, dass
// hier noch nie jemand war.
if ( '' === $note ) {
	return;
}
?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","anchor":"bewertungen","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div id="bewertungen" class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

	<!-- wp:heading {"level":2,"fontSize":"xx-large"} -->
	<h2 class="wp-block-heading has-xx-large-font-size">Was unsere Gäste sagen</h2>
	<!-- /wp:heading -->

	<!-- wp:html -->
	<div class="bewertung-kopf" style="margin-top:var(--wp--preset--spacing--50)">
		<svg class="sterne" viewBox="0 0 108 20" role="img" aria-label="<?php echo esc_attr( $note ); ?> von 5 Sternen">
			<defs>
				<path id="<?php echo esc_attr( $kennung ); ?>-form" d="M10 1.4l2.6 5.4 5.9.9-4.3 4.2 1 5.9L10 15l-5.2 2.8 1-5.9-4.3-4.2 5.9-.9z"/>
				<clipPath id="<?php echo esc_attr( $kennung ); ?>-anteil">
					<rect x="0" y="0" width="<?php echo esc_attr( $anteil ); ?>%" height="20"/>
				</clipPath>
			</defs>
			<g fill="currentColor" opacity="0.26">
				<?php for ( $i = 0; $i < 5; $i++ ) : ?>
				<use href="#<?php echo esc_attr( $kennung ); ?>-form" x="<?php echo esc_attr( $i * 22 ); ?>"/>
				<?php endfor; ?>
			</g>
			<g fill="var(--wp--preset--color--akzent)" clip-path="url(#<?php echo esc_attr( $kennung ); ?>-anteil)">
				<?php for ( $i = 0; $i < 5; $i++ ) : ?>
				<use href="#<?php echo esc_attr( $kennung ); ?>-form" x="<?php echo esc_attr( $i * 22 ); ?>"/>
				<?php endfor; ?>
			</g>
		</svg>
		<p class="bewertung-note"><?php echo esc_html( $note ); ?></p>
		<p class="bewertung-anzahl"><?php echo esc_html( $anzahl ); ?> Bewertungen bei Google<span class="stand">Stand <?php echo esc_html( $stand ); ?></span></p>
	</div>
	<!-- /wp:html -->

	<!-- wp:columns {"style":{"spacing":{"margin":{"top":"var:preset|spacing|60"},"blockGap":{"top":"var:preset|spacing|50","left":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--60)">

		<?php foreach ( $stimmen as $stimme ) : ?>
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"className":"zitat"} -->
			<p class="zitat">„<?php echo esc_html( $stimme['text'] ); ?>“</p>
			<!-- /wp:paragraph -->
			<?php if ( '' !== $stimme['name'] ) : ?>
			<!-- wp:paragraph {"className":"zitat-quelle"} -->
			<p class="zitat-quelle"><?php echo esc_html( $stimme['name'] ); ?><?php if ( ! empty( $stimme['zeit'] ) ) : ?> · <?php echo esc_html( $stimme['zeit'] ); ?><?php endif; ?></p>
			<!-- /wp:paragraph -->
			<?php endif; ?>
		</div>
		<!-- /wp:column -->
		<?php endforeach; ?>

	</div>
	<!-- /wp:columns -->

	<?php
	/*
	 * Die Fußnote steckt in einer Gruppe mit gewöhnlichem Fluss. Direkt im
	 * constrained-Abschnitt zentriert WordPress jedes Kind mit einer max-width
	 * per `margin-inline: auto !important` — die Angabe stand mittig unter den
	 * Zitaten statt links. Mit Spezifität ist dagegen nichts auszurichten, mit
	 * der Schachtelung schon.
	 *
	 * PHP-Kommentar, nicht HTML — siehe hero-text-bild.php.
	 */
	?>
	<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}},"layout":{"type":"default"}} -->
	<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--50)">
	<!-- wp:paragraph {"className":"pflichthinweis"} -->
	<p class="pflichthinweis"><?php echo esc_html( $hinweis ); ?></p>
	<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:paragraph {"className":"strich-verweis","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
	<p class="strich-verweis" style="margin-top:var(--wp--preset--spacing--50)"><a href="<?php echo esc_url( $profil ); ?>" rel="noopener nofollow" target="_blank">Alle Bewertungen bei Google<span class="screen-reader-text"> (öffnet in einem neuen Fenster)</span></a></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
