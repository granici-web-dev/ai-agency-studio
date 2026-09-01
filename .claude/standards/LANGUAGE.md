# LANGUAGE

Agency language policy. Referenced by `CLAUDE.md` §1, §2.2 and §7.

## Two axes, never conflated

**Axis 1 — the language we speak with the client.** The owner of a German SMB is not always a
native German speaker. Family businesses, first- and second-generation founders, and
internationally owned companies are a large part of this market.

**Axis 2 — the language the deliverable speaks to the client's customers.** Independent of axis 1.
A Turkish-owned company selling to German consumers needs German output; a German-owned company
selling into NL and AT needs German plus Dutch.

Both are decided in the brief and written into `brief.md`. Neither is assumed.

## Axis 1 — communicating with the client

- Ask, do not guess: *"In welcher Sprache korrespondieren wir am besten?"* — once, in the first
  contact. Never infer a language from a surname.
- Default is German. If the client prefers another language, we switch **for correspondence** —
  but contracts, the Angebot and anything with legal effect stay German, with a translation
  attached for understanding if useful. The German version is the binding one, and that is stated.
- `pm-client-lead` writes in the agreed correspondence language. `german-language-tone` gates the
  German. For another correspondence language, a named human who speaks it reads it before
  sending — same rule, different reviewer.
- Sie/du: German follows `CLAUDE.md` §7. Other languages follow their own business register, not
  a transliteration of German formality.

## Axis 2 — the language of the deliverable

### Legal texts are not translations

This is the part that gets agencies in trouble.

**Legal obligations attach to the market, not to the language.** Selling to consumers in Germany
means German Impressum, Datenschutzerklärung, AGB, Widerrufsbelehrung and price display —
regardless of which other languages the site offers. Selling into Austria, Switzerland, France or
the Netherlands means each of those markets has its own requirements.

Consequences:

- A legal text is **per market**, sourced from a provider with liability for that market
  (IT-Recht Kanzlei, Händlerbund, Trusted Shops offer multi-market packages). We never translate a
  German Widerrufsbelehrung ourselves and ship it as the Dutch one. RDG applies to us in Germany;
  the equivalent risk exists elsewhere.
- If the shop can be ordered from in a language, the mandatory information must be available in
  that language and before the order button. A German-only Widerruf on an English checkout is a
  defect.
- `german-legal-compliance` checks the German market. **Additional markets need their own check**
  and that is scope, budget and time — it goes in the Angebot explicitly, never absorbed silently.

### Content

### Shipped languages are chosen from data, not from ambition

A client who sells "to Europe, the CIS and America" is describing their business, not specifying a
deliverable. Building for every market they have ever shipped to is how a project becomes
unaffordable and untestable.

The rule: **the enabled set is a subset we can actually review, chosen from evidence.** Where an
inbox export exists, count it — which languages and countries actually generate inquiries, and in
what proportion. Almost always a small number of markets produce the large majority. Enable those,
handle the rest by handover to a human, and say so plainly in the Angebot.

Adding a language later is a priced increment. Shipping one nobody can read is a defect.

### Two different reviews, do not merge them

- **Fact review** — is this correct for this market? Delivery time, return policy, warranty,
  price. Comes from the client's own people.
- **Language review** — is this good, natural, correctly registered text? Comes from a native
  speaker with writing ability.

They are rarely the same person. A sales director confirms the facts are right; he does not
guarantee the copy reads well. A translator makes it read well; they cannot know your delivery
times. Both are needed, and both are named per language in `brief.md`.

### Source language

- One **source language** per project, named in `brief.md`. Everything is authored in it;
  other languages derive from it. Two source languages means two divergent sites within a year.
- **Every shipped language has a named human reviewer.** Not "we ran it through a translator".
  If nobody on our side or the client's side can read it, we do not ship it — we say so and price
  a reviewer in.
  - The client's own staff is often the cheapest honest reviewer. Ask before hiring one.
  - Machine translation is a draft. Text that reached a customer without human review is a defect,
    the same as untested code.
- Locale formats are part of translation, not an afterthought: prices, decimal separators, dates,
  address order, phone formats, name order. `1.234,56 €` in German, `€ 1.234,56` in Dutch,
  `1 234,56 €` in French.
- Length varies. German runs 25–35 % longer than English; Turkish and Finnish break layouts in
  their own ways. Components are tested with the longest enabled language
  (`.claude/standards/DESIGN.md`).

### Technical

- `hreflang` for every language pair including `x-default`; language in the URL
  (`/de/`, `/en/`) — not a cookie, not IP redirection. Automatic redirection by IP is a
  well-known SEO and usability failure and, done badly, traps users in the wrong language.
- A visible language switcher that keeps the user on the same page, not one that dumps them on
  the home page.
- `lang` attribute correct per page — screen readers pronounce with it, and hyphenation depends
  on it.
- Fonts must cover the character sets actually used: `ÄÖÜäöüß`, plus `ğışçöü`, `ąćęłńóśźż`,
  Cyrillic, whatever the enabled languages need. Check the subset before shipping.
- Emails, invoices and system notifications are part of the deliverable and need the same language
  coverage as the site. Forgotten far too often.

## AI assistants specifically

- **Enabled languages are a decision, not an emergent property.** An LLM will happily answer in any
  language it is addressed in. That means it will answer in a language nobody on the team can
  review, using a knowledge base that exists in one language, with no idea whether the answer is
  correct for that market.
- Therefore: enabled languages are an explicit list. Outside the list, the assistant hands over to
  a human — in the language it was addressed in, politely.
- The knowledge base is maintained in the **source language**. Answers in other languages are
  generated from it and must not invent market-specific facts — delivery times, return policy,
  price, warranty differ per market.
- The evaluation set (`.claude/standards/TESTING.md`) covers **every enabled language**, including
  the refusal and handover cases. An assistant tested only in German and enabled for English is
  untested.
- The AI-disclosure notice and the handover message exist in every enabled language, reviewed by
  a human, and are covered by the test set.
- Language detection failure is a defined case: mixed-language input, dialect, transliterated
  Turkish or Russian in Latin script. Decide the behaviour rather than discovering it.

## Where this is recorded

`brief.md` per project:

```
## Sprachen
Korrespondenz mit dem Kunden:   <Sprache>
Quellsprache der Inhalte:       <Sprache>
Ausgelieferte Sprachen:         <Liste>
Märkte (rechtlich relevant):    <Liste>
Prüfer je Sprache:              <Name, Rolle>   ← ohne Namen wird nicht ausgeliefert
```
