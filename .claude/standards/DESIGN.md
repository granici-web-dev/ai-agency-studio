# DESIGN

Agency design baseline. Read by `impeccable`. Per-project `DESIGN.md` starts from this file and
adds the approved visual system; per-project `PRODUCT.md` comes from `brief.md`, not from a second
interview with the client.

## Who we design for

German SMB customers and their customers. Often over 45, often on a mid-range Android phone, often
in poor mobile reception, frequently spending four figures. They are not impressed by motion. They
are looking for evidence that the company is real, reachable and will still exist next year.

**Trust signals outrank delight**, every time: a real address, real photographs of the actual
business, phone number visible without scrolling, prices that are legible and complete, delivery
time stated plainly.

## Hard constraints — not style choices

These come from law or from `CLAUDE.md` §6 and cannot be traded away for a nicer layout.

- **Contrast: WCAG 2.1 AA.** 4.5:1 body text, 3:1 large text and meaningful UI boundaries. Grey
  text on white below that ratio is a defect, however good it looks in the mockup.
- **Focus is always visible**, on every interactive element, with contrast against both its own
  background and the adjacent one. Never `outline: none` without a designed replacement.
- **Touch targets ≥ 44 px**, with real spacing between them.
- **Text scales to 200 %** without loss of content or function. Nothing in a fixed-height box that
  clips.
- **Fonts self-hosted.** No call to a Google or US CDN. Two families maximum, only the weights
  actually used.
- **Motion respects `prefers-reduced-motion`.** Nothing essential communicated by animation alone.
- **Nothing conveyed by colour alone** — error, availability, required field all need a second cue.
- **No layout shift** from late-loading images, fonts or banners. Reserve the space.
- **The consent banner is part of the design.** Reject is as prominent as accept, both reachable by
  keyboard, and it does not cover the Impressum link.

## German-specific layout constraints

- **German runs 25–35 % longer than English.** Any component designed against English placeholder
  text will break: buttons, navigation, table headers, card titles. Design and test with real
  German, including the long ones — `Widerrufsbelehrung`,
  `Datenschutzerklärung`, `Versandkostenpauschale`, `Zahlungspflichtig bestellen`.
- Long compound nouns need hyphenation (`hyphens: auto` with `lang="de"`) or they overflow narrow
  columns.
- Umlauts and ß must be present in the font subset — check `ÄÖÜäöüß` before shipping a subset.
- Formal register. The interface says `Sie`, consistently, including error messages and buttons.
- Prices in German format: `1.234,56 €`, currency after the number, with `inkl. MwSt.` and the
  shipping-cost note adjacent — that adjacency is a legal requirement, not a design preference.
- Dates as `14.08.2026` or `14. August 2026`. Never `08/14/2026`.

## Visual defaults

Starting point for a new project, to be replaced by the approved client design system:

- **Layout:** one obvious column of content, generous vertical rhythm, max text measure ~70
  characters. Content-first; decoration does not push content below the fold.
- **Type scale:** body minimum 17–18 px on mobile. Line height 1.5+ for body. Headings distinct by
  size and weight, not by colour alone.
- **Spacing:** one scale, used consistently. Inconsistent spacing is the single most common reason
  a site reads as amateur.
- **Colour:** a restrained palette with one accent used for actions only. If every element is
  emphasised, nothing is.
- **Imagery:** the client's real photographs beat stock, even when technically worse. Where we
  generate images (`creative-producer-higgsfield`), never fake team photos, testimonials or
  certificates.
- **Corners, shadows, gradients:** pick one treatment and apply it everywhere, or use none. Mixed
  treatments read as a template that was edited.

## Mandatory elements on every page

Footer with Impressum and Datenschutzerklärung, reachable from every page including checkout.
Contact route visible without searching. On shops additionally: AGB, Widerrufsbelehrung, Versand &
Zahlung, and correct price display in every place a price appears — listing, detail, cart,
checkout, confirmation email.

## Using impeccable's refine commands

`clarify`, `distill`, `layout`, `typeset`, `polish` and `adapt` are the everyday tools.

`animate`, `delight`, `bolder` and `overdrive` need founder approval. They cost Lighthouse points,
they fight `prefers-reduced-motion`, and loud design is the wrong signal for this market
(`CLAUDE.md` §7).

**No refine command touches a mandatory legal text, the order button wording, the price display or
the consent banner.** If a suggestion does, it goes to `german-legal-compliance` before anything
changes.

## Checked before the design is called finished

- [ ] Contrast measured, not eyeballed
- [ ] Keyboard-only pass through every flow, focus visible throughout
- [ ] 360 / 768 / 1440 px screenshots collected
- [ ] Real German content in place, longest strings included
- [ ] Zoom to 200 %, nothing clipped
- [ ] `prefers-reduced-motion` honoured
- [ ] Lighthouse mobile ≥ 90 Performance, Accessibility, SEO
- [ ] Legal elements present and untouched by any refine pass
