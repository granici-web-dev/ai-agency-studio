# Automatisierung der Website-Linie

Beschlossen am 2026-08-17 (`.claude/entscheidungen.md`): Die Website-Linie wird als Maschine
gebaut, KI-Audit und KI-Module bleiben Handarbeit.

Diese Datei sagt, **was genau ohne den Founder laufen kann, was nicht, und in welcher Reihenfolge
gebaut wird.** Sie ersetzt `.claude/pipeline.md` nicht — die beschreibt, *was* passiert, diese hier
beschreibt, *wer es auslöst*.

## Der Maßstab

Nicht „läuft ein Agent?", sondern: **wie viele Minuten des Founders kostet eine Website von der
Zusage bis zur Rechnung?** Alles andere ist Selbstbeschäftigung. Der Wert wird pro Projekt
gemessen und in `projects/<slug>/status.md` festgehalten, sonst weiß nach dem dritten Projekt
niemand, ob es besser geworden ist.

## Zustand für Zustand

| # | Zustand | Ohne Founder? | Woran es hängt |
|---|---|---|---|
| 0 | LEAD | **läuft schon** | Lead-Bot nimmt auf, legt den Ordner an, schreibt ins CRM |
| 1 | BRIEF | ja | Fehlende Angaben erzeugen eine Rückfrage — und die ist ein Tor, kein Schritt |
| 2 | RESEARCH | **ja, vollständig** | Bestandsseite abrufen, rechtlich prüfen, Aufwandstreiber benennen. Kein Tor davor |
| 3 | ANGEBOT | Entwurf ja, Versand nein | §2.5 Founder · §2.2 Sprachprüfung durch einen Menschen |
| 4 | SETUP | halb | Hosting und Domain kosten Geld und laufen auf den Kunden — §2.5 |
| 5 | BUILD | **heute nein** | siehe unten: hier liegt das eigentliche Problem |
| 6 | CONTENT | ja | Bildgenerierung kostet Credits, also Freigabe vor der Ausgabe |
| 7 | GATE Sprache | **nein** | §2.2 verlangt eine benannte deutschsprachige Person. Die gibt es nicht |
| 8 | GATE Recht | Prüfung ja | Rechtstexte kommen von einem Anbieter mit Haftung — Anbieter noch nicht gewählt |
| 9 | QA | **ja, vollständig** | Lighthouse, Screenshots, Formulartests, Netzwerkmitschnitt sind Skriptarbeit |
| 10 | PRODUKTION | nein | §2.5, bewusst |
| 11 | HANDOVER | Entwurf ja, Übergabe nein | §2.5 |
| — | WARTUNG | **ja, fast vollständig** | Updates, Backups, Monitoring, Monatsbericht |

## Was daraus folgt, und es war nicht die erwartete Antwort

**1. Der Engpass ist BUILD, nicht die Orchestrierung.**
Recherche, QA und Wartung sind Skriptarbeit und morgen automatisierbar. Der Bau einer WordPress-Seite
ist es nicht — solange jede Seite anders gebaut wird. Kein Agent und keine noch so gute
Zustandsmaschine repariert das. Was es repariert, ist ein **Standardunterbau**: ein Theme, ein
festgelegter Plugin-Satz, eine feste Block-Bibliothek, ein fertiges Rechts-Setup. Damit wird BUILD
von Handwerk zu Konfiguration — und erst dann lohnt sich die Automatisierung darüber.

Deshalb ist der Unterbau Schritt 1 und nicht Schritt 5.

**2. Die Wartung ist der am besten automatisierbare Teil des ganzen Geschäfts — und der einzige
wiederkehrende.** Updates, Backups, Monitoring, Wiederherstellung und ein Monatsbericht laufen
ohne Menschen, die Grenzkosten je zusätzlichem Kunden gehen gegen null. Das ist ein Argument in
der offenen Preisfrage: Was Geld verdient, ohne Zeit zu kosten, ist die Betreuung, nicht der
einmalige Bau. Entschieden ist daran nichts (§4.2 bleibt VORSCHLAG), aber es gehört auf den Tisch.

**3. Drei Tore bleiben stehen, und keins davon ist ein Programmierproblem.**

- **Kein deutschsprachiger Prüfer (§2.2).** Alles, was die Maschine produziert, endet vor diesem
  Tor. Eine Website-Fabrik ohne Prüfer baut Seiten, die nicht ausgeliefert werden dürfen. Das ist
  der härteste Punkt auf dieser Seite, und er wird nicht durch mehr Code kleiner.
- **§2.5** — Versand, Produktions-Deploy, Unterschrift, Geldausgabe bleiben beim Menschen. Bewusst.
- **Kein Server.** Der Rechner ist zu, die Maschine steht. Auslöser für die Neubewertung sind die
  ersten Einnahmen (`.claude/entscheidungen.md`).

## Wie die Maschine laufen soll

Ein Ablaufdienst, kein Dauerprozess. Er wacht auf, sieht nach, tut einen Schritt, schläft wieder.

```
Wecker  →  alle projects/*/status.md lesen  →  je Projekt: welcher Zustand, was fehlt?
                                                │
                    ┌───────────────────────────┼───────────────────────────┐
                    │                           │                           │
              Agentenschritt                 Tor                        nichts zu tun
                    │                           │                           │
        Agent headless ausführen        in die Founder-Liste          weiterschlafen
        Artefakt schreiben              Telegram-Ping (nur Slug,
        status.md fortschreiben          nie Kundendaten — kein AVV)
        Review durch den Orchestrator
```

Drei Regeln, ohne die das Ding gefährlich wird:

1. **Ein Schritt pro Aufwachen.** Kein Projekt läuft in einem Rutsch durch. Wer eine Kette von
   zwölf Agentenaufrufen ohne Zwischenkontrolle startet, findet den Fehler an Stelle zwölf.
2. **Jeder Schritt ist idempotent** — dieselbe Lehre wie beim CRM-Push: der Dienst wird abstürzen,
   und der nächste Lauf darf nichts verdoppeln.
3. **Kein Tor wird selbst geöffnet.** Der Dienst darf einen Zustand nicht verlassen, dessen
   Exit-Kriterium eine menschliche Freigabe ist — auch dann nicht, wenn alles andere fertig ist.
   Diese Bedingung wird geprüft, nicht angenommen.

## Die Basis muss das Bearbeiten durch den Kunden überleben

Wir liefern ein CMS. Der Inhaber tauscht seine Bilder selbst — das ist der Sinn der Sache und
spart uns die Bildbeschaffung. Es heißt aber, dass die Seite **nach** der Übergabe weiter
funktionieren muss, ohne dass jemand von uns hinsieht. Bei 79 €/Monat mit 30 Minuten Änderungen
ist jeder Rückruf „ich hab was verschoben und jetzt ist alles kaputt" verlorene Marge.

**Ein Theme, nicht eines je Branche.** Der Unterschied zwischen Barbershop, Salon, Praxis und
Handwerk lebt in **Style Variations** (`styles/*.json`) und in den **Patterns**, nicht in einer
eigenen Codebasis. WordPress kann beides ab Werk: benannte Stilvarianten im selben Theme,
Patterns nach Kategorie. Ein Theme je Branche hieße dieselbe Rechnung wie bei gekauften Themes —
zehn Update-Pfade, zehn QA-Basen, zehn Rechts-Setups.
Gestaltungsvorlagen des Founders je Branche: `.claude/design-referenzen.md`.

Vier Anforderungen an den Unterbau, alle vor dem ersten Kunden zu erfüllen:

1. **Schmale Bearbeitungsfläche.** Bild tauschen, Text ändern, Öffnungszeiten und Preise pflegen —
   ja. Layout verschieben — nein. Block-Patterns werden gesperrt (`templateLock`), Template-Parts
   ebenso. Was nicht bearbeitbar ist, kann nicht kaputtgehen.
2. **Rolle Redakteur, nicht Administrator.** Kein Plugin-Zugriff, keine Theme-Dateien. Wer Admin
   vergibt, verkauft anschließend Wiederherstellungen als Gefälligkeit.
3. **Bildverarbeitung im Unterbau, nicht im Kunden.** Automatisches Skalieren, WebP, fester
   Seitenschnitt je Bildplatz. Sonst lädt der Inhaber ein 5-MB-Handyfoto hoch und die
   Lighthouse-Zusage aus §6 ist am nächsten Tag hinfällig — und wir haben sie schriftlich gegeben.
4. **Bildrechte sind Mitwirkungspflicht des Kunden, schriftlich.** Wer Kundinnen im Salon
   fotografiert, braucht deren Einwilligung (Recht am eigenen Bild, § 22 KUG, dazu DSGVO). Lädt
   der Inhaber solche Fotos selbst hoch, muss vorher schwarz auf weiß stehen, wer dafür geradesteht.
   Gehört in Handover und Leistungsbeschreibung, nicht in ein Gespräch.

Bilder für die **Demo-Seite** kommen aus Higgsfield (Credits sind vorhanden) und aus freien
Quellen. Ein Stock-Abo wird dafür nicht gebraucht — geprüft am 2026-08-17, Envato Elements Core
14,50 €/Monat, verworfen: die Bilder, auf die es ankommt, sind die des Kunden.

## Reihenfolge

| | Was | Warum an dieser Stelle |
|---|---|---|
| **1** | **Standardunterbau WordPress** — **eigenes Block-Theme** (kein gekauftes Theme, kein Page-Builder), Plugin-Satz, Pattern-Bibliothek je Branche, Rechts-Setup, lokale Fonts, Backup | Ohne ihn ist alles Weitere Automatisierung einer Handarbeit. Entschieden am 2026-08-17 gegen gekaufte Themes: die tragen Google Fonts, CDN-Aufrufe und gebündelte Plugins ohne eigene Update-Rechte herein und scheitern an §6 |
| **2** | **QA-Skript** — Lighthouse, Screenshots 360/768/1440, Formulartest, Mitschnitt externer Aufrufe, Ablage in `nachweise/` | Prüft ab Tag eins den Unterbau selbst, nicht erst Kundenseiten |
| **3** | **RESEARCH-Schritt** | Erster Zustand, der ganz ohne Tor durchläuft — der ehrlichste Test der Mechanik |
| **4** | **Ablaufdienst** | Erst wenn es zwei, drei Schritte gibt, die er auslösen kann |
| **5** | **Wartungsautomat** | Braucht eine erste Live-Seite, sonst gibt es nichts zu warten |

Schritt 1 und 2 brauchen **keinen Server und keinen Kunden**. Sie sind sofort machbar und werden
gegen unsere eigene Seite gebaut — die brauchen wir ohnehin, und sie ist der erste Beleg.

## Offen

- Anbieter für Rechtstexte mit Haftungsübernahme (Zustand 8) — nicht gewählt
- Deutschsprachige Prüfperson (§2.2) — nicht vorhanden
- Buchungs-Plugin, selbst gehostet, EU (§4.2 VORSCHLAG) — nicht ausgewählt
- Ob der Ablaufdienst auf dem Rechner oder auf einem Server läuft — hängt an den ersten Einnahmen
