# Gestaltungsvorlagen je Branche

Sammelstelle für die Referenzen, die der Founder vorgibt. Sie sind der Ersatz für Geschmacksraten:
Ohne sie entscheidet ein Agent über Schrift, Farbe und Bildsprache — und trifft daneben.

Daraus entstehen **Style Variations und Patterns in einem einzigen Block-Theme**, nie ein eigenes
Theme je Branche (`.claude/automatisierung.md`).

## Was eine Referenz brauchbar macht

Der Link allein trägt keine Information. Gebraucht werden drei Zeilen:

| Feld | Warum |
|---|---|
| **URL** | — |
| **Was genau gefällt** | Typografie? Bildsprache? Reihenfolge der Abschnitte? Farbe? Weißraum? „Gefällt mir" ohne das Wonach ist nicht umsetzbar |
| **Was nicht** | Verhindert, dass wir den einen Fehler mitkopieren, der später stört |

**Ungeeignet als Vorlage:** Effekt-Seiten mit schwerem JavaScript und Scroll-Animation. Sie
scheitern an §6 (Lighthouse mobil ≥ 90) und daran, dass der Inhaber die Seite später selbst
bearbeiten soll. Als Ideenquelle für einzelne Abschnitte trotzdem erlaubt — nur nicht als Ziel.

**Menge:** drei bis fünf je Branche. Mehr ist kein besseres Signal, sondern Rauschen.

---

## Barbershop — Vorlagen, nicht Zielgruppe

> **Nachtrag 2026-08-17:** Der erste reale Betrieb, Friseur Sunshine, ist ein Salon für Damen,
> Herren und Kinder. Die Referenzen unten bleiben als **gestalterische** Vorlagen gültig —
> Abschnittsfolge, Typografie, Bildhaltung sind übertragbar. Ihre **Ansprache** ist es nicht:
> „für den Herrn von heute" stand eine Weile auf der Seite, weil die Vorlage abgeschrieben wurde
> statt der Betrieb. Wer eine Referenz übernimmt, übernimmt ihre Gestaltung, nicht ihr Publikum.


Vorgegeben vom Founder am 2026-08-17. Screenshots: `.claude/design-referenzen/`.

| # | Quelle | Was der Founder daran will |
|---|---|---|
| 1 | cgbarbershop.com | alles — Gestaltung, Inhalt, Informationsarchitektur |
| 2 | houseofcuts.ae | Hero mit Foto der **gesamten** Mannschaft, Beschreibung je Mitarbeiter |
| 3 | vervebarbershop.com | Verkauf von Pflegeprodukten zusätzlich zur Dienstleistung |
| 4 | Altegio-Widget (Suburban) | Buchungsablauf: Mitarbeiter, Leistung, Datum/Uhrzeit wählen |

### Was alle drei gemeinsam haben — daraus wird das Theme

Die Struktur ist bei allen dreien dieselbe. Genau das ist der Beleg für die Entscheidung „ein
Theme, mehrere Style Variations": **gleicher Aufbau, völlig verschiedenes Aussehen.**

1. **Kopfzeile klebt mit** — Logo links, Navigation, und ein farblich abgesetzter Buchungsknopf,
   der auf jeder Höhe der Seite sichtbar bleibt. Bei allen drei.
2. **Hero** in drei Spielarten: Text links / Bild rechts asymmetrisch (1) · vollflächiges
   Mannschaftsfoto mit Namen darüber (2) · vollflächiges Stimmungsbild (3)
3. **Leistungen** als drei Kacheln (Haar, Bart, Gesicht), darunter je Leistung ein Abschnitt im
   Zickzack — Text und Bild wechseln die Seite
4. **Mannschaft** als Karten: Porträt, Name, strukturierte Angaben (Jahre Erfahrung, Sprachen),
   und **je Person ein eigener Buchungsknopf**
5. **Bewertungen** — Google-Note mit Anzahl (1: 5,0 aus 2.587)
6. **Produkte** als große Kacheln mit ruhiger Produktfotografie (3)
7. **Öffnungszeiten, Adresse, Telefon** als eigener Abschnitt
8. **Fuß** — soziale Netze, Pflichtangaben

### Typografie

Zwei Schriftfamilien, überall dasselbe Muster:

- **Anzeigenschrift** für Überschriften — kontrastreiche Serif (1 und 3), sehr groß gesetzt
- **Geometrische Grotesk** für Navigation und Fließtext, leichter Schnitt, großzügige Zeilenhöhe
- **Navigation und Knöpfe:** Versalien, weit gesperrt, klein
- Der Sprung zwischen Überschrift und Fließtext ist groß — das trägt die ganze Wirkung
- Verweise als Versalien mit einem kurzen Strich davor („— READ MORE")

### Farbe

| Referenz | Grund | Akzent | Text |
|---|---|---|---|
| 1 | fast schwarz, nicht rein schwarz | warmes Sandbraun | Creme, nicht Weiß |
| 2 | schwarz | Kupfer | Weiß |
| 3 | tiefes Waldgrün mit feinem Ornament | Creme | Creme |

**Ein dunkler Grund, genau ein warmer Akzent, nie reines Weiß im Text.** Der Akzent trägt
ausschließlich den Buchungsknopf und aktive Zustände — sonst nichts. Diese Sparsamkeit ist der
Grund, warum die Seiten teuer wirken.

### Bildsprache — und warum das die Handyfoto-Frage löst

Alle drei arbeiten dunkel und flau: (1) Schwarzweiß für Atmosphäre und Arbeitsszenen, Farbe nur
bei den Porträts; (2) ein dunkles Mannschaftsfoto über die volle Breite; (3) ruhige
Produktaufnahmen vor dunklem Grund.

Daraus folgt eine Bauentscheidung: **das Theme legt eine einheitliche dunkle Tonung über
hochgeladene Bilder** (Graustufe oder Duplex, je Style Variation). Damit fügt sich auch ein
mittelmäßiges Handyfoto des Inhabers ins Bild, statt es zu zerstören — die Sorge aus
`.claude/automatisierung.md`, Anforderung 3, ist damit gestalterisch beantwortet und nicht nur
technisch.

### Was ausdrücklich **nicht** übernommen wird

- **Hero-Karussell** (1). Slider kosten LCP und damit die Lighthouse-Zusage aus §6. Ein
  feststehendes Hero-Bild leistet dasselbe.
- **Text mittig über Gesichter** (2). Kontrast nicht kontrollierbar, scheitert an WCAG.
- **Produkte ohne Preis** (3). In Deutschland gilt die PAngV: Preise mit MwSt., bei Waren
  Grundpreis. Auch bei Dienstleistungen erwartet der deutsche Kunde die Preisliste — sie
  wegzulassen ist hier kein Stilmittel, sondern ein Mangel.

### Buchung — der Ablauf ja, der Anbieter nein

Der Ablauf aus (4) ist gut und wird nachgebaut: **eine Entscheidung je Zeile** — Mitarbeiter,
Leistung, Termin — in beliebiger Reihenfolge, jede Zeile mit Symbol und Pfeil. Dazu die bessere
Idee aus (1): der Einstieg in die Buchung steht **schon auf der Mitarbeiterkarte**, nicht erst im
Buchungsschritt.

**Altegio selbst kommt nicht auf eine deutsche Kundenseite.** Das Widget setzt eigene Cookies und
sein eigener Hinweis sagt, dass Daten an Partner aus sozialen Netzen, Werbung und Analytik
weitergegeben werden. Eingebettet in eine Salonseite hieße das: Drittanbieter-Einbindung,
Einwilligung vor dem Laden, AVV nötig, Datenfluss unklar. CLAUDE.md §4.2 hat das vorweggenommen —
Buchung läuft selbst gehostet auf EU-Hosting, nicht über Calendly, Treatwell „oder Vergleichbares".
Altegio ist Vergleichbares.

Und es ist nicht nur Formalie: dass **die Kundinnendaten in Deutschland bleiben**, ist genau das
Verkaufsargument aus §4.2. Wer das Widget einbettet, verkauft es und bricht es im selben Zug.

## Friseur / Kosmetik

*(offen)*

## Weitere Branchen

*(offen — erst nach der ersten vollständig gebauten Branche, siehe unten)*

---

## Reihenfolge

**Eine Branche vollständig, dann die nächste.** Barbershop als erste, komplett durch bis QA und
Demo-Seite. Erst daran zeigt sich, was Basis ist und was Variante — diese Trennung lässt sich nicht
vorher am Reißbrett treffen. Die zweite Branche kostet danach einen Bruchteil, weil nur noch
`styles/` und Patterns entstehen.

Fünf Branchen parallel zu beginnen heißt, fünfmal denselben ungeprüften Aufbau zu vervielfachen.

---

## Hell mit Akzentflächen — die geltende Vorlage seit 2026-08-17

Screenshots: `.claude/design-referenzen/07-perk-hell-akzentflaechen.png`,
`08-caldera-hell-laut.png`

**Der Founder hat die dunkle Plakatgestaltung abgelehnt** und die Sammlung
[styles.refero.design](https://styles.refero.design/) als Quelle vorgegeben. Vier Kandidaten
geprüft, einer gewählt.

| Vorlage | Mechanik | Befund |
|---|---|---|
| **Perk** (travelperk.com) | warmes Creme, Tinte, **eine** Farbe als Flächenfüllung, eine Schriftfamilie über den ganzen Bereich, weiche Radien, keine Schatten | **gewählt** |
| Caldera (caldera.xyz) | helles Warmgrau, ultrafette schmale Type bis 189 px, Halbtonraster, 40 px Radien | zweite Wahl — lauter, aber Orange kollidiert mit unserem Gelb, und das Halbtonfeld müsste durch etwas Salon-Taugliches ersetzt werden |
| Hungry Tiger | rostbrauner Grund, Tigergold, maximalistisches Typo-Plakat | nur falls die Kritik der Typografie galt und nicht dem dunklen Grund — sonst wiederholt es den Fehler in einem anderen Ton |
| Agence Foudre | Magazin-Aufschlag, Magenta und Waldgrün, viel Leerraum | **verworfen** — ein Ausdrucks-Sitz, kein Betriebssitz. Wer an der Haltestelle Preis und Nummer sucht, findet Leerraum. Und es wirft die gelb-schwarze Vorlage weg |

### Warum Perk und nicht die lautere Variante

**Die Mechanik ist die, die wir schon gebaut hatten.** Eine chromatische Farbe auf ansonsten
achromatischem Grund, verwendet als **Fläche** und nicht als Knopfumrandung. Unser Gelb rutscht
ohne Erfindung in diese Stelle, und die Tinte `#14140F` ist praktisch die Farbe, die schon da war.

**Es kippt genau das, was das Problem war.** Dunkel wird hell. Schwarz plus Plakattypografie
erzählt einen Männerbetrieb — dieser Salon bedient Damen, Herren und Kinder.

**Es braucht keine neue Schrift.** Perk fährt eine Familie über die ganze Stufenleiter, Archivo
tut das bereits. Keine Lizenz, kein zusätzliches Ladegewicht, §2.1 unberührt.

**Die schon geleistete Kontrastarbeit trägt.** Perk setzt keine Schrift auf die Akzentfarbe,
sondern legt die Farbe als Fläche unter dunkle Schrift — dasselbe Verfahren, das hier 13,61:1
ergibt.

### Was NICHT übernommen wird

- **Die Farbe selbst.** Elektrisches Limett `#beff50` ist die Marke von Perk. Unsere ist das Gelb
  vom Schild. Übernommen wird, wie die Farbe eingesetzt wird, nicht welche.
- **Produkt-Screenshots im Hero.** Ein Salon zeigt den Raum und die Leute, keine Oberflächen.
- **Radien in voller Höhe.** 28 px auf allen Karten macht bei einer Preisliste aus einer Liste
  eine Kachelreihe. Bei uns bleibt die Preisliste eine Liste; rund sind Bilder, Tafel und Knöpfe.

---

## Bühnen und Bänder — der geltende Aufbau seit 2026-08-17

Screenshot: `.claude/design-referenzen/09-lamborghini-buehnen.png`

**Der Wechsel auf Hell (Perk) hat nur die Farben getauscht.** Abschnittsfolge, Raster und Takt
blieben, wie sie aus den Barbershop-Vorlagen kamen. Der Founder hat das benannt, und es stimmte.

### Was diese Sammlung leistet und was nicht

**styles.refero.design ordnet nach visuellem Stil, nicht nach Informationsarchitektur.** Von acht
geprüften Einträgen hatte genau einer einen strukturell anderen Aufbau; die übrigen sind
SaaS-Landingpages mit demselben Skelett — Hero, Logos, Merkmale, Aufforderung. Wer dort nach
Struktur sucht, bekommt Paletten zurück. Für den nächsten Branchenaufbau also: **Aufbau woanders
suchen, Farbe und Schrift gern hier.**

### Der gewählte Aufbau: lamborghini.com

Am lebenden Sitz abgemessen, nicht aus der Beschreibung übernommen:

```
100 vh   Bühne    Bild, eine Aussage, ein Knopf
 26 vh   Band     Bezeichnung + Verweis
113 vh   Bühne
 32 vh   Band
```

Dazu: Hamburger-Navigation auch auf dem Desktop, keine Radien, keine Schatten, Haarlinien, und
**Gelb ausschließlich für die Handlung**.

**Die Bühne trägt, das Band ordnet.** Zwei Bühnen ohne Band dazwischen nehmen einander die Wirkung
— das ist die Regel, an der der Aufbau hängt.

### Was NICHT übernommen wird

- **Der Preis auf dem dritten Bildschirm.** Ein Sportwagen wird nicht nach dem Preis gesucht, ein
  Friseur schon. Zeiten, Adresse und Nummer stehen bei uns auf der ersten Bühne.
- **Durchgehende Versalien.** Im Deutschen wird daraus „ÖFFNUNGSZEITEN" und
  „DATENSCHUTZERKLÄRUNG" — in Versalien passt das auf dem Telefon nicht in eine Zeile. Versalien
  tragen die Bühnentitel und die Bänder, der Rest bleibt gemischt.
- **Video als Bühnengrund.** Kostet LCP und damit die Zusage aus §6.

### Voraussetzung, die noch fehlt

**Dieser Aufbau steht und fällt mit den Fotos.** Mit Platzhaltern ist eine Bühne ein leerer
Kinosaal. Vor dem Livegang braucht Sunshine echte Aufnahmen vom Salon — sonst ist dieser Aufbau
schwächer als der, den er ersetzt hat.
