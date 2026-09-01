# Agentur Basis — Blocktheme

Standardunterbau für Websites kleiner Betriebe. **Ein Theme für alle Branchen**; der Unterschied
zwischen Barbershop, Salon, Praxis und Handwerk entsteht über Style Variations und Muster, nicht
über eine eigene Codebasis (`.claude/automatisierung.md`).

Gestalterische Grundlage: die Referenzen des Founders, ausgewertet in `.claude/design-referenzen.md`.
Erste Variante ist **Schwarz/Sand** nach cgbarbershop.com.

## Was das Theme bewusst nicht hat

- **Keinen Seitenbaukasten.** Kein Elementor, kein WPBakery, keine gebündelten Premium-Plugins.
  Gebündelte Plugins haben keine eigenen Update-Rechte — wir würden einen Wartungsvertrag auf Code
  verkaufen, den wir nicht patchen können.
- **Keinen einzigen externen Aufruf.** Schriften liegen lokal (§2.1), Emoji-Skript und
  oEmbed-Erkennung sind entfernt. Geprüft: `grep` über den ausgelieferten Code findet nur die
  Schema- und Lizenz-URLs, die nie abgerufen werden.
- **Kein Karussell im Hero.** Ein Slider lädt mehrere große Bilder für den ersten Bildschirm und
  kostet den LCP — und damit die Lighthouse-Zusage aus CLAUDE.md §6.

## Schriften — eine Familie, zwei Schnitte

| Datei | Rolle | Achsen | Lizenz |
|---|---|---|---|
| `archivo-display` | Überschriften h1–h3, Wortmarke | wght 800, wdth 84 | OFL, beiliegend |
| `archivo-400` | Fließtext | wght 400, wdth 100 | OFL |
| `archivo-500` | Navigation, Knöpfe, Etiketten, Preise | wght 500, wdth 100 | OFL |

Bis 2026-08-17 standen hier Playfair Display und Poppins. Beide wurden ersetzt, aus zwei Gründen:

1. **Playfair Display steht auf der Reflex-Ablehnliste** des Gestaltungsleitfadens, zusammen mit
   dem ganzen Lane „Editorial-typografisch" (Anzeigen-Antiqua + kleine gesperrte Versalzeilen +
   Monochrom). Das ist der gesättigte KI-Reflex, und die Seite sah genau danach aus. Sie war auch
   nicht die Idee des Kunden, sondern aus einer Referenz übernommen.
2. **Archivo zeichnet bereits die Wortmarke.** Die Schildschrift des Betriebs trägt jetzt auch die
   Seite — Kontrast über Breite und Gewicht statt über eine geliehene zweite Schrift. Der
   Leitfaden nennt das ausdrücklich die stärkere Lösung.

Drei aus der variablen Archivo instanziierte und auf `latin` / `latin-ext` zugeschnittene
Schnitte, zusammen 72 KB; eine deutsche Seite lädt **36 KB**.

## Typografische Stufen

Sechs Stufen, jeder Schritt ≥ 1,25 — flachere Sprünge lesen sich unentschlossen.

| Stufe | Größe | Rolle |
|---|---|---|
| Titel der Seite | clamp 2,25 → 4,25 rem | genau ein h1 je Seite |
| Abschnitt | clamp 1,75 → 2,75 rem | h2 |
| Unterüberschrift | clamp 1,5 → 2 rem | h3 |
| Vorspann | clamp 1,19 → 1,375 rem | Einleitungssätze |
| Fließtext | 1,0625 rem, fest | |
| Etikett | 0,8125 rem, fest | Versalzeilen, Kleingedrucktes |

Titel : Fließtext = **4 : 1**.

**Der Fehler, den das behebt:** Vorher lag h2 bei bis zu 6 rem und h1 bei 3,5 rem — die
Abschnittsüberschrift schrie also lauter als der Titel der Seite. Dazu Fließtext im Gewicht 300.
Beides zusammen ergab den Eindruck, den der Founder als „visuelles Rauschen" gemeldet hat.

Fließtext steht jetzt auf **400** mit Zeilenhöhe 1,68 und 0,012 em Laufweite: helle Schrift auf
dunklem Grund wirkt leichter als sie ist und braucht Ausgleich auf allen drei Achsen.

## Farbstrategie: committed

Das Gelb trug anfangs nur Knöpfe — unter 5 % der Fläche. Eine Marke, deren Farbe man suchen muss,
ist keine. Der Terminabschnitt liegt deshalb vollflächig im Gelb mit schwarzer Schrift: dieselbe
Mechanik wie das Schild an der Fassade.

## Basis und Marke sind getrennt

| | Akzent | wo |
|---|---|---|
| **Basis** | `#E9E5DD` Knochenweiß | `theme.json` — markenneutral, gehört niemandem |
| **Sunshine** | `#F0E100` | `styles/sunshine.json` |

Die Slugs sind in beiden identisch (`akzent`, `akzent-hell`, `grund`, `schrift` …), Muster und
CSS greifen nur auf die Slugs zu. Deshalb wechselt eine Variante die Marke, ohne eine einzige
Zeile Auszeichnung anzufassen — auch das gelbe Feld der Wortmarke zieht seine Farbe aus
`var(--wp--preset--color--akzent)`. Beleg: `nachweise/15-…` gegen `nachweise/16-…`, identisches
Layout, unterschiedliche Marke.

**Die Palette der Variante enthält alle Einträge, nicht nur den Akzent.** WordPress ersetzt
Palette-Listen beim Zusammenführen vollständig, statt sie eintragsweise zu mischen; wer nur den
Akzent notiert, verliert den Rest.

Umschalten ohne Site-Editor:

```
cd produkt/lokal
docker compose run --rm wpcli eval-file /opt/skripte/variante-aktivieren.php sunshine
docker compose run --rm wpcli eval-file /opt/skripte/variante-aktivieren.php        # Basis
```

**Was beim Aufsetzen eines echten Kunden ersetzt wird:** `styles/<kunde>.json`, die Wortmarke in
`parts/header.html` und der Inhalt von `assets/img/demo/`. Alles andere bleibt, wie es ist.

## Reservierte Palette-Slugs

`text` und `background` **nicht** als Slug verwenden. WordPress erzeugt aus jedem Slug eine Klasse
`.has-<slug>-color`; bei `text` entsteht `.has-text-color`, und diese Klasse benutzt der Kern
selbst als Markierung — mit `!important`. Sie überschreibt dann jede tatsächlich gewählte Farbe.
Der Fehler kostete hier schwarze Schrift auf Gelb: sie kam als helles Creme heraus, rund 1,5:1.
Deshalb heißen die Slugs `schrift` und `schrift-hell`.

Lokal, je Schnitt getrennt nach `latin` und `latin-ext` mit `unicode-range`. Eine deutsche Seite
lädt nur `latin` — rund 46 KB. `latin-ext` wird erst nachgeladen, wenn tatsächlich ein türkischer
oder polnischer Mitarbeitername auf der Seite steht; in NRW ist das der Normalfall und nicht die
Ausnahme, deshalb ist es eingebaut und nicht weggelassen.

Die `latin-ext`-Einträge stehen **vor** den `latin`-Einträgen. Sollte eine WordPress-Version
`unicodeRange` nicht auswerten, gewinnt der zuletzt definierte Eintrag — dann lädt die Seite die
`latin`-Datei, die Deutsch vollständig abdeckt. Der Ausfall ist damit harmlos statt kaputt.

Die OFL verlangt, dass die Lizenz mitgeliefert wird: `assets/fonts/OFL-*.txt`. Ohne sie würden wir
die Schriften auf jeder Kundenseite unlizenziert weiterverbreiten.

## Farben

| Rolle | Wert | Kontrast auf Grund |
|---|---|---|
| Grund | `#141414` | — |
| Text | `#C9C2B8` | 10,7 : 1 |
| Text hell (Überschriften) | `#F2ECE1` | ~15 : 1 |
| Akzent Sand | `#C8996A` | 7,3 : 1 |

Der Akzent trägt ausschließlich Buchungsknopf, aktive Zustände und die Striche vor Verweisen.
Dunkler Text auf dem Sand-Knopf ergibt dieselben 7,3 : 1 — WCAG AA ist damit in beide Richtungen
erfüllt, ohne dass die Gestaltung nachgibt.

**Der Kunde kann keine eigenen Farben wählen.** `color.custom`, `customGradient` und
`customFontSize` stehen auf `false`. Das ist Absicht: eine schmale Bearbeitungsfläche ist die
Bedingung dafür, dass 79 €/Monat mit 30 Minuten Änderungen aufgehen.

## Bilder

Drei feste Zuschnitte — `basis-portrait` 3:4, `basis-quer` 8:5, `basis-breit` 2:1 — plus
`object-fit: cover` in den Mustern. Ein hochkant geknipstes Handyfoto sitzt damit im selben Raster
wie ein Querformat.

Zwei Duotone-Vorlagen (`sand`, `grau`) tonen Arbeits- und Stimmungsbilder ein. **Porträts bleiben
farbig** — Gesichter sollen echt wirken. So macht es die Vorlage auch.

## Stand

Gebaut:

- `theme.json` — Farben, Schrift, Abstände, Layout, Sperren
- `style.css` — nur, was theme.json nicht kann: Sprungmarke, klebende Kopfzeile, Strich-Verweis, Fokus, `prefers-reduced-motion`
- `functions.php` — Bildgrößen, Schrift-Vorladen, Musterkategorien, Ballast entfernt
- Vorlagen: `index`, `front-page`, `page`, `seite-ohne-titel`, `single`, `archive`, `search`, `404`
- Teile: `header` (klebend, mit Terminknopf), `footer` (mit Impressum und Datenschutz fest verdrahtet)
- Muster: `hero-text-bild`, `leistungen-drei`, `leistung-zickzack`, `team-karten`

Fehlt noch:

- Muster: Bewertungen, Öffnungszeiten/Anfahrt, Kontakt, Produkte, Buchungsabschnitt
- Style Variation für die zweite Branche (entsteht erst nach der ersten fertigen)
- Begleitendes Must-Use-Plugin: Redakteursrolle, Bild-Nachbearbeitung beim Hochladen, Rechtstexte, Buchung
- Die eigentliche Buchung — selbst gehostet auf EU-Hosting, **nicht** Altegio (`.claude/design-referenzen.md`)

## Geprüft am 2026-08-17

Läuft in WordPress 6.8.3, lokal über `produkt/lokal/`. Belege: `produkt/lokal/nachweise/`.

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| **Mobil** — das Tor aus §6 | **100** | **100** | **100** | 92 |
| Desktop | 100 | 98 | 100 | 92 |

LCP 1,5 s · TBT 0 ms · CLS 0 · **keine einzige externe Anfrage im ausgelieferten HTML**.
`php -l` sauber über `functions.php` und alle vier Muster; alle vier Muster registriert.

### Zwei Fehler, die erst der Browser gezeigt hat

1. **Der Inhalt klemmte auf 760 px.** `main` war in `front-page.html` als `constrained` gesetzt,
   dadurch lag `post-content` auf der Lesebreite fest — und die Muster mit `alignfull` kamen nicht
   darüber hinaus. Im Markup war davon nichts zu sehen: die Layout-Regeln waren korrekt erzeugt,
   nur der Kasten darum war zu schmal. Nebenwirkung: die Überschrift brach mitten im Wort.
   Behoben, indem `main` kein eigenes Layout mehr trägt.
2. **Übersprungene Überschriftenebene**, von Lighthouse gefunden: h3 → h5 in den Leistungskacheln,
   h2 → h6 im Fuß. Ursache war ein Denkfehler, nicht ein Tippfehler — h5 und h6 waren in
   `theme.json` als kleine Versal-Etiketten gestaltet, und dann wurde die Ebene nach dem Aussehen
   gewählt. Jetzt ist das Etikett eine CSS-Klasse (`.etikett`), h5/h6 sind wieder normale
   Überschriften, und die Preise stehen als Absatz — ein Preis war ohnehin nie eine Überschrift.

Beide Fehler sind der Grund, warum Schritt 2 des Plans ein QA-Skript ist und keine Sichtprüfung.

### Offen

- **Keine Meta-Description** (SEO 92). WordPress gibt von sich aus keine aus — gehört ins Plugin.
- Die vollständige Belegreihe nach §6 (360 / 768 / 1440, Formulartests, Mitschnitt externer
  Aufrufe) erzeugt das QA-Skript, nicht die Hand.

## Lokal starten

```
cd produkt/lokal
docker compose up -d
open http://localhost:8080
```

Zugang zum Backend: `admin` / `lokal-nur-zum-bauen` — nur für diese Wegwerf-Installation.
Das Theme wird aus dem Repository hereingespiegelt; Änderungen sind sofort sichtbar.

## Vorlagenteile (`parts/`)

Die Erläuterungen standen bis 2026-08-17 als HTML-Kommentare in den Dateien. Sie stehen jetzt hier,
weil ein `<!-- -->` **innerhalb** der Blockmarkierungen die Blockprüfung im Editor scheitern lässt:
der Kommentar ist nicht Teil dessen, was der Block beim Speichern erzeugt. WordPress behält
ungültige Blöcke dann stillschweigend wörtlich — es fällt niemandem auf, bis jemand den Site-Editor
öffnet und eine Warnung sieht, die er nicht einordnen kann.

In PHP-Mustern lässt sich derselbe Text als PHP-Kommentar an Ort und Stelle lassen. In statischem
HTML geht das nicht, deshalb dieser Abschnitt.

### `header.html` — die Wortmarke

Die Marke steht als **eingebettetes SVG**, nicht als Bilddatei: keine zusätzliche Anfrage, scharf
auf jedem Bildschirm, 5 KB. „SUNSHINE" zeichnet auf `currentColor`, damit dieselbe Datei auf hellem
Grund funktioniert, ohne dass es eine zweite Fassung braucht. Das gelbe Feld nimmt
`var(--wp--preset--color--akzent)` und wechselt damit die Farbe mit der Style Variation.

**Kundenspezifisch.** Im Produkt tritt hier der `site-logo`-Block an diese Stelle, sobald das
Must-Use-Plugin SVG-Uploads sicher erlaubt.

### `footer.html` — Pflichtverweise und Betriebsangaben

**Impressum und Datenschutzerklärung stehen fest verdrahtet und nicht in einem Menü**, das jemand
versehentlich leert. CLAUDE.md §2.1 verlangt sie von jeder Seite erreichbar — das ist kein
Gestaltungs-, sondern ein Abmahnungsthema. Das Betriebsmodul schützt zusätzlich die Permalinks
`/impressum` und `/datenschutz` gegen Änderung, weil diese Verweise sonst ins Leere zeigen.

Betriebsangaben kommen über `<!-- wp:agentur/angabe -->` aus „Ihre Angaben". Vorher standen
Telefonnummer und Öffnungszeiten hier ein zweites Mal — wer eine Stelle änderte, hinterließ die
andere falsch, und zwar unten im Fuß, wo niemand hinsieht.

## Blockgültigkeit — vier Regeln beim Schreiben von Mustern

WordPress vergleicht beim Öffnen im Editor das gespeicherte Markup mit dem, was der Block selbst
erzeugen würde. Weicht es ab, gilt der Block als ungültig. **Das fällt niemandem auf**, weil
WordPress ungültige Blöcke stillschweigend wörtlich behält — bis jemand den Editor aufmacht und
eine Warnung sieht, die er nicht einordnen kann. Am 2026-08-17 waren so acht Blöcke betroffen.

1. **Keine HTML-Kommentare innerhalb der Blockmarkierungen.** In PHP-Mustern `<?php /* … */ ?>`
   verwenden — gleicher Text, keine Ausgabe. In statischem HTML gehört die Erläuterung in dieses
   README.
2. **Handgeschriebene `div`s brauchen ihre Blockklasse.** `wp-block-group`, `wp-block-columns` und
   Verwandte gehören zusätzlich zur eigenen Klasse ins `class`-Attribut.
3. **Attribut und Inline-Style müssen denselben Wert tragen.** `{"margin":{"top":"var:preset|spacing|40"}}`
   und `style="margin-top:var(--wp--preset--spacing--50)"` ist ein Befund, auch wenn die Seite
   richtig aussieht. Ausnahme: `blockGap` steht nie im Inline-Style.
4. **Keine Bildattribute von Hand.** `srcset`, `sizes`, `width`, `height`, `loading` und
   `fetchpriority` erzeugt der Bildblock nicht. Sie kommen aus `agentur_basis_bildverzeichnis()`
   und werden beim Rendern ergänzt — derselbe Weg, den WordPress mit `wp_filter_content_tags()`
   für Mediathek-Bilder geht.

**Ein Bild austauschen** heißt deshalb: Datei ersetzen **und** die Maße im Bildverzeichnis
nachziehen. Falsche Maße sind kein Schönheitsfehler, sondern Layout-Sprünge beim Laden.

**Prüfen:** die Startseite im Editor öffnen und in der Konsole zählen —
`wp.data.select('core/block-editor').getBlocks()` rekursiv auf `isValid === false` durchsuchen.
Erwartet: null.
