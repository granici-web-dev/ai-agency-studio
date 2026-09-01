# Agent-Register

31 Agenten. Fünf sind hauseigen, 26 stammen aus dem Repo
[`granici-web-dev/agency-agents`](https://github.com/granici-web-dev/agency-agents)
(Stand `ebe9c99`, 2026-08-06) und wurden beim Import angepasst.

## Hauseigen (in diesem Repo geschrieben)

| Agent | Rolle | Gate? |
|---|---|---|
| `co-founder-orchestrator` | Zerlegt Ziele in Aufgaben, verteilt sie, **reviewt jedes Ergebnis** (`ANGENOMMEN`/`NACHARBEIT`), hält Projektstand und Eskalationen · `model: opus` | **Review-Gate** |
| `pm-client-lead` | Feste Ansprechperson des Kunden, entwirft alle Nachrichten, bündelt Rückfragen des Teams, pflegt Status und Entscheidungen · `model: opus` | nein |
| `german-legal-compliance` | Impressum, DSGVO, TDDDG, PAngV, Widerruf, BFSG, KI-Datenflüsse | **blockierend** |
| `german-language-tone` | Sprach-Gate für alle kundenseitigen deutschen Texte | **blockierend** |
| `german-web-copywriter` | Deutsche Website-Texte, Meta-Angaben, Microcopy | nein |
| `client-onboarding` | Lead-Aufnahme, Kundenfragebogen, interner Brief | nein |
| `creative-producer-higgsfield` | Bild-/Videoassets über Higgsfield, Freigabe vor Verbrauch | nein |
| `shopify-developer` | Shopify-Shops inkl. der deutschen Pflichtangaben, die Shopify nicht ab Werk liefert | nein |
| `strapi-developer` | Strapi headless CMS auf EU-Hosting, Rechte, Betrieb, Redaktions-Handover | nein |

Der Ablauf, in dem diese Agenten zusammenspielen — Zustände, Gates, Ausstiegskriterien — steht in
`.claude/pipeline.md`.

`shopify-developer` und `strapi-developer` sind Abweichungen vom Standard-Stack (CLAUDE.md §3) und
tragen die Bedingungen dafür selbst: Shopify nur mit dokumentiertem Datenfluss, DPA und Freigabe
des Founders; Strapi nur mit Wartungsvertrag.

## Importiert aus agency-agents

| Agent | Quelle im Repo |
|---|---|
| `frontend-developer` | `engineering/engineering-frontend-developer.md` |
| `backend-architect` | `engineering/engineering-backend-architect.md` |
| `cms-developer` | `engineering/engineering-cms-developer.md` |
| `wordpress-shopping-cart` | `engineering/engineering-wordpress-shopping-cart.md` |
| `wordpress-performance` | `engineering/engineering-wordpress-performance.md` |
| `payments-billing-engineer` | `engineering/engineering-payments-billing-engineer.md` |
| `devops-automator` | `engineering/engineering-devops-automator.md` |
| `ai-engineer` | `engineering/engineering-ai-engineer.md` |
| `prompt-engineer` | `engineering/engineering-prompt-engineer.md` |
| `ui-designer` | `design/design-ui-designer.md` |
| `seo-specialist` | `marketing/marketing-seo-specialist.md` |
| `linkedin-content-creator` | `marketing/marketing-linkedin-content-creator.md` |
| `email-strategist` | `marketing/marketing-email-strategist.md` |
| `reality-checker` | `testing/testing-reality-checker.md` |
| `evidence-collector` | `testing/testing-evidence-collector.md` |
| `accessibility-auditor` | `testing/testing-accessibility-auditor.md` |
| `sales-proposal-strategist` | `sales/sales-proposal-strategist.md` |
| `customer-success-manager` | `specialized/customer-success-manager.md` |
| `project-shepherd` | `project-management/project-management-project-shepherd.md` |
| `meeting-notes-specialist` | `project-management/project-management-meeting-notes-specialist.md` |
| `mcp-builder` | `specialized/specialized-mcp-builder.md` |
| `automation-governance-architect` | `specialized/automation-governance-architect.md` |
| `workflow-architect` | `specialized/specialized-workflow-architect.md` |
| `document-generator` | `specialized/specialized-document-generator.md` |
| `analytics-reporter` | `support/support-analytics-reporter.md` |
| `executive-summary-generator` | `support/support-executive-summary-generator.md` |

## Was beim Import geändert wurde

Die Dateien aus dem Repo sind so, wie sie dort liegen, **nicht** als Claude-Code-Subagenten
aufrufbar. Beim Import wurde deshalb:

1. **`name` auf einen Slug gesetzt.** Original: `name: Frontend Developer` (Leerzeichen,
   Großschreibung) — damit ist der Agent über `subagent_type` nicht adressierbar. Neu:
   `name: frontend-developer`, identisch zum Dateinamen und zur Nennung in CLAUDE.md §8.
2. **Nicht unterstützte Frontmatter-Schlüssel entfernt** (`emoji`, `vibe`, `color`), damit das
   Frontmatter sauber parst.
3. **Ein Block „Agency Operating Rules" angehängt.** Die importierten Agenten sind generisch und
   kennen weder den deutschen Markt noch unsere Gates. Der Block bindet sie an CLAUDE.md:
   deutsche Sprache über `german-language-tone`, Freigabe durch `german-legal-compliance`,
   Standard-Stack aus §3, keine autonomen Aktionen nach §2.5, Definition of Done nach §6.

Der fachliche Inhalt der Agenten wurde nicht verändert.

## Aktualisieren

```bash
git clone --depth 1 https://github.com/granici-web-dev/agency-agents.git /tmp/agency-agents
# Import-Skript erneut ausführen (siehe Scratchpad install_agents.py) und den
# angehängten Regelblock auf Aktualität gegen CLAUDE.md prüfen.
```

Beim Update gehen lokale Änderungen an den importierten Dateien verloren — eigene Anpassungen
gehören in den Regelblock am Dateiende oder in einen neuen hauseigenen Agenten.

## Noch nicht installiert

Das Quell-Repo enthält rund 250 Agenten. Installiert ist bewusst nur der Roster aus CLAUDE.md §8
(„install selectively"), weil jede Agentenbeschreibung Kontext kostet und der Großteil des Repos
für uns irrelevant ist (Unreal Engine, Roblox, GIS, chinesische Social-Media-Plattformen,
Healthcare, Game Development).

Naheliegende Kandidaten für später, falls Bedarf entsteht:

- `engineering/engineering-privacy-engineer`, `specialized/data-privacy-officer` — DSGVO-Technik
- `engineering/engineering-technical-writer` — Handover- und Wartungsdokumentation
- `engineering/engineering-code-reviewer`, `engineering/engineering-git-workflow-master`
- `testing/testing-performance-benchmarker` — Lighthouse-Ziele aus §6
- `marketing/marketing-content-creator`, `marketing/marketing-social-media-strategist`
- `specialized/specialized-pricing-analyst`, `specialized/operations-manager`
- `sales/sales-discovery-coach`, `sales/sales-offer-lead-gen-strategist`
- `support/support-support-responder` — Wartungsvertrag-Betreuung
