# Research 01 · Auswertung des Lead-Exports

**Quelle:** `Clienți potențiali.csv`, 2.000 Datensätze, 21 Spalten
**Zeitraum:** 15.03.2026 – 14.08.2026 (fünf Monate) → **rund 13 Leads pro Arbeitstag**
**Erstellt:** 2026-08-14 · `co-founder-orchestrator`
**Art der Daten:** CRM-Leads, **keine** Support-Konversationen. Das ist eine andere Datenlage als
angenommen — und eine wertvollere.

> **Datenschutz zuerst:** Die Datei enthält Namen, Telefonnummern und E-Mail-Adressen von rund
> 2.000 realen Personen im Klartext, unverschlüsselt in einem Projektordner. Siehe Abschnitt am
> Ende — vor jeder weiteren Verarbeitung zu klären.

---

## 1 · Kanäle — die frühere Annahme war falsch

| Sursa | Anteil | Für T2 erreichbar? |
|---|---|---|
| **Showroom** | **35,0 %** | nein — physisches Gespräch |
| Site | 22,0 % | **ja** |
| Telefon | 14,6 % | nein — T4 |
| Mail | 12,8 % | **ja** |
| WhatsApp | 11,6 % | **ja** |
| Colaborare / Recomandare / Architekt | 2,9 % | teilweise |
| **Meta (Messenger + Meta ADS direkt)** | **1,0 %** | ja, aber unbedeutend |

**Korrektur zur Architektur-Notiz:** Facebook und Instagram sind hier **kein Nachrichtenkanal**.
`meta_ads` taucht zwar in 10,6 % der UTM-Quellen auf, aber als **Traffic-Treiber auf die Website** —
der Lead entsteht dann als „Site". Direkte Meta-Nachrichten sind vier Datensätze. Ein Meta-Adapter
mit App Review, Berechtigungsprüfung und Terminrisiko wäre für 1 % der Leads gebaut. **Streichen
aus v1.**

**Erreichbare Grundlast für einen Textassistenten: 46,4 %** (Site + Mail + WhatsApp).
Telefon 14,6 % ist T4. Showroom 35 % ist gar nicht adressierbar.

Das ist die Zahl, mit der im Angebot gerechnet wird. Eine Entlastungszusage über die gesamte
Anfragelast wäre falsch.

## 2 · Die eigentliche Last ist nicht FAQ, sondern Qualifizierung

| Status | Anzahl | Anteil |
|---|---|---|
| IRELEVANT | 449 | 22,4 % |
| BUGET | 332 | 16,6 % |
| NU A RĂSPUNS | 307 | 15,3 % |
| PRODUS NEPOTRIVIT | 139 | 7,0 % |
| A REFUZAT | 139 | 7,0 % |
| Stand BY | 125 | 6,2 % |
| **Clienți (gewonnen)** | **118** | **5,9 %** |
| CONCURENȚA | 107 | 5,3 % |
| DESIGNER | 106 | 5,3 % |
| TIMP | 101 | 5,0 % |

**Rund 51 % der Leads scheitern aus Gründen, die vor dem ersten Verkäuferkontakt feststellbar
wären:** irrelevant, Budget passt nicht, falsches Produkt, Lieferzeit zu lang.

Dazu: **nur 33,7 % erhalten überhaupt ein Angebot** (`Ofertat ✅DA` = 674). Zwei Drittel der Arbeit
von sechs Vertriebsmitarbeitern fließt in Leads, die nie ein Angebot bekommen.

### Was daraus folgt

> **Der wertvollste KI-Baustein hier ist kein FAQ-Bot, sondern ein Qualifizierer mit
> Preisspanne.**

Ein Assistent, der vor dem Verkäufer Modell, Maße, Stoff, Stadt und Zeitrahmen abfragt und eine
**unverbindliche Preisspanne** nennt, filtert BUGET (16,6 %), PRODUS NEPOTRIVIT (7 %) und TIMP
(5 %) automatisch heraus und übergibt dem Vertrieb einen vorqualifizierten Lead mit Zusammenfassung.

**Damit fallen T1 und T2 zusammen.** Sie sind nicht zwei Projekte, sondern ein Produkt mit zwei
Gesichtern: nach außen beantwortet es Fragen, nach innen qualifiziert es. Das gehört so in das
Angebot und verändert die Reihenfolge der Teilprojekte.

## 3 · Warum so viele am Budget scheitern

**Auf der Website steht kein einziger Preis.** Der Kunde fragt an, erfährt den Preis, geht.
16,6 % der Leads sind genau dieser Vorgang, und er kostet jedes Mal Vertriebszeit.

Das ist keine Nachlässigkeit — bei Maßanfertigung ist ein Festpreis schwierig. Aber eine
**Spanne pro Modell und Größe** ist möglich und filtert vor dem Gespräch. Die Angst, Kunden durch
Preise zu verlieren, verliert hier tatsächlich Vertriebszeit statt Kunden.

Zu prüfen mit dem Kunden: bewusste Entscheidung oder gewachsen?

## 4 · Der wiederkehrende Ablauf, wörtlich aus den Notizen

Häufigste Begriffe in `Informatii` / `Revenire 1`: **oferta (762×)**, canapea (503), Belle (481),
showroom (449), trimis (403), dorește (326), extensibil (202+119), dimensiunile (159), preț (144),
designer (118).

Das Muster, das sich hunderte Male wiederholt:

1. Kunde fragt nach Preis oder Modell
2. Verkäufer **schickt den Katalog** — meist per WhatsApp
3. Kunde sucht ein Modell aus und meldet sich wieder
4. Verkäufer erstellt ein Angebot
5. Ein bis drei Nachfassrunden (`Revenire 1` 61 %, `Revenire 2` 41 %, `Revenire 3` 25 %)

**Die Schritte 1 bis 3 sind vollständig automatisierbar.** Katalog ausliefern, nach Maßen und
Modell fragen, Spanne nennen, Ergebnis strukturiert an den Vertrieb — das ist der Kern von v1.

Wiederkehrende Sachfragen aus den Notizen: Maße und Größen, ausziehbar mit Matratze für den
täglichen Gebrauch, Lieferzeit, Stoffe, Öffnungszeiten und Samstagsöffnung, Ratenzahlung,
Showroom-Besuch, Designer-Konditionen.

**Wiederkehrende Absagegründe:** Lieferzeit zu lang („termen de livrare foarte mare", „timp de
execuție prea mare") und Budget. Beides ist vorab kommunizierbar.

Auffällig: ein spürbarer Anteil Datenmüll — falsche Nummern, Verwechslungen, keine Nummer
hinterlassen. `NU A RĂSPUNS` mit 15,3 % passt dazu. Eine sofortige automatische Erstreaktion
adressiert genau diesen Block.

## 5 · Weitere Befunde

- **Showrooms:** București 44,9 %, Brașov 29,1 %, Cluj 25,2 %. Der Showroom ist der stärkste
  Einzelkanal — ein Assistent, der zuverlässig **in den Showroom einlädt** und Termine vorbereitet,
  wirkt auf die stärkste Konversionsstrecke.
- **Sechs Vertriebsmitarbeitende**, Last relativ gleich verteilt (25 / 23 / 20 / 18 / 7 / 6 %).
- **Feld `Oraș` nur zu 19 % gefüllt** — die Stadt wird selten erfasst, obwohl sie über Showroom und
  Lieferung entscheidet. Eine Frage, die der Assistent immer stellen kann.
- **`Interacțiuni` praktisch leer** (2 %) — Interaktionszählung wird nicht gepflegt.
- Website: kein Preis, kein Warenkorb, kein Konfigurator. Katalog als PDF gegen Formular.
  Produktionszeit „ab 30 Tagen", 36 Monate Garantie, 0 % Finanzierung über 12 Raten, Outlet bis
  −30 %. Rumänisch, WhatsApp-Button vorhanden, Rechtstexte vorhanden (Termeni, Confidențialitate,
  Cookies).

## 6 · Widerspruch zur Marktannahme — zu klären

Die Website nennt **vier Showrooms: Brașov, București, Cluj-Napoca und Ramsgate (UK)**.

Die Angabe „verkauft ausschließlich in Rumänien" ist damit nicht vollständig. Falls in
Großbritannien an Verbraucher verkauft wird, gilt dort seit dem Brexit **eigenes
Verbraucherrecht**, unabhängig vom EU-Recht — anderes Widerrufsregime, andere Pflichtangaben,
andere Aufsicht.

**Vor dem Angebot zu klären.** Wenn ja, muss der Assistent britische Anfragen erkennen und
übergeben, statt rumänische Bedingungen zu nennen. Das ist kein Randfall, sondern ein
Haftungsthema.

## 7 · Datenschutz — vor jeder weiteren Verarbeitung

Der Export enthält im Klartext: Namen, Telefonnummern, E-Mail-Adressen, Stadt, Gesprächsnotizen
mit persönlichen Umständen („Haus im Bau", „braucht es bis Monatsende"), zugeordnete
Mitarbeitende. DSGVO gilt in Rumänien vollumfänglich.

Erforderlich, bevor irgendetwas davon in ein KI-Werkzeug geht:

1. **Rechtsgrundlage** für die Zweckänderung — die Daten wurden zur Angebotserstellung erhoben,
   nicht zum Training oder zur Analyse durch einen Dienstleister
2. **Auftragsverarbeitungsvertrag** zwischen Kunde und Agentur, bevor wir weiterarbeiten
3. **Pseudonymisierung**: Namen, Telefonnummern, E-Mail-Adressen entfernen. Für die Themenanalyse
   ist die Identität ohne Wert
4. **Kein Rohexport** in ein Werkzeug ohne AVV — auch nicht „nur zum Anschauen"
5. **Nicht im Repository ablegen.** Die Datei gehört in einen zugriffsbeschränkten Ablageort mit
   Löschdatum
6. Auch die Mitarbeiternamen in `Desemnat` sind personenbezogene Daten

Diese Auswertung wurde ausschließlich aggregiert erstellt; einzelne Personen wurden nicht
ausgewertet und keine Kontaktdaten übernommen.

---

## Empfehlung für den Angebotsumfang v1

1. **Ein Produkt statt zwei** — Qualifizierer mit Preisspanne, der zugleich Standardfragen
   beantwortet. T1 und T2 zusammenlegen
2. **Drei Kanäle:** Website, WhatsApp, E-Mail — 46,4 % der Leads. Meta streichen, Telefon ist T4
3. **Einheitlicher Übergabe-Posteingang**, Voraussetzung statt Zusatz
4. **Grundlastmessung vor dem Start**, sonst ist der Nutzen später nicht belegbar
5. **Preisspannen je Modell** gemeinsam mit dem Kunden erarbeiten — der größte einzelne Hebel
6. **Feste geprüfte Antworten** für Widerruf, Lieferzeit, Anzahlung, Garantie; alles andere Übergabe
7. Erst wenn das läuft: Bestellstatus (Stufe 2) und Sprachassistent (T4)
