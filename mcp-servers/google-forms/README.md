# MCP-Server: Google Forms

Erlaubt es den Agenten, Google-Formulare **selbst** anzulegen, Fragen einzufügen, zu
veröffentlichen und Antworten zu lesen. Der claude.ai-Drive-Connector kann das nicht — er kennt
nur Dateioperationen.

Registriert in `.mcp.json` im Projektwurzelverzeichnis.

## Einrichtung — einmalig

### 1 · Google Cloud

1. `console.cloud.google.com` → **neues Projekt** anlegen, z. B. „agency-automation".
2. **APIs & Dienste → Bibliothek** → **Google Forms API** suchen → *Aktivieren*.
   Ebenso **Google Drive API** aktivieren (wird für `forms_share` gebraucht).
3. **APIs & Dienste → OAuth-Zustimmungsbildschirm**
   - Nutzertyp **Extern**
   - App-Name, Support-E-Mail, Entwickler-E-Mail ausfüllen
   - Bereiche müssen hier nicht eingetragen werden
   - Unter **Testnutzer** die E-Mail-Adresse hinzufügen, der die Formulare gehören sollen
     (`granici.design@gmail.com`). Ohne diesen Eintrag scheitert die Anmeldung.
   - Die App bleibt im Status „Testing". Das genügt; Refresh-Tokens laufen dann allerdings nach
     sieben Tagen ab. Wer das nicht will, veröffentlicht die App — dafür ist bei diesen Bereichen
     keine Google-Überprüfung nötig, weil sie nur eigene Daten betreffen.
4. **Anmeldedaten → Anmeldedaten erstellen → OAuth-Client-ID**
   - Anwendungstyp: **Desktop-App**
   - Client-ID und Client-Secret kopieren

### 2 · Zugangsdaten ablegen

In die `.env` im Projektwurzelverzeichnis (steht in `.gitignore`):

```
GOOGLE_OAUTH_CLIENT_ID=...
GOOGLE_OAUTH_CLIENT_SECRET=...
```

### 3 · Einmal anmelden

```bash
cd mcp-servers/google-forms
npm install
npm run auth
```

Öffnet eine URL. **Mit dem Konto anmelden, dem die Formulare gehören sollen.** Der Warnhinweis
„Diese App ist nicht überprüft" ist bei einer Test-App normal → *Erweitert* → *Weiter zu …*.

Das Refresh-Token landet in `~/.config/mcp-google-forms/token.json`, nicht im Repository.

### 4 · Sitzung neu starten

Claude Code liest `.mcp.json` beim Start. Danach `/mcp` — `google-forms` muss verbunden sein.

## Werkzeuge

| Werkzeug | Zweck |
|---|---|
| `forms_create` | leeres Formular anlegen (nur Titel möglich, API-Beschränkung) |
| `forms_set_info` | Titel und Beschreibung setzen |
| `forms_add_items` | Fragen aus einer flachen Liste anfügen — der Normalfall |
| `forms_get` | Struktur inkl. Item-IDs lesen |
| `forms_batch_update` | roher batchUpdate für alles Übrige |
| `forms_publish` | veröffentlichen und Antworten freischalten |
| `forms_list_responses` | Antworten lesen |
| `forms_share` | Datei an eine Adresse freigeben |

### Fragetypen für `forms_add_items`

`SECTION` (Seitenumbruch) · `TEXT` (kurz) · `PARAGRAPH` (lang) · `CHOICE` · `CHECKBOX` ·
`DROPDOWN` · `SCALE` · `DATE` · `INFO` (Textblock ohne Antwort)

```json
{ "type": "CHOICE", "title": "…", "description": "Hilfetext",
  "required": true, "options": ["Da", "Nu"], "other": true }
```

## Zwei Fallstricke

**Formulare sind nach der Erstellung nicht veröffentlicht.** Seit dem 30.06.2026 legt die API sie
im Zustand „unpublished" an — der Antwortlink funktioniert erst nach `forms_publish`. Wird das
vergessen, bekommt der Kunde eine tote Adresse.

**`forms_create` nimmt nur den Titel.** Beschreibung und Fragen gehen ausschließlich über
batchUpdate. Der Ablauf ist daher immer: `forms_create` → `forms_set_info` → `forms_add_items` →
`forms_publish` → `forms_share`.

## Datenschutz

Formulare und Antworten liegen bei Google, einem US-Verarbeiter. Für Antworten mit
personenbezogenen Daten gilt dasselbe wie überall sonst (CLAUDE.md §2.4): Rechtsgrundlage, AVV,
Löschfrist. **Keine Dateiupload-Felder für Kundendaten** — dafür einen Kanal wählen, über den wir
Kontrolle haben.

`forms_share` gibt bewusst nur an benannte Adressen frei, nicht „jeder mit dem Link".
