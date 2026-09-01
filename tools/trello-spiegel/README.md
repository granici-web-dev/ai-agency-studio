# Trello-Spiegel

Zeigt die Aufgaben aus dem CRM (Twenty) auf einem Trello-Board an. **In eine Richtung.**

## Wozu

Twenty läuft auf `localhost`. Vom Telefon aus ist es nicht erreichbar, Trello schon. Wer unterwegs
sehen will, wo etwas hängt, schaut aufs Board.

Das löst das eigentliche Problem nicht — das CRM liegt auf einem Notebook, und wenn das aus ist,
ändert sich auf dem Board nichts. Der Spiegel macht diesen Zustand erträglich, nicht richtig.

## Warum nur in eine Richtung

Trello wurde am 2026-08-15 abgelöst, weil zwei Werkzeuge für denselben Stand bedeuten, dass eines
gepflegt wird und das andere lügt. Ein Spiegel, in den man auch hineinschreiben kann, wäre genau
dieses Problem mit einem freundlicheren Namen.

Wer eine Karte von Hand verschiebt, sieht sie beim nächsten Abgleich zurückwandern. Das Protokoll
nennt sie beim Namen:

```
↩  von Hand nach "Fertig" geschoben, zurück nach "Bereit"   [Recht] Pflichtangaben …
     (zuletzt gespiegelt als "Bereit" — im CRM ändern, nicht hier)
```

Möglich ist das nur, weil die Marke in der Kartenbeschreibung den zuletzt gespiegelten Zustand
mitträgt: `[crm:<id>|BEREIT]`. Ohne ihn sehen „das CRM ist weitergerückt" und „jemand hat hier
geschoben" identisch aus.

## Was hinüberwandert

Titel, Spalte, `wartetAuf`, `abnahme`, `agent`. Sonst nichts.

Nicht hinüber wandern Ansprechpartner, Telefonnummern, Mailadressen und `blockiertDurch` — in
letzterem stehen mündliche Zusagen im Wortlaut, also Aussagen von Personen. Trello gehört
Atlassian, jede Karte ist eine Übermittlung ins Drittland (CLAUDE.md §2.7).

Der Betriebsname steht auf den Karten, weil das Board sonst unlesbar wäre. Bei
Einzelunternehmen ist der Name selbst personenbezogen („Friseur Müller"); dafür gibt es
`TRELLO_ANONYM=1` in der `.env` — dann steht der Projektordner-Slug dort.

## Bedienung

```bash
node spiegel.mjs --init          # einmalig: Board anlegen, TRELLO_BOARD in .env schreiben
node spiegel.mjs --dry           # Probelauf, schreibt nichts
node spiegel.mjs                 # einmal abgleichen
node spiegel.mjs --watch=300     # dauerhaft, alle 300 s
```

Für den laufenden Betrieb `--watch`. Ohne ihn wandern erledigte Aufgaben erst dann nach „Fertig",
wenn jemand daran denkt — und dann ist das Board wieder eine Ablage statt einer Anzeige.

Fällt Twenty aus (Docker gestoppt, Rechner geschlafen), bricht der Spiegel nicht ab. Er meldet den
Fehler und versucht es beim nächsten Takt erneut.

## Umgebungsvariablen

| Variable | Zweck |
|---|---|
| `TRELLO_KEY`, `TRELLO_TOKEN` | Zugang |
| `TRELLO_BOARD` | Board-ID, wird von `--init` eingetragen |
| `TRELLO_ANONYM` | `1` = Projektordner statt Betriebsname |
| `TWENTY_API_KEY`, `TWENTY_URL` | Quelle |

## Grenzen

- Karten, die jemand selbst auf dem Board anlegt, haben keine Marke und werden beim nächsten Lauf
  archiviert. Das Board ist kein Notizblock.
- Spalten dürfen nicht umbenannt werden — der Abgleich bricht dann mit Hinweis ab, statt zu raten.
- 200 Aufgaben und 100 Betriebe pro Lauf. Darüber hinaus fehlt die Seitenweiterschaltung.
