# CRM — Datenmodell (Twenty)

**Status: in Betrieb seit 2026-08-15.** Twenty v2.31.1, self-hosted, derzeit lokal unter
`~/Documents/PROJECTS/twenty-crm/twenty/`. **Hier wird gearbeitet, hier steht der Stand** —
Beschluss des Founders, umgesetzt am selben Tag: 34 Schemaelemente angelegt, 16 Karten aus dem
alten Trello-Board übernommen.

Trello ist seit 2026-08-16 nicht mehr Ablösung, sondern **Anzeige**: ein gespiegeltes Board für
den Blick vom Telefon, in eine Richtung befüllt (siehe *Spiegel nach Trello*). Das alte Board
„Sofabelle" bleibt unverändert als Archiv liegen (`.claude/trello.md`).

Aufgesetzt wird es mit `tools/twenty-provision/` — `provision.mjs` (Objekte und Felder),
`tasks.mjs` (Aufgaben und Trello-Übernahme), `seed.mjs` (Bestandskunden). Alle drei sind
idempotent: zweimal laufen lassen ändert nichts.

> **Auswahllisten nur ergänzen, nie ersetzen.** Twenty hängt den gespeicherten Wert an der
> Options-ID, nicht am Text. Wer die Optionsliste mit frischen UUIDs überschreibt, löscht die
> Werte aller bestehenden Datensätze — ohne Fehlermeldung. Am 2026-08-16 stand Sofa Belle danach
> auf leerem AVV statt auf „offen". `auswahlErweitern()` in `provision.mjs` behält vorhandene
> Optionen und hängt nur fehlende an.

---

## Die Abgrenzung, ohne die es schadet

Die abgelöste Regel lautete: *Trello führt den Stand der Arbeit, die Platte führt den Inhalt.*
Sie war richtig und gilt weiter — nur führt den Stand jetzt das CRM. Käme es zusätzlich dazu,
ohne dass etwas weggeht, hätten wir **drei** Wahrheiten. Genau davor warnt CLAUDE.md §5.

Deshalb gilt hier:

| | führt | führt nicht |
|---|---|---|
| **Platte** `projects/<slug>/` | Inhalt — Brief, Research, Angebotstext, Mails, Nachweise, Reviews | Status |
| **CRM** | Beziehungen, Zustand, Gates, Termine, Beträge | Inhalt |

**Bei Widerspruch gewinnt die Platte.** Unverändert.

**Das CRM ersetzt Trello, es ergänzt es nicht.** Zwei Werkzeuge für denselben Stand bedeuten,
dass eines gepflegt wird und das andere lügt. Trello ist deshalb außer Dienst; das Board bleibt
als Archiv, `scripts/trello.sh` funktioniert nur noch lesend.

Nebenbei fällt eine Einschränkung weg: auf Trello durften **keine** personenbezogenen Daten
liegen, weil jede Karte eine Übermittlung in ein Drittland war. Im selbst gehosteten CRM dürfen
die Kontaktdaten unserer Ansprechpartner stehen — die zweite Klasse aus CLAUDE.md §2.7. Die erste
weiterhin nicht.

**Der Schlüssel zwischen beiden Welten ist das Feld `Projektordner`** — der Slug, z. B. `kunde-b`.
Jeder Datensatz trägt ihn. Ohne ihn ist ein CRM-Eintrag nicht auffindbar und damit wertlos.

---

## Objekte

Vier, nicht mehr. Zwei davon hat Twenty ab Werk.

```
Company (Betrieb)
   ├── Person (Ansprechpartner)          n
   └── Opportunity (Anfrage → Angebot)   n     Zustände 0–3
          └── Projekt (Lieferung)        1     Zustände 4–11 + Wartung
```

### Warum Anfrage und Projekt getrennt sind

Naheliegend wäre ein Objekt mit zwölf Zuständen. Zwei Gründe dagegen:

1. **Die Kennzahlen werden sonst unbrauchbar.** Eine Abschlussquote über einen Trichter, in dem
   Zustand 8 „QA" heißt, misst nichts.
2. **Ein Kunde hat mehrere Vorhaben.** Kunde A hat vier (T1–T4) in unterschiedlichen Zuständen.
   Mit einem Objekt je Kunde ist das nicht darstellbar.

Die Trennlinie liegt genau auf unserem eigenen Gate. Pipeline-Zustand 3: *„Kunde hat schriftlich
zugesagt. Ohne diese Zusage beginnt keine Produktion."* Genau dort wird aus einer Opportunity ein
Projekt.

---

## Company · Betrieb

| Feld | Typ | Zweck |
|---|---|---|
| Name | Text | Standard |
| Website | Link | Standard |
| Branche | Auswahl | Friseur · Kosmetik · Gastronomie · Handwerk · Einzelhandel · Produktion · Dienstleistung · Sonstiges |
| Stadt / Land | Adresse | Standard |
| **Absatzmarkt** | Mehrfachauswahl | DE · AT · CH · EU-übrig · Nicht-EU |
| **Zielprofil** | Auswahl | passt §1 · Ausnahme erteilt · **Ausnahme offen** · passt nicht |
| **Projektordner** | Text | Slug auf der Platte |
| Quelle | Auswahl | Founder/Netzwerk · Vertrieb vor Ort · Website-Formular · Empfehlung · Sonstiges |
| Korrespondenzsprache | Auswahl | Deutsch · Russisch · Rumänisch · Englisch · andere |
| **Sprachprüfer** | Text | benannte Person (CLAUDE.md §2.2) |
| **Weitere Sprachen** | Text | Sprachen neben der Korrespondenzsprache |
| AVV | Auswahl | **ungeprüft** · nicht nötig · offen · unterzeichnet |
| Wartungsvertrag | Auswahl | keiner · angeboten · aktiv · abgelehnt |

**Absatzmarkt und Zielprofil sind unsere Erfindung, nicht CRM-Standard**, und sie sind der Grund,
warum ein Standard-CRM nicht reicht. CLAUDE.md §1 macht den *Absatzmarkt* zum Aufnahmekriterium,
nicht den Sitz. Das Feld beantwortet die Frage, die sonst erst im Angebot auffällt.

**Sprachprüfer leer bei einer anderen Sprache als Deutsch = die Sprache ist nicht lieferbar.**
Nicht „noch zu klären" — nicht lieferbar, bis jemand benannt ist. *Weitere Sprachen* steht daneben,
weil die Korrespondenzsprache nur einen Wert kennt: bei „Deutsch + Englisch" war die zweite Sprache
sonst zwischen Handoff und CRM verschwunden, und mit ihr die Prüferpflicht.

**AVV hat vier Werte, und der erste ist `ungeprüft`** — dieselbe Überlegung wie bei den mündlichen
Zusagen. „Nicht nötig" ist eine Feststellung, die jemand getroffen hat. Beim Lead hat sie niemand
getroffen. Der Bot setzt `offen`, sobald der Wunsch personenbezogene Daten der Kunden des Betriebs
berührt (Buchung, Newsletter, Assistent, Datenübernahme, Bestelldaten) — sonst `ungeprüft`. Er
behauptet nie, ein AVV sei entbehrlich. Nach Art. 28 DSGVO und CLAUDE.md §2.7 hängt daran nicht die
Auslieferung, sondern ob wir die Daten überhaupt anfassen dürfen.

## Person · Ansprechpartner

| Feld | Typ | Zweck |
|---|---|---|
| Name, E-Mail, Telefon | Standard | |
| Rolle | Auswahl | Inhaber/Entscheider · fachlich · Buchhaltung · Technik |
| **Entscheidet über Geld** | Ja/Nein | trennt Anforderungsgeber vom Zahler |
| Anrede | Auswahl | Sie · Du · unklar |
| Sprache | Auswahl | kann von der Firmensprache abweichen |
| Bevorzugter Kanal | Auswahl | E-Mail · WhatsApp · Telefon · Signal · Telegram · LinkedIn |

Mehr nicht. **Keine Notizen zu Personen im CRM** — Gesprächsinhalte gehören in
`projects/<slug>/`, sonst entsteht die zweite Wahrheit durch die Hintertür.

## Opportunity · Anfrage → Angebot

**Phasen:** `LEAD → BRIEF → RESEARCH → ANGEBOT → GEWONNEN | VERLOREN`

| Feld | Typ | Zweck |
|---|---|---|
| Betrag, Abschlussdatum | Standard | |
| Paket | Auswahl | Starter · Business · Shop · Basis (499) · Termin (999) · Komplett (1499) · Betreuung Website (79) · KI-Analyse (490) · KI-Audit · KI-Umsetzung · KI-Compliance · KI-Schulung · KI-Betreuung · Wartung · Sonderprojekt (§4) |
| **Mündliche Zusagen** | Auswahl | **unbekannt** · keine · vorhanden |
| Zusagen wörtlich | Langtext | was genau gesagt wurde |
| Zusagen geprüft | Ja/Nein | halten wir sie? |
| Stack-Abweichung | Auswahl | keine · Shopify · Strapi · andere (§3) |
| Founder-Freigabe | Auswahl | nicht nötig · offen · erteilt · verweigert |
| Verlustgrund | Auswahl | Preis · Zeit · kein Budget · Wettbewerb · kein Bedarf · wir abgelehnt |

**„Mündliche Zusagen" hat drei Werte, nicht zwei, und die Vorgabe ist `unbekannt`.** Das ist der
wichtigste Entwurfsentscheid in diesem Dokument. „Keine Zusagen" ist eine Feststellung, die jemand
getroffen hat. „Unbekannt" heißt, niemand hat den Vertrieb gefragt. Mit einem Ja/Nein-Feld sehen
beide gleich aus — und der Fall, in dem etwas versprochen wurde und niemand danach gefragt hat, ist
genau der Fall, der uns Geld kostet.

Jeder neue Lead startet auf `unbekannt` und ist damit sichtbar, bis jemand ihn anfasst.

## Projekt · Lieferung

**Phasen:** `SETUP → BUILD → CONTENT → GATE SPRACHE → GATE RECHT → QA/STAGING → PRODUKTION → HANDOVER → WARTUNG`

| Feld | Typ | Zweck |
|---|---|---|
| Betrieb, Opportunity | Beziehung | |
| Projektordner | Text | Slug |
| **Gate Sprache** | Auswahl | offen · FREIGABE · NACHARBEIT |
| **Gate Recht** | Auswahl | offen · JA · NEIN · Risiko bewusst getragen |
| **QA §6** | Auswahl | offen · bestanden · Nacharbeit |
| Staging-URL, Live-URL | Link | |
| Nächster Meilenstein | Datum | |
| Blockiert durch | Text | eine Zeile, kein Aufsatz |

Die drei Gates stehen als eigene Felder da, weil sie unter Termindruck als Erstes übersprungen
werden. Ein Projekt in PRODUKTION mit `Gate Recht = offen` muss auf einen Blick als Fehler
erkennbar sein. „Risiko bewusst getragen" gibt es, weil CLAUDE.md §8 dem Founder genau diese
Möglichkeit gibt — protokolliert, nicht stillschweigend.

---

## Task · Aufgabe

Twenty bringt Tasks mit. Erweitert um das, was unsere Pipeline braucht:

**Status** — die sieben früheren Trello-Listen, jetzt ein Feld:
`Backlog → Bereit → In Arbeit → Review → Blockiert → Freigabe Founder → Fertig`

| Feld | Typ | Zweck |
|---|---|---|
| `bereich` | Auswahl | Aufnahme · Research · Angebot · Setup · Build · Content · Sprache · Recht · QA · Handover · Entscheidung |
| `agent` | Text | zuständiger Agent. `assignee` bleibt für Menschen |
| **`wartetAuf`** | Auswahl | nichts · Kunde · Founder · Gate · intern |
| `abnahme` | Text | woran der Orchestrator Fertigkeit erkennt |
| `datei` | Text | Pfad auf der Platte |
| `blockiertDurch` | Text | eine Zeile |

Verknüpfung über `taskTargets` an Betrieb, Anfrage, Person oder Projekt.

**`wartetAuf` ist das Feld, das die Woche steuert.** Nach der Übernahme der 16 Altkarten sah es so
aus: 8 warten auf den Kunden, 3 auf den Founder, 3 auf eigene Vorarbeit, 2 auf nichts. Zwei
Drittel des Stillstands sind also Warten, nicht Arbeit — und ein Drittel davon wartet auf uns
selbst. Das war auf sieben Trello-Listen verteilt und damit unsichtbar.

- Alles auf `KUNDE` → nächstes Fragenbündel des PM (`.claude/flows/kundenmails.md`)
- Alles auf `FOUNDER` → nächste Entscheidungsliste
- Alles auf `INTERN` → hier hängt es an uns, nicht am Kunden

### Spiegel nach Trello

Twenty läuft auf `localhost` und ist vom Telefon nicht erreichbar. Damit der Vertrieb und der
Founder unterwegs sehen, wo etwas hängt, spiegelt `tools/trello-spiegel/spiegel.mjs` die Aufgaben
auf das Board „Agentur — Aufgaben (Spiegel)".

**In eine Richtung.** Das ist die ganze Regel. Zwei Quellen der Wahrheit gehen in der zweiten
Woche auseinander, und danach weiß niemand, welche stimmt — genau deshalb wurde Trello am
2026-08-15 abgelöst. Eine von Hand verschobene Karte wandert beim nächsten Abgleich zurück, und
das Protokoll nennt sie beim Namen statt es still zu tun. Die Marke `[crm:<id>|<Zustand>]` in
der Kartenbeschreibung merkt sich den zuletzt gespiegelten Zustand; nur dadurch ist „das CRM ist
weitergerückt" von „jemand hat hier geschoben" unterscheidbar.

Es wandert wenig hinüber: Titel, Spalte, `wartetAuf`, `abnahme`. Keine Ansprechpartner, keine
Nummern, keine wörtlichen Kundenzitate — Atlassian ist Drittland (CLAUDE.md §2.7). `blockiertDurch`
bleibt absichtlich hier, weil dort mündliche Zusagen im Wortlaut stehen können. Wo schon der
Betriebsname personenbezogen ist (Einzelunternehmen), schaltet `TRELLO_ANONYM=1` auf den
Projektordner-Slug um.

Laufend mit `node tools/trello-spiegel/spiegel.mjs --watch=300`. Fällt Twenty aus, läuft der
Spiegel weiter und fängt sich beim nächsten Takt.

**Der Spiegel ersetzt das Hosting nicht.** Er verdeckt nur, dass das CRM auf einem Notebook
läuft — solange es aus ist, ändert sich auf dem Board nichts.

## Ansichten — daran hängt der Nutzen

Ein CRM ohne gespeicherte Ansichten ist eine Tabelle. Diese fünf rechtfertigen den Aufwand:

1. **Zusagen ungeprüft** — Opportunities mit `Mündliche Zusagen ≠ keine` und `geprüft = nein`.
   Das Sicherheitsnetz von Flow A. Ist diese Liste leer, ist der Flow eingehalten.
1a. **Wartet auf Kunde** und **Wartet auf Founder** — zwei Aufgabenlisten, gefiltert auf
   `wartetAuf`. Die erste ist die Arbeitsliste des PM, die zweite die des Founders.
2. **Founder-Freigabe offen** — was auf eine Entscheidung wartet, die fünf Minuten dauert.
3. **Sprachprüfer fehlt** — Betriebe mit Sprache ≠ Deutsch und leerem Prüferfeld.
4. **Gate offen, Phase zu weit** — Projekte ab QA mit einem Gate auf `offen`. Sollte immer leer sein.
5. **Trichter nach Phase** — nur über Opportunities, deshalb aussagekräftig.

---

## Was nie ins CRM kommt

- **Inhalte** — Brief, Research, Angebotstext, Mailverläufe. Platte.
- **Daten der Kunden unserer Kunden** — Buchungen, Bestellungen, Anfragenexporte (CLAUDE.md §2.7).
  Ein CRM ist genau der Ort, an dem so etwas versehentlich landet.
- **Rechtstexte und Zugangsdaten.**

Erlaubt sind Geschäftskontaktdaten unserer Gegenüber — zweite Klasse aus §2.7.

---

## Anschluss an die Agenten

Twenty bringt einen **eigenen MCP-Server** mit. Damit lesen und schreiben `co-founder-orchestrator`
und `pm-client-lead` das CRM direkt, ohne dass wir einen Adapter bauen. Das ist der eigentliche
Grund, dieses Werkzeug und kein anderes zu prüfen.

Der Lead-Bot (`tools/leadbot/`) sammelt bereits genau diese Felder. Beim Umstieg ist das ein
Import, keine Umstellung — und bis dahin geht nichts verloren.

## Lizenz — was wir dürfen und was Geld kostet

Geprüft am 2026-08-16 gegen die `LICENSE` des Projekts. Kein Rechtsrat, aber die Lage ist klar
genug, um danach zu handeln.

**Selbst hosten kostet nichts, auch gewerblich.** Der Kern steht unter AGPLv3. Die erlaubt
kommerzielle Nutzung, Betrieb auf eigenen Servern und Änderungen. Eine Lizenzgebühr entsteht dafür
nicht, und ein Bußgeld gibt es hier nicht — ein Lizenzverstoß wäre eine Urheberrechtsverletzung
(Abmahnung, Unterlassung, Schadensersatz), keine Behördenstrafe. Voraussetzung ist, dass wir
gegen die Lizenz überhaupt verstoßen, und dafür braucht es mehr als Installieren.

Zwei Ausnahmen in der `LICENSE`:

1. **Dateien mit `/* @license Enterprise */`** stehen unter kommerzieller Lizenz und dürfen
   *in Produktion* nur mit gültigem Abonnement genutzt werden. Das sind die abgeriegelten
   Funktionen: SAML/OIDC-SSO, zeilengenaue Rechte, Audit-Log, Schlüsselrotation, eigene Domain.
   Sie schalten sich ohne Lizenzschlüssel nicht ein. **Die Grenze ist also nicht „auf einem Server
   installiert", sondern „eine gesperrte Funktion benutzt oder die Sperre umgangen".**
2. **Einige Pakete stehen unter MIT** (SDKs, UI-Bibliothek) — großzügiger als AGPL.

**Unsere eigenen Werkzeuge bleiben unsere.** Die `LICENSE` enthält eine *Twenty Application
Exception* nach AGPLv3 §7: was über die veröffentlichten Schnittstellen angebunden ist — REST,
GraphQL, Webhooks, SDK — löst den Copyleft nicht aus. Genau so arbeiten `tools/leadbot/crm.mjs`
und `tools/trello-spiegel/`: von außen über `/rest`, ohne eine Zeile im Kern zu ändern. Wir
müssen sie also nicht offenlegen.

**Was wirklich Pflichten auslösen würde:**

- **Den Kern ändern und andere darauf zugreifen lassen.** AGPLv3 §13 verlangt dann, den Nutzern
  den Quelltext der Änderungen anzubieten — Mitarbeiter sind Nutzer. Tun wir nicht; wenn doch,
  ist die Pflicht billig zu erfüllen, aber sie muss jemandem bewusst sein.
- **Das CRM als Dienst an Kunden weitergeben** („wir hosten Ihr CRM"). Die AGPL erlaubt das,
  macht aber jeden Kunden zum Netzwerknutzer mit Anspruch auf den Quelltext geänderter Versionen.
  Bevor daraus ein Angebot wird, gehört das einmal richtig geprüft — als Geschäftsmodell, nicht
  nebenbei.
- **Branding entfernen oder Lizenzprüfungen herauspatchen.** Das ist die Grenze, hinter der es
  unangenehm wird.

**Regel für den Betrieb:** Version festnageln, Images unverändert nutzen, `LICENSE` und Hinweise
nicht anfassen, keine Enterprise-Funktion einschalten. Brauchen wir irgendwann SSO oder ein
Audit-Log, ist das der Moment zu zahlen — nicht früher.

Das eigentliche Risiko beim Umzug auf einen Server ist ohnehin ein anderes und dort gibt es echte
Bußgelder: **AVV mit dem Hoster nach Art. 28 DSGVO** und der Eintrag im Verzeichnis nach Art. 30.
In der Datenbank stehen Namen, Mailadressen und Telefonnummern unserer Ansprechpartner.

## Offen

1. **Wo es läuft — entschieden am 2026-08-16: vorerst nirgends anders.** Es bleibt auf dem Rechner
   des Founders, bis die ersten Einnahmen da sind. Der Vertrieb bekommt bewusst **keinen
   CRM-Zugang**; seine einzige Schnittstelle ist der Telegram-Bot, alles danach machen die Agenten.
   Damit fällt das Argument „der Vertrieb kommt nicht heran" weg — es bleibt: **kein Backup**, und
   nichts läuft, wenn der Rechner aus ist. Empfehlung für später steht oben (Hetzner CAX11, rund
   8 €/Monat netto). Betriebsregeln bis dahin: `tools/BETRIEB.md`.
2. **Backup, Updates, Zugriffsschutz** — verbindlich, nicht als Vorsatz. CLAUDE.md §3 sagt über
   Strapi, dass selbst gehostete Software ohne diese drei eine Sicherheitslücke ist, die man dem
   Kunden übergibt. Hier ist der Kunde wir selbst, und in der Datenbank stehen Kontaktdaten.
   - **Backup: seit 2026-08-16 eingerichtet und geprüft** — `de.agentur.twenty-backup`, täglich
     und bei jeder Anmeldung, mit Rückspieltest bei jedem Lauf. Anleitung und Wiederherstellung:
     `tools/twenty-backup/README.md`. Die **Kopie außer Haus** ist vorbereitet (Hetzner Storage
     Box BX11, 3,20 €/Monat netto, verschlüsselt mit restic), aber **zurückgestellt bis zu den
     ersten Einnahmen — derselbe Auslöser wie beim Serverumzug**. Nichts bestellt, nichts
     hochgeladen, Dienst nicht geladen. Bis dahin getragene Lücke: die Sicherung schützt gegen
     Fehlbedienung, nicht gegen den Verlust des Rechners.
   - **Updates und Zugriffsschutz: weiterhin offen.** Twenty läuft auf `v2.31.1`; ein Verfahren,
     wie und wann aktualisiert wird, gibt es nicht.
3. ~~**`ENCRYPTION_KEY` liegt nur in der `.env`.**~~ Erledigt am 2026-08-16. Er liegt jetzt an drei
   Stellen, die nicht gemeinsam ausfallen: in `twenty/.env`, im Anmelde-Schlüsselbund und in
   Passwords.app, von dort über den iCloud-Schlüsselbund außer Haus. Dasselbe gilt für
   `PG_DATABASE_PASSWORD`.

   ```bash
   security find-generic-password -a twenty-crm -s twenty-encryption-key -w
   security find-generic-password -a twenty-crm -s twenty-db-password    -w
   ```

   **Vor jeder Neuerzeugung des Schlüssels erst den alten als `FALLBACK_ENCRYPTION_KEY` eintragen**
   — sonst ist alles bereits Verschlüsselte nicht mehr lesbar, und ein Backup hilft dabei nicht.

   Beim Umzug auf den Server wandert der Schlüssel **nicht** in ein Repository und nicht in die
   Serverdokumentation, sondern bleibt im Passwortmanager; auf dem Server steht er in einer
   `.env` mit `chmod 600` (beide `.env` hier tragen diese Rechte seit dem 2026-08-16).
4. **MCP-Anbindung der Agenten** — dafür wurde dieses Werkzeug gewählt, genutzt wird sie noch nicht.
