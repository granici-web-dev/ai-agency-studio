# Delivery Pipeline

How work moves from a lead to production. `CLAUDE.md` §5 names the phases; this file defines the
state machine, who owns each state and what has to be true to leave it.

Owner of the pipeline is `co-founder-orchestrator`. Owner of the client relationship is
`pm-client-lead`. Every phase transition is written into `projects/<slug>/status.md`.

## States

```
0  LEAD          → 1 BRIEF   (Anfrage da, noch nichts verstanden)
1  BRIEF         → 2 RESEARCH
2  RESEARCH      → 3 ANGEBOT
3  ANGEBOT       → 4 SETUP        [GATE: Founder + Kunde]
4  SETUP         → 5 BUILD
5  BUILD         → 6 CONTENT
6  CONTENT       → 7 GATES        [Sprache]
7  GATES         → 8 QA           [Legal]
8  QA            → 9 STAGING      [QA §6 + reality-checker]
9  STAGING       → 10 PRODUKTION  [GATE: Kundenabnahme + Founder]
10 PRODUKTION    → 11 HANDOVER
11 HANDOVER      → WARTUNG
```

Ein Zustand wird nur verlassen, wenn sein Exit-Kriterium erfüllt und in `status.md` vermerkt ist.
Zurückspringen ist erlaubt und normal; stillschweigend überspringen nicht.

---

## 0 · LEAD

Zwei Varianten mit unterschiedlichem Risiko, deshalb zwei ausformulierte Abläufe:

- **`.claude/flows/lead-a-founder.md`** — der Founder bringt den Kontakt. Vertrauen ist da,
  Qualifizierung erledigt. Risiko: mündliche Zusagen, die niemand aufgeschrieben hat. Der Flow
  beginnt deshalb beim Founder, nicht beim Kunden. Aufgenommen wird über den Lead-Bot
  (`tools/leadbot/`), der den Projektordner gleich anlegt.
- **`.claude/flows/lead-b-website.md`** — Anfrage über das Formular. Kein Kontext, unbekannte
  Qualität. Risiko: Antwortzeit und Streuung. Der Flow beginnt beim Formular und endet in einer
  Einstufung A / B / C.

Textbausteine für beide: `.claude/vorlagen/lead-antworten.md`.
Wie eine Kundenmail entsteht, geprüft wird und rausgeht — für **alle** Zustände, nicht nur für
den Erstkontakt: `.claude/flows/kundenmails.md`.

**Exit:** siehe Exit-Kriterium im jeweiligen Flow. In beiden Fällen gilt: `projects/<slug>/`
existiert mit `status.md`, `offene-fragen.md`, `entscheidungen.md`.

## 1 · BRIEF

**Owner:** `client-onboarding` · **Review:** `co-founder-orchestrator`

Antworten auswerten, `brief.md` schreiben: Ziel, Zielgruppe, Scope, **explizit Out-of-Scope**,
Budget, Wunschtermin, Bestand (Hosting, Domains, Zugänge, Altsystem), Risiken, fehlende Angaben.

**Exit:** `brief.md` vom Orchestrator angenommen, keine blockierende Lücke offen.
**Häufigster Fehler:** mit einem halben Brief in RESEARCH gehen. Ein unklarer Scope wird später
nicht klarer, nur teurer.

## 2 · RESEARCH

**Owner:** je nach Gewerk · **Review:** `co-founder-orchestrator`

Bevor irgendetwas geplant wird, wird der Ist-Zustand angesehen — nicht angenommen.

- **Bestandssystem:** Was läuft heute? Umfang (Produkte, Varianten, Seiten, Bestellungen/Monat),
  Plugins, Zahlarten, Schnittstellen (Warenwirtschaft, Versand, Buchhaltung), URLs, Traffic.
- **Rechtlicher Ist-Zustand:** `german-legal-compliance` prüft die bestehende Seite. Was heute
  schon fehlt, ist Bestandteil des Projekts und gehört ins Angebot.
- **Datenfluss bei KI-Projekten:** welche Daten, welcher Anbieter, wo verarbeitet, AVV vorhanden
  (CLAUDE.md §2.4). Ohne diese Antwort kein Angebot.
- **Technische Machbarkeit:** was der gewählte Stack ab Werk *nicht* kann und deshalb Bauaufwand
  ist. Das ist der Posten, an dem Angebote sterben.
- **Migration:** was übernommen wird, was nicht übernommen werden *kann*, was aufbewahrungspflichtig
  ist.

### Discovery-Unterprozess (design-thinking)

Wenn der Kunde **nicht weiß, was gebaut werden soll** — ein Wunsch, aber keine prüfbare
Anforderung — und das Teilprojekt bei mindestens 5.000 € liegt oder strategisch ist, läuft hier
das Toolkit `design-thinking` als Unterprozess: Empathize → Define → Ideate → Prototype → Test.

Nicht bei Visitenkarte und Business-Paket, nicht bei Migrationen, nicht wenn die Anforderung
schon feststeht. Bedingungen und Einbindung: `.claude/toolkits.md`.

`co-founder-orchestrator` bleibt Prozessführer und nimmt das Ergebnis per normalem Review ab.
Artefakte nach `projects/<slug>/research/`.

**Exit:** `research.md` mit Befunden, Annahmen und Aufwandstreibern, angenommen.
**Regel:** Jede Zahl im Angebot muss auf etwas in `research.md` zurückführbar sein.

## 3 · ANGEBOT

**Owner:** `sales-proposal-strategist` · **Gates:** `german-language-tone`, dann Founder

Festpreis oder klar begrenzte Positionen, benannte Liefergegenstände, Termine, Annahmen,
Mitwirkungspflichten des Kunden, Ausschlüsse. Wartungsvertrag immer mit anbieten.

**Exit:** Founder freigegeben, Angebot versendet, Kunde hat schriftlich zugesagt.
**Ohne diese Zusage beginnt keine Produktion.**

## 4 · SETUP

**Owner:** `devops-automator` · **Support:** `pm-client-lead` (Zugänge)

Hosting/Umgebungen, Repository, Staging, Zugänge, Backups, Domain- und DNS-Plan, Zugangsliste.
Accounts bei Dritten laufen auf den Kunden, nicht auf die Agentur.

**Exit:** Staging erreichbar und passwortgeschützt, Zugänge dokumentiert, Backup läuft.

## 5 · BUILD

**Owner:** Gewerk (`shopify-developer`, `wordpress-shopping-cart`, `frontend-developer`,
`ai-engineer`, …) · **Review:** `co-founder-orchestrator` pro Teilstück

In Teilstücken liefern, nicht in einem Rutsch. Jedes Teilstück wird einzeln reviewt. Design vor
Umsetzung vom Kunden freigeben lassen.

**Bei echter Code-Arbeit** (Shopify-Theme, WordPress-Child-Theme, Plugin, Strapi, KI-Bausteine)
je Teilstück: `/rigorous shape` → `/rigorous craft` → `/rigorous critique` vor der Abgabe.
Standards aus `.claude/standards/` sind zu Projektbeginn ins Projektrepo kopiert; `/rigorous teach`
wird **nicht** pro Kunde neu durchlaufen.

**Bei Frontend-Arbeit** zusätzlich `/impeccable polish` am Ende des Teilstücks, gegen
`.claude/standards/DESIGN.md`. `animate`, `delight`, `bolder`, `overdrive` nur mit Freigabe des
Founders. Kein Refine-Befehl fasst Pflichttexte, Bestellbutton, Preisdarstellung oder
Consent-Banner an.

Nicht anwenden auf Klickstrecken (Elementor-Aufbau, reine Theme-Konfiguration) — dort findet
`rigorous` nichts und kostet nur Zeit. Details: `.claude/toolkits.md`.

**Exit:** alle Teilstücke angenommen, Funktion auf Staging vorführbar.

## 6 · CONTENT

**Owner:** `german-web-copywriter`, `creative-producer-higgsfield`

Texte, Bilder, Meta-Angaben, Microcopy, E-Mail-Vorlagen. Platzhaltertexte sind ab hier ein Befund,
kein Zwischenstand.

**Exit:** keine Lorem-Ipsum-, keine `[[PLATZHALTER]]`-Stellen mehr, oder sie stehen als offene
Frage beim Kunden.

## 7 · GATES — Sprache

**Owner:** `german-language-tone` · **blockierend**

Alles, was der Kunde oder dessen Kunden lesen: Website-Texte, Buttons, Fehlermeldungen,
E-Mail-Vorlagen, Antworten des KI-Assistenten, Admin-Beschriftungen, Handover-Dokumente.

**Exit:** `FREIGABE`. Bei `NACHARBEIT` zurück nach CONTENT oder BUILD.

## 8 · GATES — Recht

**Owner:** `german-legal-compliance` · **blockierend**
**Support:** `accessibility-auditor` (BFSG/WCAG bei Shops)

Blöcke A–H prüfen, mit Nachweis. Rechtstexte kommen von einem Anbieter mit Haftungsübernahme, nicht
von uns (RDG).

**Exit:** `Freigabe: JA`. Der Orchestrator kann dieses Gate nicht überstimmen — nur der Founder
kann entscheiden, ein benanntes Risiko bewusst zu tragen, und das wird in `entscheidungen.md`
protokolliert.

## 9 · QA / STAGING

**Owner:** `reality-checker` · **Nachweise:** `evidence-collector`

Checkliste CLAUDE.md §6 vollständig, mit Belegen: Screenshots 360/768/1440, Lighthouse mobil
≥ 90 (Performance, Accessibility, SEO), Formulare getestet, Netzwerk-Mitschnitt auf externe
Aufrufe, bei Shops eine echte Testbestellung inklusive Storno und Rückerstattung.

Vor dem Gate, nicht statt dessen: `/rigorous audit` (Performance, Security, Abhängigkeiten) und
`/impeccable audit` (visuell, UX, Zugänglichkeit). Beide Berichte nach `nachweise/`. Sie finden
Probleme früher — abgenommen wird trotzdem von `reality-checker` gegen CLAUDE.md §6, und
Barrierefreiheit im Rechtssinn von `accessibility-auditor`. Ein grüner Toolkit-Bericht ist ein
Indiz, keine Abnahme.

Testtiefe und was automatisiert getestet wird statt belegt: `.claude/standards/TESTING.md`.

**Exit:** `reality-checker` bestätigt, Kunde nimmt auf Staging ab (schriftlich).
**Regel:** „Bei mir läuft es" ist kein Nachweis.

## 10 · PRODUKTION

**Owner:** `devops-automator` · **Gate:** Founder gibt frei (CLAUDE.md §2.5)

Deploy-Fenster mit dem Kunden abgestimmt, Backup unmittelbar vorher, 301-Weiterleitungen aktiv,
Rollback-Weg beschrieben und getestet. Nach dem Deploy: Legal-Kurzprüfung live (Impressum
erreichbar, Consent greift, keine unerlaubten externen Aufrufe), Monitoring an.

**Exit:** live, Nachkontrolle bestanden, Rollback-Weg dokumentiert.

## 11 · HANDOVER

**Owner:** `customer-success-manager` · **Support:** `pm-client-lead`

Handover-Dokument auf Deutsch, Einweisung, Zugänge übergeben, Wartungsvertrag anbieten,
Nachkontrolle nach zwei und nach vier Wochen terminieren.

**Exit:** Kunde bestätigt Übergabe, Wartungsvertrag entschieden (ja oder nein, schriftlich).

---

## Der Review-Loop

Jedes Artefakt aus jedem Zustand:

```
Agent liefert  →  co-founder-orchestrator prüft gegen Brief + Gate + Nachweis
                  ├── ANGENOMMEN  → nächster Zustand
                  └── NACHARBEIT  → nummerierte Liste zurück an denselben Agenten
                                    Runde 3 = Aufgabe neu schneiden, nicht nochmal zurückgeben
```

Verdikte liegen in `projects/<slug>/reviews/`. Der Founder kann jederzeit hineinsehen, ohne zu
fragen, wie es steht.

## Was den Founder erreicht

Nur fünf Dinge, jeweils entscheidungsreif mit Empfehlung:

1. Scope, Budget oder Termin ändern sich
2. Ein Gate blockiert etwas auf dem kritischen Pfad
3. Geld soll ausgegeben werden
4. Ein Rechtsrisiko soll bewusst getragen werden
5. Etwas geht an den Kunden raus oder in die Produktion

Alles andere entscheidet der Orchestrator.
