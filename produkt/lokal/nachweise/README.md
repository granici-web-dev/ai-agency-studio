# Nachweise — erster Durchlauf des Basis-Themes

Aufgenommen am 2026-08-17 gegen die lokale Installation auf `http://localhost:8080`,
WordPress 6.8.3, Theme `agentur-basis` 0.1.0.

## Lighthouse

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| Mobil (das Tor aus CLAUDE.md §6) | **100** | **100** | **100** | 92 |
| Desktop | 100 | 98 | 100 | 92 |

FCP 1,2 s · LCP 1,5 s · TBT 0 ms · CLS 0

Offen: **keine Meta-Description.** WordPress gibt von sich aus keine aus; das gehört
in das begleitende Must-Use-Plugin. 92 liegt über der Schwelle aus §6, der Punkt bleibt
trotzdem auf der Liste.

## Screenshots

| Datei | Was zu sehen ist |
|---|---|
| `01-erster-aufbau-1440-defekt.png` | **Der Fehler**, bevor er gefunden war: Inhalt auf 760 px eingeklemmt, Überschrift mitten im Wort gebrochen |
| `02-nach-layoutkorrektur-1440.png` | dasselbe nach der Korrektur an den Vorlagen |
| `03-mannschaft-und-fuss-1440.png` | Mitarbeiterkarten mit eigenem Terminknopf, Fuß mit Impressum und Datenschutz |
| `04-mobil-390.png` | Mobil: Menü als Klappe, Terminknopf bleibt sichtbar |

Der Fehlerstand ist absichtlich aufgehoben. Er zeigt, warum Schritt 2 des Plans
(`.claude/automatisierung.md`) ein QA-Skript ist und keine Sichtprüfung: im Markup war
nichts zu sehen, im Browser sofort.

Die vollständige Belegreihe nach §6 — 360 / 768 / 1440, Formulartests, Mitschnitt
externer Aufrufe — erzeugt das QA-Skript, nicht die Hand.

---

## Zweiter Durchlauf — echte Daten und Bilder, 2026-08-17

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| Mobil | **99** | **100** | **100** | 92 |

LCP 2,2 s · TBT 0 ms · CLS 0 · Seitengewicht **232 KiB**

Der Punkt Performance ging von 100 auf 98 zurück, als die Bilder dazukamen: das Hero-Bild
ist der LCP der Seite und wurde ohne `srcset` auch auf dem Telefon in voller Größe geladen.
Mit drei Breiten, `sizes`, `fetchpriority="high"` und ohne Lazy-Loading steht es bei 99.

`05-endstand-hero-1440.png` · `06-endstand-kontakt-1440.png` · `lighthouse-mobil-mit-bildern.json`

---

## Dritter Durchlauf — Marke des Kunden, 2026-08-17

Logo, Farbe und Betriebsdaten von **Friseur Sunshine**, Frankfurter Str. 11, 53840 Troisdorf.

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| Mobil | 98 | **100** | **100** | 92 |

Seitengewicht 215 KiB · Farbkontrast-Prüfung bestanden.

- `07-logo-svg-nachbau.png` — der Nachbau der Wortmarke in drei Größen und auf hellem Grund
- `08-marke-und-gelb-1440.png` · `09-leistungen-gelb-1440.png`

---

## Vierter Durchlauf — Typografie und Farbstrategie, 2026-08-17

Ausgelöst durch den Befund des Founders: „die Hierarchie der Schriftgrößen sieht aus wie
visuelles Rauschen". Geprüft mit den Skills `ui-ux-pro-max` und `impeccable`.

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| Mobil | 99 | **100** | **100** | 92 |

LCP 2,0 s · CLS 0 · **153 KiB** (vorher 215)

- `10-typo-umbau-hero-1440.png` — nach dem Schriftwechsel, Titel noch zu groß für die Spalte
- `11-kontrastfehler-auf-gelb-1440.png` — **der Fehler**: heller Text auf Gelb, rund 1,5:1
- `12-hierarchie-berichtigt-1440.png` — Titelspalte und Titelstufe angepasst
- `13-endstand-gelb-1440.png` · `14-endstand-mobil-390.png`

---

## Fünfter Durchlauf — Marke als Style Variation, 2026-08-17

Das Gelb lag bis dahin in der Basispalette des Themes; jeder weitere Kunde hätte es geerbt.
Jetzt steht es in `styles/sunshine.json`, die Basis ist markenneutral.

| Datei | |
|---|---|
| `15-basis-ohne-variante-1440.png` | Theme ohne Variante — Akzent `#E9E5DD`, kein Gelb, auch nicht im Logo |
| `16-variante-sunshine-1440.png` | Variante Sunshine aktiv — identisches Layout, nur der Akzent wechselt |

Lighthouse mobil mit aktiver Variante: 99 / 100 / 100 / 92 · 153 KiB · Kontrastprüfung bestanden.

Umschalten:

```
docker compose run --rm wpcli eval-file /opt/skripte/variante-aktivieren.php sunshine
docker compose run --rm wpcli eval-file /opt/skripte/variante-aktivieren.php        # Basis
```

---

## Sechster Durchlauf — Salon für Damen, Herren und Kinder, 2026-08-17

Der Founder hat die Positionierung bestätigt: **kein Barbershop, ein Salon für alle.** Die Seite
sprach bis dahin nur Männer an, weil die Referenzen abgeschrieben worden waren statt der Betrieb.

Geändert: Titelzeile, Preisliste (jetzt nach Damen / Herren / Kindern gegliedert), Abschnitt
„Farbe und Strähnen" statt Herrenschnitt, gemischte Besetzung der Mannschaft, zwei neu erzeugte
Bilder (Salon statt Barbershop, Farbarbeit statt Bartschnitt), Musterkategorie
`agentur-barbershop` → `agentur-friseur`.

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| Mobil | 99 | **100** | **100** | 92 |

LCP 1,8 s · **125 KiB** · Kontrast und Überschriftenreihenfolge bestanden.

`17-salon-fuer-alle-1440.png`

---

## Siebter Durchlauf — Korrekturen aus der Kritik, 2026-08-17

`/impeccable critique` mit zwei isolierten Unteragenten. Ergebnis 24/40 (Acceptable),
ein P0, vier P1, ein P2. Snapshot: `produkt/agentur-basis/.impeccable/critique/`.

Behoben:

- **P0** — die Farbumkehr auf der Markenfläche hing an einer handgeschriebenen Klasse. Eine
  Gruppe, der jemand im Editor nur den Akzent als Hintergrund gibt, hatte 1,20:1. Die Regel
  hängt jetzt am Hintergrundklassennamen: dieselbe Probe ergibt **13,58:1**.
- **P1** — Öffnungszeiten, Adresse und wählbare Telefonnummer stehen als Statusleiste unter der
  h1 statt bei 77 % Scrolltiefe.
- **P1** — die Nummer steht als schwarzes Feld mit 44px statt als 13px-Knopfbeschriftung.
- **P1** — Sie-Form durchgehend; „Termin vereinbaren" auf „Termin buchen" vereinheitlicht.
- **P1** — Mannschaftsüberschrift von 68px auf 44px, sichtbares „Fotos folgen", und die Auswahl
  eines Mitarbeiters wird bis zum Anruf durchgetragen; dazu ein Weg für „egal bei wem".
- Entwickler-Platzhalter im Fuß ersetzt, Firmierung im Copyright.

Beim Umbau selbst eingebaut und wieder behoben: die Nummer war kurzzeitig ein blanker Verweis
und damit **Gelb auf Gelb, 1,0:1**. Verweise auf Markenflächen sind jetzt generell abgesichert.

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| Mobil | 99 | **100** | **100** | 92 |

126 KiB · LCP 2,0 s · Kontrast und Überschriftenreihenfolge bestanden.

Offen, weil ohne den Inhaber nicht lösbar: echte Fotos, bestätigte Preise, Google-Bewertung (P2).

---

## Achter Durchlauf — Termin- und Mannschaftsabschnitt, 2026-08-17

Auf Befund des Founders: „die Typografie im Termin-Abschnitt ist schlecht, und unter den Fotos
stimmen die Abstände nicht".

**Termin-Abschnitt.** Der Absatz lief auf 22 px mit Zeilenhöhe 1,68 in einer Spalte von rund
45 Zeichen, mit Silbentrennung — im wichtigsten Satz der Seite stand „eintei-len". Jetzt: Vorspann
auf 1,25 ohne Trennung, der praktische Nachsatz wandert in eine Leiste. Zeiten, Anfahrt und der
Hinweis „Ohne Termin" stehen als drei gleichrangige Spalten über die volle Breite unter beiden
Spalten, abgetrennt durch eine Linie. Damit ist auch die tote Ecke unten links weg und die Nähe
stimmt: die Bezeichnung klebt an ihrem Wert (0,55 rem), nicht am Block darüber.

**Mannschaft.** Der Zuschnitt lag bei 3:4, also 555 px hohe leere Flächen. Erster Versuch mit 4:5
brachte 35 px — ein Rechenfehler, ein Porträt wird kürzer, wenn das Verhältnis *größer* wird.
Jetzt 1:1, also 416 px. Der Rhythmus unter dem Bild war dreimal derselbe Abstand und bildete
deshalb keine Gruppen; jetzt 22 / 10 / 28 px: Name und Angaben gehören zusammen, der Knopf steht
abgesetzt. Die Angaben (Erfahrung, Sprachen, Schwerpunkte) standen auf 13 px als schwächste Zeilen
der Karte und stehen jetzt auf Fließtextgröße — sie sind das, was diesen Salon von einer Kette
unterscheidet.

Nebenbefund: WordPress gibt Preset-Größenklassen mit `!important` aus. Eine Klasse im Stylesheet
gewinnt dagegen nicht — die Größenangabe muss aus dem Block heraus, nicht im CSS überschrieben
werden.

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| Mobil | 99 | **100** | **100** | 92 |

127 KiB · LCP 1,8 s · CLS 0 · Kontrast und Überschriftenreihenfolge bestanden.

`20-gelb-typografie-neu-1440.png` · `21-team-rhythmus-neu-1440.png` · `22-gelb-mobil-390.png`

---

## Neunter Durchlauf — Abschnittsabstände, 2026-08-17

Founder: „die vertikalen Paddings zwischen den Abschnitten etwas größer".

Beim Nachmessen kam ein anderer Befund heraus: die Abschnitte liefen mit **36 px** Padding, nicht
mit den 10rem, die in `theme.json` standen. **WordPress benutzt die `spacingSizes` des Themes
nicht** — der Kern erzeugt aus `settings.spacing.spacingScale` eine eigene Leiter mit denselben
Slugs und schreibt die Theme-Ebene dabei leer. Geprüft über
`WP_Theme_JSON_Resolver::get_merged_data()`: die Herkunft „theme" kommt leer heraus, sowohl mit
`spacingScale: { steps: 0 }` als auch ganz ohne den Schlüssel.

Im Markup steht die richtige Variable, sie löst nur zu etwas anderem auf — deshalb fällt das beim
Lesen des Codes nicht auf, sondern erst beim Messen im Browser.

Behoben, indem die sechs Variablen in `style.css` unter `:root:root` gesetzt werden (doppeltes
`:root`, damit die Regel die Inline-Ausgabe der Global Styles unabhängig von der Reihenfolge der
Stylesheets schlägt). Der Zickzack-Abschnitt lief außerdem auf einer kleineren Stufe als die
übrigen und liegt jetzt auf derselben.

Abstand zwischen allen Abschnitten: **72 px → 120 px**, gleichmäßig.

Lighthouse mobil unverändert bei 99 / 100 / 100 / 92.

---

## Zehnter Durchlauf — Bewertungen, 2026-08-17

Schließt den P2-Befund aus der Kritik („null Sozialnachweis auf einer Persuade-Fläche").

**Kein Google-Widget.** Widget und Places-API im Browser laden von fremden Servern, setzen Cookies
und brauchen eine Einwilligung vor dem Laden — dieselbe Rechnung wie bei der eingebetteten Karte.
Note, Anzahl und Zitate stehen im Inhalt; der Verweis auf das Profil öffnet erst auf Klick.
Nachgemessen: weiterhin **null externe Aufrufe**.

**Platzhalter mit Absicht.** Erfundene oder geschönte Bewertungen stehen seit 2022 ausdrücklich im
Anhang zu § 3 UWG und sind abmahnbar. Deshalb `[[…]]` statt plausibel klingender Sätze — so kann
der Abschnitt nicht versehentlich mit Erfundenem live gehen. Vor dem Livegang wörtlich aus dem
echten Profil übernehmen.

**Keine AggregateRating-Auszeichnung.** Für den eigenen Betrieb auf der eigenen Seite ist sie von
Google nicht vorgesehen und kann eine manuelle Maßnahme auslösen. Die Sterne sind Gestaltung.

Sterne als Inline-SVG mit `clipPath` auf den Notenanteil, Farbe aus der Palette — also folgt sie
der Style Variation.

| | Performance | Accessibility | Best Practices | SEO |
|---|---|---|---|---|
| Mobil | 99 | 100 | 100 | 92 |

`24-bewertungen-1440.png`

### Nachtrag: echte Google-Zahlen, 2026-08-17

Vom Founder verlinkte Rezensionsübersicht ausgewertet. Abgelesen:

| | |
|---|---|
| Note | **4,5 / 5** |
| Anzahl | **234 Rezensionen** |
| Name bei Google | **Barber SUNSHINE**, Frankfurter Str. 11, 53840 Troisdorf |
| Facebook | „Friseur Sunshine", 4,3 (82) |
| Instagram | @friseursunshine — „Barbier | Coiffeur, Mo–Sa 09–20 Uhr" |

Die Öffnungszeiten aus dem Instagram-Profil bestätigen unabhängig, was der Founder genannt hat.

**Die Rezensionstexte konnten nicht gelesen werden** — die Liste lädt bei Google erst hinter der
Einwilligung nach. Zitate bleiben `[[Platzhalter]]`.

Note und Anzahl stehen mit **sichtbarem Stand-Datum** auf der Seite: beide Zahlen bewegen sich, und
wer sie ein Jahr später ungeprüft stehen lässt, wirbt mit Zahlen, die nicht mehr stimmen.

Der Verweis nutzt das dokumentierte, sitzungsunabhängige Kartenformat
(`maps/search/?api=1&query=…`) statt der Such-URL mit Sitzungsparametern. Besser wäre der Kurzlink
aus dem Unternehmensprofil des Inhabers — beim Kunden zu erfragen.

**Offene Frage an den Kunden:** Der Betrieb heißt bei Google **Barber SUNSHINE**, auf dem Schild und
bei Facebook **Friseur Sunshine**. Für die lokale Auffindbarkeit sollten Profilname, Seitentitel und
Impressum denselben Namen tragen.

---

## Durchgang 12 — Betriebsmodul, Google-Anbindung (2026-08-17)

Nachweis: `27-bewertungen-aus-modul.png`, `lighthouse-mobil-mu-modul.json`

Erstes mu-plugin der Agentur: `produkt/agentur-mu/`. Die Bewertungen kommen jetzt aus einer Option,
die ein täglicher Cronjob **auf dem Server** mit der Places API abgleicht. Der Browser des Besuchers
spricht nie mit Google.

**Lighthouse mobil unverändert: 99 / 100 / 100 / 92, 129 KiB, 0 externe Aufrufe.** Das war der
eigentliche Prüfpunkt — eine Anbindung, die die Zusage aus §2.1 bricht, wäre wertlos gewesen.

### Was geprüft wurde

`docker compose run --rm wpcli eval-file /opt/skripte/bewertungen-pruefen.php` — **30 Prüfungen,
alle bestanden.** Geprüft werden ausdrücklich die Wege, die im Normalbetrieb nie laufen und deshalb
still kaputtgehen:

| Bereich | Prüfung |
|---|---|
| Nicht eingerichtet | kein Abgleich, kein Zeitplan, keine Fehlerzeile |
| Namen | „Maria Schneider" → „Maria S.", Umlaute überleben, Filter schaltet ab |
| Antwort auswerten | `originalText` schlägt die Übersetzung · Bewertung ohne Text fällt raus · **Zwei-Sterne-Bewertung bleibt drin** · Reihenfolge unverändert · nie mehr als fünf · kein Feld für ein Profilbild |
| 30-Tage-Grenze | zu alter Bestand gilt nicht mehr **und wird gelöscht** · Seite fällt auf die Handarbeit zurück und zeigt weiter eine Note |
| Fehlerfall | Note bleibt stehen, Fehler wird vermerkt, **kein Schlüssel im Vermerk** |
| § 5b Abs. 3 UWG | Wortlaut unterscheidet API („die Auswahl trifft Google") von Handarbeit („Ausgewählte Bewertungen") |

Zusätzlich am lebenden System: ohne gepflegte Daten **verschwindet der Abschnitt vollständig**
(HTTP 200, keine PHP-Meldung). Ein Block mit leeren Sternen und „0 Bewertungen" wäre schlechter als
keiner.

### Ein Fehler, der beim Bauen auffiel

Die Pflichtangabe nach § 5b Abs. 3 UWG stand zuerst **mittig** unter den Zitaten statt links. Grund:
die constrained-Layoutregel von WordPress zentriert jedes direkte Kind mit einer `max-width` per
`margin-inline: auto !important`. Mit Spezifität ist dagegen nichts auszurichten — der Absatz steckt
jetzt in einer Gruppe mit gewöhnlichem Fluss. Kontrast der Angabe gemessen: **5,80:1** bei 13 px.

### Was noch fehlt

- **Google-Cloud-Konto und API-Schlüssel.** Ohne beides läuft das Modul im Handbetrieb — das ist
  der gewollte Auslieferungszustand, keine Baustelle. Freikontingente vor dem Anschluss prüfen,
  Google hat die Tarife im März 2025 umgestellt.
- **Place ID des Betriebs**, aus dem Unternehmensprofil.
- **Entscheidung zur Namenskürzung** durch `german-legal-compliance`: Datenminimierung gegen die
  Places-Richtlinie zur Urheberangabe. Voreinstellung ist die Kürzung.
- **Die drei Zitate wörtlich** — weiter offen, siehe Durchgang 11.

---

## Durchgang 13 — Meta-Description und Open Graph (2026-08-17)

Nachweis: `lighthouse-mobil-meta.json`, `lighthouse-mobil-meta-impressum.json`

**SEO 92 → 100.** Die fehlende Meta-Description war der einzige offene Punkt; alle anderen
SEO-Prüfungen standen schon auf grün. Gesamtbild jetzt:

| | Start | Impressum |
|---|---|---|
| Performance | 99 | — |
| Accessibility | 100 | — |
| Best Practices | 100 | — |
| **SEO** | **100** | **100** |

129 KiB, **0 externe Aufrufe** — unverändert. Zweites Modul im mu-plugin, kein SEO-Plugin.

### Was geprüft wurde

**Am lebenden System, nicht nur im Code:**

- Alle drei Seiten geben `description` + 8 OG-Tags aus, jede mit eigenem Text und eigener URL
- **Rückweg über den Blockeditor vollständig durchgespielt:** Panel gefunden → Feld gelesen
  (130 Zeichen, Zähler grün) → Text geändert → `savePost()` → in der Datenbank angekommen → im
  Frontend ausgegeben. `show_in_rest` und `auth_callback` sind genau die Stellen, an denen so ein
  Feld sonst still ins Leere speichert
- Keine JavaScript-Fehler in der Konsole des Editors
- Befunde der Prüfung: `zu lang` bei 319 Zeichen, `sehr kurz` bei 8, `ok` dazwischen
- Ausgabe wird an der **Wortgrenze** gekürzt: 319 → 154 Zeichen, endet auf „…niemand…"

### Zwei Fehler, die beim Bauen auffielen

**`og:locale` stand auf `de` statt `de_DE`.** `get_bloginfo('language')` liefert den Wert für das
`lang`-Attribut, Open Graph erwartet Sprache_REGION. Bei einem ungültigen Wert fällt Facebook
kommentarlos auf `en_US` zurück — die geteilte Seite hätte englisch ausgesehen, ohne dass irgendwo
eine Fehlermeldung erscheint. Auf `get_locale()` umgestellt.

**Zu lange Texte gingen ungekürzt in den `<head>`.** Ein 319-Zeichen-Satz stand vollständig im Tag.
Jetzt wird beim **Ausgeben** gekürzt und beim **Speichern** nicht: im Editor bleibt der volle Text
stehen und der Zähler warnt, damit ein Mensch kürzt.

### Offen

- **Sprachgate §2.2.** Die drei Beschreibungen sind kundenseitiger deutscher Text. Sie brauchen
  `german-language-tone` und einen benannten deutschsprachigen Prüfer. Gesetzt ist nicht freigegeben.
- **OG-Bild ist ein Platzhalter** — aus `interieur.webp` auf 1200×630 beschnitten. Ersetzen, sobald
  echte Fotos da sind. Als JPEG, weil der Crawler von Facebook kein WebP liest.

---

## Durchgang 14 — Rolle „Inhaber" (2026-08-17)

Nachweis: `28-inhaber-startbildschirm.png`, `lighthouse-mobil-rollen.json`

Drittes Modul im mu-plugin. **34 Prüfungen, alle bestanden** — nachdem der erste Lauf einen echten
Befund geliefert hat, siehe unten. Lighthouse unverändert: 99 / 100 / 100 / 100, 129 KiB,
0 externe Aufrufe.

Der Inhaber sieht im Backend nur noch: Dashboard, Beiträge, Medien, Seiten, Kommentare, Profil.
Kein Design, keine Plugins, keine Benutzer, keine Einstellungen, keine Werkzeuge.

### Der Befund, der den Test gerechtfertigt hat

**Der erste Prüflauf hat das echte Impressum gelöscht.** Die Sperre hing allein an `map_meta_cap` —
und `wp_trash_post()` sowie `wp_delete_post()` prüfen selbst **keine Berechtigungen**. Geprüft wird
eine Ebene darüber, im Backend und in der REST-Schnittstelle. Ein Direktaufruf geht daran vorbei.

Der Kommentar im Modul behauptete zu diesem Zeitpunkt das Gegenteil („so greift die Sperre auch
über WP-CLI und jeden anderen Weg"). Beides korrigiert: zweite Sperre in `pre_trash_post` und
`pre_delete_post`, Kommentar richtiggestellt.

Zweite Lehre: der Test lief gegen die **echte** Seite. Jetzt legt er eine Wegwerfseite an, trägt
sie vorübergehend als Pflichtseite ein und zerstört nur die. Ein Test, der bei einem Befund die
Daten mitnimmt, ist schlechter als keiner.

### Was geprüft wurde

| Abschnitt | Inhalt |
|---|---|
| 1 | 11 Berechtigungen, die fehlen müssen — Theme, Site-Editor, Plugins, Benutzer, Einstellungen, Update, Export, `unfiltered_html` |
| 2 | was er **darf**: Seiten bearbeiten, veröffentlichen, Bilder hochladen, Pflichtseiten bearbeiten aber nicht löschen |
| 3 | Direktaufruf `wp_trash_post()` und `wp_delete_post()` prallen ab |
| 4 | Permalink bleibt, Veröffentlichung bleibt, **Titel darf sich ändern** |
| 5 | `<script>`, `<iframe>` und `onclick` werden beim Speichern entfernt, der Text bleibt (§2.1) |
| 6 | gewöhnliche Seiten darf er löschen — eine Rolle, die nichts zulässt, ist genauso kaputt |
| 7 | der Administrator kommt weiterhin durch |
| 8 | die echten Pflichtseiten stehen nach dem Lauf unversehrt |

### Nebenbefund: die Startseite friert beim ersten Speichern ein

Im Editor der Startseite gemessen, ohne zu speichern:

- **Der handgetunte Code bleibt erhalten.** `srcset`, `sizes`, `fetchpriority` und `width/height`
  am LCP-Bild überstehen den Durchlauf — WordPress behält ungültige Blöcke wörtlich, statt sie neu
  zu erzeugen. Das war die Sorge, und sie hat sich nicht bestätigt.
- **Aber:** der gespeicherte Inhalt wüchse von **358 Byte auf 26 439 Byte**. Die Musterverweise
  (`<!-- wp:pattern … /-->`) werden zu vollem Markup ausgeschrieben. Ab dem ersten Speichern folgt
  die Startseite des Kunden nicht mehr den Mustern des Themes — eine spätere Korrektur im Theme
  erreicht diese Seite nicht mehr.
- 8 Blöcke gelten als `isValid: false`. **Der Inhaber sieht davon nichts**, die
  `contentOnly`-Sperre unterdrückt die Warnungen. Ursachen: HTML-Kommentare innerhalb des
  Blockmarkups, fehlendes `wp-block-group` an handgeschriebenen `div`s, eine Abweichung zwischen
  Inline-Style und Blockattribut bei `core/columns`, und die von Hand ergänzten Bildattribute.

Das ist eine **Produktentscheidung, keine Fehlerbehebung**: entweder das Einfrieren akzeptieren,
oder die Startseite für den Inhaber gar nicht editierbar machen und ihm stattdessen benannte Felder
geben. Nicht nebenbei entschieden.

---

## Durchgang 15 — „Ihre Angaben", Startseite geschlossen (2026-08-17)

Nachweis: `29-ihre-angaben.png`, `30-startseite-aus-angaben.png`, `lighthouse-mobil-angaben.json`

**49 Prüfungen, alle bestanden.** Lighthouse: 99 / 100 / 100 / 100, 130 KiB, 0 externe Aufrufe.
Die Startseite sieht unverändert aus — sie kommt nur aus einer anderen Quelle.

Für den Inhaber ist sie zu: in der Seitenübersicht steht bei ihr „Wird über ‚Ihre Angaben' gepflegt",
der Bearbeiten-Verweis fehlt, der Direktaufruf endet mit „Du bist leider nicht berechtigt". Die
Musterverweise bleiben auch bei einem `wp_update_post()`-Direktaufruf stehen.

### Der Nachweis, um den es ging

Telefonnummer **einmal** geändert → erscheint an allen fünf Stellen der Seite neu, der `tel:`-Verweis
zieht mit, die alte Nummer taucht nirgends mehr auf. Vorher stand sie dreimal fest im Theme.

Formular-Rundlauf im Browser als Inhaber durchgespielt: Preiszeile hinzugefügt („Pony schneiden,
8 €") → erscheint auf der Seite. Mannschaftszeile entfernt → Deniz verschwindet. Etikett geleert →
„Fotos folgen" verschwindet, genau wie im Feldhinweis angekündigt.

### Drei Fehler beim Bauen — zwei davon im Prüfwerkzeug selbst

**1. Der Zähler des Prüfskripts hat nicht gezählt.** `wp eval-file` führt den Inhalt innerhalb
einer Funktion aus; ein `$fehler = 0;` auf scheinbar oberster Ebene ist deshalb **lokal**, während
`global $fehler;` in der Zählfunktion eine andere Variable meint. Der Lauf meldete „alle Prüfungen
bestanden", während zwei danebengingen. Auf statische Zählung umgestellt, in beiden Skripten.

Damit steht auch die Zahl aus Durchgang 14 unter Vorbehalt: dort war jede einzelne Zeile sichtbar
„ok", das Ergebnis stimmte also — verlassen konnte man sich darauf aber nicht.

**2. Der Abruf im Test lief ins Leere.** `home_url()` ist `http://localhost:8080`; aus dem
wpcli-Container zeigt „localhost" auf den Container selbst. Der Antwortkörper war leer, und die
Prüfung „alte Nummer nirgends mehr" ging damit als **bestanden** durch. Jetzt über den Dienstnamen
`http://wordpress/`, mit einer vorgeschalteten Prüfung, dass überhaupt etwas angekommen ist.

**3. Die Rolle wäre dauerhaft kaputt geblieben.** Der Versionsstempel wurde unbedingt gesetzt —
auch wenn das Angaben-Modul in dem Moment nicht geladen war und die Rolle das nötige Recht deshalb
nicht bekam. Weil der Stand als erledigt galt, wurde nie nachgebessert. Sichtbar wurde es als 403
auf „Ihre Angaben". Jetzt wird nur gestempelt, wenn der Schreibvorgang vollständig war.

### Noch offen

- **Sprachgate §2.2** für alle Feldbeschriftungen und Hinweistexte im Formular — das ist
  kundenseitiger deutscher Text.
- **Die 8 ungültigen Blöcke** aus Durchgang 14 bestehen weiter. Sie schaden jetzt weniger, weil
  niemand die Startseite mehr im Editor öffnet, aber die Ursachen bleiben: HTML-Kommentare im
  Blockmarkup, fehlendes `wp-block-group` an handgeschriebenen `div`s, Abweichung zwischen
  Inline-Style und Blockattribut.

---

## Durchgang 16 — die 8 ungültigen Blöcke (2026-08-17)

Nachweis: `31-nach-blockreparatur.png`, `lighthouse-mobil-bloecke.json`

**0 von 117 Blöcken ungültig** — vorher 8. Zusätzlich geprüft: die Vorlagenteile `header` (8 Blöcke)
und `footer` (17 Blöcke) sind ebenfalls sauber. Keine Konsolenfehler im Editor.

**Lighthouse Performance 99 → 100.** LCP 1,7 s, CLS 0, 128 KiB, 0 externe Aufrufe.

### Vier Ursachen, vier Behandlungen

**1. HTML-Kommentare innerhalb der Blockmarkierungen.** Ein `<!-- -->` ist nicht Teil dessen, was
ein Block beim Speichern erzeugt; die Prüfung schlägt fehl. In PHP-Mustern jetzt PHP-Kommentare
(`<?php /* … */ ?>`) — gleicher Text, an derselben Stelle, ohne Ausgabe. In den statischen
Vorlagenteilen geht das nicht: dort entfernt und der Inhalt nach `produkt/agentur-basis/README.md`
verschoben, Abschnitt „Vorlagenteile".

**2. Fehlendes `wp-block-group` an handgeschriebenen `div`s.** `class="statusleiste"` und
`class="infoleiste"` mussten `class="wp-block-group statusleiste"` heißen.

**3. Abweichung zwischen Blockattribut und Inline-Style.** In `team-karten.php` stand im Attribut
`spacing|40` und im Style `spacing--50`, an anderer Stelle `30` gegen `20`. Gerendert wurde jeweils
der Style — die Seite sah richtig aus, der Block galt als ungültig. Solche Abweichungen entstehen
beim Nachjustieren von Hand.

Dafür gibt es jetzt eine Suche über alle Muster; sie meldete drei Treffer, von denen zwei
Scheintreffer waren (`blockGap` steht nie im Inline-Style).

**4. Von Hand ergänzte Bildattribute.** `srcset`, `sizes`, `width`, `height`, `loading` und
`fetchpriority` standen im Markup, der Bildblock erzeugt sie nicht. Sie kommen jetzt beim **Rendern**
dazu (`agentur_basis_bild_attribute` in `functions.php`) — derselbe Weg, den WordPress mit
`wp_filter_content_tags()` für Mediathek-Bilder geht. Diese Bilder liegen im Theme, also macht es
das Theme.

**Nebenwirkung, gemessen:** der Filter gibt jetzt allen Theme-Bildern Maße, nicht nur dem Hero.
Damit ist `unsized-images` grün und die Performance auf 100. Vorher fehlten sie bei `pflege.webp`
und `farbe.webp`.

### Ein Schreckmoment und was er geklärt hat

Nach der Reparatur fehlte im Bildvergleich das Bild in „Farbe und Strähnen". Live gemessen war es
aber da: geladen, 663 × 415 px, sichtbar. Ursache war der Bildvergleich selbst — der Filter setzt
für Bilder unter dem Falz `loading="lazy"`, und die Vollseitenaufnahme wartet darauf nicht.

Damit ist rückwirkend auch der alte Befund erklärt, die rechte Hälfte des gelben Abschnitts sei
leer: dasselbe Artefakt. **Screenshots vor dem Vergleich durchscrollen**, sonst prüft man die
Ladestrategie statt der Gestaltung.

### Nebenbefund, nicht behoben

Ist der Inhaber angemeldet, lädt die **Adminleiste einen Gravatar von secure.gravatar.com** — ein
externer Aufruf mit dem Hash seiner E-Mail-Adresse. Besucher sind nicht betroffen (Lighthouse ohne
Anmeldung: 0 externe Aufrufe), aber §2.1 ist als Zusage weiter gefasst. Zu entscheiden: Avatare
abschalten oder die Adminleiste im Frontend für diese Rolle ausblenden.

---

## Durchgang 17 — Wechsel auf die helle Fläche (2026-08-17)

Nachweis: `32-hell-startseite-1440.png`, `33-hell-startseite-390.png`,
`lighthouse-mobil-hell.json`

Der Founder hat die dunkle Plakatgestaltung abgelehnt. Neue Vorlage nach Vergleich von vier
Kandidaten: **travelperk.com** (`.claude/design-referenzen.md`).

**Lighthouse mobil: 100 / 100 / 100 / 100.** LCP 1,7 s, CLS 0, 130 KiB, 0 externe Aufrufe.
Blockgültigkeit: 0 von 117 ungültig, Vorlagenteile ebenfalls sauber. Alle Modulprüfungen bestanden.

### Was sich geändert hat

| | vorher | jetzt |
|---|---|---|
| Grund | `#141414` Anthrazit | `#F5F5EB` warmes Creme |
| Schrift | hell auf dunkel | `#14140F` Tinte, `#44443C` Fließtext |
| Gelb | Schrift **und** Fläche | **nur Fläche** |
| Form | harte Kanten | Pillen-Knöpfe, 24 px Radien, keine Schatten |
| Terminabschnitt | randlose Bahn | eingerückte Tafel mit 32 px Radius |

Zwei Slugs umbenannt, weil ihre Namen nach dem Wechsel das Gegenteil bedeuteten:
`schrift-hell` → `schrift-stark`, `akzent-hell` → `akzent-tief`. 30 Vorkommen.

### Die Falle, die alles betraf

**Zwanzig Regeln benutzten `Grund` als Schriftfarbe.** Auf Schwarz war das richtig — es hieß
„schwarze Schrift auf gelber Fläche". Auf Creme heißt derselbe Code „cremefarbene Schrift auf
gelber Fläche": **1,24:1**.

Deshalb steht die Umkehr jetzt als Rolle an einer Stelle (`--ton-auf-akzent`). Eine spätere dunkle
Variante ändert einen Block statt zwanzig Regeln.

### Fünf Befunde beim Umbau, alle behoben

1. **Etikett „Damen/Herren/Kinder"** stand gelb auf Creme, 1,24:1. Jetzt als Marke **in** der
   Farbe, schwarze Schrift darauf.
2. **Statusleiste**, **Strichverweis**, **Sprungmarke** — dieselbe Ursache, dieselbe Behandlung.
3. **`.stand` und `.pflichthinweis`** waren über `opacity` gedimmt: 3,14:1 und 4,00:1. Deckkraft
   mischt in das, was dahinterliegt, und ist damit nicht prüfbar. Jetzt ausdrückliche Farbe
   (`--ton-leise`, 5,53:1).
4. **Porträt-Platzhalter** war ein schwarzes Rechteck — auf Creme standen drei schwarze Blöcke im
   Abschnitt „Wer Sie bedient" und zogen alle Aufmerksamkeit auf die Stellen, an denen noch nichts
   ist. Jetzt hell, mit angedeuteter Figur.
5. **Der ausgetauschte Platzhalter blieb schwarz**, obwohl die Datei hell war — Browsercache.
   Behoben mit `agentur_basis_datei()`: Theme-Dateien bekommen die Änderungszeit als
   Versionsstempel. Beim Kunden wäre das ein Bild, das nach dem Austausch wochenlang das alte
   bleibt.

Kontrolle am Ende: DOM-weiter Kontrastdurchlauf über jeden Textknoten in `header`, `main` und
`footer` — **0 Verstöße** gegen 4,5:1 beziehungsweise 3:1 bei großer Schrift.

### Eigener Fehler beim Arbeiten

Eine Änderung ging **halb** durch: `cd verzeichnis && cat >> functions.php` — das `cd` schlug fehl,
die `&&`-Kette brach ab, die Hilfsfunktion wurde nie geschrieben. Das Muster rief sie trotzdem
schon auf, und die Seite lief in einen Fatal Error. Schreibende Befehle laufen ab jetzt mit
absolutem Pfad statt mit vorangestelltem `cd`. Es ist innerhalb dieser Sitzung das zweite Mal.

### Offen

- **Sprachgate §2.2** — an der Gestaltung ändert sich nichts an den Texten, aber die neuen
  Bausteine (Etiketten in der Marke) zeigen dieselben Wörter in anderer Form.
- **Bildsprache** — die Fotos sind noch die dunklen Stimmungsbilder aus der Plakatzeit. Auf Creme
  wirken sie schwer. Vor dem Livegang mit den echten Fotos neu beurteilen.

---

## Durchgang 18 — neuer Aufbau: Bühnen und Bänder (2026-08-17)

Nachweis: `34-buehnen-1440.png`, `35-buehnen-390.png`, `lighthouse-mobil-buehnen.json`

**Befund des Founders zum Durchgang 17: „du hast nur die Farben geändert".** Das stimmte. Der
Wechsel auf Hell war eine Umlackierung — Abschnittsfolge, Raster und Takt blieben, wie sie aus den
Barbershop-Vorlagen gekommen waren: sechsmal Überschrift, Text, Bild daneben.

Neue Vorlage nach Vergleich, diesmal nach **Aufbau** gesucht: **lamborghini.com**.

**Wichtige Einschränkung zur Quelle:** styles.refero.design ordnet nach *visuellem Stil*, nicht
nach Informationsarchitektur. Von acht geprüften Einträgen war Lamborghini der einzige mit einem
strukturell anderen Aufbau; der Rest sind SaaS-Landingpages mit identischem Skelett. Wer dort nach
Struktur sucht, bekommt Paletten.

### Der neue Aufbau

Am lebenden Vorbild abgemessen (Abschnittshöhen in Prozent des Fensters):

| | Lamborghini | jetzt bei uns |
|---|---|---|
| Bühne | 100 vh, Bild, eine Aussage, ein Knopf | 100 svh minus Kopfzeile |
| Band | 26 vh, Bezeichnung + Verweis | Bezeichnung + ein Satz, Haarlinie |
| Wechsel | Bühne · Band · Bühne · Band | dunkle Bühne · helle Fläche · dunkle Bühne · helle Fläche · dunkle Schlussbühne |
| Navigation | Hamburger auch auf dem Desktop | `overlayMenu: always` |
| Formen | keine Radien, keine Schatten | Radien wieder auf 0 |
| Gelb | genau ein Knopf | Knopf, Nummer, Statusleiste — sonst nichts |

**Eine bewusste Abweichung:** Lamborghini schickt den Preis auf den dritten Bildschirm. Ein
Sportwagen wird nicht nach dem Preis gesucht, ein Friseur schon. Deshalb steht die Statusleiste —
Zeiten, Adresse, Nummer — auf der ersten Bühne über der Falz. Steht im Musterkopf, damit sie
niemand als Rest wegräumt.

### Ergebnis

Lighthouse mobil **99 / 100 / 100 / 100**, LCP 1,7 s, CLS 0, 133 KiB, 0 externe Aufrufe.
Blockgültigkeit 0 von 108, Vorlagenteile sauber. DOM-weiter Kontrastdurchlauf: **0 Verstöße** —
der Prüfer rechnet auf Bühnen gegen die Bühnenfarbe, weil der Schleier über dem Foto liegt.

### Vier Befunde beim Umbau

1. **Das Bühnenbild war auf 1280 px begrenzt und zentriert** — links und rechts blitzte der helle
   Grund durch. Die constrained-Layoutregel gibt **jedem** direkten Kind eine max-width und
   zentrierende Ränder mit `!important`, auch einem absolut positionierten Bild. Dieselbe Ursache
   wie bei der Pflichtangabe im Bewertungsteil. Die Bühne läuft jetzt im gewöhnlichen Fluss, die
   Textbreite regelt das Stylesheet.
2. **Die erste Bühne war höher als der Bildschirm** — 100 svh plus klebende Kopfzeile. Der Knopf
   lag unter der Falz. Kopfzeilenhöhe wird abgezogen.
3. **Die Telefonnummer war zweimal gelb auf gelb**, aus zwei verschiedenen Gründen. Der zweite ist
   der lehrreiche: `:not()` erbt die Spezifität seines Arguments, also schlägt
   `.auf-buehne a:not(.wp-block-button__link)` (0,2,1) die eigene Ausnahme
   `.auf-buehne .telefonnummer` (0,2,0). Die Ausnahmeregel für Knöpfe machte die allgemeine Regel
   stärker als die Ausnahme für die Nummer.
4. **Die Schlussbühne hatte keinen Hintergrund** — die Farbe `buehne` stand in `sunshine.json`,
   aber der aktive Benutzer-Global-Styles-Beitrag war vorher geschrieben worden. Eine neue Farbe in
   einer Style Variation erfordert `variante-aktivieren.php` erneut.

### Offen

- **Die Bilder tragen den Aufbau jetzt.** Das ist die angekündigte Kehrseite: mit Platzhaltern ist
  eine Bühne ein leerer Kinosaal. Vor dem Livegang braucht es echte Fotos vom Salon, sonst ist
  dieser Aufbau schwächer als der vorige.
- **Die Preisliste bricht auf breiten Schirmen zweispaltig um**; `break-after: avoid` hält die
  Gruppenüberschrift bei ihrer ersten Position, aber eine Gruppe kann weiterhin über den
  Spaltenumbruch laufen. Bei mehr Positionen prüfen.

---

## Durchgang 19 — schwarze Kopfzeile und Galerie „Unsere Arbeiten" (2026-08-17)

Nachweis: `36-kopf-schwarz-arbeiten-1440.png`, `lighthouse-mobil-arbeiten.json`

**Lighthouse mobil 100 / 100 / 100 / 100**, LCP 1,7 s, CLS 0, 135 KiB, 0 externe Aufrufe.
Blockgültigkeit 0 von 113, Vorlagenteile sauber, DOM-weiter Kontrastdurchlauf 0 Verstöße.

### Kopfzeile

Sie lief auf hellem Grund und stand als heller Streifen über der ersten Bühne — ein Bruch genau da,
wo die Seite anfängt. Jetzt schwarz und damit Fortsetzung der Bühne. Die Umkehrung steht in
`style.css` am Bauteil, nicht in `theme.json`: eine Style Variation soll die Palette ändern können,
ohne dass jemand die Kopfzeile nachzieht.

**Befund dabei:** zwischen Kopfzeile und Bühne klaffte ein **12 px heller Streifen** — der globale
`blockGap` zwischen Vorlagenteil und `main`. Auf hellem Grund unsichtbar, auf schwarzem eine Naht
quer über den Schirm. Die oberste Ebene bekommt jetzt keinen Abstand mehr; die Abschnitte bringen
ihren eigenen mit.

### Galerie

Der Abschnitt, den ein Friseur am nötigsten hat und der bis jetzt fehlte: **was dabei herauskommt.**
Die Seite erzählte Preise, Leute und Meinungen und zeigte kein einziges Ergebnis.

**Dunkel**, aus zwei Gründen: damit geht der Wechsel über die ganze Seite auf —
dunkel · hell · dunkel · hell · **dunkel** · hell · dunkel —, und Bilder auf Schwarz werden als
Galerie gelesen, nicht als Kachelreihe.

**Ein Hauptstück** über zwei Spalten und zwei Zeilen, vier kleinere daneben. Vier gleich große
Kacheln behaupten, alle Aufnahmen seien gleich gut.

Zwei Befunde beim Bauen: die Kacheln kamen als 632×693 und 308×339 heraus — `height: 100 %` am Bild
und eine Bildunterschrift **im** `figure` rechneten gegeneinander. Feste Zeilenhöhe im Raster, und
die Bezeichnung liegt jetzt im Bild statt darunter. Und mit vier Bildern blieb unten rechts ein
Loch; es sind fünf.

### Vor dem Livegang — nicht übersehen

- **Das sind Stimmungsbilder, keine Arbeiten.** Ein Salon, der unter „Unsere Arbeiten" fremde
  Aufnahmen zeigt, wirbt mit fremder Leistung — irreführend nach § 5 UWG. Das Etikett steht
  sichtbar über der Wand, muss aber vor dem Livegang durch echte Bilder ersetzt werden.
- **Erkennbare Kundinnen und Kunden brauchen schriftliche Einwilligung** (§ 22 KUG), bei
  Vorher-Nachher-Aufnahmen ausdrücklich für diesen Zweck. Ein „ja" im Salon reicht nicht.
- Die Galeriebilder liegen noch im Theme, nicht in „Ihre Angaben". Wenn der Inhaber sie selbst
  tauschen soll, gehört die Liste ins Betriebsmodul.

### Nachtrag zu Durchgang 19 — die Wortmarke

Auf dem gelben Feld der Wortmarke stand „FRISEUR" hell statt schwarz. Ursache ist **zum dritten
Mal dieselbe**: das SVG füllte den Schriftzug mit `var(--wp--preset--color--grund)`. Solange
`grund` das dunkle Anthrazit war, hieß das „schwarz auf gelb". Seit `grund` die helle Fläche ist,
heißt derselbe Code „hell auf gelb".

Auf `schrift-stark` umgestellt — die Farbe, die *immer* die dunkle Schriftfarbe meint, unabhängig
davon, ob der Grund hell oder dunkel ist.

**Lehre, die inzwischen dreimal Geld gekostet hat:** `grund` und `schrift` sind Flächen- und
Textfarben, keine Rollen. Wer „die Farbe, die auf der Markenfläche lesbar ist" meint, schreibt
`--ton-auf-akzent` beziehungsweise `schrift-stark` — nie `grund`. Das gilt auch in SVG-Attributen,
und dort sucht niemand danach.

## Durchgang 20 — die Schlussbühne (2026-08-17)

Nachweis: `37-schlussbuehne-1440.png`, `37-schlussbuehne-390.png`

Der Founder hat den Abschnitt „Termin? Rufen Sie einfach an." abgelehnt. Nachgemessen im Browser,
vier Befunde — und alle vier hatten dieselbe Ursache: der Abschnitt war eine Ansage über eine
Handlung, nicht die Handlung.

1. **Die Handlung war das kleinste Element.** Überschrift 68 px, Telefonnummer 36 px in einem Feld
   von 190 px Breite. Jetzt läuft die Nummer auf 152 px über 870 px Breite und ist das größte
   Element der Seite; die Aufforderung steht als 13-px-Zeile darüber.
2. **Der Satz stand mittig im Nichts.** `.vorspann` hatte eine max-width, und die
   constrained-Layoutregel zentriert jedes direkte Kind mit max-width per
   `margin-inline: auto !important`. **Vierter Fall derselben Falle** — nach Pflichthinweis,
   Bühnenbild und Bühnentitel. Behandlung wie dort: der Inhalt liegt jetzt in einer Gruppe im
   normalen Fluss, wo die Regel nicht greift.
3. **Die rechte Hälfte war leer.** Nichts lief über die halbe Breite hinaus.
4. **Zeiten und Adresse standen zweimal untereinander.** Drei Spalten im Abschnitt, keine 100 px
   darunter dieselben Angaben im Fuß. Die dritte Spalte („Ohne Termin — Sie sind ebenso
   willkommen") war Füllmaterial in Spaltenform; der Hinweis ist jetzt ein Halbsatz im Fließtext.
   Geblieben sind zwei Felder — und nur, weil `#zeiten` und `#kontakt` die Ziele zweier Menüpunkte
   sind. Ohne diese Anker liefen zwei Verweise ins Leere.

### Was dabei sonst noch auffiel

- **`em` am falschen Element.** Fläche und Unterstreichung der Nummer standen zuerst am Verweis.
  Der erbt 16 px, also waren `0,16em` rund 2,5 px — ein Rand, den niemand sieht. Sie sitzen jetzt
  am Span mit der 152-px-Schrift. Nebenbei: die Unterstreichung eines `inline-block`-Kindes
  zeichnet das Elternelement ohnehin nicht.
- **Kein `white-space: nowrap` auf der Nummer.** Das ist ein Themebaustein: was für
  „0178 5184291" passt, muss auch für „+49 (0) 2241 1234567" passen. Mit 20 Zeichen auf 360 px
  geprüft — 326 px, kein waagerechtes Scrollen.

### Gemessen

- Blockgültigkeit **0 von 109 ungültig** (Editor, `getBlocks()` rekursiv).
- DOM-weiter Kontrastdurchlauf im Abschnitt: **0 Verstöße**, niedrigster Wert 7,96:1
  (Etiketten, 13 px). Nummer 14,32:1, Fließtext 17,34:1.
- Kein waagerechtes Scrollen bei 360, 390 und 1440 px.

### Offen

- Die Beschriftungen in „Ihre Angaben" sprachen noch vom „gelben Abschnitt" — der ist seit dem
  Bühnenumbau schwarz. Nachgezogen. Alle Feldtexte gehen weiterhin ungeprüft raus: **§2.2 hat
  keinen benannten deutschsprachigen Prüfer**, und ohne den geht nichts an einen Kunden.
