# TESTING

Agency testing policy. Read by `rigorous`. The delivery gate itself is `CLAUDE.md` §6 — this file
says what we actually test, and honestly what we do not.

## The honest baseline

We are an agency, not a product team. Full unit-test coverage of a WordPress theme is theatre and
nobody pays for it. So the rule is not "test everything", it is:

> **Test what costs money when it breaks, and prove the rest with evidence.**

Two categories, two different standards.

## Category 1 — automated tests, mandatory

Written as real tests, run in CI, failing build on red.

- **Price and tax calculation.** Every rule: net/gross, reduced rate, shipping thresholds, Grundpreis
  per kg/l/m, staffel prices, discount and the 30-day lowest-price display. Money bugs are the ones
  that end in a lawyer's letter.
- **Order and payment state transitions.** Including the paths nobody demos: payment failed,
  double webhook, cancelled mid-checkout, partial refund. Webhook handlers must be provably
  idempotent — a test that fires the same event twice and asserts one effect.
- **Anything with a legal deadline or legal text attached.** Widerrufsfrist calculation, what the
  confirmation email contains.
- **AI assistant behaviour** (see below).
- **Data migrations.** A migration gets a test with real exported data, run against a copy, before
  it runs anywhere else.
- **Our own reusable tooling.**

## Category 2 — evidence, not tests

Proven by artefacts in `projects/<slug>/nachweise/`, collected by `evidence-collector`, signed off
by `reality-checker`.

- Responsive rendering: screenshots at 360, 768, 1440 px
- Lighthouse mobile: Performance, Accessibility, SEO ≥ 90 — the report file, not a number someone
  typed
- Network trace of the live page proving no unconsented external call (fonts, CDN, maps, tracking)
- Consent banner actually blocking, not merely displaying — trace with and without consent
- Forms: submitted, received, spam protection working, DSGVO checkbox enforced
- Shop: a real test order per active payment method, including cancellation and refund
- 404 page, favicon, OG tags, `sitemap.xml`, `robots.txt`
- Backup taken and restored once

"I clicked it and it worked" is not evidence. A screenshot with a visible URL and date is.

## Testing AI assistants

This is where an agency gets hurt, because the failure is not a crash — it is a confident wrong
answer to a paying customer.

- **A fixed evaluation set**, minimum 30 real questions taken from the client's actual inbox, with
  the expected behaviour for each: correct answer, or correct handover to a human. It is run
  before every prompt or model change. A change that improves one case and breaks two is a
  regression, and without the set nobody notices.
- **Refusal cases are tested explicitly**: questions the assistant must *not* answer — legal
  advice, binding price commitments, delivery promises, anything about another customer's order.
- **Injection attempts** in the set: a customer message that tries to make the assistant ignore
  its instructions or reveal them.
- **Handover works**: when the assistant gives up, a human actually receives it. Tested end to end,
  not assumed.
- **Transparency**: the assistant discloses that it is an AI system. Tested as a case, because a
  prompt change can silently remove it, and it is a legal requirement, not a nicety.
- **Fallback**: model unavailable, model slow, model returns unparseable output. All three have a
  defined, tested behaviour.
- **Cost and latency** measured on the evaluation set, so a change that triples the bill is visible
  before the invoice.

For the offer assistant specifically: a set of input combinations with the price the client's own
calculation produces. Any deviation is a bug, not a model quirk. If the price is legally binding,
this set is the most important test file in the project.

## Definition of done

A deliverable is done when: category-1 tests pass in CI, category-2 evidence is filed,
`german-language-tone` gave `FREIGABE`, `german-legal-compliance` gave `JA`, `reality-checker`
signed off, and `co-founder-orchestrator` returned `ANGENOMMEN`.

Missing any one of those, it is not done — it is in progress.
