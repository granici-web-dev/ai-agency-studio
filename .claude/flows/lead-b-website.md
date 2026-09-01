# Flow B — Der Kunde stellt eine Anfrage über die Website

Zustand 0 · LEAD, Variante B. Gilt für jede Anfrage über Formular, Mail an die allgemeine Adresse,
Rückrufwunsch oder Chat auf unserer eigenen Seite.

**Owner:** `client-onboarding` (Triage) → `pm-client-lead` (Antwort) · **Review:** `co-founder-orchestrator`

## Was hier anders ist

Kein Vertrauen, kein Kontext, unbekannte Qualität. Der größere Teil eingehender Anfragen im
Agenturgeschäft ist kein Projekt: Agenturen, die SEO verkaufen wollen, Preisvergleicher, Studenten,
Anfragen außerhalb unseres Angebots. Zwei Dinge entscheiden:

1. **Antwortgeschwindigkeit.** Wer im deutschen Mittelstand am selben Werktag antwortet, ist in der
   engeren Auswahl. Wer nach drei Tagen antwortet, ist raus — unabhängig von der Qualität.
2. **Aussortieren, ohne unhöflich zu werden.** Eine gute Absage bringt Empfehlungen. Eine
   ignorierte Anfrage bringt eine schlechte Google-Bewertung.

## Schritt B0 · Das Formular selbst

Das Formular ist Teil des Prozesses, nicht Vorstufe. Es entscheidet, ob Triage überhaupt möglich
ist. Vorgabe für unsere Seite — und wiederverwendbar für Kundenprojekte:

| Feld | Pflicht | Zweck |
|---|---|---|
| Name | ja | Ansprache |
| Firma | ja | erste Recherche vor dem Rückruf |
| E-Mail | ja | Antwortweg |
| Telefon | nein | wer sie angibt, will reden — starkes Signal |
| Worum geht es? | ja | Auswahl: Website · Onlineshop · KI im Unternehmen · Wartung · Sonstiges |
| Kurz beschrieben | ja | Freitext, das eigentliche Triage-Material |
| Budgetrahmen | nein | Auswahl inkl. „weiß ich noch nicht" |
| Wann soll es fertig sein? | nein | Auswahl inkl. „noch offen" |
| Einwilligung Datenschutz | ja | Checkbox, ungesetzt, mit Link zur Datenschutzerklärung |

**Nicht mehr als das.** Der Fragebogen kommt später; ein Formular mit 20 Feldern wird nicht
ausgefüllt. Datensparsamkeit ist hier keine Pflichtübung, sondern deckt sich mit dem
Konversionsinteresse.

Technisch verbindlich:
- **Kein Google reCAPTCHA.** Honeypot-Feld plus Zeitfalle (Absenden unter drei Sekunden = Bot),
  bei Bedarf Friendly Captcha oder eine andere EU-Lösung.
- Einwilligungs-Checkbox **nicht vorausgewählt**, Text nennt Zweck und Speicherdauer.
- Bestätigungsseite statt nur Meldung im Formular — sonst ist keine Konversion messbar.
- Übermittlung an eine Adresse, die tatsächlich gelesen wird, plus Ablage im Projekt-Posteingang.
- Löschroutine: Anfragen ohne Projekt nach spätestens sechs Monaten löschen.

## Schritt B1 · Eingangsbestätigung, automatisch

Sofort, innerhalb einer Minute, ausgelöst vom Formular selbst.

Inhalt: Eingang bestätigt, wann eine echte Antwort kommt (am selben Werktag, spätestens am
nächsten Vormittag), wer sich melden wird, eine direkte Telefonnummer für Eiliges.

Vorlage: `.claude/vorlagen/lead-antworten.md` → *Eingangsbestätigung*.

**Zu CLAUDE.md §2.5:** Das ist kein Verstoß gegen „Agenten versenden keine Mails". Die
Eingangsbestätigung ist ein einmalig freigegebener statischer Text, den das Formularsystem
verschickt — kein Agent formuliert und versendet hier etwas. Jede Änderung am Text geht erneut
durch `german-language-tone` und den Founder.

## Schritt B2 · Triage

**Owner:** `client-onboarding`, innerhalb weniger Stunden. Kurze Vorabrecherche: Website des
Anfragenden ansehen, Impressum, Größenordnung, aktueller Stand.

Ergebnis ist eine von drei Einstufungen, festgehalten in `leads/<datum>-<firma>.md`:

### A — passt, ernsthaft

Leistung liegt in unserem Angebot, Budget plausibel oder wahrscheinlich, Anfragender wirkt
entscheidungsbefugt, Aufgabe konkret.

→ Antwort am selben Werktag mit **Terminvorschlag** (zwei konkrete Zeitfenster, nicht „wann passt
es Ihnen?"). Danach weiter wie Flow A ab Schritt A3.

### B — vielleicht, noch zu unklar

Passt grundsätzlich, aber Umfang, Budget oder Zuständigkeit sind offen.

→ Antwort am selben Werktag: freundlich, mit **drei bis fünf konkreten Fragen**, die die
Einstufung entscheiden. Kein Termin, kein Angebot, keine Preisnennung. Wer antwortet, wird A. Wer
nach zwei Wochen nicht antwortet, wird abgelegt — ein Nachfassen, dann Ruhe.

Die häufigste Unklarheit ist das Budget. Nicht direkt danach fragen, sondern verankern:
„Projekte in dieser Größenordnung liegen bei uns üblicherweise zwischen X und Y — passt das
grundsätzlich zu Ihrer Vorstellung?" Das beantwortet die Frage, ohne den Kunden auszufragen.

### C — passt nicht

→ Absage, ebenfalls am selben Werktag. Kurz, respektvoll, mit Grund und wenn möglich einem
Hinweis, an wen sich der Anfragende sinnvoll wenden kann. Vorlage: *Absage*.

Eine gute Absage ist kein verlorener Lead, sondern eine Empfehlung, die später zurückkommt.

## Ausschlusskriterien für C

- Leistung außerhalb unseres Angebots (App-Entwicklung, Print, reines SEO-Mandat, Werbeschaltung)
- Budget erkennbar unterhalb des Starter-Pakets (CLAUDE.md §4) ohne Aussicht auf Wachstum
- Agentur oder Dienstleister, der uns etwas verkaufen will
- Anfrage nach unbezahlter Vorleistung („schicken Sie uns einen Entwurf, dann entscheiden wir")
- Erkennbar nur Preisvergleich für ein bereits vergebenes Projekt
- Zusagen verlangt, die niemand geben kann (Platz 1 bei Google, garantierte Umsatzsteigerung)
- Laufender Streit mit der Vorgängeragentur, in den wir hineingezogen werden sollen
- Inhalte, die wir nicht bauen (rechtswidrig, irreführend, Nachbau einer fremden Seite)

Grenzfälle entscheidet `co-founder-orchestrator`, nicht die Triage. Im Zweifel B, nicht C — eine
Frage kostet weniger als ein verlorenes Projekt.

## Schritt B2a · Kontaktdaten sind schon da — Kanal aber nicht

Anders als in Flow A liefert das Formular Name, Firma, E-Mail und optional Telefon frei Haus. Was
es **nicht** liefert, ist der Kanal, über den der Kunde tatsächlich reden will.

Deshalb enthält die erste persönliche Antwort immer eine Zeile dazu:

> „Wie erreiche ich Sie am besten — per E-Mail, telefonisch oder über WhatsApp?"

Die Antwort wird in `brief.md` und auf der Lead-Karte festgehalten. Ab da wird dieser Kanal
benutzt und kein anderer. Wer per WhatsApp fragt und per Förmlichkeitsmail antwortet, verliert den
Faden — und meist den Lead.

Hat der Kunde eine Telefonnummer hinterlassen, ohne danach gefragt zu werden, ist das bereits ein
Signal: er will sprechen.

**Ab der ersten Antwort führt `pm-client-lead` die Kommunikation** und gibt sie nicht mehr ab. Die
Triage ist eine interne Einstufung, kein Gesprächspartnerwechsel.

## Schritt B3 · Antwort

**Owner:** `pm-client-lead`. Entwurf → `german-language-tone` → Freigabe → Versand durch die
PM-Person.

Bei A und B mit hoher Frequenz wird das zum Engpass. Deshalb: die Antworten für A, B und C sind
vorformuliert und einmal freigegeben; `pm-client-lead` passt sie an den Einzelfall an, statt jedes
Mal neu zu schreiben. Nur die angepassten Stellen gehen durch das Sprach-Gate.

## Exit-Kriterium

- [ ] Eingangsbestätigung raus (automatisch)
- [ ] Triage-Notiz in `leads/` mit Einstufung und Begründung
- [ ] Persönliche Antwort raus, am selben Werktag
- [ ] Bei A: `projects/<slug>/` angelegt, Termin steht oder Fragebogen versendet
- [ ] Bei B: Nachfass-Datum gesetzt
- [ ] Bei C: Absage raus, Notiz abgelegt

→ Bei A: Zustand 1 · BRIEF. Bei B: Warteschleife. Bei C: Ende.

## Was gemessen wird

Sonst lässt sich der Flow nicht schärfen. Monatlich durch `analytics-reporter`:

- Anfragen gesamt, Verteilung A / B / C
- Zeit bis zur ersten persönlichen Antwort (Ziel: unter vier Arbeitsstunden)
- Anteil B, der zu A wird
- Anteil A, der zu einem Angebot wird, und Anteil davon, der zusagt
- Woher die Anfragen kommen

Wenn der C-Anteil dauerhaft hoch ist, liegt das nicht an den Anfragenden, sondern an unserer
eigenen Website: sie beschreibt das Angebot oder die Größenordnung nicht deutlich genug.

---

Handwerk der Mails selbst — Format, Humanizer, Platzhalter, Entwurf-statt-Versand:
`.claude/flows/kundenmails.md`.
