/**
 * Zeilen hinzufügen und entfernen in den Listen unter „Ihre Angaben".
 *
 * Reines JavaScript ohne Bibliothek und ohne Bauschritt: das Formular wird
 * gewöhnlich abgeschickt, die Felder heißen `listen[preise][0][leistung]`.
 * Für zwei Knöpfe eine Werkzeugkette einzurichten wäre das Gegenteil von
 * wartbar.
 *
 * @package agentur-basis-mu
 */

( function () {
	'use strict';

	/**
	 * Nummeriert die Felder einer Liste durch.
	 *
	 * Nötig nach jedem Entfernen: bliebe eine Lücke in der Nummerierung,
	 * käme die Reihenfolge beim Speichern durcheinander — PHP sortiert
	 * numerische Schlüssel, und aus 0,2,3 würde stillschweigend etwas
	 * anderes als das, was auf dem Bildschirm stand.
	 */
	function neuNummerieren( tabelle ) {
		var liste = tabelle.getAttribute( 'data-liste' );

		Array.prototype.forEach.call( tabelle.querySelectorAll( 'tbody > tr' ), function ( zeile, nr ) {
			Array.prototype.forEach.call( zeile.querySelectorAll( 'input[name]' ), function ( feld ) {
				feld.name = feld.name.replace(
					new RegExp( '^listen\\[' + liste + '\\]\\[[^\\]]*\\]' ),
					'listen[' + liste + '][' + nr + ']'
				);
			} );
		} );
	}

	document.addEventListener( 'click', function ( ereignis ) {
		var ziel = ereignis.target;

		if ( ziel.classList.contains( 'agentur-zeile-neu' ) ) {
			ereignis.preventDefault();

			var liste = ziel.getAttribute( 'data-liste' );
			var tabelle = document.querySelector( '.agentur-liste[data-liste="' + liste + '"]' );
			var vorlage = document.querySelector( '.agentur-zeilenvorlage[data-liste="' + liste + '"]' );

			if ( ! tabelle || ! vorlage ) {
				return;
			}

			var koerper = tabelle.querySelector( 'tbody' );
			var neue = vorlage.content.cloneNode( true );

			koerper.appendChild( neue );
			neuNummerieren( tabelle );

			var erstes = koerper.lastElementChild.querySelector( 'input' );

			if ( erstes ) {
				erstes.focus();
			}

			return;
		}

		if ( ziel.classList.contains( 'agentur-zeile-weg' ) ) {
			ereignis.preventDefault();

			var zeile = ziel.closest( 'tr' );
			var eigene = ziel.closest( '.agentur-liste' );

			if ( ! zeile || ! eigene ) {
				return;
			}

			// Ohne Rückfrage: die Zeile ist noch nicht gespeichert, und wer
			// sich vertut, verlässt die Seite ohne zu speichern.
			zeile.parentNode.removeChild( zeile );
			neuNummerieren( eigene );
		}
	} );
} )();
