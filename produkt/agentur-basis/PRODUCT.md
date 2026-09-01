# PRODUCT.md — Agentur Basis

**register: brand**

Die Seite *ist* das Produkt. Ein Betrieb ohne Vertrieb, ohne Newsletter, ohne Funnel — der erste
Eindruck erledigt die ganze Arbeit oder gar keine.

## Was das ist

Ein WordPress-Blocktheme als Standardunterbau für Websites kleiner, inhabergeführter Betriebe in
der Region Köln-Bonn: Friseur, Barbershop, Praxis, Handwerk. **Ein Theme für alle**, die Branche
und die Marke leben in Style Variations und Mustern (`.claude/automatisierung.md`).

Erste reale Anwendung: **Friseur Sunshine**, Frankfurter Str. 11, 53840 Troisdorf. **Ein Salon
für Damen, Herren und Kinder** — kein Barbershop. Das entscheidet Ansprache, Preisliste, Bilder
und Besetzung der Mannschaft, und es wurde am 2026-08-17 vom Founder bestätigt, nachdem die
Seite versehentlich nur Männer angesprochen hatte.

## Wer davorsitzt

Eine physische Szene, weil daran die Theme-Entscheidungen hängen:

> Jemand steht an der Frankfurter Straße, sucht am Telefon einen Friseur — für sich, für den Mann,
> für das Kind am Samstag — und will drei Dinge wissen: Was kostet es, wann habt ihr offen, wie
> rufe ich an.

Daraus folgt, dass Preis, Zeiten und Telefonnummer keine Unterpunkte sind, sondern über dem Falz
stehen. Alles andere kommt danach.

## Markenstimme, drei Wörter

**Direkt · handfest · nachbarschaftlich.**

Kein Dubai-Luxus. Der Betrieb heißt Sunshine und hängt sich ein gelbes Schild an die Fassade — das
ist Schilder-Sprache, nicht Magazin-Sprache. Wer aus einem Friseur an einer Ausfallstraße eine
Hochglanz-Boutique macht, lügt in der ersten Sekunde, und der Gast merkt es beim Hereinkommen.

## Identität, die schon feststand

Nicht von uns gewählt, sondern vom Schild abgelesen — deshalb gilt hier Identitätserhalt vor
jeder Reflex-Ablehnliste:

- **Gelb `#F0E100`**, gemessen am Foto der Fassade
- Die Mechanik der Marke ist ein **gelbes Feld mit schwarzer Schrift**
- Schriftcharakter: **fetter, leicht schmaler Grotesk** — Schildschrift

Diese Mechanik trägt die Handlung: gelbe Fläche, schwarze Schrift darauf — der Terminknopf, die
Telefonnummer, die Nummer in der Statusleiste. Nicht mehr ganze Abschnitte (siehe „Der Aufbau").

## Die Flächen — hell und dunkel im Wechsel, seit 2026-08-17

Bis dahin lief die Seite auf Schwarz. Die Begründung war die Bushaltestelle am Abend: dunkler
Bildschirm, halbe Helligkeit. **Der Founder hat die Gestaltung abgelehnt**, und die Begründung
hielt der Nachfrage ohnehin nicht stand — sie war eine Vermutung über eine Tageszeit, keine
Messung, und sie hat den Rest der Kundschaft gekostet: Schwarz plus Plakattypografie erzählt einen
Männerbetrieb. Genau diesen Fehler hatten wir in den Texten schon einmal korrigiert und in der
Farbe stehen lassen.

Geblieben ist aus diesem Schritt die Farbmechanik; der Aufbau kam einen Schritt später dazu und
brachte die dunkle Fläche als **Bühne** zurück — nicht als Grundton der Seite, sondern als
Gegenstück zu den hellen Inhaltsflächen.

| | |
|---|---|
| Heller Grund | **`#F2F2F0`** — die ruhigen Inhaltsflächen |
| Dunkle Bühne | **`#0D0D0D`** — die Bildbühnen |
| Schrift | **Tinte `#14140F`** für Überschriften, `#44443C` im Fließtext |
| Akzent | **genau eine Farbe**, und sie liegt als **Fläche** unter schwarzer Schrift |

**Die eine Regel, an der alles hängt: Gelb ist Fläche, nie Schrift — auf hellem Grund.** Auf der
dunklen Bühne trägt Gelb sehr wohl Schrift; dort ist der Grund schwarz. Auf Creme hat `#F0E100` als
Schriftfarbe 1,24:1 und verschwindet; als Fläche mit Tinte darauf sind es 13,61:1. Beim Umbau war
das an sechs Stellen falsch — Etiketten, Statusleiste, Strichverweise, Sprungmarke. Wer hier etwas
ergänzt, prüft diese eine Sache zuerst.

## Der Aufbau — Bühnen und Bänder, seit 2026-08-17

Der Wechsel auf Hell hatte nur die Farben getauscht; die Seite blieb ein Prospekt aus sechs gleich
gebauten Abschnitten. Neuer Aufbau nach **lamborghini.com** (`.claude/design-referenzen.md`):

```
KOPFZEILE  schwarz, geht in die erste Bühne über           dunkel
BÜHNE      volle Höhe, ein Bild, eine Aussage, ein Knopf   dunkel
FLÄCHE     Preise                                           hell
BÜHNE      eine Leistung                                    dunkel
FLÄCHE     Mannschaft                                       hell
WAND       Unsere Arbeiten — Galerie                        dunkel
FLÄCHE     Bewertungen                                      hell
BÜHNE      Schluss: die Handlung, kein Bild                 dunkel
```

**Die Bühne trägt, das Band ordnet.** Zwei Bühnen ohne Band dazwischen nehmen einander die Wirkung.

**Gelb gehört der Handlung.** Knopf, Telefonnummer, Statusleiste — sonst nichts. Eine ganze Fläche
in der Markenfarbe nimmt dem Knopf die Auszeichnung: wenn alles gelb ist, ist nichts gelb.

**Die Galerie zeigt Ergebnisse, nicht Stimmung.** Ein Friseur verkauft etwas Sichtbares; die Seite
kam bis 2026-08-17 ohne ein einziges Ergebnis aus. Ein Hauptstück, vier kleinere daneben — vier
gleich große Kacheln behaupten, alle Aufnahmen seien gleich gut.

**Auf der Schlussbühne ist die Telefonnummer die Schrift.** Sie stand dort als Feld von 190 px
Breite unter einer Überschrift von 68 px — der Satz über der Handlung war größer als die Handlung.
Jetzt läuft die Nummer auf bis zu 152 px und ist das größte Element der Seite; die Aufforderung
steht klein darüber. Regel für jeden weiteren Abschnitt: **das größte Element ist das, was getan
werden soll**, nicht die Ansage, dass etwas getan werden soll.

**Was im Fuß steht, steht nicht 100 px darüber noch einmal.** Die Schlussbühne trug Zeiten,
Anfahrt und einen Füllblock in drei Spalten — und direkt darunter wiederholte der Fuß dieselben
Angaben. Geblieben sind zwei Felder, und nur, weil zwei Menüpunkte auf ihre Anker zeigen.

**Der Preis bleibt über der Falz.** Die Vorlage schickt ihn auf den dritten Bildschirm; ein
Sportwagen wird nicht nach dem Preis gesucht, ein Friseur schon. Zeiten, Adresse und Nummer stehen
auf der ersten Bühne — wer sie wegräumt, nimmt der Seite ihren Zweck.

**Dieser Aufbau steht und fällt mit den Fotos.** Mit Platzhaltern ist eine Bühne ein leerer
Kinosaal. Das ist die bewusst eingegangene Wette; ohne echte Aufnahmen vom Salon ist er schwächer
als der, den er ersetzt hat.

## Register-Entscheidungen

| | |
|---|---|
| Ästhetik-Lane | **Bühne und Band** — Kinoauftritt, kein Prospekt |
| Farbstrategie | **Gelb gehört der Handlung** — Knopf, Nummer, Statusleiste. Keine gelbe Fläche mehr |
| Schrift | **eine Familie**, Archivo, von 68 px Titel bis 13 px Etikett. Dieselbe Familie zeichnet die Wortmarke |
| Form | **hart** — keine Radien, keine Schatten, Haarlinien. Trennung über Fläche und Ton |
| Bild | Schwarzweiß, damit die einzige Farbe der Seite das Gelb der Marke bleibt |

## Was hier nicht gebaut wird

- Keine Anzeigen-Antiqua als Zweitschrift. Playfair Display, Cormorant und Verwandte stehen auf der
  Reflex-Ablehnliste, und die Seite hat keinen Magazin-Auftrag.
- Keine gleich großen Karten in Dreierreihe. Ein Friseur hat eine **Preisliste**, keine Kacheln —
  und sie ist nach Damen, Herren und Kindern gegliedert, weil sonst die Hälfte der Kundschaft
  nicht weiterliest.
- Keine reine Barbershop-Bildsprache. Lederstühle, Rasiermesser und heißes Tuch erzählen einen
  Männerbetrieb; dieser ist keiner.
- Kein Karussell, keine Scroll-Animation auf jedem Abschnitt.
- Keine kleine gesperrte Versalzeile über jeder Überschrift.
- **Kein Gelb als Schriftfarbe.** Auf dem cremefarbenen Grund 1,24:1. Es liegt als Fläche unter
  der Schrift oder gar nicht.
- **Keine Schatten.** Ebenen werden über Ton getrennt, wie in der Vorlage. Ein Schatten auf Creme
  sieht nach Baukasten aus.
- **Kein Dimmen über `opacity`.** Kleingedrucktes bekommt eine ausdrückliche Farbe. Deckkraft
  mischt in das, was zufällig dahinter liegt, und ist damit nicht prüfbar — beim Wechsel auf Hell
  fielen genau so zwei Stellen unter 4,5:1.

## Harte Rahmenbedingungen

Aus `CLAUDE.md`, nicht verhandelbar:

- Lighthouse mobil ≥ 90 in Performance, Accessibility, SEO (§6)
- Kein externer Aufruf beim Seitenaufruf, Schriften lokal (§2.1)
- Impressum und Datenschutzerklärung von jeder Seite erreichbar (§2.1)
- Der Inhaber pflegt die Seite selbst: schmale Bearbeitungsfläche, gesperrte Layouts
