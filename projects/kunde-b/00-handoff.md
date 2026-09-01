cla# Handoff — Kunde B (Friseursalon)

Flow A, Schritt A1 (`.claude/flows/lead-a-founder.md`).

**Status:** teilweise beantwortet (Punkt 3 offen und blockierend) · **Datum:** 2026-08-15
**Übergeben von:** Vertrieb (über den Founder) · **Erfasst von:** `co-founder-orchestrator`

> Ordner umbenennen, sobald der Salonname feststeht.

---

## 1 · Wer ist es?

Friseursalon in **Deutschland**. **Eine Person nimmt Termine an — der Inhaber selbst.**
Es gibt **bereits eine Website**, aber keine Terminbuchung darauf.

`[!]` Name, Ort, bestehende Website-Adresse — offen.

Wie der Kontakt zustande kam: über den eigenen Vertrieb. `[!]` Persönlich vor Ort, Empfehlung
oder Anfrage — offen.

## 2 · Was wurde angefragt?

In den Worten des Vertriebs:

1. **Website** für den Salon
2. **Buchungssystem auf der Website** — Kundinnen buchen Termine selbst
3. **Benachrichtigung in einen Telegram-Kanal** bei jeder neuen Buchung
4. **Verwaltung der Termine** — ansehen, manuell anlegen, löschen, verschieben

Punkt 4 ist der wichtigste Satz in diesem Handoff und wird unten aufgegriffen.

## 3 · Was wurde bereits zugesagt?

`[!]` **Offen — und das ist die kritische Lücke dieses Flows.**

Zu klären, wörtlich, bevor der Kunde angeschrieben wird:

- Ist eine Zahl gefallen? Auch „so um die", auch als Spanne, auch als „das ist nicht teuer"?
- Ist ein Termin gefallen? „Bis zum Sommer", „in ein paar Wochen"?
- Wurde etwas zugesagt oder impliziert? „Telegram ist kein Problem", „das können wir"?
- Wurde etwas ausgeschlossen?

Was hier steht, wird **wörtlich** in `entscheidungen.md` übernommen, mit dem Zusatz
„mündlich zugesagt, noch nicht geprüft".

## 4 · Wer entscheidet, wer zahlt?

| Rolle | Person | Kontakt |
|---|---|---|
| **Inhaber** | `[!]` Name offen | `designer.nefele@gmail.com` |

`[!]` Ob der Inhaber auch derjenige ist, der die Termine täglich verwaltet, ist offen. Bei einem
Salon meist ja — aber wenn es eine Empfangskraft gibt, ist sie die eigentliche Nutzerin des
Verwaltungsteils, und ihre Anforderungen entscheiden über die Bedienbarkeit.

## 5 · Dringlichkeit

`[!]` Offen.

## 6 · Was wissen wir, das der Kunde nicht gesagt hat?

**Es gibt eine Website ohne Buchung.** Damit ist der Auftrag kein reiner Neubau: entweder wir
erweitern den Bestand oder wir ersetzen ihn. Das entscheidet sich erst, wenn wir die Seite gesehen
haben — Adresse und Lesezugang gehören in die erste Mail.

Wenn die bestehende Seite technisch tragfähig ist, wird das Projekt deutlich kleiner und der
Kunde zahlt nur für die Buchung. Ist sie es nicht, ist der Ersatz zu begründen, nicht zu behaupten.

`[!]` Offen: wer die Seite gebaut hat und ob wir dort überhaupt Zugriff bekommen. Das ist
erfahrungsgemäß der langsamste Punkt (Flow A, Schritt A4) — am selben Tag anfragen.

## 7 · Sprache

**Deutsch**, beide Achsen: Korrespondenz und Produkt. `german-language-tone` plus der
PM-Human als benannter Prüfer (CLAUDE.md §2.2). Kein Übersetzungsrisiko, kein fehlender Prüfer —
zum ersten Mal in diesem Repo der einfache Fall.

## 8 · Kontaktweg und Ton

Bekannt: **E-Mail an `designer.nefele@gmail.com`.**

`[!]` Offen: Anrede und Name, Sie oder du, ob eine Telefonnummer existiert und ob der Vertrieb
bereits eine Rückmeldung bis zu einem bestimmten Tag versprochen hat.

Hinweis: eine Gmail-Adresse als Geschäftskontakt spricht dafür, dass es keine eigene Domain gibt.
Passt zum Bild „noch keine Website" und ist ein Kaufsignal, kein Makel.

## 9 · Was sollen wir nicht anfassen?

`[!]` Offen.

---

## Was aus diesem Handoff bereits folgt

Ohne eine einzige Frage an den Kunden, allein aus der Aufgabenbeschreibung:

### A · Das ist kein Starter-Paket

CLAUDE.md §4 Starter meint 1–5 Seiten mit Rechtstexten und SEO-Grundlagen. Hier kommt eine
**Anwendung** dazu: Terminvergabe, Verwaltungsoberfläche, manuelles Anlegen, Verschieben, Löschen,
plus eine ausgehende Schnittstelle. Das ist eine eigene Position, keine Unterzeile der Website.

### B · Telegram ist der Punkt, an dem es rechtlich klemmt

Eine Buchung enthält Name, Telefonnummer, Uhrzeit und gewünschte Leistung. Wer das in einen
Telegram-Kanal schiebt, überträgt personenbezogene Daten von Verbraucherinnen an einen Anbieter
außerhalb der EU — ohne AVV, den wir kontrollieren, und in einen Kanal, dessen Mitgliederkreis
über einen Einladungslink wächst statt über eine Rechteverwaltung. Wer den Link irgendwann bekommt,
liest rückwirkend jede Buchung mit Namen und Nummer.

Zwei gangbare Wege, **vor** dem Angebot zu entscheiden, weil sie den Bau verändern:

1. **Benachrichtigung ohne Personenbezug.** Telegram bekommt nur „Neue Buchung, Di 14:30,
   Damenhaarschnitt". Namen und Nummer stehen im Verwaltungsbereich hinter Login. Der Inhaber
   behält seinen gewohnten Kanal, das Datenschutzproblem verschwindet fast vollständig.
2. **Anderer Kanal** — E-Mail an die Salonadresse oder Push aus dem Verwaltungsbereich.

Empfehlung ist Weg 1: er erfüllt den eigentlichen Wunsch („ich will sofort sehen, dass was
reinkam"), ohne die Kundendaten zu exportieren. `german-legal-compliance` gibt das ab.

### C · „manuell anlegen, verschieben, löschen" vergrößert den Umfang — aber weniger als befürchtet

Diese drei Verben bedeuten: das System ist **der Hauptkalender des Salons**, nicht ein Zusatz
neben dem Papierkalender.

**Entwarnung durch den Handoff: nur eine buchbare Person.** Damit entfallen die teuersten Punkte —
Kalender je Mitarbeiter, Zuordnung bei der Buchung, Rechte, wer wessen Termine sieht. Ein Kalender,
ein Login. Das ist der günstigste denkbare Zuschnitt und liegt klar im Bereich einer fertigen Lösung.

Was trotzdem zu klären bleibt:

- Öffnungszeiten, Pausen, Urlaub, Feiertage
- Dauer je Leistung — ein Schnitt ist 30 Minuten, eine Farbe zwei Stunden mit Einwirkzeit dazwischen
- Was bei Absage durch die Kundin passiert

**Und die Frage, die durch „nur einer" erst wichtig wird:** wenn der Inhaber allein arbeitet, ist
er während der Arbeit am Kunden. Er kann weder ans Telefon noch ins Backend. Genau deshalb will er
die Benachrichtigung — und genau deshalb muss die Bedienung auf dem Telefon funktionieren, nicht
auf einem Desktop-Backend. Das ist eine Anforderung, keine Geschmacksfrage.

### D · Erinnerungen sind nicht angefragt und werden erwartet

Onlinebuchung ohne Erinnerung erzeugt No-Shows. Erinnerung per SMS kostet Geld pro Nachricht und
braucht einen Absender; per E-Mail ist sie günstig und schwächer. Wird nicht gefragt, wird aber
nach vier Wochen Betrieb verlangt. Gehört ins Angebot — als Position oder als benannter Ausschluss.

### E · Kaufen statt bauen

Für WordPress existieren fertige Buchungslösungen; daneben gibt es eigenständige, EU-selbst-hostbare
Systeme. Eine Terminverwaltung für einen Friseursalon von Grund auf zu bauen ist nicht vertretbar.
Welche Basis, entscheidet `research`, nicht das Angebot — aber die Entscheidung fällt **vor** der
Zahl, nicht danach.

### F · Barrierefreiheit — prüfen, nicht annehmen

BFSG kennt eine Ausnahme für Kleinstunternehmen bei Dienstleistungen. Ein Salon fällt sehr
wahrscheinlich darunter. **Wahrscheinlich ist keine Auskunft** — Mitarbeiterzahl und Umsatz
abfragen, Ergebnis in die Akte. Unabhängig davon bauen wir die Buchungsstrecke bedienbar: während
des Baus kostet das fast nichts, nachträglich viel.

### G · Deutschland — unser Zielprofil, ohne Ausnahme

Anders als Kunde A. CLAUDE.md §1 greift unmittelbar, keine Founder-Entscheidung nötig, und der
Rechte-Gate ist hier Verkaufsargument statt Einschränkung.

---

## Nächster Schritt

Erst Punkt 3 klären, dann Erstkontakt. **Vorher geht keine Mail raus** — sonst bestätigen oder
dementieren wir etwas, von dem wir nicht wissen, dass es gesagt wurde.
