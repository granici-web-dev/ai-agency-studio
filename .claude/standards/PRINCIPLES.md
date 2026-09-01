# PRINCIPLES

Agency-wide engineering principles. Read by `rigorous`. Derived from `CLAUDE.md` — where the two
disagree, `CLAUDE.md` wins.

Copy this file into a client project at kickoff. Deviations are allowed, but only written down at
the top of the copy with a reason. A silent deviation is a defect.

## The constraint everything follows from

**We hand the code to someone who cannot maintain it.** A German SMB with no IT staff, or a
freelancer we will never meet, inherits everything we write. Cleverness we understand today is a
liability they carry for years.

Consequences, in priority order:

1. **Boring beats elegant.** The obvious solution a stranger can read in one pass wins over the
   compact one that needs a paragraph of explanation.
2. **Every dependency is something the client inherits.** A plugin, a package, an app: each one
   has an update path, a security surface, a monthly cost and a day it is abandoned. Adding one is
   a decision with a justification, not a convenience.
3. **If twenty lines of our own code replace a dependency, write the twenty lines.**
4. **Write for the person doing the emergency fix at 22:00**, not for the code review.

## Abstraction

- **Rule of three.** No abstraction before the third real occurrence. Two similar things are two
  things; the shared shape only becomes visible at the third.
- No configuration option without a caller that needs it today. Speculative flexibility is the
  most expensive kind of dead code.
- No wrapper that only forwards. A layer must decide something, translate something or protect
  something — otherwise delete it.
- Duplication is cheaper than the wrong abstraction. Coupling two things that merely look alike
  produces a change in one that breaks the other.

## Defensive ceremony

- Validate at the boundary — user input, webhook, API response, form submission, file upload,
  anything from an LLM. Inside a module, trust your own types.
- No try/catch that logs and rethrows unchanged. Either handle it or let it travel.
- No null check for a value that cannot be null. It hides the real invariant.
- An error message names what failed and what to do next. `"Fehler"` is not an error message; the
  German wording of anything a user sees goes through `german-language-tone`.

## Naming and structure

- Say the domain word, in the domain's language: `bestellung`, `widerrufsfrist`, `grundpreis`,
  `versandkostenfrei`. Do not translate German legal or commercial terms into approximate English
  — `withdrawal_period` and `Widerrufsfrist` are not reliably the same thing, and the second one is
  what the law names.
- Code and identifiers are English; domain terms with legal meaning stay German. Comments explain
  **why**, never **what**.
- Group by feature, not by technical layer, once a project passes a handful of files.

## Secrets, data, money

- No secret in the repository, ever. Environment variables, separate per environment.
- No personal data in logs, error trackers or monitoring outside the EU (`CLAUDE.md` §2.4).
- Anything touching payment, price calculation or order state is written to be **idempotent** and
  is never "improved" without a test that reproduces the old behaviour first.
- Prices are integers in the smallest unit. Never floats. Rounding rules are decided once and
  written down.

## Working with LLMs in production code

- An LLM answer is untrusted input. Validate it against a schema before it touches anything.
- Every generated answer that reaches a customer has a defined fallback for "the model is down",
  "the model is slow" and "the model is confidently wrong".
- No autonomous action from a model output — no order change, no refund, no email — without a
  human step or a hard, tested rule (`CLAUDE.md` §2.5).
- Prompts are versioned artefacts in the repository, not strings pasted into a dashboard.
- Log what was asked and what was answered, with a retention period, so a wrong answer can be
  reconstructed. Personal data in those logs follows the same EU rules as everything else.

## What "done" means

Not "works on my machine". `CLAUDE.md` §6, with evidence. See `TESTING.md`.
