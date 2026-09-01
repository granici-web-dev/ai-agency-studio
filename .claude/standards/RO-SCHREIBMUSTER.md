# Rumänisch — KI-Schreibmuster

Für Deutsch (`humanizer-de`) und Russisch (`humanizer-ru`) gibt es gepflegte Skills. **Für
Rumänisch existiert keiner** — Stand 2026-08-14 gesucht und nichts gefunden. Diese Datei ist der
Ersatz, und sie ist bewusst unfertig: der sprachspezifische Teil wächst aus den Korrekturen des
muttersprachlichen Prüfers.

Anzuwenden auf jeden rumänischen Entwurf, bevor er den Kunden erreicht.

## Teil 1 — Strukturelle Muster, sprachunabhängig

Diese übertragen sich eins zu eins aus den anderen Skills. Sie sind hier vollständig prüfbar, auch
ohne Rumänischkenntnisse — und sie sind der größere Teil des Problems.

| # | Muster | Woran erkennbar |
|---|---|---|
| S1 | **Negative Parallelismen** | „nu X, ci Y", „nici … nici". Einmal ist Rhetorik, dreimal in einer kurzen Mail ist eine Signatur |
| S2 | **Dreierregel** | Aufzählungen bestehen auffällig oft aus genau drei Gliedern. Zwei oder vier wirken menschlich |
| S3 | **Aphoristische Schlusssätze** | Der Absatz endet auf einen Merksatz. Klingt gut, wirkt tot — so schreibt man Claims, keine Geschäftspost |
| S4 | **Gleichmäßiger Rhythmus** | Alle Absätze etwa gleich lang, jeder Satz „landet". Echte Mails haben einen Absatz aus zwei Wörtern und einen, der ausfranst |
| S5 | **Gedankenstrich-Inflation** | Mehr als ein bis zwei Gedankenstriche auf eine Mail |
| S6 | **Superlative und Bedeutungsaufblähung** | „cea mai importantă", „esențial", „revoluționar" ohne Beleg |
| S7 | **Symmetrischer Aufbau** | Jeder Abschnitt gleich gebaut: Behauptung, Begründung, Folgerung |
| S8 | **Alles beantworten** | Auf jeden Punkt wird eingegangen. Menschen lassen Nebensächliches weg |
| S9 | **Keine Unsicherheit** | Kein „nu sunt sigur", kein Zugeständnis, kein „asta o aflu abia joi" |
| S10 | **Fettdruck und Listen** in einer Mail, die keine braucht |

**Gegenprobe, ohne die Sprache zu können:** Satzlängen zählen. Gedankenstriche zählen.
Aufzählungsglieder zählen. Prüfen, ob irgendwo ein Zugeständnis steht. Das findet den größten Teil.

## Teil 2 — Was einem rumänischen Text zusätzlich fehlt

Vermutungen, bis der Prüfer sie bestätigt oder verwirft. **Nicht als gesichert behandeln.**

- **Anrede und Register.** `Stimate domnule X` ist korrekt formell. Ob es für einen laufenden
  Austausch mit einem Vertriebsleiter nicht zu steif wird, weiß nur ein Muttersprachler.
- **Diakritika.** `ă â î ș ț` müssen durchgehend korrekt sein. Ein Text mit fehlenden Diakritika
  wirkt nachlässig; ein Text mit *perfekten* Diakritika in einer schnellen Chatnachricht wirkt
  umgekehrt maschinell — im Alltag lassen Rumänen sie oft weg. Für Geschäftspost: immer setzen.
- **Übersetztes Deutsch/Englisch.** Zusammengesetzte Substantive und Schachtelsätze aus dem
  Deutschen übertragen sich schlecht. Wenn ein Satz nur mit Mühe zu lesen ist, ist er meist
  übersetzt und nicht gedacht.
- **Idiomatik.** Ein Text ohne eine einzige idiomatische Wendung liest sich maschinell. Welche
  Wendungen im Geschäftsverkehr passen, kann diese Datei nicht sagen.
- **Anglizismen.** Im rumänischen Geschäftsleben verbreitet, aber die Dosis entscheidet.

## Teil 3 — Korrekturen des Prüfers

**Hier wächst die Datei.** Jede Korrektur, die der muttersprachliche Prüfer an unseren Texten
vornimmt, wird hier als Muster festgehalten — sonst machen wir denselben Fehler beim nächsten Mal.

| Datum | Unser Entwurf | Korrigiert zu | Warum |
|---|---|---|---|
| — | *(noch keine Einträge)* | | |

## Vorgehen bis dahin

1. Teil 1 selbst prüfen — das geht ohne Rumänisch und findet das meiste.
2. Muttersprachler drüberlesen lassen (`.claude/standards/LANGUAGE.md`: ohne benannten Prüfer wird
   nicht ausgeliefert).
3. Seine Korrekturen in Teil 3 eintragen.

**Grundsatz, von den beiden anderen Skills übernommen:** Ziel ist natürlicher Text, **nicht** ein
grünes Ergebnis in einem KI-Detektor. Werkzeuge, die auf Detektoren optimieren, streuen Rauschen
und Tippfehler ein — für Geschäftspost macht das den Text schlechter, nicht besser.
