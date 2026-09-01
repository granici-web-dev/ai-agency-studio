# Flow A — Der Founder bringt den Kunden

Zustand 0 · LEAD, Variante A. Gilt, wenn der Kontakt über den Founder kommt: Empfehlung,
Netzwerk, Messe, früherer Kunde, persönliches Gespräch.

**Owner:** `pm-client-lead` · **Support:** `client-onboarding` · **Review:** `co-founder-orchestrator`

## Was hier anders ist

Vertrauen ist schon da, Qualifizierung ist im Wesentlichen erledigt — der Founder hätte den
Kontakt sonst nicht weitergegeben. Dafür gibt es ein Risiko, das Flow B nicht hat:

> **Im Gespräch ist bereits etwas gesagt worden, das niemand aufgeschrieben hat.**

Eine Zahl, ein Zeitraum, ein „das ist kein Problem". Der Kunde erinnert sich daran, das Team nicht.
Jede spätere Auseinandersetzung über Scope in diesem Flow geht auf diesen Punkt zurück. Deshalb
beginnt Flow A nicht beim Kunden, sondern beim Founder.

## Schritt A1 · Übergabe vom Founder

Bevor irgendetwas an den Kunden geht, beantwortet der Founder oder der Vertrieb diese Fragen.
Kurz, stichpunktartig, in seiner Sprache. `pm-client-lead` fragt aktiv nach, wenn etwas fehlt —
das ist keine Unhöflichkeit, das ist der Job.

**Regelweg ist der Lead-Bot** (`tools/leadbot/`): `/new_client` in Telegram, Fragen auf dem
Telefon beantworten, fertig. Der Bot legt `projects/<slug>/` mit Handoff, Status, Entscheidungen
und offenen Fragen an.

Nicht die Bequemlichkeit ist der Grund, sondern der **Zeitpunkt**: zwei Minuten nach dem Gespräch
erinnert der Vertrieb noch den halben Satz, am Abend nur noch die aufgeräumte Fassung. Deshalb
fragt der Bot Punkt 3 in drei getrennten Fragen ab — auf „Hast du etwas zugesagt?" antwortet
jeder Vertrieb mit Nein.

Ohne Bot gilt derselbe Katalog von Hand; er steht in `tools/leadbot/questions.mjs`.

```
HANDOFF — <Kunde>                                    Datum:

1  Who is it, what do they do, how did we meet?
2  What did they actually ask for, in their own words?
3  What did I already say?  ← the important one
   - any number mentioned?            (price, range, "around X")
   - any timeline mentioned?          ("before Christmas", "a few weeks")
   - anything I promised or implied?  ("sure, that's easy")
   - anything I ruled out?
4  Who decides on their side? Who pays?
5  How urgent is it really, and why?
6  Anything I know that they haven't said?
   (competitor, unhappy with current agency, funding, family business politics)
7  Languages — with the client, and of the product? Who can proofread them?
8  CONTACT DATA — per person. Without this there is no first contact.
   role · name and salutation · preferred channel (mail / WhatsApp / phone /
   Signal / Telegram / LinkedIn) · address or number for it · fallback channel ·
   reachable when · Sie or du, formal or relaxed
   - is that number business or private? WhatsApp is often the same device — ask, don't assume
   - did I already promise when we would get back to them?
9  Anything I do NOT want us to touch?
```

Ergebnis liegt als `projects/<slug>/00-handoff.md` im Projektordner. **Punkt 3 wird wörtlich in
`entscheidungen.md` übernommen**, mit dem Zusatz „mündlich zugesagt, noch nicht geprüft". Damit ist
es sichtbar, statt später zu überraschen.

Wenn der Founder etwas zugesagt hat, das wir nicht halten können, ist das **jetzt** zu klären, nicht
im Angebot. `co-founder-orchestrator` meldet es zurück, bevor der Kunde kontaktiert wird.

## Schritt A2 · Erstkontakt

Warm, kurz, mit Bezug auf das Gespräch. Nicht formal-anonym, nicht das Standardanschreiben aus
Flow B — der Kunde hat mit einem Menschen gesprochen, die erste Mail muss daran anknüpfen.

Vorlage: `.claude/vorlagen/lead-antworten.md` → *Erstkontakt nach Empfehlung*.

Inhalt: Bezug auf das Gespräch, in einem Satz was wir verstanden haben, der Fragebogen oder ein
Terminvorschlag, ein klarer nächster Schritt.

**Was der Founder gesagt hat, wird hier nicht wiederholt und nicht bestätigt.** Keine Zahl, kein
Termin, bevor Research gelaufen ist. Wenn der Kunde die Zahl nennt: „Darauf komme ich zurück,
sobald ich den Shop gesehen habe" — nicht bestätigen, nicht dementieren.

## Schritt A3 · Aufnahme

Zwei Wege, je nach Kunde:

**Schriftlich** — Fragebogen aus `client-onboarding`, passend zum Projekttyp. Gut bei Kunden, die
strukturiert arbeiten und Zahlen zur Hand haben.

**Gespräch** — bei Kunden, die nicht gern Formulare ausfüllen (die Mehrheit im Mittelstand). Dann:
`pm-client-lead` erstellt eine Agenda entlang des Fragebogens, die PM-Person führt das Gespräch,
`meeting-notes-specialist` macht daraus ein Protokoll, und das Protokoll geht **zur schriftlichen
Bestätigung an den Kunden zurück**. Erst die Bestätigung macht es zur Grundlage.

Blöcke A–C des Fragebogens sind in jedem Fall verpflichtend, auch mündlich. Ohne Firmierung,
Bestandsdaten und Sortimentsstruktur kein Research.

## Schritt A4 · Zugänge anfordern

Parallel, nicht danach. Lesezugang zum Bestandssystem ist in fast jedem Projekt der langsamste
Punkt, weil er beim Kunden bei jemand anderem liegt. Am selben Tag anfragen wie den Fragebogen.

## Übergabe an die Projektleitung

Ab Schritt A2 ist **`pm-client-lead` der einzige Kanal zum Kunden.** Der Founder stellt vor,
danach läuft alles über die Projektleitung — auch das, was der Founder selbst beantworten könnte.
Sonst entstehen zwei Gesprächsfäden, und der Stand ist nirgends vollständig.

Ausnahme: Der Inhaber korrespondiert in einer Sprache, die nur der Founder spricht. Dann bleibt
der Founder der Übermittler, aber `pm-client-lead` formuliert und protokolliert weiterhin — der
Founder ist Kanal, nicht Projektleitung.

## Exit-Kriterium

- [ ] `00-handoff.md` liegt vor, Punkt 3 in `entscheidungen.md` übernommen
- [ ] Kontaktdaten und bevorzugter Kanal je Ansprechpartner erfasst
- [ ] Betrieb, Ansprechpartner und Anfrage im CRM angelegt (`.claude/crm.md`)
- [ ] Erstkontakt versendet (durch die PM-Person)
- [ ] Fragebogen versendet **oder** Termin steht
- [ ] Zugänge angefragt
- [ ] `projects/<slug>/` angelegt mit `status.md`, `offene-fragen.md`, `entscheidungen.md`

→ Zustand 1 · BRIEF

## Typische Fehler in diesem Flow

- **Zu schnell in die Umsetzung.** Weil das Vertrauen da ist, wird der Brief übersprungen. Genau
  hier entstehen die unangenehmen Projekte.
- **Freundschaftsrabatt ohne Scope-Reduktion.** Wenn der Preis sinkt, sinkt der Umfang. Sonst
  arbeiten wir zum halben Satz am vollen Projekt.
- **Kein Angebot, weil „wir kennen uns".** Es gibt immer ein schriftliches Angebot mit benannten
  Liefergegenständen. Das schützt beide Seiten, nicht nur uns.
- **Der Founder bleibt Ansprechpartner.** Nach dem Erstkontakt übernimmt die PM-Person sichtbar.
  Sonst laufen Absprachen weiter an der Projektleitung vorbei und niemand kennt den Stand.

---

Handwerk der Mails selbst — Format, Humanizer, Platzhalter, Entwurf-statt-Versand:
`.claude/flows/kundenmails.md`.
