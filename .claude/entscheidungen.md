# Entscheidungen der Agentur

Nicht die Entscheidungen eines Projekts — die stehen in `projects/<slug>/entscheidungen.md`.
Hier steht, was für die **Agentur als Ganzes** gilt: Preise, Modelle, Werkzeuge, Verträge,
laufende Kosten.

## Warum es diese Datei gibt

Am 2026-08-16 ist derselbe Fehler zweimal an einem Tag passiert: Der Founder hat laut nachgedacht
— einmal über eine ausgelagerte Sicherung bei Hetzner, einmal über ein Preismodell — und beides
wurde als beschlossen in die Dateien geschrieben. Beim zweiten Mal stand ein ganzes Kapitel in
CLAUDE.md, das niemand entschieden hatte.

Der Fehler ist nicht Unachtsamkeit, sondern eine fehlende Unterscheidung. Ein Satz wie „lass uns
X machen" ist im Gespräch mehrdeutig: er kann *entscheide das* heißen oder *denk mal darüber
nach*. Ohne einen Ort, an dem der Unterschied sichtbar wird, gewinnt immer die Auslegung, die mehr
Arbeit erzeugt.

## Die Regel

Jede Sache hier hat genau einen von drei Zuständen:

| Zustand | Bedeutung |
|---|---|
| **VORSCHLAG** | Ausgearbeitet, nicht beschlossen. Wird nicht angewendet, nicht zitiert, nicht dem Kunden gezeigt. |
| **ENTSCHIEDEN** | Mit Datum. Gilt, bis sie hier widerrufen wird. |
| **ZURÜCKGESTELLT** | Beschlossen, aber nicht jetzt — mit benanntem Auslöser, nicht „später". |

Daraus folgt für jeden Agenten und jede Sitzung:

1. **Was Geld kostet, laufend oder einmalig, ist nie beschlossen, bevor es hier steht.** Das gilt
   auch dann, wenn der Founder „ja, mach" gesagt hat — dann wird es hier eingetragen, und der
   Eintrag ist der Beschluss.
2. **Wer etwas vorschlägt, das einer bestehenden Entscheidung widerspricht, sagt das im selben
   Satz.** Nicht die Entscheidung stillschweigend überholen.
3. **Ausgearbeitete Vorschläge dürfen in den Dateien liegen** — sie sind Arbeit, nicht Müll. Aber
   sie tragen die Markierung VORSCHLAG an der Stelle, an der sie stehen, nicht nur hier.

---

## ENTSCHIEDEN

### 2026-08-17 · Was automatisiert wird — und was ausdrücklich nicht
Die Website-Linie wird als Maschine gebaut: Aufnahme, Recherche, Bau, Prüfungen, QA und Wartung
laufen ohne den Founder, der greift nur an den Toren ein (CLAUDE.md §2.5). **KI-Audit und die
KI-Module bleiben Handarbeit** — jedes ist ein eigener Beratungsfall mit Interviews, Prozessen und
Abnahme, und sie zu automatisieren hieße, die Beratung wegzurationalisieren, die dort verkauft wird.

Damit ist auch das Auswahlkriterium für Pakete gesetzt: **was nicht wiederholbar ist, gehört nicht
in ein Paket.** Preise bleiben davon unberührt und weiter VORSCHLAG.
→ `.claude/automatisierung.md`

### 2026-08-16 · Kein Server, bis Einnahmen da sind
Das CRM bleibt auf `localhost`. Der Vertrieb bekommt keinen CRM-Zugang, sondern übergibt über den
Telegram-Bot. **Diese Entscheidung deckt jeden laufenden Fremdkostenpunkt**, nicht nur den Server.
Auslöser für eine Neubewertung: erste Einnahmen.
→ `tools/BETRIEB.md`, `.claude/crm.md`

### 2026-08-16 · Sicherung läuft täglich und prüft sich selbst
Lokal, mit Rückspieltest bei jedem Lauf, 14 tägliche und 6 monatliche Stände.
→ `tools/twenty-backup/README.md`

### 2026-08-16 · Schlüssel gehören in den Passwortmanager, nicht in Dateien
`ENCRYPTION_KEY` und `PG_DATABASE_PASSWORD` liegen im Anmelde-Schlüsselbund und in Passwords.app.
Beim Serverumzug wandern sie nicht ins Repository und nicht in die Serverdokumentation.
→ `.claude/crm.md`

---

## ZURÜCKGESTELLT

### Ausgelagerte Sicherung — Hetzner Storage Box, 3,20 €/Monat
Skript, Dienst und Anleitung stehen fertig, der Dienst ist nicht geladen, nichts bestellt, nichts
hochgeladen. **Auslöser: erste Einnahmen** — dieselbe Bedingung wie beim Serverumzug.
Getragene Lücke bis dahin: die Sicherung schützt gegen Fehlbedienung, nicht gegen den Verlust des
Rechners.
→ `tools/twenty-backup/README.md`

### Serverumzug — Hetzner CAX11, rund 8 €/Monat
AVV nach Art. 28 DSGVO **vor** dem Umzug, nicht danach.
→ `.claude/crm.md`

---

## VORSCHLAG

### Pakete für kleine Betriebe — 499 / 999 / 1.499 € plus 79 €/Monat
Ausgearbeitet am 2026-08-16 auf Skizze des Founders, der ausdrücklich gesagt hat, dass er sich
noch nicht sicher ist. Vollständig in CLAUDE.md §4.2 und
`.claude/vorlagen/leistungsbeschreibung.md`, beide als VORSCHLAG markiert.

Offen daran, unabhängig von der Grundsatzfrage:

- Laufzeit der Betreuung — 12 Monate ist der aktuelle Stand, 24 war der erste Gedanke
- AGB und Bindung sind **nicht anwaltlich geprüft**
- Es gibt keine benannte deutschsprachige Prüfperson, also ist der Kundentext ohnehin nicht
  auslieferbar (§2.2)

### KI-Leistungen — Analyse 490 €, Module, Betreuung, Compliance, Schulung
Ausgearbeitet am 2026-08-16 gegen den Marktabgleich in CLAUDE.md §4.4. Nie bestätigt.

### Pilotpreis für die ersten drei Salons
Hängt an der Paketfrage und fällt mit ihr.
