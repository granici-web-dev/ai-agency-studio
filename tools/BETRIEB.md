# Betrieb — solange alles auf dem Rechner des Founders läuft

**Entscheidung vom 2026-08-16 (Founder):** Kein Server, bis die ersten Einnahmen da sind. Das CRM
bleibt auf `localhost`. Der Vertrieb bekommt **keinen CRM-Zugang** — er übergibt Kunden über den
Telegram-Bot, alles danach machen die Agenten.

Die Empfehlung für den späteren Umzug steht in `.claude/crm.md` (Hetzner CAX11, rund 8 €/Monat
netto). Auslöser für die Entscheidung: erste Einnahmen.

**Diese Entscheidung deckt jeden laufenden Fremdkostenpunkt**, nicht nur den Server. Auch die
ausgelagerte Sicherung (Hetzner Storage Box, 3,20 €/Monat) ist deshalb vorbereitet, aber
zurückgestellt — `tools/twenty-backup/README.md`. Wer hier etwas mit Monatsbeitrag vorschlägt,
sagt dazu, dass es dieser Entscheidung widerspricht, und lässt den Founder entscheiden.

## Was daraus folgt

Der Vertrieb hat damit genau eine Schnittstelle — den Bot. Das ist die einfachste Lösung und die
einzige, die ohne Schulung funktioniert. Es heißt aber auch: **läuft der Bot nicht, gibt es keine
Übergabe.** Kein zweiter Weg, kein Formular, keine Weboberfläche als Rückfallebene.

Deshalb sind drei Dinge nötig, nicht zwei:

1. Der Bot läuft. — *startet automatisch, siehe unten*
2. Docker läuft (für Twenty). — *startet automatisch*
3. Der Rechner ist an. — **das bleibt an dir**

Fehlt (2), geht die Aufnahme trotzdem durch: die Dateien entstehen, und der CRM-Eintrag wandert in
die Nachreichmappe (`~/Documents/Kundendaten-vertraulich/_leadbot-state/crm-offen/`). Der Bot
versucht es alle fünf Minuten von selbst und meldet sich im Chat, sobald es geklappt hat. Der
Vertrieb muss davon nichts wissen und nichts tun.

Nach einem Neustart braucht Docker eine knappe Minute. Fällt eine Aufnahme genau in dieses
Fenster, greift derselbe Weg: Dateien entstehen, CRM-Eintrag wird nachgereicht. Der Spiegel meldet
in dieser Minute einmal `Abgleich fehlgeschlagen` und fängt sich beim nächsten Takt von selbst.

Fehlt (1) oder (3), ist die Aufnahme verloren — Telegram hält die Nachricht zwar 24 Stunden vor,
aber der Vertrieb bekommt keine Antwort und bricht ab.

## Es läuft von selbst — seit 2026-08-16

Beide Dienste starten bei der Anmeldung und kommen nach einem Absturz zurück:

| Dienst | Label | Protokoll |
|---|---|---|
| Lead-Bot | `de.agentur.leadbot` | `~/Library/Logs/agentur-leadbot.log` |
| Trello-Spiegel | `de.agentur.trello-spiegel` | `~/Library/Logs/agentur-trello-spiegel.log` |
| Twenty-Sicherung | `de.agentur.twenty-backup` | `~/Library/Logs/agentur-twenty-backup.log` |
| Auslagern zu Hetzner | `de.agentur.twenty-auslagern` | `~/Library/Logs/agentur-twenty-auslagern.log` |

Der letzte ist **noch nicht aktiviert** — er wartet auf die bestellte Storage Box und den
unterzeichneten AVV (`tools/twenty-backup/README.md`, Abschnitt „Was noch zu tun ist").

Die Sicherung ist kein Dauerdienst — sie läuft täglich um 13:00 und bei jeder Anmeldung, prüft
sich selbst durch einen Rückspieltest und endet. `tools/twenty-backup/README.md` erklärt, was
gesichert wird und wie man es zurückholt. Ein Blick, ob sie noch greift:

```bash
node tools/twenty-backup/sichern.mjs --status
```

Am 2026-08-16 durch einen echten Neustart bestätigt: beide Dienste standen nach der Anmeldung mit
`runs = 1`, der Bot mit offener Verbindung zu Telegram, alle Container oben, Twenty auf 200.

Docker Desktop startet ebenfalls mit — `AutoStart: True` in
`~/Library/Group Containers/group.com.docker/settings-store.json`, die Container tragen
`restart: always` und sind nach einem Docker-Neustart in wenigen Sekunden wieder da.

```bash
# Neu laden nach Codeänderungen
launchctl kickstart -k gui/$(id -u)/de.agentur.leadbot

# Anhalten / wieder starten
launchctl bootout    gui/$(id -u)/de.agentur.leadbot
launchctl bootstrap  gui/$(id -u) ~/Library/LaunchAgents/de.agentur.leadbot.plist
```

## Die Falle dabei: TCC

**macOS lässt LaunchAgents nicht an `~/Documents`** — dort liegen aber sowohl das Repository als
auch `Kundendaten-vertraulich`. Ohne Freigabe startet der Prozess, hängt beim `open()` der eigenen
Skriptdatei und tut nie etwas: keine Fehlermeldung, kein Absturz, und `launchctl` meldet dabei
fröhlich `state = running`. Ein Testagent auf derselben Ebene bekommt:

```
ls: /Users/…/Documents/PROJECTS/agency-team: Operation not permitted
```

Gelöst durch **Festplattenvollzugriff für die node-Binärdatei**
(*Systemeinstellungen → Datenschutz & Sicherheit*):

```
/opt/homebrew/Cellar/node/26.0.0/bin/node
```

> **Nach jedem `brew upgrade node` neu erteilen.** Der Pfad enthält die Versionsnummer. Nach dem
> Upgrade zeigt die Freigabe auf eine Version, die es nicht mehr gibt, und das Fehlerbild ist
> exakt das oben beschriebene: Dienste „laufen", der Bot schweigt.

Der Schnitt ist grob — danach darf jedes node-Skript auf dem Rechner überall lesen. Bewusst so
entschieden am 2026-08-16. Die saubere Alternative bleibt offen: Repository und
`Kundendaten-vertraulich` aus `~/Documents` in die Home-Ebene verschieben, dann ist TCC gar nicht
beteiligt und der Vollzugriff kann wieder weg.

Auf dem Server stellt sich die Frage nicht — Linux kennt kein TCC, dort ist es ein systemd-Unit.

## Der Rückfallweg, wenn das CRM weg ist

Das ist die einzige Zusage im Autonomiebetrieb, die man nicht sieht, solange alles läuft: geht
Twenty mitten in einer Aufnahme aus, entstehen die Dateien trotzdem, der CRM-Eintrag wandert in
die Nachreichmappe, und der Bot versucht es alle fünf Minuten allein. Zwei Tests halten das fest
(`npm run test:offline` kommt ohne Twenty aus, `npm test` nimmt den dritten dazu).

Zwei Dinge daran waren am 2026-08-16 kaputt und sind es nicht mehr:

- Die Nachreichmappe wurde nur beim Start angelegt. War sie zur Laufzeit weg, warf ausgerechnet
  der Rettungszweig einen Fehler — die Aufnahme wäre verloren gewesen, und der Vertrieb hätte
  gar keine Antwort bekommen.
- Nur der Betrieb selbst wurde vor doppeltem Anlegen geschützt, alles dahinter nicht. Ein Lauf,
  der nach dem Betrieb abbricht, hätte bei jedem Fünf-Minuten-Takt einen weiteren
  Ansprechpartner, eine weitere Anfrage und einen weiteren Satz Aufgaben erzeugt. Über Nacht rund
  280 Versuche, ohne dass jemand zusieht.

## Ob es wirklich läuft — richtig prüfen

`launchctl list` und `state = running` sagen **nichts** darüber aus, ob der Bot arbeitet; im
TCC-Fall meldeten sie Erfolg, während der Prozess hing. Zwei Prüfungen, die nicht lügen:

```bash
# 1. Hält der Bot eine Verbindung zu Telegram?  Muss ESTABLISHED zeigen.
lsof -p $(pgrep -f leadbot/bot.mjs) -nP | grep TCP

# 2. Schreibt er?  Nach dem Start müssen vier Zeilen im Protokoll stehen.
cat ~/Library/Logs/agentur-leadbot.log
```

Nicht geeignet ist ein eigener `getUpdates`-Aufruf: Telegram beendet dabei die laufende Abfrage
des Bots und schickt den 409 **ihm**, nicht dem Prüfenden. Man bekommt `ok: true` und hält das
fälschlich für „der Bot läuft nicht" — und hat ihm nebenbei die Verbindung abgeschnitten.

## Was der Vertrieb wissen muss

Eine Regel, mehr nicht:

> Nach `/new_client` antwortet der Bot **sofort**. Kommt binnen weniger Sekunden nichts, ist er
> gerade nicht erreichbar — dann kurz durchrufen und die Aufnahme später machen. Nicht die
> Antworten ins Leere schreiben.

Kein Wort über Docker, CRM oder Server. Das ist nicht seine Aufgabe.
