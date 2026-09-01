<?php
/**
 * Style Variation anwenden, ohne den Site-Editor zu öffnen.
 *
 *   docker compose run --rm wpcli eval-file /opt/skripte/variante-aktivieren.php sunshine
 *   docker compose run --rm wpcli eval-file /opt/skripte/variante-aktivieren.php --  (Basis)
 *
 * Zwei Stolperstellen, beide kosten eine halbe Stunde, wenn man sie nicht kennt:
 *
 * 1. In `styles/<name>.json` steht `settings.color.palette` als flache Liste.
 *    Im Benutzer-Global-Styles-Beitrag erwartet WordPress dieselben Listen
 *    nach Herkunft geschachtelt: `settings.color.palette.theme`. Die flache
 *    Form wird stillschweigend ignoriert — kein Fehler, keine Wirkung, die
 *    Basisfarben bleiben stehen.
 *
 * 2. Der Beitrag muss dem Term des Themes in der Taxonomie `wp_theme`
 *    zugeordnet sein, sonst findet ihn `WP_Theme_JSON_Resolver` nicht und
 *    legt bei **jedem** Aufruf einen neuen an. WordPress setzt diesen Term
 *    beim Anlegen über `tax_input` — und `tax_input` wird verworfen, wenn
 *    kein angemeldeter Benutzer das Recht zum Zuweisen hat. Unter WP-CLI ist
 *    genau das der Fall. Deshalb wird der Term hier ausdrücklich gesetzt.
 *
 * @package agentur-basis
 */

$name       = $args[0] ?? '';
$stylesheet = get_stylesheet();

$einstellungen = array();
$stile         = array();
$titel         = 'Basis';

if ( '' !== $name ) {
	$pfad = get_template_directory() . "/styles/$name.json";
	if ( ! file_exists( $pfad ) ) {
		WP_CLI::error( "Variante nicht gefunden: $pfad" );
	}
	$v             = json_decode( file_get_contents( $pfad ), true );
	$einstellungen = $v['settings'] ?? array();
	$stile         = $v['styles'] ?? array();
	$titel         = $v['title'] ?? $name;

	foreach ( array( 'palette', 'duotone', 'gradients' ) as $liste ) {
		if ( isset( $einstellungen['color'][ $liste ] ) && array_is_list( $einstellungen['color'][ $liste ] ) ) {
			$einstellungen['color'][ $liste ] = array( 'theme' => $einstellungen['color'][ $liste ] );
		}
	}
	if ( isset( $einstellungen['typography']['fontSizes'] ) && array_is_list( $einstellungen['typography']['fontSizes'] ) ) {
		$einstellungen['typography']['fontSizes'] = array( 'theme' => $einstellungen['typography']['fontSizes'] );
	}
}

// Vorhandene Beiträge einsammeln. Ohne den Term aus Punkt 2 sind über die
// Zeit mehrere entstanden; hier bleibt genau einer übrig.
$vorhandene = get_posts( array(
	'post_type'   => 'wp_global_styles',
	'post_status' => 'any',
	'numberposts' => -1,
	'orderby'     => 'ID',
	'order'       => 'ASC',
) );

$ziel = 0;
foreach ( $vorhandene as $p ) {
	if ( $ziel ) {
		wp_delete_post( $p->ID, true );
		WP_CLI::log( "verwaisten Beitrag #{$p->ID} entfernt" );
		continue;
	}
	$ziel = $p->ID;
}

if ( ! $ziel ) {
	$ziel = wp_insert_post(
		array(
			'post_type'    => 'wp_global_styles',
			'post_status'  => 'publish',
			'post_name'    => "wp-global-styles-$stylesheet",
			'post_content' => '{}',
		),
		true
	);
	if ( is_wp_error( $ziel ) ) {
		WP_CLI::error( $ziel->get_error_message() );
	}
}

wp_set_object_terms( $ziel, $stylesheet, 'wp_theme' );
wp_update_post(
	array(
		'ID'           => $ziel,
		'post_title'   => $titel,
		'post_content' => wp_json_encode(
			array(
				'version'                     => WP_Theme_JSON::LATEST_SCHEMA,
				'isGlobalStylesUserThemeJSON' => true,
				'settings'                    => $einstellungen,
				'styles'                      => $stile,
			)
		),
	)
);

$terme = wp_get_post_terms( $ziel, 'wp_theme', array( 'fields' => 'names' ) );
WP_CLI::success(
	sprintf(
		'"%s" aktiv — Beitrag #%d, wp_theme=[%s].',
		$titel,
		$ziel,
		implode( ',', $terme )
	)
);
