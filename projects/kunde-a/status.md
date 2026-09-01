# Status — Kunde A

> Ordner umbenennen, sobald der Kundenname feststeht.

**Phase:** 1 · BRIEF — `brief.md` liegt als Entwurf vor, 12 blockierende Angaben offen
**Aktuelles Teilprojekt:** T1+T2 zusammengelegt · Qualifizierer mit Preisspanne
**Stand:** 2026-08-14
**Orchestrator:** `co-founder-orchestrator` · **Kundenkontakt:** `pm-client-lead`

## Auftrag in einem Satz

**Rumänischer Hersteller individuell gefertigter Polstermöbel.** Bestandsshop auf
WordPress/Elementor; gewünscht sind ein Relaunch auf Shopify und drei KI-Bausteine. Wir beginnen
mit dem Assistenten für Kundenanfragen.

## Beteiligte

| Rolle | Sprache | Zuständig |
|---|---|---|
| Inhaber — **Maxim Ciornii** | Russisch | Angebot, Unterschrift, Zahlung, Scope |
| Vertriebsleiter — **Iordache Razvan** | Rumänisch | fachliche Abstimmung, Wissensbasis, Faktenpruefung |

## Teilprojekte

| # | Teilprojekt | Phase | Owner | Reihenfolge |
|---|---|---|---|---|
| T2 | Chat-Assistent für Kundenanfragen | **0 LEAD** | `ai-engineer` | **läuft** |
| T1 | Angebots-Assistent mit Sofortpreis | 0 LEAD | `ai-engineer` | 2 |
| T3 | Shopify-Relaunch inkl. Migration | 0 LEAD | `shopify-developer` | 3 |
| T4 | Sprachassistent | 0 LEAD | `ai-engineer` | 4 |

**Warum T2 zuerst:** kleinster abgeschlossener Umfang mit sofortigem Nutzen, kann **vor** dem
Relaunch live gehen und überlebt ihn, wenn er portabel gebaut wird (siehe
`t2-chat-assistent/00-vorueberlegungen.md`). Läuft einmal komplett durch den Prozess — damit
testen wir den Ablauf, nicht das Projekt.

## Läuft gerade

- **Schritt A1 · Handoff** — beantwortet, in `00-handoff.md` erfasst. Zentrales Ergebnis: **es
  wurde nichts zugesagt**, wir sind in Preis, Umfang und Termin frei.

## Korrektur 2026-08-14

Der Kunde verkauft **ausschließlich in Rumänien**. Die frühere Angabe „GUS, Europa, Amerika" war
ein Missverständnis. Ein Markt, eine Rechtsordnung, eine Sprache — deutlich kleinerer Umfang als
gedacht, aber außerhalb unseres Zielprofils.

## Blockiert

- **Founder-Entscheidung:** Der Kunde verkauft nicht in den DACH-Raum und fällt damit aus der eben
  beschlossenen Regel (CLAUDE.md §1). Ausnahme mit eingeschränkter Rechtszusicherung, oder Regel
  öffnen? Ohne diese Entscheidung kein Angebot
- Umfang T2 — wartet auf die Antworten zu Kanälen, Volumen, Datenzugriff und Wissensstand
- Aufwandsschätzung — wartet auf den Anfragen-Export

## Research 01 — Lead-Export ausgewertet (2026-08-14)

Vollständig: `research/01-lead-analyse.md`. 2.000 Leads, fünf Monate, ~13/Arbeitstag.

**Die Aufgabenstellung verschiebt sich.** Rund 51 % der Leads scheitern an Budget, falschem
Produkt, zu langer Lieferzeit oder Irrelevanz — vor dem ersten Verkäuferkontakt feststellbar. Nur
33,7 % erhalten überhaupt ein Angebot, 5,9 % werden Kunde. Die Last liegt nicht bei
FAQ-Beantwortung, sondern bei **Qualifizierung**.

→ **T1 und T2 sind ein Produkt**, kein zwei. Qualifizierer mit Preisspanne, der zugleich
Standardfragen beantwortet.

**Kanäle korrigiert:** Meta ist hier **kein** Nachrichtenkanal (1,0 %) — Meta-Anzeigen treiben
Traffic auf die Website. Meta-Adapter aus v1 streichen. Erreichbar für einen Textassistenten:
**46,4 %** (Site 22 + Mail 12,8 + WhatsApp 11,6). Showroom 35 % gar nicht, Telefon 14,6 % ist T4.

**Neuer Widerspruch:** Die Website nennt einen Showroom in **Ramsgate, UK**. „Nur Rumänien" ist
damit unvollständig — bei Verkauf an britische Verbraucher gilt eigenes Recht. Vor dem Angebot
klären.

**Datenschutz:** Die CSV mit 2.000 Klarnamen, Telefonnummern und E-Mail-Adressen liegt
unverschlüsselt im Projektordner. AVV, Rechtsgrundlage, Pseudonymisierung und ein Ablageort mit
Löschdatum sind vor jeder weiteren Verarbeitung zu klären.

## Kernfeststellung Kanäle (2026-08-14, teilweise überholt durch Research 01)

Anfragen kommen über **alle vier Kanäle**: Formular/E-Mail, WhatsApp, Facebook/Instagram, Telefon.

- Ein Website-Widget allein verfehlt das Ziel — der Kern wird kanalunabhängig gebaut, Kanäle sind
  Adapter
- **Ohne einheitlichen Übergabe-Posteingang erhöht der Assistent die Belastung**, statt sie zu
  senken. Voraussetzung, keine Zusatzleistung
- Meta-Verifizierung und App Review sind Terminrisiken außerhalb unserer Kontrolle — mit eigenem
  Datum in den Projektplan
- Telefon ist T4, zählt aber in die Grundlast — keine Entlastungsversprechen, die nur Textkanäle
  betreffen

## Aufgabenbrett

Board „Sofabelle" übernommen und bestückt — IDs in `trello.md`, gesicherter Altinhalt in
`research/02-bestandsboard.md`. Stand: 6 blockierte Karten (warten auf Kunde), 3 bereit,
2 beim Founder, 4 im Backlog.

## Kommunikation

Kanal: **nur E-Mail**, Absender `granici.design@gmail.com`, Sprache **Rumänisch**.
Mail 01 an den Vertriebsleiter ist versandfertig (`t2-chat-assistent/03-mail-01-entwurf.md`) —
es fehlt nur seine E-Mail-Adresse. Entwurf wird in Gmail abgelegt, gesendet wird von Hand.

## Nächste Schritte

1. **Founder liest `brief.md`** und entscheidet über die Empfehlung: T1+T2 zusammenlegen,
   Reihenfolge ändern
2. Rumänischen Gegenleser klären — blockiert den Versand des Fragebogens
3. `t2-chat-assistent/02-chestionar-ro.md` versenden; Priorität sind die fünf Punkte, die das
   Angebot bestimmen: Preisspannen, Bestellstatus, UK, Individualität der Fertigung,
   Konversations-Export
4. AVV und Pseudonymisierung klären, bevor mit dem Export weitergearbeitet wird
5. Zustand 2 · RESEARCH — erst danach Umfang und Preis

## Offene Founder-Entscheidungen

- **Rumänischer Kunde — Abweichung von CLAUDE.md §1.** Der Rechts-Gate der Agentur kennt deutsches
  Recht. Für Rumänien können wir keine Rechtskonformität zusichern, nur strukturell prüfen. Das
  gehört als Einschränkung ins Angebot. Details in `entscheidungen.md`
- Arbeitssprache mit dem Vertriebsleiter: Englisch vereinbaren oder Freelancer einplanen
- **Shopify (T3)** bleibt Abweichung von §3 — für T2 nicht blockierend, beeinflusst aber die
  Architektur des Assistenten
- Firmenname, Standort, Shop-URL

## Fachliche Kernfeststellung

**Maßanfertigung schließt das Widerrufsrecht aus — aber die Ausnahme ist eng.** „Kann ich das
zurückgeben?" gehört zu den häufigsten Fragen und ist in beide Richtungen teuer zu beantworten.
Diese und rund sechs weitere Fragen bekommen feste, geprüfte Antworten statt freier Formulierung
durch das Modell. Siehe `t2-chat-assistent/00-vorueberlegungen.md`.
