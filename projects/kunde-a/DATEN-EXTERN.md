# Kundendaten — liegen außerhalb dieses Repos

Stand 2026-08-15. Diese Datei enthält selbst **keine** personenbezogenen Daten und darf
das auch nie tun.

## Was ausgelagert wurde

| Datei | War | Liegt jetzt |
|---|---|---|
| Lead-Export, 5 Monate | `projects/kunde-a/Clienți potențiali.csv` | `~/Documents/Kundendaten-vertraulich/kunde-a/2026-08-14_Clienti-potentiali_export-5-monate.csv` |

Verschoben, nicht kopiert. SHA-256 vor und nach dem Verschieben identisch
(`4c400e6e…bbef64df`). Rechte: Ordner `700`, Datei `600`.

## Was drin steht

2.904 Datensätze, 21 Spalten. Personenbezogen sind: `Nume`, `Telefon`, `E-mail`, `Oraș`
sowie die Freitextspalten `Informatii` und `Revenire 1–3`, die Gesprächsnotizen der Verkäufer
enthalten. `Desemnat` ist der Name eines Mitarbeiters — ebenfalls personenbezogen, und
arbeitsrechtlich heikler als die Kundendaten, weil daraus Leistungsvergleiche ableitbar sind.

Betroffene sind ganz überwiegend **Verbraucher in Rumänien**. Zuständige Aufsicht: ANPC
beziehungsweise die rumänische Datenschutzbehörde.

## Rechtlicher Stand — offen

**Es liegt kein AVV / Contract de prelucrare mit dem Kunden vor.** Wir verarbeiten die Daten
also derzeit ohne schriftliche Grundlage. Das ist der eigentliche Befund, nicht der Speicherort.

Vor jeder weiteren Verarbeitung nötig:

1. AVV nach Art. 28 DSGVO mit dem Kunden, unterzeichnet
2. Zweckbindung schriftlich: Auswertung zur Angebotserstellung, nichts anderes
3. Löschfrist benannt — Vorschlag: 30 Tage nach Angebotsentscheidung
4. Klärung, ob der Kunde die Betroffenen über die Weitergabe an uns informieren muss

## Regeln für den weiteren Umgang

- **Der Originalexport wird nicht ins Repo zurückgeholt.** Auch nicht „nur kurz zum Nachrechnen".
- Auswertungen laufen gegen die externe Datei; ins Repo kommen nur **Aggregate** —
  Anteile, Häufigkeiten, Zeitreihen. Kein Name, keine Nummer, keine Adresse, kein wörtliches Zitat.
- Wird eine Arbeitskopie gebraucht, dann pseudonymisiert: Name, Telefon und E-Mail durch
  laufende IDs ersetzt, Freitextspalten weggelassen. Auch die Arbeitskopie bleibt außerhalb des Repos.
- **Nicht in die Wissensbasis des Assistenten.** Die Trainings- und Referenzdaten für T2 werden
  vom Vertrieb neu geschrieben, nicht aus echten Kundengesprächen kopiert.
- Nichts davon auf Trello — Trello ist ein US-Prozessor (`.claude/trello.md`).
- Keine Datei-Uploads für Kundendaten in Formularen (`.claude/flows/kundenmails.md`, Teil B.4).

## Was bereits im Repo bleiben darf

`research/01-lead-analyse.md` wurde geprüft: keine Telefonnummern, keine E-Mail-Adressen,
keine Namen, keine wörtlichen Zitate. Nur Anteile und Wortfrequenzen. Bleibt.

## Zu prüfen, wenn der Ordner ein Git-Repo wird

Dieses Verzeichnis ist derzeit **kein** Git-Repository. `.gitignore` existiert und schließt
`*.csv` sowie `*.xlsx` aus, greift aber noch nicht. Vor dem ersten `git init` prüfen, dass
keine Datei mit Personenbezug erfasst wird — nach dem ersten Commit ist sie in der Historie.
