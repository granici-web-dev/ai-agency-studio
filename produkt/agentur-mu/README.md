# Agentur Basis — Betriebsmodul

Das mu-plugin, das neben dem Theme läuft. Hier liegt alles, was **Betrieb** ist:
Anbindungen, Cronjobs, Rollen, Bildverarbeitung. Im Theme liegt nur, was **Aussehen** ist.

Die Trennung ist keine Ordnungsliebe. Ein Theme darf abgeschaltet werden; eine Google-Anbindung
und ein Zeitplan dürfen das nicht. Und dasselbe Modul soll bei jedem Kunden laufen, während sich
die Themes unterscheiden.

`mu-plugins/` lädt WordPress ohne Aktivierung und ohne Möglichkeit zur Deaktivierung im Backend.
Nur PHP-Dateien **direkt** im Ordner werden geladen — `agentur-basis-mu.php` ist deshalb nur der
Einstieg, die Module liegen im gleichnamigen Unterordner und werden dort namentlich eingebunden.
Kein `glob()`: eine neue Datei soll nicht ungefragt in jeder Kundeninstallation aktiv werden.

---

## Modul: Google-Bewertungen

**Was es leistet.** Ein Cronjob holt einmal täglich Note, Anzahl und die von Google gelieferten
Bewertungen über die Places API — **auf dem Server**. Die Seite gibt nur noch Text aus. Der
Besucher spricht nie mit Google, es fällt kein Cookie an, es braucht keinen Einwilligungsbanner.

Das ist der ganze Unterschied zu jedem fertigen Bewertungs-Widget. Die werben mit
„DSGVO-konform" und laden dabei beim Seitenaufruf von einem fremden Server nach. Wer so etwas
einbettet, verkauft das Argument und bricht es im selben Zug.

### Vier Grenzen, die man vorher kennen muss

| | |
|---|---|
| **Höchstens 5 Bewertungen** | Harte Grenze der Places API, in v1 wie in der alten Fassung. Es gibt keinen Parameter für „alle", und Google wählt selbst aus. Wer 234 Bewertungen auf der Seite erwartet, erwartet etwas, das die Schnittstelle nicht kann — bei jedem Anbieter, alle sitzen auf derselben API |
| **Höchstens 30 Tage speichern** | Die Maps-Platform-Bedingungen erlauben dauerhaftes Speichern nur für die Place ID. Deshalb verfällt der Bestand hier hart nach 28 Tagen: läuft der Abgleich länger auf Fehler, werden die Bewertungen **gelöscht** und die Seite fällt auf die gepflegten Angaben zurück |
| **Keine Profilbilder** | Die Bild-URLs zeigen auf `googleusercontent.com`. Einbinden hieße externer Aufruf beim Seitenaufruf; herunterladen verstößt gegen die 30-Tage-Regel. Es bleibt der Name |
| **Google-Cloud-Konto mit Abrechnung** | Ein Aufruf pro Tag, also ~30 im Monat. Google hat die Tarife im März 2025 umgestellt — die Freikontingente **vor dem Anschluss prüfen**, nicht aus dem Gedächtnis annehmen |

### Der Schlüssel

Ausschließlich als Konstante in der `wp-config.php`:

```php
define( 'AGENTUR_GOOGLE_PLACES_KEY', '…' );
```

Nie als Option. Eine Option landet im Export, im Backup und in jedem Datenbank-Dump, den jemand
per Mail verschickt. Der Schlüssel wird auch nicht geloggt — bei einem HTTP-Fehler vermerkt das
Modul nur den Statuscode, weil Google den Schlüssel im Antwortkörper zurückspiegeln kann.

Im Google-Cloud-Konto den Schlüssel auf die Places API **und** auf die Server-IP beschränken.
Ein unbeschränkter Schlüssel ist eine offene Rechnung.

### Einrichten

```bash
wp agentur-bewertungen ort ChIJ…          # Place ID aus dem Unternehmensprofil
wp agentur-bewertungen abgleich           # sofort holen statt auf den Cron warten
wp agentur-bewertungen status             # Quelle, Stand, letzter Fehler
```

Ohne Schlüssel oder ohne Place ID trägt sich **kein Zeitplan** ein. Sonst stünde bei jedem Kunden
ohne Google-Konto ein Cronjob im System, der täglich anläuft, abbricht und eine Zeile ins
Fehlerprotokoll schreibt — nach einem Jahr sucht jemand danach.

### Handbetrieb — der Auslieferungszustand

Ohne Anbindung ist die Seite **nicht kaputt**, sie aktualisiert sich nur nicht selbst:

```bash
wp agentur-bewertungen handarbeit --note=4.5 --anzahl=234 --stand=2026-08-17 \
  --profil='https://g.page/…' \
  --stimmen='[{"text":"…","name":"Vorname N."}]'
```

Handarbeit und API-Bestand liegen **getrennt**. Ein Abgleich überschreibt die Handarbeit nie —
sonst wäre sie beim ersten Lauf weg und niemand wüsste, wo die Zitate geblieben sind. Fällt die
API aus oder verfällt der Bestand, steht die Handarbeit wieder da.

Ohne Note **und** ohne Handarbeit verschwindet der Abschnitt ganz. Ein Block mit leeren Sternen
und „0 Bewertungen" ist schlechter als keiner — er sagt dem Besucher, hier war noch nie jemand.

### Recht — kein Beiwerk

**§ 5b Abs. 3 UWG.** Wer Verbraucherbewertungen zugänglich macht, muss angeben, **ob und wie** er
die Echtheit sicherstellt. Das Modul erzeugt diesen Hinweis selbst und formuliert ihn je nach
Quelle unterschiedlich, denn der Unterschied ist nicht kosmetisch: bei der API trifft **Google**
die Auswahl, bei der Handpflege der **Betrieb**. Wer von Hand drei gute aus 234 heraussucht und
dazuschreibt „die Auswahl trifft Google", behauptet etwas Falsches — und landet bei genau der
Vorschrift, die er erfüllen wollte.

**Anhang zu § 3 UWG Nr. 23b.** Bewertungen als echt darstellen, ohne dass sie es sind, ist
abmahnbar. Deshalb wird im Code nichts sortiert, nichts nach Note gefiltert und nichts gekürzt.
Bewertungen ohne Text fallen raus, weil ein leeres Zitat nichts anzeigt — die Note bleibt davon
unberührt, sie zählt alle mit. **Eine Zwei-Sterne-Bewertung, die Google liefert, steht auf der
Seite.** Das ist keine Nachlässigkeit, das ist die Vorschrift.

**Übersetzungen.** Google übersetzt Bewertungen automatisch. Eine übersetzte Bewertung ist nicht
mehr wörtlich. Wo `originalText` vorliegt, gilt der — nicht `text`.

**Namen.** Voreinstellung ist die Kürzung auf „Vorname N." Hier stehen sich Datenminimierung
(Art. 5 Abs. 1 lit. c DSGVO) und die Places-Richtlinie zur Urheberangabe gegenüber.
**`german-legal-compliance` entscheidet das vor der ersten Auslieferung.** Umstellen ohne
Codeänderung:

```php
add_filter( 'agentur_bewertungen_namen_kuerzen', '__return_false' );
```

**Keine AggregateRating-Auszeichnung.** Bewertungssterne für den eigenen Betrieb auf der eigenen
Seite sind von Google nicht vorgesehen und riskieren eine manuelle Maßnahme. Die Sterne im Theme
sind Gestaltung, keine Auszeichnung für die Suchmaschine.

**Bei Kündigung** muss `wp agentur-bewertungen verwerfen` laufen. Die Bewertungen dürfen dann
nicht in der Datenbank liegen bleiben.

### Was das Theme davon merkt

`patterns/bewertungen.php` ruft `agentur_bewertungen()` auf, wenn es die Funktion gibt, und läuft
sonst mit eingebauten Werten weiter. Kein HTTP im Seitenaufbau, in keinem Fall.

Ein Theme, das ohne sein Plugin eine leere Fläche zeigt, zerlegt beim ersten Themewechsel die
Kundenseite. Deshalb die Prüfung mit `function_exists`.

### Prüfen

```bash
docker compose run --rm wpcli eval-file /opt/skripte/bewertungen-pruefen.php
```

30 Prüfungen über die Wege, die im Normalbetrieb nie laufen und deshalb still kaputtgehen:
Fehler bei Google, Bestand über der Grenze, übersetzter statt originaler Text, mehr als fünf
Bewertungen, Kürzung von Umlaut-Namen, Wortlaut der Pflichtangabe.

---

---

## Modul: Meta-Description und Open Graph

**Was es leistet.** Schreibt `<meta name="description">` und die Open-Graph-Angaben in den
`<head>`. Damit steht **SEO bei 100** statt 92 — die Beschreibung war der einzige offene Punkt.
Titel und Canonical setzt WordPress selbst; genau diese beiden fehlten.

### Warum kein Yoast oder Rank Math

Für eine Seite mit fünf Unterseiten holt man sich damit mehrere Megabyte Code, ein Dashboard voller
Werbung für die Bezahlfassung, regelmäßige Sicherheitslücken und bei einigen Anbietern eine
Anbindung an fremde Server. Gebraucht werden davon vier Zeilen im `<head>`.

### Wer die Texte pflegt

**Der Inhaber, im Blockeditor.** In der Seitenleiste steht das Panel „Beschreibung für Google" mit
einem Zeichenzähler, der bei über 158 rot und unter 70 gelb wird. Reines JavaScript, kein
Bauschritt — ein Build-Prozess für ein einzelnes Textfeld hieße node_modules im Repository und ein
Artefakt, das in zwei Jahren niemand mehr bauen kann.

Für die Agentur zusätzlich die Kommandozeile:

```bash
wp agentur-meta start "…"        # Startseite (eigene Option, siehe unten)
wp agentur-meta seite 5 "…"      # einzelne Seite
wp agentur-meta pruefen          # alle Seiten mit Befund — vor jeder Auslieferung
```

`pruefen` ist der Befehl, der die Seite findet, die jemand angelegt und dabei die Beschreibung
vergessen hat. Das ist der Normalfall, nicht die Ausnahme.

### Entscheidungen im Modul

**Die Startseite braucht Handarbeit.** Ihr Inhalt besteht in einem Blocktheme nur aus
Musterverweisen (`<!-- wp:pattern … /-->`); daraus lässt sich kein Satz gewinnen. Deshalb eine
eigene Option. Ein gepflegtes Feld an der Seite schlägt sie trotzdem — sonst ließe sich
ausgerechnet die Startseite als Einzige nicht im Editor pflegen.

**Keine erfundene Beschreibung.** Ist keine gepflegt und lässt sich keine aus dem Inhalt gewinnen,
wird **nichts** ausgegeben. Google verwendet eine zusammengestückelte Beschreibung ohnehin nicht,
aber sie steht in der Vorschau, wenn jemand die Seite teilt.

**Der Untertitel der Website steht nicht in der Rückfallkette.** Er ist drei Wörter lang und
wiederholt den Titel; als Beschreibung ist er eine verschenkte Zeile im Suchergebnis.

**Gekürzt wird beim Ausgeben, nicht beim Speichern.** Im Editor bleibt der volle Text stehen und
der Zähler warnt, damit ein Mensch kürzt. Im `<head>` wird an der Wortgrenze abgeschnitten —
mitten im Wort sieht das Suchergebnis nach kaputter Software aus.

**Open Graph teilt sich die Beschreibung.** §6 verlangt OG-Tags; sie getrennt zu pflegen hieße,
denselben Satz zweimal zu schreiben und beim zweiten Mal zu vergessen.

**Das OG-Bild ist ein JPEG.** Der Crawler von Facebook liest kein WebP. 1200×630, weil das
Verhältnis alle Netze ohne Beschnitt anzeigen. Es wird nie im Seitenaufruf geladen, nur von
Crawlern — sein Gewicht zählt für Lighthouse nicht. Reihenfolge: Beitragsbild → Website-Logo →
Rückfall aus dem Theme (`assets/img/demo/og-standard.jpg`, per Filter austauschbar).

**`og:locale` kommt aus `get_locale()`, nicht aus `get_bloginfo('language')`.** Letzteres liefert
den Wert für das `lang`-Attribut, hier schlicht `de`. Open Graph erwartet `de_DE`; bei einem
ungültigen Wert fällt Facebook kommentarlos auf `en_US` zurück und die geteilte Seite sieht
englisch aus. Der Fehler war beim Bauen drin und ist nur durch Nachsehen aufgefallen.

**Archive, Suche und 404 bekommen keine Beschreibung.** Sie gehören nicht in den Index.

### Sprachgate

Die Beschreibungen sind **kundenseitiger deutscher Text** und brauchen nach CLAUDE.md §2.2
`german-language-tone` und einen benannten deutschsprachigen Prüfer, bevor sie live gehen. Die
Sätze für Sunshine sind gesetzt, aber **nicht freigegeben**.

---

---

## Modul: Rolle „Inhaber"

**Der Inhaber bekommt keinen Administrator-Zugang.** Nicht aus Misstrauen, sondern weil ein
Administratorkonto bei einem Betrieb mit fünf Angestellten und einem gemeinsamen Passwort die
wahrscheinlichste Art ist, wie eine Kundenseite kaputtgeht: ein Plugin „das ein Bekannter
empfohlen hat", ein Theme-Wechsel zum Ausprobieren, ein gelöschtes Impressum.

Die eingebaute Rolle `editor` kann bereits keine Themes wechseln, keine Plugins aktivieren, keine
Benutzer anlegen und keine Einstellungen ändern — das ist WordPress-Standard und wird nicht
nachgebaut. Die Rolle wird **von `editor` abgeleitet**, nicht abgeschrieben: WordPress ändert die
Ausstattung eingebauter Rollen zwischen Hauptversionen, eine eingefrorene Liste würde
stillschweigend veralten.

```bash
wp agentur-rolle anlegen
wp agentur-rolle zuweisen inhaber@kunde.de
wp agentur-rolle pflichtseiten          # sucht /impressum und /datenschutz
wp agentur-rolle pruefen                # vor jeder Übergabe
```

### Die drei Dinge, die WordPress nicht von sich aus kann

**1. Kein `unfiltered_html`.** Ein Editor darf im Standard beliebiges HTML einfügen, auch
`<script>` und `<iframe>`. Damit ist die Zusage aus §2.1 an genau einer Stelle einklebbar: der
Inhaber fügt das Google-Maps-iframe ein, das ihm jemand empfohlen hat, und die Seite lädt beim
Aufruf von einem fremden Server. Ohne Einwilligung, ohne dass es jemand merkt, und wir haben es
verkauft. Ohne diese Berechtigung räumt WordPress das beim Speichern selbst weg — **der wirksamste
Schutz im ganzen Modul.**

**2. Pflichtseiten sind unlöschbar.** Impressum und Datenschutzerklärung müssen von jeder Seite
erreichbar sein (§2.1); die Verweise im Fuß stehen fest auf `/impressum` und `/datenschutz`. Ein
Editor darf im Standard Seiten löschen, in den Papierkorb legen, auf Entwurf zurückstellen **und
den Permalink ändern** — jedes davon bricht die Verweise, und keines meldet WordPress. Ein 404 auf
das Impressum ist genau der Zustand, für den abgemahnt wird.

Geschützt wird nach **ID**, nicht nach Titel: den Titel darf der Inhaber ändern („Impressum &
Kontakt"), die Seite bleibt dieselbe.

**3. Kein Site-Editor.** `edit_theme_options` wird bewusst nicht vergeben. Damit bliebe der
Site-Editor offen, und dort lassen sich Vorlagenteile bearbeiten — auch der Fuß mit den
Pflichtverweisen. Ein Schutz, der die Seite bewacht und die Vorlage offenlässt, bewacht nichts.

**Der Preis gehört ins Übergabegespräch:** der Inhaber kann Menü, Logo und Vorlagen nicht selbst
ändern. Das macht die Betreuung.

### Zwei Sperren, nicht eine — und warum

`map_meta_cap` allein reicht **nicht**. `wp_trash_post()` und `wp_delete_post()` prüfen selbst
keine Berechtigungen; geprüft wird eine Ebene darüber, im Backend und in der REST-Schnittstelle.
Wer die Funktionen direkt aufruft — ein Plugin, ein Import, ein Skript — löscht das Impressum
trotz Berechtigungssperre.

**Im ersten Prüflauf ist genau das passiert: die Seite war weg.** Deshalb liegt eine zweite Sperre
in `pre_trash_post` und `pre_delete_post`, also in den Funktionen selbst. Abgewiesene Versuche
landen im Fehlerprotokoll.

Läuft **gar kein Benutzer** — WP-CLI, Cron, Wartungsskript —, greift die zweite Sperre nicht. Wer
die Kommandozeile hat, hat den Server; ihn auszusperren würde nur die eigene Wartung behindern und
keinen Angreifer aufhalten.

### Der Startbildschirm

Die Standardkacheln (WordPress-Neuigkeiten, Aktivität, „Willkommen") sind für jemanden, der zweimal
im Jahr ein Foto tauscht, eine Wand aus Fremdwörtern voller Verweise, die diese Rolle nicht öffnen
darf. Ersetzt durch eine Kachel: was Sie selbst ändern können, was die Betreuung macht, warum
Impressum und Datenschutz gesperrt sind. Die Ansprechperson steht in
`agentur_betreuung_kontakt` und erscheint zusätzlich in der Fußzeile des Backends.

Der Menüpunkt **Werkzeuge** wird entfernt — für diese Rolle ist die Seite nachweislich leer, und
ein Menüpunkt ins Nichts erzeugt genau einen Anruf pro Kunde. Design, Plugins, Benutzer und
Einstellungen stehen bewusst **nicht** in der Aufräumliste: die blendet WordPress schon anhand der
Berechtigungen aus, ein zweites Entfernen sähe nach Sicherheit aus und wäre keine.

`DISALLOW_FILE_EDIT` gehört in die `wp-config.php` jeder Kundeninstallation — der Theme- und
Plugin-Editor ist eine Server-Einstellung, kein Rollenthema.

### Prüfen

```bash
docker compose run --rm wpcli eval-file /opt/skripte/rolle-pruefen.php
```

34 Prüfungen. Der Test meldet nicht Berechtigungen, sondern **versucht die Seite kaputtzumachen**:
löschen, in den Papierkorb legen, umbenennen, zurückstellen, `<script>` und `<iframe>` einfügen.
Die zerstörenden Prüfungen laufen auf einer **Wegwerfseite**, nicht am echten Impressum — der erste
Lauf hat die echte Seite gelöscht, und ein Test, der bei einem Befund die Daten mitnimmt, ist
schlechter als keiner.

Geprüft wird auch die Gegenrichtung: dass der Inhaber gewöhnliche Seiten sehr wohl löschen darf und
der Administrator weiterhin durchkommt. Eine Rolle, die nichts mehr zulässt, ist genauso kaputt wie
eine, die alles zulässt.

---

## Modul: „Ihre Angaben"

Der Menüpunkt, über den der Inhaber seine Website pflegt — statt der Startseite, die für ihn
geschlossen ist.

### Zwei Gründe, beide gemessen

**Die Startseite friert beim ersten Speichern ein.** Sie besteht aus Musterverweisen. Öffnet der
Inhaber sie im Editor und speichert, schreibt WordPress die Muster als vollständiges Markup aus:
**358 Byte werden 26 439 Byte.** Ab dann folgt seine Startseite nicht mehr dem Theme — jede spätere
Korrektur erreicht diesen Kunden nicht mehr, und niemand merkt es, weil die Seite ja weiter richtig
aussieht.

**Dieselbe Angabe stand mehrfach im Theme.** Die Telefonnummer **dreimal**: Hero, Kontaktabschnitt,
Fuß. Die Öffnungszeiten in drei Schreibweisen. Wer eine änderte, hinterließ zwei falsche — und die
falsche stand dann im Fuß, wo niemand hinsieht, bis ein Kunde vergeblich anruft.

Jetzt ist jede Angabe **einmal** hinterlegt. Nachgewiesen: eine geänderte Telefonnummer erscheint
an allen fünf Stellen der Seite neu, der Wählverweis `tel:` zieht mit, die alte Nummer taucht
nirgends mehr auf.

### Was der Inhaber pflegt

| Gruppe | Inhalt |
|---|---|
| Betrieb | Name, Straße, PLZ, Ort, Telefon, E-Mail |
| Öffnungszeiten | Kurzform und ausgeschriebene Form, plus Zusatz |
| Texte auf der Startseite | Überschrift, Einleitung zur Preisliste, Etikett über der Mannschaft, gelber Abschnitt, Satz im Fuß |
| Preisliste | Zeilen aus Gruppe, Leistung, Preis — Zeilen hinzufügen und entfernen |
| Mannschaft | Zeilen aus Name, Erfahrung, Sprachen, Schwerpunkt |

**Unter jedem Feld steht, wo es erscheint.** Ohne diese Angabe tippt jemand einen Text ein und weiß
nicht, wo er landet — dann wird nichts gepflegt, aus Sorge, etwas kaputtzumachen.

### Abgeleitete Felder

Der Inhaber pflegt Straße, PLZ und Ort. Daraus entstehen `plz_ort`, `adresse_kurz` und die
vollständige Anschrift. Aus der angezeigten Telefonnummer entsteht `telefon_wahl` — „0178 5184291"
wird „+491785184291".

Das ist Fehlervermeidung, nicht Komfort: ein zweites Feld für den Wählverweis wäre das Feld, das
als Erstes veraltet, und ein falscher `tel:`-Verweis fällt niemandem auf, der die Nummer daneben
lesen kann.

### Platzhalter in Fließtexten

`{telefon}`, `{adresse_kurz}`, `{zeiten_kurz}` und jedes andere Feld lassen sich in Fließtexte
schreiben, allen voran in die Beschreibung für Google:

> Friseur in {ort} für Damen, Herren und Kinder. Termin unter {telefon} oder einfach vorbeikommen.

**Beim Prüfen aufgefallen:** ohne das wäre die Meta-Description die eine Stelle, an der die alte
Nummer stehen bliebe. Sie stand auf der Seite dreimal neu und im Suchergebnis noch zweimal alt.

Der Zeichenzähler im Editor zählt die **aufgelöste** Länge, nicht die des Platzhalters —
`{telefon}` sind 9 Zeichen, die Nummer zwölf. Ein Zähler, der auf Grün steht und trotzdem
abgeschnitten wird, ist schlimmer als keiner.

### Vorlagenteile

`parts/footer.html` ist statisches HTML und kann kein PHP aufrufen. Dafür der serverseitig
gerenderte Block:

```html
<!-- wp:agentur/angabe {"felder":["strasse","plz_ort"]} /-->
<!-- wp:agentur/angabe {"als":"telefon"} /-->
```

Bewusst **ohne** block.json und ohne Editor-Oberfläche: der Block gehört in die Vorlage, nicht in
den Werkzeugkasten des Inhabers. Er soll ihn nicht einfügen können, er soll ihn nicht einmal sehen.
Leere Felder fallen heraus — ein leeres `<p>` im Fuß erzeugt eine Lücke, die aussieht wie ein Fehler.

### Das Theme läuft ohne das Modul weiter

Jedes Muster prüft mit `function_exists()` und fällt sonst auf eingebaute Werte zurück. Ein Theme,
das ohne sein Plugin eine leere Seite zeigt, zerlegt beim ersten Themewechsel die Kundenseite.

### Befehle

```bash
wp agentur-angaben zeigen                        # alle Felder, auch die abgeleiteten
wp agentur-angaben setzen telefon "02241 123456"
```

---

## Noch nicht gebaut

Die übrigen Punkte aus der Produktplanung, alle im selben Modulordner vorgesehen:

- **Bildverarbeitung beim Hochladen** — Tonung nach `.claude/design-referenzen.md`, damit ein
  mittelmäßiges Handyfoto sich einfügt statt die Seite zu zerstören
- **Sicheres SVG-Hochladen** — für das Logo, mit Bereinigung
- **Buchungssystem** — selbst gehostet auf EU-Hosting. Nie Calendly, Treatwell oder Altegio
  (CLAUDE.md §4.2)
