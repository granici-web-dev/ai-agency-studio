# Trello — seit 2026-08-16 nur noch Anzeige

> **Hier wird nicht gearbeitet.** Aufgaben, Leads und Projektstatus laufen über unser eigenes
> CRM: `.claude/crm.md`. Die 16 offenen Karten des Boards „Sofabelle" wurden am 2026-08-15 nach
> Twenty übernommen (`tools/twenty-provision/tasks.mjs`), das Board bleibt als Archiv bestehen
> und wird nicht gelöscht.
>
> **Was Trello noch tut:** Das Board „Agentur — Aufgaben (Spiegel)" zeigt die Aufgaben aus dem
> CRM an — in eine Richtung, per `tools/trello-spiegel/spiegel.mjs`. Grund ist rein praktisch:
> Twenty läuft auf `localhost` und ist vom Telefon nicht erreichbar, Trello schon. Wer eine
> Karte dort verschiebt, sieht sie beim nächsten Abgleich zurückwandern; das Protokoll nennt
> sie dann namentlich. **Der Zustand wird im CRM geändert, nie auf dem Board.**
>
> Auf den Spiegel wandert bewusst wenig: Titel, Spalte, Wartepartner, Abnahmekriterium. Keine
> Ansprechpartner, keine Nummern, keine wörtlichen Kundenzitate — Atlassian bleibt Drittland
> (CLAUDE.md §2.7). Wo schon der Betriebsname personenbezogen ist (Einzelunternehmen
> „Friseur Müller"), schaltet `TRELLO_ANONYM=1` auf den Projektordner-Slug um.
>
> **Warum abgelöst:** Trello gehört zu Atlassian, jede Karte war eine Übermittlung in ein
> Drittland — deshalb durften nie Kundendaten darauf. Das eigene CRM hat diese Einschränkung
> nicht. Und zwei Werkzeuge für denselben Stand bedeuten, dass eines gepflegt wird und das
> andere lügt.
>
> Diese Datei bleibt als Beleg, wie der Stand bis dahin geführt wurde, und für den Zugriff auf
> das Archiv. `scripts/trello.sh` funktioniert weiterhin lesend.

---

## Historischer Stand bis 2026-08-15

Alles Folgende beschreibt, wie bis zu diesem Tag gearbeitet wurde.
Keine dieser Anweisungen ist noch gültig.

Trello ist das **Aufgabenbrett**. Es beantwortet: was ist zu tun, wer macht es, was hängt, was
wartet auf wen.

## Die Trennung, die eingehalten werden muss

> **Trello führt den Stand der Arbeit. Die Platte führt den Inhalt.**

| | Trello | `projects/<slug>/` |
|---|---|---|
| Aufgabe, Zuständigkeit, Fälligkeit, Spalte | ✅ | — |
| Brief, Research, Entscheidungen, Reviews, Nachweise | — | ✅ |
| Was besprochen und zugesagt wurde | — | ✅ |
| Schneller Blick „wo stehen wir" | ✅ | `status.md` |

**Eine Karte enthält niemals Inhalt, der nicht auch auf der Platte steht.** Sie ist Verweis plus
Status. Wer den Brief in eine Kartenbeschreibung kopiert, hat zwei Wahrheiten erzeugt, und
spätestens beim ersten Widerspruch weiß niemand mehr, welche gilt.

Bei Konflikt gewinnt die Platte. Trello ist rekonstruierbar, `entscheidungen.md` nicht.

## Datenschutz — bindend

Trello gehört zu Atlassian, einem US-Anbieter. Jede Karte ist eine Übermittlung in ein Drittland.

- **Keine personenbezogenen Daten von Kunden oder deren Kunden in Trello.** Keine Namen, keine
  Telefonnummern, keine E-Mail-Adressen, keine Gesprächsinhalte, keine Exporte, keine
  Screenshots mit Kundendaten.
- Karten benennen das Projekt und die Aufgabe, nicht die Person: „Kunde A · Rückfrage Preisspannen
  offen", nicht „Herr X will keine Preise nennen".
- Ansprechpartner werden über die Rolle referenziert („Vertriebsleiter"), nicht über den Namen.
- Boards sind privat, nicht öffentlich, nicht über Link teilbar.
- Vor dem produktiven Einsatz: Atlassian-DPA prüfen und in die Ablage (`german-legal-compliance`).

Wenn eine Aufgabe ohne personenbezogene Daten nicht beschreibbar ist, gehört sie nicht auf die
Karte, sondern in die Datei — die Karte verlinkt sie dann nur.

## Boards

### 1 · „Agentur — Leads" (ein Board für alle Kunden)

Bedient Zustand 0 · LEAD, beide Flows.

| Liste | Inhalt |
|---|---|
| Neu | Anfrage eingegangen, noch nicht angesehen |
| Triage | wird eingestuft (Flow B) bzw. Handoff läuft (Flow A) |
| Kontaktiert | Erstantwort raus, Ball beim Kunden |
| Wartet auf Antwort | mit Nachfassdatum |
| Qualifiziert → Projekt | wird zum eigenen Projektboard |
| Abgesagt | mit Grund als Kommentar |

### 2 · Projektboard, eines je Kunde

| Liste | Bedeutung |
|---|---|
| Backlog | erfasst, noch nicht geschnitten |
| Blockiert | wartet auf Kunde, Founder oder ein Gate — **Grund steht auf der Karte** |
| Bereit | Voraussetzungen erfüllt, kann begonnen werden |
| In Arbeit | genau eine Karte je Agent |
| Review | beim `co-founder-orchestrator` |
| Freigabe Founder | alles nach CLAUDE.md §2.5 |
| Fertig | angenommen |

Pipeline-Zustand ist ein **Label**, keine Liste. Sonst hat das Board 13 Spalten und niemand
benutzt es.

## Karten

**Titel:** `[T2] Wissensbasis aufbauen` — Teilprojekt in eckigen Klammern, dann die Aufgabe.

**Beschreibung**, immer diese vier Zeilen:

```
Datei:      projects/kunde-a/t2-chat-assistent/00-vorueberlegungen.md
Owner:      ai-engineer
Abnahme:    <woran der Orchestrator prüft>
Blockiert durch: <Karte oder "nichts">
```

**Labels:** Pipeline-Zustand · Teilprojekt · `blockierend` · zuständiger Agent
**Fällig:** nur setzen, wenn das Datum echt ist. Erfundene Fälligkeiten machen das Brett wertlos.
**Checkliste:** die Abnahmekriterien, nicht die Arbeitsschritte.

## Wer schreibt

Ein Kartentyp, ein Schreiber. Sonst überschreiben sich zwei Agenten gegenseitig.

| | Schreibt |
|---|---|
| `co-founder-orchestrator` | Aufgabenkarten: anlegen, verschieben, Review-Ergebnis kommentieren |
| `pm-client-lead` | Lead-Board, Karten zur Kundenkommunikation, Blockiert-Karten mit Kundenbezug |
| alle anderen Agenten | **nichts** — sie melden an den Orchestrator, der pflegt die Karte |

Jede Statusänderung, die auf der Karte landet, landet auch in `status.md`. Die Karte ist die
schnelle Sicht, `status.md` die belastbare.

## Zugang

Trello vergibt API-Schlüssel nur über ein Power-Up. Ablauf:

1. **Power-Up anlegen** — `https://trello.com/apps/admin`, „New" · Name z. B. „Agency Automation",
   Workspace wählen. Es muss nichts veröffentlicht werden; das Power-Up ist nur der Träger des
   Schlüssels.
2. Power-Up öffnen → Reiter **API Key** → *Generate a new API Key*.
3. Auf derselben Seite rechts neben dem Key auf **Token** klicken → *Allow* → der Token wird
   angezeigt.

Der **Key** ist nicht geheim. Der **Token** ist es: er hängt am persönlichen Konto und hat dessen
Rechte — wie ein Passwort behandeln, nie ins Repository, nie in eine Trello-Karte.

Ablage in `~/.zshrc`, damit alle Sitzungen sie haben:

```bash
export TRELLO_KEY=...
export TRELLO_TOKEN=...
```

Prüfen: `scripts/trello.sh boards` — listet die eigenen Boards.

Helfer: `scripts/trello.sh`. Beispiele:

```bash
scripts/trello.sh boards                          # Boards auflisten
scripts/trello.sh lists <boardId>                 # Listen eines Boards
scripts/trello.sh cards <listId>                  # Karten einer Liste
scripts/trello.sh new-board "Kunde A"             # Projektboard mit Standardlisten
scripts/trello.sh add <listId> "[T2] Titel" "Beschreibung"
scripts/trello.sh move <cardId> <listId>
scripts/trello.sh comment <cardId> "Review: NACHARBEIT — Punkte 1,3"
```

Board- und Listen-IDs eines Projekts gehören in `projects/<slug>/trello.md`, damit niemand sie
jedes Mal sucht.

## Wenn Trello nicht erreichbar ist

Weiterarbeiten. Die Platte ist die Quelle; das Brett wird nachgezogen. Ein Ausfall des
Aufgabenbretts hält kein Projekt auf — das ist der Sinn der Trennung oben.

---

> **Offener Punkt:** Ein CRM-Datenmodell liegt als Entwurf in `.claude/crm.md`.
> Wird es produktiv, **ersetzt** es dieses Board für Leads und Projektstatus, statt daneben
> zu laufen — zwei Statuswerkzeuge bedeuten, dass eines lügt. Bis dahin gilt diese Datei.
