# Lead-Bot

Telegram-Bot für **Flow A, Schritt A1** (`.claude/flows/lead-a-founder.md`). Der Vertrieb schickt
`/new_client`, beantwortet den Fragenkatalog auf dem Telefon, und der Bot legt den Projektordner
auf der Platte an **und den Betrieb im CRM**.

## Wozu

Flow A hat ein Risiko, das Flow B nicht hat: **im Gespräch wurde etwas gesagt, das niemand
aufgeschrieben hat.** Eine Zahl, ein Termin, ein „das ist kein Problem". Der Betrieb erinnert sich
daran, wir nicht — und jede spätere Auseinandersetzung über den Umfang geht darauf zurück.

Der Bot löst nicht das Formular, sondern den **Zeitpunkt**. Zwei Minuten nach dem Gespräch, auf
dem Bürgersteig, erinnert der Vertrieb noch den halben Satz. Am Abend erinnert er die aufgeräumte
Fassung, und die ist wertlos.

Deshalb sind die drei Fragen zu den Zusagen getrennt gestellt. Auf „Hast du etwas zugesagt?"
antwortet jeder Vertrieb mit Nein. Auf „Ist eine Zahl gefallen, auch ungefähr?" antworten viele
mit Ja.

## Einrichtung

**1 · Bot anlegen.** In Telegram [@BotFather](https://t.me/BotFather) anschreiben, `/newbot`,
Namen und Benutzernamen vergeben. BotFather gibt einen Token zurück.

Befehlsliste, Beschreibung und Name setzt danach `setup-telegram.mjs` über die Bot-API — nicht
BotFather. Grund: dort wird die Liste als Text eingetippt, und ein unsichtbares Zeichen aus der
Zwischenablage (geschützte Leerzeichen aus einem Terminal) lässt sie ohne brauchbare Meldung
scheitern. Als Code ist sie wiederholbar und mitversioniert.

```bash
node tools/leadbot/setup-telegram.mjs
```

Einzige Einstellung, die nur BotFather kann: `/setjoingroups` → *Disable*. Der Bot ist für
Einzelchats gedacht; in einer Gruppe ist sein Verhalten nicht durchdacht. Am 2026-08-16 gesetzt.
Nachprüfen lässt sie sich trotzdem über die API — `getMe` muss `can_join_groups: false` liefern:

```bash
curl -s "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/getMe" | grep -o 'can_join_groups":[a-z]*'
```

**2 · Zugang beschränken.** Der Bot nimmt nur von benannten Telegram-IDs etwas an. Die eigene ID
erfährt man, indem man dem Bot einmal schreibt — er antwortet mit „Kein Zugang. Deine
Telegram-ID: …".

**3 · `.env` im Repo-Wurzelverzeichnis ergänzen:**

```
TELEGRAM_BOT_TOKEN=123456789:AA...
TELEGRAM_ALLOWED_IDS=111111111,222222222
```

Ohne `TELEGRAM_ALLOWED_IDS` kommt niemand rein — das ist Absicht. Ein offener Bot, der Ordner im
Repo anlegt, ist eine Einladung.

**4 · Starten:**

```bash
cd tools/leadbot
npm run bot
```

Keine Abhängigkeiten, Node ≥ 18 genügt. Der Bot läuft auf dem Rechner, auf dem das Repo liegt —
er schreibt direkt dorthin. Für den Dauerbetrieb später auf einen kleinen EU-Server, dann muss
der Schreibweg neu gedacht werden (Repo per Git statt Dateisystem).

## Was dabei herauskommt

```
projects/<slug>/
  00-handoff.md        alle neun Punkte, Lücken sichtbar als [!]
  status.md            Zustand 0 · LEAD mit Exit-Kriterium
  entscheidungen.md    mündliche Zusagen wörtlich, als "noch nicht geprüft"
  offene-fragen.md     die Fragen, die sich aus jedem Lead ergeben
  research/ reviews/ nachweise/
```

Existiert der Ordner schon, legt der Bot nichts an und sagt das. Er überschreibt nie.

Dazu im CRM (`.claude/crm.md`): **Betrieb, Ansprechpartner, Anfrage** und **drei bis vier
Aufgaben** — Erstkontakt entwerfen, Zugänge anfragen, Pflichtangaben erfragen. Wurde etwas
mündlich zugesagt, kommt eine vierte dazu: *Zusagen prüfen*, Status `Freigabe Founder`,
`wartetAuf = FOUNDER`. Damit landet eine Zusage des Vertriebs automatisch auf dem Tisch des
Founders, statt im Angebot aufzutauchen.

Das Zielprofil nach CLAUDE.md §1 wird aus dem Absatzmarkt **gerechnet, nicht geraten**: kein
DACH-Markt oder unbekannt heißt `Ausnahme offen`, nicht `passt`.

**Das CRM kann die Aufnahme nicht gefährden.** Zuerst die Dateien, dann das CRM. Antwortet es
nicht, wandert die Aufnahme nach `~/Documents/Kundendaten-vertraulich/_leadbot-state/crm-offen/`
und der Bot sagt es. Nachholen mit `/sync`.

**Der Handoff ist eine Rohaufnahme, keine Analyse.** Was daraus folgt, schreibt
`co-founder-orchestrator` beim Review dazu.

## Bedienung

| Befehl | Wirkung |
|---|---|
| `/new_client` | neue Aufnahme |
| `/skip` | überspringt eine freiwillige Frage; bei Pflichtfeldern lehnt der Bot ab |
| `/cancel` | bricht ab, speichert nichts |
| `/status` | wie weit die laufende Aufnahme ist |
| `/sync` | ausstehende Aufnahmen ins CRM nachreichen |

Eine angefangene Aufnahme übersteht einen Neustart des Bots.

## Fragen ändern

Nur in `questions.mjs`. `bot.mjs` kennt keine einzelne Frage. Nach jeder Änderung:

```bash
npm run selftest
```

Der Selbsttest prüft ohne Telegram und ohne CRM, ob Ordnernamen sauber gebildet werden, ob die
Nachfragen an wirklich möglichen Antworten hängen, ob beide Fälle — mit und ohne Zusagen — die
richtigen Dateien erzeugen, und ob **jede** Auswahlantwort eine CRM-Entsprechung hat. Der letzte
Punkt ist der wichtigste: eine nicht übersetzte Option würde sonst still zu „Sonstiges" werden. `SHOW_HANDOFF=1 npm run selftest` gibt zusätzlich einen Beispiel-Handoff aus.

## Die drei Tests

```bash
npm test              # alle drei
npm run test:offline  # die beiden, die ohne Twenty auskommen
```

| Test | Braucht | Prüft |
|---|---|---|
| `selftest.mjs` | nichts | Fragen, Vorlagen, Übersetzungstabellen, die abgeleiteten Regeln |
| `test-nachreichen.mjs` | nichts | den Weg, wenn das CRM **nicht** antwortet |
| `test-idempotenz.mjs` | laufendes Twenty | dass ein zweiter Anlauf nichts verdoppelt |

Die letzten beiden gehören zusammen und beschreiben denselben Vorgang: das CRM ist weg, die
Aufnahme wandert in die Nachreichmappe, der Bot versucht es alle fünf Minuten erneut. Der erste
Test prüft, dass sie dort ankommt; der zweite, dass die Wiederholungen keinen Schaden anrichten.

Solange Twenty läuft, sieht ein kaputter Rückfallweg genauso aus wie ein heiler. Auffallen würde
er an dem Tag, an dem der Vertrieb einen echten Kunden aufnimmt und der Rechner gerade Docker
neu startet — also genau dann, wenn es niemand gebrauchen kann.

`test-idempotenz.mjs` schreibt echte Datensätze („ZZ Testbetrieb") und räumt sie danach restlos
weg, auch aus der Papierkorbebene der Datenbank. Ein Testdatensatz, der weich gelöscht liegen
bleibt, taucht in drei Monaten in einer Auswertung auf und sieht dann aus wie ein echter Lead.

## Datenschutz

**Was durch Telegram läuft.** Name, geschäftliche E-Mail und gegebenenfalls Telefonnummer der
Ansprechperson. Das sind geschäftliche Kontaktdaten unserer Gegenüber — die zweite Klasse aus
CLAUDE.md §2.7, ohne die `pm-client-lead` keine Mail schreiben kann.

**Warum das etwas anderes ist als der Fall, den wir bei Kunden beanstanden.** Wir raten Betrieben
davon ab, Buchungen mit Kundendaten in einen Telegram-Kanal zu schicken. Der Unterschied liegt in
Menge, Kreis und Betroffenen: dort laufend Verbraucherdaten in einen Kanal mit wachsendem
Mitgliederkreis, hier einzelne Geschäftskontakte in einem Einzelchat mit benannten Empfängern.
Das ist ein Unterschied, aber kein Freibrief — **Kundendaten des Betriebs gehören nie in diesen
Bot.**

**Angefangene Formulare** liegen unter `~/Documents/Kundendaten-vertraulich/_leadbot-state/`
(Rechte 700/600), außerhalb des Repos, und werden nach Abschluss gelöscht. Über
`LEADBOT_STATE_DIR` verlegbar.

**Was der Bot nicht tut:** er schreibt keine Mail und verschickt nichts an den Kunden. Er nimmt
auf und trägt ein. Alles Ausgehende bleibt bei einem Menschen (CLAUDE.md §2.5).

## Was der Bot aus den Antworten schließt

In `regeln.mjs` stehen die Ableitungen, die sowohl die Dateien als auch das CRM brauchen. Sie
liegen dort einmal, weil eine Regel an zwei Orten nach dem zweiten Umbau an einem der beiden
falsch ist — und niemand merkt, an welchem. Alle drei sind aus einem Fehllauf entstanden
(2026-08-16):

| Ableitung | Was sie verhindert |
|---|---|
| `bestandsart` | Der Bot hatte Zugänge zu einer Website erfragt, die es nicht gab. „Telefon und Papier" ist eine Antwort, kein fehlender Wert. Ohne Bestand entstehen weder Aufgabe noch Rückfrage. |
| `avvAnlass` / `avvStatus` | Der Bot hatte behauptet, ein AVV sei nicht nötig — bei einem Buchungssystem, also genau dort, wo wir Termindaten der Kunden des Betriebs verarbeiten. Er stellt das jetzt nicht mehr fest, sondern meldet `offen` oder `ungeprüft`. |
| `zweitsprache` | „Deutsch + Englisch" war im Handoff sichtbar und im CRM verschwunden. Damit auch die Prüferpflicht aus CLAUDE.md §2.2. |

Geprüft werden sie in `selftest.mjs`, Abschnitt 7. Wer eine Regel ändert, ändert dort die Erwartung
mit — sonst fällt es erst beim Kunden auf.

## Grenzen

- Ein Betrieb pro Aufnahme. Mehrere Ansprechpartner werden nachträglich in `00-handoff.md` ergänzt.
- Keine Bilder, keine Sprachnachrichten. Eine Visitenkarte abzufotografieren wäre der nächste
  sinnvolle Schritt, ist aber nicht gebaut.
- Läuft der Bot nicht, ist das Formular in `questions.mjs` weiterhin die Vorlage — dann eben von Hand.
