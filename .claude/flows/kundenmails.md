# Flow — Kundenmails

Wie eine Mail an den Kunden entsteht, geprüft wird und rausgeht. Festgehalten am 2026-08-14
nach dem ersten kompletten Durchlauf bei Kunde A (Sofa Belle) — inklusive der Fehler, die dabei
gemacht wurden.

**Owner:** `pm-client-lead`. Aufgaben, die aus einer Antwort entstehen, gehen an
`co-founder-orchestrator`, nicht direkt an Fachagenten.

---

## Teil A · Gilt für jede Mail an einen Kunden

### 1 · Format: HTML mit echtem Anker

Nur-Text-Mails überlassen die Verlinkung dem Client, und der macht daraus je nach Laune eine
kaputte oder gar keine Verlinkung. Ein Link ist ein `<a href>`, kein nackter URL im Fließtext.

Bei einem wichtigen Link zusätzlich die volle Adresse klein darunter — für den Fall, dass die
Formatierung beim Empfänger nicht ankommt.

### 2 · Sprache

Entwurf in der vereinbarten Korrespondenzsprache. **Ohne benannten muttersprachlichen Prüfer geht
nichts raus** (`.claude/standards/LANGUAGE.md`). Wird trotzdem gesendet, weil der Founder es so
entscheidet, kommt der Vermerk in die Datei — nicht als Vorwurf, sondern damit später
nachvollziehbar ist, warum kein Prüfvermerk vorliegt.

### 3 · Humanizer-Durchgang, vor jedem Versand

| Sprache | Werkzeug |
|---|---|
| Deutsch | Skill `humanizer-de` |
| Russisch | Skill `humanizer-ru` |
| Rumänisch | `.claude/standards/RO-SCHREIBMUSTER.md` — kein Skill vorhanden |
| andere | Teil 1 der RO-Datei anwenden, sie ist sprachunabhängig |

**Die strukturellen Marker prüft man ohne die Sprache zu können**, durch Zählen: Satzlängen,
Gedankenstriche, Glieder je Aufzählung, ob irgendwo ein Zugeständnis steht. Das findet den größten
Teil. Was bleibt, ist Idiomatik — und die kann nur ein Muttersprachler beurteilen.

Der Skill ersetzt das Sprach-Gate nicht: er räumt Muster weg, über Fachlichkeit und Register
entscheidet weiterhin der Prüfer.

### 4 · Entwurf, nicht Versand

`pm-client-lead` legt die Mail als **Entwurf** ab. Gesendet wird von einem Menschen
(CLAUDE.md §2.5). Antworten liest der Agent selbst — Lesen ist unkritisch und braucht niemanden.

Damit ist der Founder ein Klick und kein Teilnehmer.

### 5 · Sichtbarer Platzhalter für alles Fehlende

Fehlt etwas Pflichtiges — Signatur, Pflichtangaben, ein Name — steht an seiner Stelle eine
**auffällige Warnzeile**, kein stiller Leerraum:

```
[!] SIGNATUR FEHLT — vor dem Versand ergänzen
```

Eine unvollständige Mail muss beim Draufschauen als unvollständig erkennbar sein. Ein leerer
Platz sieht aus wie Absicht.

### 6 · Spiegelung im Projektordner

Jede Mail liegt zusätzlich als Datei unter `projects/<slug>/`, mit Empfänger, Sprache, Kanal,
Entwurfs-ID und internen Anmerkungen zur Begründung einzelner Formulierungen. **Beides wird
gepflegt.** Eine Datei, die zwei Fassungen hinter dem Entwurf liegt, ist schlimmer als keine.

### 7 · Keine Zahl, kein Termin, keine Zusage

Bis Research abgeschlossen und das Angebot freigegeben ist, enthält keine Mail einen Preis, ein
Datum oder ein „das ist kein Problem". Wenn der Kunde danach fragt: „Darauf komme ich zurück,
sobald ich X gesehen habe."

Das offensiv zu begründen wirkt besser als es zu umgehen: *„Sonst wäre jede Zahl geraten."*

### 8 · Betreff stabil halten

Über den ganzen Vorgang derselbe Betreff, damit der Thread zusammenbleibt.

---

## Teil B · Erstkontakt

### 1 · Empfänger nach Rolle trennen

Wer über Geld entscheidet, ist selten der, der die fachlichen Antworten hat. Das sind **zwei
Mails**, nicht eine mit zwei Adressaten.

| Rolle | Bekommt | Umfang |
|---|---|---|
| Inhaber / Entscheider | **genau eine Frage**, die nur er beantworten kann | kurz |
| Fachlicher Ansprechpartner | den Detailkatalog | ausführlich |

Wer einem Inhaber fünf Fragen stellt, bekommt auf die wichtigste keine Antwort.

### 2 · Recherche vor der Frage

Vor der ersten Zeile: Website ansehen, gelieferte Daten auswerten, Projektordner lesen. **Jede
Frage, die daraus beantwortbar gewesen wäre, ist ein Fehler** (`client-onboarding`).

Bei Kunde A hat die Auswertung des Lead-Exports 9 von 31 Fragen gestrichen und die Hälfte der
übrigen in Bestätigungen verwandelt.

### 3 · Dem Kunden seine eigenen Zahlen zurückgeben

Ein Absatz mit dem, was wir aus seinen Daten gelesen haben. Das ist der Absatz, der diese Mail von
jeder anderen Agenturmail in seinem Postfach unterscheidet — und er ersetzt jede Behauptung über
unsere Sorgfalt.

### 4 · Viele Fragen gehören in ein Formular, nicht in die Mail

Ab etwa zehn Fragen: Google-Formular (MCP-Server `google-forms`), Mail wird zur kurzen
Begleitnachricht mit Link.

- **Bestätigungen** als Einzelauswahl mit „Sonstiges" für die Korrektur — ein Klick statt eines
  Antwortsatzes.
- **Der Kontext, der sonst in der Mail stünde, wandert in den Hilfetext unter das Feld.** Ohne ihn
  kippen die Antworten ins Einsilbige — Formulare ziehen kurze Antworten an.
- Antworten bearbeitbar lassen, sonst wird bei 20 Fragen in der Mitte abgebrochen.
- E-Mail-Erfassung an: liefert nebenbei die Adresse.
- **Keine Datei-Uploads für Kundendaten.** Der Transferweg wird separat vereinbart.

### 5 · Fünf Prioritätsfragen benennen

Auch bei 25 Fragen: die fünf nennen, die den Angebotsumfang entscheiden. Ein Kunde, der nur diese
beantwortet, hat uns weitergebracht.

### 6 · Ein Ventil am Schluss

*„Wenn eine Frage unklar oder zu technisch ist, schreiben Sie mir, ich formuliere sie um."*
Ohne diesen Satz wirkt ein reiner Fragenkatalog abweisend. Ersetzt das Gesprächsangebot, wenn
keine Termine angeboten werden.

---

## Teil C · Nach dem Versand

1. Antwort lesen (`search_threads`, `get_thread`) bzw. Formularantworten abrufen
2. Verteilen: Fakten → `brief.md`, Offenes → `offene-fragen.md`, Zusagen → `entscheidungen.md`
3. Karten auf dem Board nachziehen, Blockierer aus *Blockiert* nach *Bereit*
4. Nächsten Entwurf vorbereiten

---

## Fehler aus dem ersten Durchlauf

Damit sie nicht wiederholt werden.

**E-Mail-Verhalten nie durch Versand an die eigene Adresse prüfen.** Der Zustellweg ist ein
anderer. Eine Google-Zwischenseite beim Formularlink kostete rund eine Stunde Untersuchung und war
ein Artefakt des Selbstversands im Gmail-Webinterface — in der Gmail-App und in anderen Clients
trat sie nie auf. Wenn Linkverhalten geprüft werden muss: an eine **fremde** Adresse, möglichst
nicht bei demselben Anbieter.

**Ein Abruf über die Gmail-API liefert die gerenderte Fassung, nicht das Original.** Die
`google.com/url?q=`-Umhüllung stammt aus Gmails Darstellung, nicht aus der versendeten Nachricht.
Diese Verwechslung hat die Fehlersuche in die falsche Richtung geschickt.

**Keine CDATA-Klammern um HTML-Inhalte.** Sie landen sichtbar als `]]>` am Mailende.

**Die eigenen Anti-KI-Regeln auch anwenden.** Der Regelsatz stand in `pm-client-lead`, und der
erste Entwurf enthielt trotzdem drei negative Parallelismen, neun Gedankenstriche und zwei
aphoristische Schlusssätze. Regeln aufschreiben ersetzt den Durchgang nicht — deshalb ist er in
Teil A als Schritt geführt.

---

## Checkliste vor dem Versand

- [ ] Recherche gemacht, überflüssige Fragen gestrichen
- [ ] Empfänger nach Rolle getrennt
- [ ] HTML mit echtem Anker, volle Adresse als Rückfallebene
- [ ] Humanizer-Durchgang gelaufen, Befunde behoben
- [ ] Sprachprüfer benannt — oder Abweichung protokolliert
- [ ] Keine Zahl, kein Termin, keine Zusage
- [ ] Kein `[[…]]`, kein `[!]` mehr im Text
- [ ] Signatur mit Pflichtangaben vorhanden
- [ ] Datei im Projektordner stimmt mit dem Entwurf überein
- [ ] Entwurf abgelegt — **Versand durch den Menschen**
