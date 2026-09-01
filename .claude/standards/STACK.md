# STACK

Agency default stack. Read by `rigorous`. Authoritative source is `CLAUDE.md` §3 — this file adds
the engineering detail, it does not override.

Copy into a client project at kickoff; record any deviation at the top with a reason and the date
the founder approved it.

## Defaults

| Layer | Default | Notes |
|---|---|---|
| Small business site | static, or lightweight WordPress | no page builder where a theme does it |
| Shop | WordPress + WooCommerce | agency standard |
| Shop, on deviation | Shopify | §3 conditions apply — see below |
| Headless CMS | Strapi, self-hosted EU | only with a Wartungsvertrag |
| Frontend for headless | Astro preferred, Next.js where interactivity justifies it | SSG by default |
| Hosting | Hetzner, IONOS, all-inkl | German/EU only for client sites |
| Database | PostgreSQL | never SQLite in production |
| Analytics | Matomo | never Google Analytics |
| PSP | Mollie, Stripe | PayPal, Klarna, SEPA, card, invoice where viable |
| Fonts | self-hosted, always | never `fonts.googleapis.com` |
| Captcha | honeypot + time trap, Friendly Captcha | never Google reCAPTCHA |
| Maps | static image, or an EU provider behind consent | never an unconsented Google Maps embed |
| Video | self-hosted or EU provider | YouTube only behind consent, no-cookie domain |
| Error tracking | EU-hosted, or none | no personal data leaving the EU |
| Automation | n8n self-hosted, or Make | document the data flow |
| Repo & CI | Git, EU-hosted runner where the build touches client data | |

## The two approved deviations

**Shopify** — only when the client already runs it, insists after being informed, or the case
clearly calls for it (international sales, POS, no IT staff). Requires, before build starts: a
written justification, a documented data flow, Shopify's DPA in the project file, founder
approval. Shopify does not deliver the German Pflichtangaben out of the box — Grundpreis,
order-button wording, legal texts in the confirmation email, 30-day lowest-price display. Those
are build work, budgeted as such. See `shopify-developer`.

**Strapi** — fits §3 (self-hosted, EU), but never for a five-page site and never without a
Wartungsvertrag. See `strapi-developer`.

## Languages and versions

- PHP: the current supported version of the host, never an EOL branch. WordPress and WooCommerce
  on current major.
- Node: current LTS. Package manager pinned per project, lockfile committed.
- TypeScript for anything beyond a handful of lines of browser JS.
- Liquid for Shopify. Online Store 2.0 — sections and blocks, JSON templates, no hardcoded
  page templates.
- Python only where an AI/data library requires it. Otherwise Node.

## Things we do not introduce without founder approval

- Any US-only SaaS in a client data path
- Any service without an available AVV/DPA
- A JavaScript framework on a site that has no application behaviour
- A headless architecture for a catalogue the client edits twice a month
- A paid app or plugin with a recurring cost the client has not agreed to
- A build step on a site the client will later maintain in the admin panel

## Performance budget

Non-negotiable, from `CLAUDE.md` §6: Lighthouse mobile ≥ 90 for Performance, Accessibility and
SEO. Practically that means:

- Images sized and served in modern formats with `srcset`; no full-size originals in the page
- Fonts subset, `font-display: swap`, at most two families and the weights actually used
- No second slider or icon library because one component wanted it
- Third-party scripts only after consent, and counted against the budget like our own
- On WooCommerce: object cache (Redis), page cache, and query review before adding a plugin

## Environments

Every project has at least local, staging and production. Staging is password-protected and
`noindex`. No work directly on a live client system — on Shopify that means an unpublished theme
and a development store, on WordPress a staging install, never the live editor.

## Backups

Database and uploads, daily, before every deploy, with a restore that has been tested at least
once. An untested backup is not a backup.
