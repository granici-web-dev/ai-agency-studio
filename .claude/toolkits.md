# Externe Toolkits

Drei fremde Skill-Sammlungen, die wir benutzen. Sie sind **Werkzeuge innerhalb von
Pipeline-Zuständen**, keine eigenen Prozesse. Prozessführer bleibt in jedem Fall
`co-founder-orchestrator`, Gates bleiben `german-legal-compliance`, `german-language-tone`,
`accessibility-auditor` und `reality-checker`.

| Toolkit | Beantwortet | Greift in Zustand |
|---|---|---|
| **design-thinking** | *Was* ist überhaupt das Richtige? | 2 · RESEARCH |
| **rigorous** | *Wie* wird sauber gebaut? | 5 · BUILD, 9 · QA |
| **impeccable** | *Wie* sieht es aus und fühlt es sich an? | 5 · BUILD (Frontend) |

Alle drei folgen demselben Muster: einmal geschriebene Standarddateien, die alle Befehle lesen.
**Diese Standarddateien schreiben wir einmal für die Agentur**, nicht pro Projekt — sonst bekommt
jedes Projekt eine andere Ingenieurshaltung, also genau die Drift, gegen die die Toolkits antreten.
Sie liegen in `.claude/standards/` und leiten sich aus CLAUDE.md ab.

---

## Grundregel: unsere Gates entscheiden

`rigorous audit` und `impeccable audit` sind Verbesserungswerkzeuge **vor** dem Gate. Sie ersetzen
keine Abnahme.

- Was ausgeliefert werden darf, entscheidet `german-legal-compliance`.
- Ob es fertig ist, entscheidet `reality-checker` gegen CLAUDE.md §6, mit Nachweisen.
- Barrierefreiheit im Rechtssinn (BFSG/WCAG 2.1 AA) entscheidet `accessibility-auditor`.
  `impeccable audit` findet vieles davon früher — das ist nützlich und trotzdem kein Nachweis.

Ein grüner Toolkit-Bericht in `nachweise/` ist ein Indiz, keine Abnahme.

## Namenskollisionen — immer mit Präfix aufrufen

Die Toolkits teilen sich Befehlsnamen. Ohne Präfix ist nicht klar, welches gemeint ist:

| Befehl | rigorous | impeccable |
|---|---|---|
| `shape` | Feature-Brief vor dem Code | UI-Konzept |
| `audit` | Performance, Security, Dependencies | visuelles und UX-Audit |
| `critique` | Codekomplexität, Notwendigkeit | Designkritik |
| `harden` | Validierung im Code | robuste Zustände, Fehlerfälle im UI |
| `optimize` | Profiling, Laufzeit | wahrgenommene Geschwindigkeit, Ladeverhalten |
| `document` | Standards aus Code rückwärts ableiten | Designsystem nach `DESIGN.md` extrahieren |

**Immer `/rigorous <befehl>` bzw. `/impeccable <befehl>` schreiben.** Wer im Projektlog „audit
gelaufen" notiert, hat nichts dokumentiert.

---

## 1 · design-thinking

Installiert (global), Repo `granici-web-dev/design-thinking-agents`. Neun Agenten:
Brief → Empathize → Define → Ideate → Prototype → Test → Implement → Build, plus `dt-orchestrator`.

### Wann — und nur dann

Innerhalb von **Zustand 2 · RESEARCH**, wenn **beide** Bedingungen zutreffen:

1. Der Kunde weiß nicht genau, was gebaut werden soll — es gibt einen Wunsch, aber keine
   überprüfbare Anforderung. Typisch: „wir wollen irgendwas mit KI", ein neues Produkt, ein
   Selbstbedienungsprozess, ein Assistent.
2. Das Teilprojekt liegt bei mindestens **5.000 €** oder ist strategisch (unsere eigene Website,
   ein wiederverwendbares Produkt).

### Wann ausdrücklich nicht

- Visitenkarte und Business-Paket (CLAUDE.md §4). Acht Etappen Dizajn-Denken auf einer
  Fünf-Seiten-Website verbrennen die gesamte Marge. Das ist das reale Risiko dieses Toolkits:
  nicht dass es schlecht wäre, sondern dass es überall angewendet wird.
- Migrationen. WooCommerce → Shopify ist kein Dis­covery, die Anforderungen sind bekannt.
- Alles, wo der Kunde eine fertige, prüfbare Anforderung mitbringt.

### Wie eingebunden

- `co-founder-orchestrator` ruft es als **Unterprozess** auf und nimmt das Ergebnis per normalem
  Review ab. **`dt-orchestrator` wird nicht als oberste Instanz verwendet** — zwei Prozessführer
  heißt keiner. Wenn er benutzt wird, dann für die Etappenführung innerhalb von RESEARCH, und er
  berichtet an `co-founder-orchestrator`.
- **Artefakte gehören nach `projects/<slug>/research/`.** Obsidian, FigJam und Figma sind optional
  und nur zusätzlich. Projektstand liegt in `status.md`, nirgendwo sonst — Zustand, der auf zwei
  Orte verteilt ist, ist kein Zustand.
- Die dt-Agenten sind generisch und englisch. Alles, was den Kunden erreicht — Interviewleitfäden,
  Testaufgaben, Prototyp-Texte — geht durch `german-language-tone`. Nutzertests mit echten Kunden
  des Auftraggebers berühren personenbezogene Daten: vorher `german-legal-compliance`.

### Was wir davon in jedem Fall übernehmen

Die Grill-Technik aus `dt-brief`, unabhängig davon ob der volle Zyklus läuft — sie ist in
`client-onboarding` eingebaut: eine Frage nach der anderen, „alle" wird als Zielgruppe
zurückgewiesen, jede Aussage wird als **Fakt** oder **Hypothese** markiert, und es gibt immer eine
riskanteste Annahme mit Abbruchkriterium.

---

## 2 · rigorous

**Noch nicht installiert.** `npx skills add CoRLab-Tech/skills@rigorous`

13 Befehle in fünf Gruppen: Setup (`teach`, `document`), Build (`shape`, `craft`, `tdd`),
Evaluate (`critique`, `audit`, `architect`), Refine (`simplify`, `harden`, `refactor`),
Fix (`debug`, `optimize`). Liest `PRINCIPLES.md`, `STACK.md`, `TESTING.md`.

### Wann

Überall dort, wo wir **wirklich Code schreiben**:

- Shopify-Themes (Liquid, Sections, JSON-Templates), WordPress-Child-Themes und eigene Plugins
- Strapi-Projekte und deren Frontends
- KI-Bausteine: Assistenten, Automatisierungen, MCP-Server, Schnittstellen
- unsere eigenen Werkzeuge

### Wann nicht

Klickstrecken. Ein Elementor-Aufbau oder eine reine Shopify-Theme-Konfiguration ist kein
Codeprojekt; `rigorous` findet dort nichts und kostet nur Zeit.

### Wie eingebunden

- **`/rigorous teach` wird pro Kundenprojekt nicht neu durchlaufen.** Die 15 Fragen sind für die
  Agentur einmal beantwortet; das Ergebnis liegt in `.claude/standards/`. Beim Projektstart werden
  die drei Dateien in das Projektrepo kopiert und nur dort angepasst, wo das Projekt abweicht —
  die Abweichung wird im Kopf der Datei begründet.
- Ablauf im Zustand 5 · BUILD: `/rigorous shape` vor dem Teilstück → `/rigorous craft` →
  `/rigorous critique` vor der Abgabe an den Orchestrator.
- Im Zustand 9 · QA: `/rigorous audit`, Bericht nach `projects/<slug>/nachweise/`.
- `/rigorous simplify` und `/refactor` sind erlaubt und erwünscht, aber **nicht während einer
  offenen Nacharbeit** — sonst ist nicht mehr unterscheidbar, was die Korrektur war.

---

## 3 · impeccable

Installiert (global). `npx impeccable install` im Projektordner, wenn es projektlokal gebraucht
wird. 23 Befehle: Create (`impeccable`, `shape`), Evaluate (`audit`, `critique`),
Refine (`animate`, `bolder`, `colorize`, `delight`, `layout`, `overdrive`, `quieter`, `typeset`),
Simplify (`adapt`, `clarify`, `distill`), Harden (`harden`, `onboard`, `optimize`, `polish`),
System (`document`, `extract`, `init`, `live`). Liest `PRODUCT.md` und `DESIGN.md`.

### Wann

Frontend-Arbeit mit eigenem Markup und CSS: Shopify-Theme, WordPress-Theme, Strapi-Frontend,
Oberflächen der KI-Assistenten (Chat-Fenster, Angebotsstrecke), unsere eigene Website.

### Wie eingebunden

- **`PRODUCT.md` ist pro Kunde**, nicht agenturweit — es beschreibt dessen Zielgruppe und
  Positionierung. Quelle ist `brief.md`, nicht ein zweites Interview mit dem Kunden.
  Erzeugt im Zustand 4/5 durch `ui-designer`.
- **`DESIGN.md`** entsteht pro Projekt aus dem freigegebenen Entwurf. Der agenturweite Grundstock
  — Zugänglichkeit, Schriftauslieferung, Zielwerte — steht in `.claude/standards/DESIGN.md` und
  wird übernommen.
- Sinnvolle Befehle in unserem Alltag: `/impeccable audit` vor dem Barrierefreiheits-Gate,
  `/impeccable polish` am Ende von BUILD, `/impeccable clarify` für überladene Seiten,
  `/impeccable quieter` gegen zu laute Kundenwünsche.
- **Vorsicht bei `animate`, `delight`, `overdrive`, `bolder`.** Deutsche Mittelstandskunden kaufen
  Verlässlichkeit, nicht Effekt (CLAUDE.md §7). Bewegung kostet außerdem Lighthouse-Punkte und
  kollidiert mit `prefers-reduced-motion`. Diese Befehle nur mit Freigabe des Founders.
- **Nichts aus `impeccable` überschreibt Rechtstexte, Pflichtangaben oder den Bestellbutton.**
  „Clarify" darf einen Widerrufstext nicht kürzen. Wenn ein Vorschlag einen Pflichttext berührt →
  `german-legal-compliance`.

---

## Zusammenspiel in einem Satz

Design-Thinking klärt vor dem Angebot, was gebaut werden soll; rigorous sorgt dafür, dass der Code
in einem halben Jahr noch wartbar ist; impeccable dafür, dass die Oberfläche nicht nach Baukasten
aussieht. Abgenommen wird trotzdem gegen CLAUDE.md §6, mit Nachweisen.
