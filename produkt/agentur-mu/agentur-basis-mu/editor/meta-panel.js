/**
 * Feld „Beschreibung für Google" in der Seitenleiste des Blockeditors.
 *
 * Reines JavaScript, kein Bauschritt. Für ein einzelnes Textfeld einen
 * Build-Prozess einzurichten hieße: node_modules im Repository, eine Kette
 * von Abhängigkeiten zu pflegen und ein Artefakt, das niemand mehr bauen
 * kann, wenn in zwei Jahren jemand eine Zeile ändern will.
 *
 * @package agentur-basis-mu
 */

( function ( wp ) {
	'use strict';

	var einstellungen = window.agenturMeta || { schluessel: '_agentur_beschreibung', max: 158, min: 70 };

	/**
	 * PluginDocumentSettingPanel ist seit WordPress 6.6 nach wp.editor
	 * umgezogen; in älteren Fassungen liegt es in wp.editPost. Beide Wege
	 * abfragen, statt eine Mindestversion zu erzwingen — sonst verschwindet
	 * das Feld bei einem Kunden mit älterer Installation kommentarlos.
	 */
	var Panel =
		( wp.editor && wp.editor.PluginDocumentSettingPanel ) ||
		( wp.editPost && wp.editPost.PluginDocumentSettingPanel );

	if ( ! Panel || ! wp.plugins || ! wp.element ) {
		return;
	}

	var el = wp.element.createElement;

	function Beschreibung() {
		var meta = wp.data.useSelect( function ( select ) {
			var editor = select( 'core/editor' );

			return editor ? editor.getEditedPostAttribute( 'meta' ) : null;
		}, [] );

		var setzeMeta = wp.data.useDispatch( 'core/editor' ).editPost;

		// Bei Inhaltstypen ohne angemeldetes Feld gibt es kein meta-Objekt.
		// Dann gar nichts anzeigen statt ein Feld, das ins Leere speichert.
		if ( ! meta || typeof meta[ einstellungen.schluessel ] === 'undefined' ) {
			return null;
		}

		var wert = meta[ einstellungen.schluessel ] || '';

		// Gezählt wird, was am Ende im Suchergebnis steht — also mit
		// aufgelösten Platzhaltern. „{telefon}" sind 9 Zeichen, die Nummer
		// zwölf; ein Zähler, der auf Grün steht und trotzdem abgeschnitten
		// wird, ist schlimmer als keiner.
		var werte = einstellungen.werte || {};
		var aufgeloest = wert.replace( /\{([a-z_]+)\}/g, function ( ganzes, feld ) {
			return Object.prototype.hasOwnProperty.call( werte, feld ) ? werte[ feld ] : ganzes;
		} );

		var laenge = aufgeloest.length;

		var hinweis;
		var farbe;

		if ( laenge === 0 ) {
			hinweis = 'Ohne Eintrag entscheidet Google selbst, was im Suchergebnis steht.';
			farbe = '';
		} else if ( laenge > einstellungen.max ) {
			hinweis = laenge + ' Zeichen — ab etwa ' + einstellungen.max + ' schneidet Google ab.';
			farbe = '#b32d2e';
		} else if ( laenge < einstellungen.min ) {
			hinweis = laenge + ' Zeichen — kurz. Es ist Platz bis ' + einstellungen.max + '.';
			farbe = '#996800';
		} else {
			hinweis = laenge + ' von ' + einstellungen.max + ' Zeichen.';
			farbe = '#007017';
		}

		return el(
			Panel,
			{
				name: 'agentur-meta',
				title: 'Beschreibung für Google',
				className: 'agentur-meta-panel',
			},
			el( wp.components.TextareaControl, {
				label: 'Beschreibung',
				help:
					'Der Satz, der unter dem Titel im Suchergebnis steht. Das Wichtigste zuerst: was, für wen, wo. ' +
					'Für Telefon, Adresse und Zeiten bitte {telefon}, {adresse_kurz} oder {zeiten_kurz} schreiben — ' +
					'die füllt die Website aus „Ihre Angaben" und sie bleiben richtig, wenn sich etwas ändert.',
				value: wert,
				rows: 4,
				__nextHasNoMarginBottom: true,
				onChange: function ( neu ) {
					var geaendert = {};
					geaendert[ einstellungen.schluessel ] = neu;
					setzeMeta( { meta: geaendert } );
				},
			} ),
			el(
				'p',
				{
					style: {
						margin: '4px 0 0',
						fontSize: '12px',
						color: farbe || '#757575',
					},
				},
				hinweis
			)
		);
	}

	wp.plugins.registerPlugin( 'agentur-meta', { render: Beschreibung } );
} )( window.wp );
