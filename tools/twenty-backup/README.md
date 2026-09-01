# Sicherung von Twenty

In der Datenbank stehen die Ansprechpartner unserer Kunden. Bis zum 2026-08-16 lagen sie in genau
einem Docker-Volume — ein `docker compose down -v`, ein beschädigtes Volume, ein Fehlgriff beim
Aufräumen, und sie wären weg gewesen, ohne dass vorher irgendetwas kaputt ausgesehen hätte.

Gesichert wird **täglich, geprüft und automatisch**.

## Was gesichert wird

| Datei | Inhalt | Warum |
|---|---|---|
| `datenbank.dump` | `pg_dump -Fc` der Datenbank `default` | Firmen, Personen, Aufgaben, Chancen |
| `rollen.sql` | `pg_dumpall --globals-only` | beantwortet beim Zurückspielen die Frage „welche Benutzer gab es" |
| `dateien.tar.gz` | Volume `twenty_server-local-data` | Anhänge und Profilbilder. Wer nur die Datenbank sichert, stellt eine Oberfläche voller toter Bilder wieder her |
| `MANIFEST.txt` | Prüfsummen, Versionen, Umfang, Schlüssel-Fingerabdruck | damit man einem Stand ansieht, ob er vollständig ist, ohne ihn einzuspielen |

**Nicht enthalten: der `ENCRYPTION_KEY`.** Läge er neben den Daten, die er schützt, wäre die
Trennung aufgehoben — wer den Ordner hat, hätte alles. Im Manifest steht nur sein Fingerabdruck.

## Wo sie liegt

```
~/Documents/Kundendaten-vertraulich/_twenty-backup/JJJJ-MM-TT_HHMM/
```

Im Ordner für Kundendaten, **nicht im Repository** — CLAUDE.md §2.7. Verschlüsselt ist der Ordner
dadurch, dass die Platte es ist (FileVault ist an). Ein eigener Sicherungsschlüssel wäre ein
zweiter Schlüssel, den man verlieren kann, und das Problem hatten wir gerade erst.

Aufbewahrt werden die letzten **14 Stände** plus der **jeweils erste Stand der letzten 6 Monate**.
Der zweite Teil ist der wichtigere: der Fehler, den man wirklich fürchtet, ist nicht die kaputte
Platte, sondern der stillschweigend gelöschte Datensatz, der drei Wochen später auffällt.

## Wann sie läuft

`de.agentur.twenty-backup`, täglich 13:00 **und** bei jeder Anmeldung. Der zweite Auslöser ist
kein Übereifer: an dem Tag, an dem der Rechner um 13 Uhr zugeklappt war, gäbe es sonst keinen
Stand. Ein Stand, der jünger als 12 Stunden ist, hält den Lauf an, damit nicht bei jedem Anmelden
ein neuer entsteht.

Läuft Docker nicht, endet der Lauf mit `Übersprungen` und Rückgabewert 0. Das ist Absicht: auf
diesem Rechner ist Docker regelmäßig aus, und ein Dienst, der dafür rot leuchtet, wird nach der
dritten Woche ignoriert.

```bash
node tools/twenty-backup/sichern.mjs --status      # wie alt ist der jüngste Stand?
node tools/twenty-backup/sichern.mjs --test        # sichern und testweise zurückspielen
node tools/twenty-backup/sichern.mjs --erzwingen   # auch wenn der letzte Stand frisch ist
node tools/twenty-backup/sichern.mjs --trocken     # nur zeigen, was passieren würde
```

## Warum jeder Lauf geprüft wird

Eine Sicherung, die nie zurückgespielt wurde, ist eine Vermutung. Deshalb:

1. **Jeder Lauf** liest den frischen Dump sofort wieder (`pg_restore --list`) und zählt Schemata,
   Tabellen und Dateien. Ist der Dump leer oder unlesbar, wird der Ordner **gelöscht** statt
   behalten — ein kaputter Stand, der wie ein guter aussieht, ist schlimmer als keiner.
2. **`--test`** (so läuft der Dienst) spielt zusätzlich in eine Wegwerf-Datenbank zurück und
   vergleicht die Tabellenzahl mit dem Original. Die Wegwerf-Datenbank wird danach gelöscht.

Erster geprüfter Stand: 2026-08-16, 100 Tabellen, 46 Dateien, Rückspieltest bestanden.

## Zurückspielen

> Vorher `MANIFEST.txt` lesen. Stimmt der Fingerabdruck des `ENCRYPTION_KEY` nicht mit dem
> überein, den man einsetzen will, sind hinterher alle verschlüsselten Felder unlesbar — und das
> merkt man erst an einer kaputten Integration, nicht beim Zurückspielen.
>
> ```bash
> printf %s "$ENCRYPTION_KEY" | shasum -a 256 | cut -c1-16
> ```

```bash
STAND=~/Documents/Kundendaten-vertraulich/_twenty-backup/2026-08-16_1629
CD=~/Documents/PROJECTS/twenty-crm/twenty

# 1. Twenty anhalten, Datenbank stehen lassen
docker compose -f $CD/docker-compose.yml stop server worker

# 2. Datenbank neu anlegen und einspielen
docker exec twenty-db-1 psql -U postgres -c 'drop database if exists "default"'
docker exec twenty-db-1 psql -U postgres -c 'create database "default"'
docker run --rm --network container:twenty-db-1 -v "$STAND:/b:ro" postgres:16 \
  pg_restore -h localhost -U postgres -d default --no-owner --no-privileges /b/datenbank.dump

# 3. Dateien zurück ins Volume
docker run --rm -v twenty_server-local-data:/d -v "$STAND:/b:ro" alpine \
  sh -c 'rm -rf /d/* && tar xzf /b/dateien.tar.gz -C /d'

# 4. Wieder starten
docker compose -f $CD/docker-compose.yml start server worker
```

Schritt 2 wirft die bestehende Datenbank weg. Wer nur einen einzelnen gelöschten Datensatz sucht,
nimmt stattdessen den Weg über eine Nebendatenbank und kopiert die eine Zeile heraus:

```bash
docker exec twenty-db-1 psql -U postgres -c 'create database probe'
docker run --rm --network container:twenty-db-1 -v "$STAND:/b:ro" postgres:16 \
  pg_restore -h localhost -U postgres -d probe --no-owner --no-privileges /b/datenbank.dump
# … heraussuchen, zurückschreiben, dann:
docker exec twenty-db-1 psql -U postgres -c 'drop database probe'
```

## Die Kopie außer Haus — Hetzner Storage Box

> **Zurückgestellt bis zu den ersten Einnahmen** — derselbe Auslöser wie beim Serverumzug
> (`tools/BETRIEB.md`). Die Storage Box ist ein laufender Fremdkostenpunkt, und die Entscheidung
> vom 2026-08-16 lautet: bis dahin läuft alles hier, auf `localhost`, ohne Monatsbeiträge.
> Vorbereitet ist es trotzdem — Skript, Dienst und Anleitung stehen, der Dienst ist bewusst
> **nicht geladen**. Nichts bestellt, nichts hochgeladen.
>
> Bis dahin gilt: die Sicherung schützt gegen Fehlbedienung, nicht gegen den Verlust des Rechners.
> Das ist eine bewusst getragene Lücke, keine übersehene.

Alle lokalen Stände liegen auf derselben Platte wie die Datenbank. Gegen Fehlbedienung hilft das,
gegen Plattenschaden, Diebstahl und Feuer nicht. Vorgesehen dafür: **Hetzner Storage Box BX11**,
1 TB für **3,20 €/Monat netto**, deutscher Standort, AVV verfügbar, keine Mindestlaufzeit, kein
Server nötig.

Kein iCloud, kein Dropbox, kein Google Drive: die Stände enthalten personenbezogene Daten, und
diese Anbieter sind Drittland ohne AVV, den wir geschlossen hätten.

### Verschlüsselt, bevor es den Rechner verlässt

Ausgelagert wird mit **restic**: die Stände werden hier verschlüsselt und dedupliziert, Hetzner
sieht verschlüsselte Blöcke, nicht die Namen unserer Ansprechpartner. Geprobt am 2026-08-16 gegen
eine lokale Ablage — erster Lauf 8,2 MB, zweiter Lauf **0 B** (nichts geändert, nichts übertragen),
Rückspielung Byte für Byte identisch mit der Prüfsumme im Manifest.

Bei einem Stand pro Tag und diesem Datenvolumen füllt sich das Terabyte praktisch nie.

### Wenn es so weit ist

Erst wenn die Zurückstellung oben aufgehoben ist. Bestellen und den Vertrag unterschreiben kann
ohnehin nur ein Mensch (CLAUDE.md §2.5) — das Skript verweigert bis dahin den Dienst.

1. **Bestellen** — Hetzner-Konto → Storage Box → **BX11**. Notieren: Benutzername `uXXXXXX`.
2. **AVV unterzeichnen**, im Hetzner-Konto unter *Rechtliches → Auftragsverarbeitung*. **Vor dem
   ersten Upload**, nicht danach.
3. **SSH-Schlüssel hinterlegen** — in der Verwaltung der Box, Inhalt von `~/.ssh/id_ed25519.pub`.
   Storage Boxen sprechen SSH auf **Port 23**:
   ```bash
   ssh -p23 uXXXXXX@uXXXXXX.your-storagebox.de
   ```
4. **`.env` ergänzen:**
   ```
   HETZNER_BOX_USER=uXXXXXX
   HETZNER_BOX_PATH=/twenty
   HETZNER_AVV=JJJJ-MM-TT
   ```
5. **Ablage anlegen und ersten Lauf starten:**
   ```bash
   node tools/twenty-backup/auslagern.mjs --init
   node tools/twenty-backup/auslagern.mjs
   ```
6. **Dienst aktivieren** (läuft dann täglich 14:00, eine Stunde nach der lokalen Sicherung):
   ```bash
   launchctl bootstrap gui/$(id -u) ~/Library/LaunchAgents/de.agentur.twenty-auslagern.plist
   ```

### Drei Riegel, die vor dem Hochladen greifen

Das Skript lädt nichts hoch, solange eines davon fehlt — jeweils mit Anleitung statt mit
Fehlermeldung:

| fehlt | Begründung |
|---|---|
| `HETZNER_BOX_USER` | es gibt noch keine Box |
| `HETZNER_AVV` | Übergabe personenbezogener Daten an einen Dienstleister ohne Vertrag nach Art. 28 DSGVO — auch verschlüsselt eine Auftragsverarbeitung. Einmal hochgeladen ist nicht rückgängig zu machen |
| Ablage-Passwort im Schlüsselbund | ohne es ist die ausgelagerte Kopie unentschlüsselbar, auch für uns |

### Das Ablage-Passwort

Liegt im Anmelde-Schlüsselbund unter `twenty-crm` / `restic-repo-password` und gehört daneben in
Passwords.app — **dieselbe Regel wie beim `ENCRYPTION_KEY`, aus demselben Grund**: geht es
verloren, ist die Kopie bei Hetzner wertlos, und ein Backup des Backups gibt es nicht.

```bash
security find-generic-password -a twenty-crm -s restic-repo-password -w
```

### Betrieb

```bash
node tools/twenty-backup/auslagern.mjs --status    # was liegt oben?
node tools/twenty-backup/auslagern.mjs --pruefen   # Ablage auf Vollständigkeit prüfen
node tools/twenty-backup/auslagern.mjs --trocken   # zeigen, was passieren würde
```

Aufbewahrung oben wie unten: 14 tägliche und 6 monatliche Stände. Nicht aus Platzgründen —
personenbezogene Daten unbegrenzt aufzuheben ist Art. 5 DSGVO, nicht Sparsamkeit.

Zurückholen, wenn diese Platte weg ist: restic auf dem neuen Rechner installieren, Passwort aus
Passwords.app, dann

```bash
export RESTIC_PASSWORD='…'
restic -r sftp:uXXXXXX@uXXXXXX.your-storagebox.de:/twenty -o sftp.args='-p 23' \
  restore latest --target /wohin
```

und weiter nach der Anleitung unter „Zurückspielen".
