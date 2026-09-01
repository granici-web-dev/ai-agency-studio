# AGENCY OPERATING SYSTEM — CLAUDE.md

> This file is the constitution of the agency. Every agent and every session must follow it.
> The founder (owner) is the final decision-maker. The CO-Founder agent orchestrates; humans approve.

## 1. Who We Are

- **Business:** AI-first digital agency based in Germany (Siegburg / Köln-Bonn region).
- **Services:**
  1. **Websites** — from one-page business sites (Visitenkarte) to full online shops (WooCommerce).
  2. **AI implementation for SMBs (Mittelstand)** — process audits, automations (Make/n8n), chatbots, RAG/knowledge systems, MCP integrations.
- **Market:** SMBs **selling into the DACH region**, starting with NRW. The client's own location and nationality are not the criterion — the sales market is. A Romanian, Turkish or Polish manufacturer selling to German consumers is a fit; a German company selling only outside DACH is not, because our legal gate would be worthless to them.
  - Where the client also sells into other markets, we cover **the DACH market fully** and everything else **structurally only**: presence and plausibility of the required elements, never a compliance sign-off. That limitation is stated in the Angebot, not discovered later. Legal texts for those markets come from a local provider with liability.
- **Language:** default output is **German (Sie-Form, business register)**, because the market is German. The client's own language and the deliverable's languages are two separate decisions, made in the brief, never assumed. Policy: `.claude/standards/LANGUAGE.md`.
- **Team model:** Founder (owner, orchestration, quality gates) + 1 German-speaking human for support & project communication + AI agents for all production work.

## 2. Non-Negotiable Rules (apply to EVERY project)

1. **Legal by default.** Every website ships with: Impressum, Datenschutzerklärung, cookie consent (if needed), locally hosted fonts (NEVER Google Fonts from Google servers), EU/German hosting. Shops additionally: AGB, Widerrufsbelehrung, Preisangaben (inkl. MwSt., Versandkosten), "zahlungspflichtig bestellen" button. The `german-legal-compliance` agent must sign off before any delivery.
2. **Language gate.** No client-facing text (email, Angebot, website copy, report, assistant reply) leaves the system without a gate pass **and a named human reviewer who speaks that language**. German goes through `german-language-tone` and the German-speaking PM. Any other shipped language needs its own named reviewer — the client's own staff counts, machine translation does not. A language with no reviewer does not ship; it gets priced or dropped. Legal texts are per market, sourced with liability, never translated by us (`.claude/standards/LANGUAGE.md`).
3. **Quality gate.** No website is declared "done" without the QA checklist (see §6) and evidence (screenshots, Lighthouse scores, checklist ticks).
4. **Privacy answer ready.** For AI projects, we must always be able to answer: Which data? Which provider? Where processed? AVV/DPA available? EU hosting option?
5. **Human approval points.** Agents never autonomously: send emails to clients, deploy to client production, sign anything, or spend money. They PREPARE; humans APPROVE.
6. **Accessibility.** BFSG applies since June 2025 to e-commerce. Shops must pass basic WCAG 2.1 AA checks (`accessibility-auditor`).
7. **The client's data never lives in this repo — the client's contact people do.** Two classes, and confusing them either leaks data or makes the work impossible.
   - **Their people's data** — customer lists, exports, order data, booking records, mailbox dumps, recordings, staff tables: **outside the repository**, one restricted folder per client. Into `projects/<slug>/` go **aggregates only** — shares, counts, frequencies, time series. No name, no number, no address, no verbatim quote, not even as an example. Each project carries a `DATEN-EXTERN.md` naming what we received, where it lies and under which AVV. **Receiving data is not permission to process it**: without a signed AVV (Art. 28 DSGVO) we store it and do nothing else. Working copies are pseudonymised and also stay outside.
   - **Our counterparts at the client** — name, role, business email and phone of the two or three people we actually correspond with: allowed in `projects/<slug>/`, because `pm-client-lead` cannot write a single mail without them. Kept to what the work needs, nothing collected "for later".
   - The second class lives in our self-hosted CRM (`.claude/crm.md`); the first never does. Neither goes into a third-country tool, and neither is pasted into a tool we have not checked.

   Applies to intake too: whatever collects a lead — form, bot, mail — obeys the same split.

## 3. Standard Tech Stack (don't reinvent per project)

- **Visitenkarte / small business site:** static or lightweight WordPress; Matomo (not GA) by default.
- **Online shop:** WordPress + WooCommerce. Payments: PayPal, Klarna, SEPA-Lastschrift, credit card, Rechnungskauf where feasible (Mollie or Stripe as PSP).
- **Hosting:** German/EU providers (e.g., Hetzner, IONOS, all-inkl). No US-only hosting for client sites.
- **AI projects:** prefer EU-processing options; document data flows.
- **Deviations from the defaults, allowed but never silent:**
  - **Shopify** (`shopify-developer`) — only if the client already runs it, insists after being informed, or the case clearly calls for it (international sales, POS, no IT staff). US processing, so document the data flow, file Shopify's DPA, get founder approval before the project starts. Shopify does not deliver German Pflichtangaben out of the box (Bestellbutton, Grundpreis, price display, Textform in emails) — those are build work, not settings.
  - **Strapi** (`strapi-developer`) — headless CMS, self-hosted on German/EU servers, so it fits this section. Use for content-heavy or multilingual sites with an own frontend, never for a 5-page Visitenkarte, and never without a Wartungsvertrag: self-hosted software without updates, backups and monitoring is a security liability we hand the client.

## 4. Service Packages (reference pricing, adjust per Angebot)

Full customer-facing wording, inclusions, exclusions and the client's obligations:
`.claude/vorlagen/leistungsbeschreibung.md`. That file is what a client may see; this table is
the internal reference.

### 4.1 Websites — one-time

| Package | Scope | Price |
|---|---|---|
| Starter (Visitenkarte) | 1–5 pages, design, copy, legal pages, SEO basics | 1.500–3.000 € |
| Business | 5–15 pages, custom design, blog, copywriting | 3.000–6.000 € |
| Shop | WooCommerce, payments, legal, product setup | ab 5.000 € |
| Wartungsvertrag | updates, backups, monitoring, small changes | 50–200 €/Monat |

### 4.2 Websites for small local businesses — VORSCHLAG, not decided

> **Status: proposal, 2026-08-16. Nothing here is agreed.** The founder sketched these packages
> and explicitly said he is not sure about them yet. Nobody quotes these prices to a client, and
> no agent treats them as the rule, until this block says ENTSCHIEDEN with a date.
> Decision log: `.claude/entscheidungen.md`.

The client buys and owns the site. WordPress, our stack per §3. Aimed at salons, trades, practices
and other owner-run businesses. One-time pricing brings more cash in year one than the rental
model considered before and needs no Ablöse construct.

| | Basis | Termin | Komplett |
|---|---|---|---|
| einmalig | **499 €** | **999 €** | **1.499 €** |
| | WordPress site, UX writing, **legal minimum per §2.1** | + booking system | + basic SEO, lead notification to the phone, AGB where required |
| Betreuung | 79 €/Monat, 12 Monate, danach monatlich kündbar — für alle drei Pakete | | |

Year one: 1.447 € / 1.947 € / 2.447 €. That places even the smallest package inside the Starter
range of §4.1, only paid differently.

**The legal minimum is not a package feature.** Impressum, Datenschutzerklärung, cookie handling
and self-hosted fonts ship with **every** site including Basis — §2.1 is non-negotiable and a site
without them is an Abmahnung we sold. What Komplett adds is **AGB**, and only where the client
actually needs them: a shop, or services concluded and paid online. A salon that merely takes
appointments usually needs none, and then we do not invoice for them.

**Lead notification without exporting personal data.** The Telegram bot from `tools/leadbot/` can
be reused so the owner gets a ping on the phone. But Telegram offers no usable AVV, and these are
the client's customers, not ours. Therefore the message carries **no content** — "Neue Anfrage
eingegangen, im Postfach ansehen", nothing more. Name, message and contact details go only to the
client's own EU mailbox. Never the inquiry text through Telegram, never as a convenience.

**Betreuung is mandatory for the first 12 months, and that is stated openly.** WordPress plus
plugins without updates is a security hole we handed the client — the same argument §3 makes about
Strapi. Included: updates, backups, monitoring, restore on failure, 30 minutes of changes per
month, not cumulative. After 12 months monthly cancellable. Booking runs on a self-hosted plugin
on EU hosting, never Calendly, Treatwell or comparable — their customers' data staying in Germany
is the argument we sell.

> **AGB and the 12-month term go to a lawyer before the first sale.** `german-legal-compliance`
> prepares, a human signs. 12 months tied to a security argument is defensible; it is not a
> licence to skip the check.

**Pilot, first 3 salons:** Komplett for 499 € instead of 1.499 €, in exchange for a named reference
and before/after figures (appointments per month before, and six months after). The Betreuung is
paid normally. Without the figures there is no case study, and without a case study the discount
bought nothing. Limited on purpose and with a stated end, so the low price does not become the
regional market price.

### 4.3 KI — VORSCHLAG, not decided

> **Status: proposal, 2026-08-16.** Prices and modules were drafted against the market check in
> §4.4 but never confirmed. Same rule as §4.2: no quoting, no agent treating this as settled.
> `.claude/entscheidungen.md`.

| | Scope | Price |
|---|---|---|
| KI-Analyse | up to 10 staff, 3 processes, shortlist, ~2 weeks | **490 €**, fully credited against a project ordered within 3 months |
| KI-Audit Standard | up to 50 staff, 6 processes, on-site workshop | 2.500 € |
| Schulung „KI im Betrieb" | half a day, German, their own examples — Art. 4 AI Act duty | 990 € |
| KI-Compliance-Paket | inventory, Art. 50 labelling, Art. 30 record, AVVs, Art. 35 check | 1.200–2.500 € |
| Betreuung | monitoring, model changes, prompt fixes, quarterly quality report | 199–499 €/Monat, monatlich kündbar |

Modules (each: solution document, build, test phase against written acceptance criteria, AVV,
Art. 30 record, Art. 50 labelling where user-facing, 90 min staff training, German handover):

| Module | Price | Typical payback |
|---|---|---|
| E-Mail- und Anfragen-Triage | 3.000–5.000 € | 2–4 months |
| Angebotserstellung aus Anfrage und Preisliste | 3.500–6.000 € | 2–3 months |
| Belegerfassung PDF → DATEV/Lexware | 4.000–8.000 € | 3–6 months |
| Interner Wissens-Assistent (RAG) | 5.000–9.000 € | 4–8 months |
| Kundenchatbot Website | 4.000–7.000 € | 3–6 months |
| KI-Telefonassistent | 5.000–8.000 € | 2–4 months |
| Terminerinnerungen, No-Show-Reduktion | 1.500–3.000 € | 1–3 months |
| Rezensionsmanagement | 1.500–3.000 € | 3–6 months |
| WhatsApp-Business-Automatisierung | 2.500–5.000 € | 2–4 months |

**Never sold:** CV screening, applicant ranking, or anything else deciding on employment. Annex III
high-risk under the AI Act. The Digital Omnibus (Reg. (EU) 2026/1744) deferred those duties to
2 December 2027 — deferred, not dropped, and we would be the supplier when they arrive.

### 4.4 What the market charges (checked 2026-08-16)

Reference points, so our numbers are set against reality and not against a feeling. Re-check
before changing prices.

- Dare Solutions (Koblenz): Analyse 490 € credited · pilot from 1.900 € · Betrieb from 199 €/Monat,
  monthly cancellable · chatbot from 2.900 € · phone assistant 149 €/Monat.
- Prozessmeister (Hamburg): entry 99 € · first project from 2.900 € · 490 € + 199 €/Monat,
  1.990 € + 999 €/Monat · per-use-case table with payback in months.

Two things follow. **Nobody in this market ties customers down** — monthly cancellation is
advertised as a feature. And **the audit is a door, not a product**: at 1.500 € an unknown agency
sells none.

Our difference is not price. Competitors put "DSGVO-konform" in a bullet list; here
`german-legal-compliance` blocks delivery (§2.1). Since 2 August 2026 Art. 50 AI Act requires a
chatbot to disclose that it is one — most existing bots do not. That is a cold-call opener and a
product, not a footnote.

## 5. Orchestration Model

- **Full state machine incl. gates and exit criteria: `.claude/pipeline.md`.** The list below is the short form.
- **Which of those states run without the founder, and in which order they get built: `.claude/automatisierung.md`.** Decided 2026-08-17: the website line becomes a machine, KI-Audit and the KI modules stay hand work. A state whose exit criterion is a human approval is never left by a machine — that is checked, not assumed.
- **CO-Founder agent** (`co-founder-orchestrator`) receives goals from the founder, decomposes into tasks, assigns to specialist agents, **reviews every deliverable and returns `ANGENOMMEN` or `NACHARBEIT`**, tracks status, and reports back. It answers for all agents. Runs on the strongest model.
- **PM agent** (`pm-client-lead`) owns the client relationship: drafts every client message, bundles the specialists' questions into one weekly package, keeps status, open questions and decisions current. Runs on the strongest model. It drafts — the PM-Human sends under their own name (§2.5).
- **All client communication goes through the PM agent.** No specialist agent ever writes to the client; questions are bundled and routed. The founder introduces in Flow A and then steps back.
- **Project state lives on disk**, in `projects/<slug>/`: `status.md`, `brief.md`, `research.md`, `offene-fragen.md`, `entscheidungen.md`, `reviews/`, `nachweise/`.
- **Our own CRM is the task board and the contact register**, not a second source of truth: it holds task status, ownership, gates and relationships; the disk holds the content; on conflict the disk wins. It is Twenty, self-hosted, so client data never leaves our infrastructure. Data model and the split against the disk: `.claude/crm.md`. Trello was retired on 2026-08-15 (`.claude/trello.md`).
- **Pipeline for a website project:**
  1. Lead → `client-onboarding` prepares brief questionnaire → human sends
  2. Brief in → CO-Founder creates project plan → founder approves scope & Angebot (`sales-proposal-strategist` + `german-language-tone`)
  3. Design → founder/client approves
  4. Build (frontend/CMS/shop agents) → copy (`german-web-copywriter`) → legal (`german-legal-compliance`)
  5. QA (`reality-checker` + checklist §6) → founder approves
  6. Deploy to staging → client approval → production
  7. Handover doc + Wartungsvertrag offer (`customer-success-manager`)
- **Marketing assets:** `creative-producer-higgsfield` generates images/short videos via the founder's Higgsfield account (credits available). Founder approves prompts/spends.

## 6. Website Delivery Checklist (QA gate)

- [ ] Impressum & Datenschutzerklärung linked in footer, reachable from every page
- [ ] Cookie consent correct (or no non-essential cookies at all — preferred for small sites)
- [ ] Fonts self-hosted; no external calls to Google Fonts/US CDNs without consent
- [ ] Lighthouse: Performance ≥ 90 (mobile), Accessibility ≥ 90, SEO ≥ 90
- [ ] Responsive: 360px / 768px / 1440px screenshots collected
- [ ] Forms tested (incl. spam protection, DSGVO checkbox on contact forms)
- [ ] Shop only: AGB, Widerruf, Versand & Zahlung page, correct price display, test order completed
- [ ] Backups + updates configured; admin access documented
- [ ] 404 page, favicon, OG tags, sitemap.xml, robots.txt

## 7. Communication Style (client-facing)

Applies to German, the default. For another correspondence language, keep the structure and the
restraint; do not transliterate German formality into a language that does not use it.


- Sie-Form unless client switches to du.
- Clear structure: Betreff, kurze Einleitung, nummerierte Punkte, klarer nächster Schritt.
- No hype, no exclamation-mark marketing. German SMBs buy reliability: fixed price, fixed timeline, named deliverables.
- Sign-off: "Mit freundlichen Grüßen" + name.

## 8. Agent Roster (phase 1)

All agents live in `.claude/agents/`. Full register incl. sources and import notes: `.claude/agent-register.md`.

Custom (written in this repo): `co-founder-orchestrator`, `pm-client-lead`, `german-legal-compliance`, `german-language-tone`, `german-web-copywriter`, `client-onboarding`, `creative-producer-higgsfield`, `shopify-developer`, `strapi-developer`.

`co-founder-orchestrator` and `pm-client-lead` are pinned to the strongest model (`model: opus`) — the first because it reviews everyone else's work and a weak reviewer is worse than none, the second because its output is read by a paying client.

Installed from the agency-agents repo (26, selectively — the repo holds ~250): frontend-developer, backend-architect, cms-developer, wordpress-shopping-cart, wordpress-performance, payments-billing-engineer, ui-designer, seo-specialist, reality-checker, evidence-collector, accessibility-auditor, devops-automator, sales-proposal-strategist, customer-success-manager, project-shepherd, meeting-notes-specialist, ai-engineer, prompt-engineer, mcp-builder, automation-governance-architect, workflow-architect, analytics-reporter, executive-summary-generator, document-generator, linkedin-content-creator, email-strategist.

Imported agents are generic. Each carries an appended "Agency Operating Rules" block binding it to this file: German output via `german-language-tone`, sign-off by `german-legal-compliance`, stack per §3, no autonomous sending/deploying/spending per §2.5, done-criteria per §6. When updating an imported agent from upstream, re-apply that block.

## 8a. External Toolkits

Three third-party skill sets are used as **tools inside pipeline states**, never as parallel
processes. Full rules, conditions and command-name collisions: `.claude/toolkits.md`.

- **design-thinking** — discovery, only inside state 2 · RESEARCH, only when the client does not
  yet know what should be built *and* the sub-project is ≥ 5.000 € or strategic. Never for
  Visitenkarte/Business packages or migrations — the process costs more than those projects earn.
- **rigorous** — engineering discipline in state 5 · BUILD and 9 · QA, wherever we write real code.
- **impeccable** — UI work in state 5 · BUILD, front-end only.

Their standards files are written **once for the agency**, in `.claude/standards/`
(`PRINCIPLES.md`, `STACK.md`, `TESTING.md`, `DESIGN.md`), derived from this file and copied into
client projects at kickoff. They are not re-derived per project — that would reintroduce exactly
the drift the toolkits exist to prevent.

`co-founder-orchestrator` stays the process owner. Toolkit reports are evidence, never approval:
delivery is decided by `german-legal-compliance`, done is decided by `reality-checker` against §6.

## 8b. Proposal or Decision

Agency-wide decisions live in **`.claude/entscheidungen.md`**, each in one of three states:
VORSCHLAG · ENTSCHIEDEN (with date) · ZURÜCKGESTELLT (with a named trigger, never "later").

Three rules follow, and they exist because both were broken on 2026-08-16:

1. **Anything that costs money is not decided until it stands in that file** — even if the founder
   said "yes, do it". The entry *is* the decision.
2. **A proposal that contradicts a standing decision says so in the same breath.** Do not quietly
   overtake it.
3. **Worked-out proposals may stay in the files** — they are work, not waste — but they carry the
   VORSCHLAG marker where they stand, not only in the log.

Thinking out loud is not an instruction. When it is unclear which one it was, ask.

## 9. Definitions

- **Founder** = owner, Russian/English-speaking, final authority.
- **PM-Human** = German-speaking employee/freelancer; owns client calls, final language check, sending.
- **Client** = German SMB; assume low technical knowledge, high expectation of correctness.
