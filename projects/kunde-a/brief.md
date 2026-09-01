# Brief: Sofa Belle

Eingang: 2026-08-14 · Quelle: **Flow A** (Founder-Kontakt) · Bearbeiter: `client-onboarding`
Stand: **Entwurf** — die blockierenden Angaben unten fehlen noch. Kein Angebot vor deren Klärung.

## Kurzfassung

Sofa Belle ist ein rumänischer Hersteller individuell gefertigter Polstermöbel mit Showrooms in
București, Brașov, Cluj-Napoca und Ramsgate (UK). Der Vertrieb bearbeitet rund 13 Anfragen pro
Arbeitstag über fünf Kanäle; sechs Personen wenden dabei den größten Teil ihrer Zeit für Anfragen
auf, die nie zu einem Angebot führen — nur 33,7 % werden überhaupt ofertat, 5,9 % werden Kunde.
Gewünscht ist ein KI-Assistent, der die Verkäufer entlastet. Die Auswertung von 2.000 Leads zeigt,
dass die Last nicht bei wiederkehrenden Sachfragen liegt, sondern bei der **Qualifizierung** —
rund die Hälfte der Anfragen scheitert an Budget, Produktpassung, Lieferzeit oder Relevanz, und
zwar an Kriterien, die vor dem ersten Verkäuferkontakt feststellbar wären.

## Kunde

| | |
|---|---|
| Marke | Sofa Belle, `sofabelle.ro` |
| Firmierung, Rechtsform, Register | **offen** |
| Branche | Herstellung von Polstermöbeln nach Maß (Sofas, Ecksofas, Betten, Matratzen, Sessel, Outdoor) |
| Standorte | Showrooms București, Brașov, Cluj-Napoca, **Ramsgate (UK)**; Produktion in Rumänien |
| Größe | 6 Vertriebsmitarbeitende namentlich in den Daten; Gesamtzahl **offen** |
| Inhaber | **Maxim Ciornii** — entscheidet über Angebot, Unterschrift, Zahlung, Scope · Korrespondenz **Russisch** |
| Vertriebsleiter | **Iordache Razvan** — täglicher Kontakt, fachliche Fragen, **Aufbau der Wissensbasis** · **Rumänisch** |

**Anforderungsgeber ≠ Zahler.** Scope-Änderungen gehen schriftlich an den Inhaber, nie nur an den
Vertriebsleiter.

## Sprachen

| | |
|---|---|
| Korrespondenz Inhaber | Russisch (direkt über den Founder) |
| Korrespondenz Vertriebsleiter | Rumänisch; Englisch möglich. Entscheidung Freelancer vs. Englisch **offen** |
| Quellsprache der Inhalte | Rumänisch |
| Ausgelieferte Sprachen (v1) | Rumänisch |
| Märkte, rechtlich relevant | Rumänien; **UK zu klären** |
| Prüfer Fakten | Vertriebsleiter |
| Prüfer Sprache Rumänisch | **offen — ohne benannten Prüfer wird nicht ausgeliefert** |

## Projekt

**Typ:** KI-Umsetzung (CLAUDE.md §4). Vier Teilprojekte gewünscht: T1 Angebots-Assistent,
T2 Chat-Assistent, T3 Shopify-Relaunch, T4 Sprachassistent.

**Ziel des Kunden:** Entlastung des Vertriebs.

**Erfolgskriterium:** noch nicht mit Zahl hinterlegt. Vorschlag, mit dem Kunden zu vereinbaren —
*Anteil der Anfragen, die ohne Verkäufer abschließend beantwortet oder als nicht passend
aussortiert werden.* Voraussetzung: Grundlastmessung vor dem Start.

**Wunschtermin:** offen. **Budgetrahmen:** offen.

## Ausgangslage — recherchiert, nicht erfragt

Quellen: `Clienți potențiali.csv` (2.000 Leads, 15.03.–14.08.2026), `sofabelle.ro`,
Founder-Handoff. Vollständige Auswertung: `research/01-lead-analyse.md`.

**Anfragevolumen:** ~13 pro Arbeitstag, 2.000 in fünf Monaten.

**Kanäle:** Showroom 35,0 % · Site 22,0 % · Telefon 14,6 % · Mail 12,8 % · WhatsApp 11,6 % ·
Meta direkt 1,0 %.
→ **Für einen Textassistenten erreichbar: 46,4 %.** Showroom ist physisch, Telefon ist T4.
→ Meta ist **kein** Nachrichtenkanal; Meta-Anzeigen (10,6 % der UTM) treiben Traffic auf die Site.

**Trichter:** ofertat 33,7 % · Kunde 5,9 %. Absagegründe: IRELEVANT 22,4 %, BUGET 16,6 %,
NU A RĂSPUNS 15,3 %, PRODUS NEPOTRIVIT 7,0 %, A REFUZAT 7,0 %, CONCURENȚA 5,3 %,
DESIGNER 5,3 %, TIMP 5,0 %.

**Wiederkehrender Ablauf** (häufigstes Wort in den Notizen: *oferta*, 762×): Kunde fragt nach
Preis oder Modell → Verkäufer schickt den Katalog, meist per WhatsApp → Kunde wählt ein Modell →
Angebot → ein bis drei Nachfassrunden (61 % / 41 % / 25 %).

**Website:** WordPress mit Elementor, rumänisch. **Kein Preis, kein Warenkorb, kein
Konfigurator.** Katalog 2026 als PDF gegen Formular. WhatsApp-Button. Produktionszeit „ab 30
Tagen", 36 Monate Garantie, 0 % Finanzierung über 12 Raten, Outlet bis −30 %. Rechtstexte
vorhanden: Termeni, Confidențialitate, Cookies.

**Modelle aus Daten und Kampagnen:** Imperial, Belle, Nocturne, Paturi tapitate, Fotoliu Deisy.
Häufig nachgefragt: ausziehbare Sofas mit Matratze für den täglichen Gebrauch, Ecksofas in
konkreten Maßen (300×170, 300×200).

**Marketing:** Meta ads dominierend, Google gering, TikTok vernachlässigbar. Kampagnenthemen:
Rabatte, Designer-Kooperation, Showroom-Besuch. Teilweise KI-generierte Motive.

**Datenqualität:** `Oraș` nur zu 19 % gefüllt, `Interacțiuni` faktisch ungenutzt. Spürbarer Anteil
Fehleinträge und falscher Nummern — passt zu NU A RĂSPUNS 15,3 %.

## Zentrale Feststellung

> **T1 und T2 sind ein Produkt, nicht zwei.**

Ein Assistent, der vor dem Verkäufer Modell, Maße, Stoff, Stadt und Zeitrahmen abfragt und eine
**unverbindliche Preisspanne** nennt, beantwortet nach außen die Standardfragen und filtert nach
innen BUGET (16,6 %), PRODUS NEPOTRIVIT (7 %) und TIMP (5 %) heraus. Der Vertrieb bekommt einen
vorqualifizierten Lead mit Zusammenfassung statt eines Rohkontakts.

Der strukturelle Grund für die Budget-Absagen ist, dass **auf der Website kein Preis steht**. Das
ist eine kaufmännische Entscheidung des Kunden, keine technische — aber sie ist der größte
einzelne Hebel des Projekts und gehört als solche ins Gespräch.

## Scope-Vorschlag v1

**Enthalten:**
- Qualifizierer mit Preisspanne, zugleich Beantwortung der Standardfragen
- Drei Kanäle: Website-Widget, WhatsApp, E-Mail
- Wissensbasis aus echten Anfragen, aufgebaut mit dem Vertriebsleiter
- Feste, geprüfte Antworten für Widerruf, Lieferzeit, Anzahlung, Garantie
- Übergabe an den Menschen mit Verlauf, in **einen** gemeinsamen Posteingang
- Auswertungsset auf Rumänisch inkl. Verweigerungs- und Übergabefällen
- Grundlastmessung vor dem Start
- KI-Transparenzhinweis nach EU-KI-VO Art. 50

**Nicht enthalten (im Angebot ausdrücklich abgrenzen):**
- Telefon — das ist T4
- Showroom-Gespräche, 35 % der Anfragen, technisch nicht adressierbar
- Meta Messenger und Instagram — 1 % der Anfragen, Aufwand steht in keinem Verhältnis
- Bestellstatus-Auskunft — Stufe 2, abhängig davon, ob die Daten überhaupt existieren
- Rechtstexte, Rechtsberatung, Rechtskonformität nach rumänischem Recht
- Verbindliche Preise oder Lieferzusagen durch den Assistenten
- Shopify-Relaunch (T3), Sprachassistent (T4)

**Architekturvorgabe:** kanalunabhängiger Kern plus Adapter, außerhalb von WordPress. Der
Assistent überlebt damit den geplanten Shopify-Wechsel und kann **vor** dem Relaunch live gehen.

## Mitwirkung des Kunden

| Was | Wer | Bis wann | Aufwand |
|---|---|---|---|
| Export echter Konversationen (Mail, WhatsApp) | Vertriebsleiter | vor Angebot | gering |
| Aufbau und Freigabe der Wissensbasis | Vertriebsleiter | Bauphase | **erheblich, laufend** |
| Preisspannen je Modell festlegen | Inhaber + Vertriebsleiter | vor Bauphase | mittel |
| Faktenprüfung aller Antworten | Vertriebsleiter | vor Livegang | mittel |
| Rechtstexte aus rumänischer Quelle | Kunde | vor Livegang | — |
| Empfänger und Zeiten für die Übergabe benennen | Kunde | Bauphase | gering |
| **Benannte Vertretung für den Vertriebsleiter** | Inhaber | Projektstart | — |

Ohne Zeitbudget und Vertretung für den Vertriebsleiter steht das Projekt an einer Person still.

## Rechtliche Hinweise

- **Markt Rumänien.** Unser Rechts-Gate ist auf deutsches Recht gebaut. EU-Grundlagen übertragen
  sich (DSGVO, KI-VO, RL 2011/83/EU, ePrivacy, RL 2019/882), nationale Umsetzung und Aufsicht
  (ANPC) nicht. **Wir sichern keine Konformität mit rumänischem Recht zu** — Einschränkung gehört
  wörtlich ins Angebot. Founder hat die Ausnahme zu CLAUDE.md §1 am 2026-08-14 genehmigt.
- **Ramsgate (UK).** Falls dort an Verbraucher verkauft wird, gilt eigenes britisches Recht. Der
  Assistent müsste britische Anfragen erkennen und übergeben. **Blockierend zu klären.**
- **Widerrufsrecht bei Maßanfertigung.** Ausnahme nach RL 2011/83/EU Art. 16 lit. c greift, aber
  eng — sie hängt davon ab, wie individuell tatsächlich gefertigt wird. Feste geprüfte Antwort
  statt freier Formulierung durch das Modell.
- **KI-Transparenz** nach Art. 50, anwendbar seit 02.08.2026. Als Testfall im Auswertungsset.
- **Personenbezogene Daten.** Der Lead-Export enthält 2.000 Klarnamen, Telefonnummern und
  E-Mail-Adressen sowie Mitarbeiternamen. AVV, Rechtsgrundlage für die Zweckänderung,
  Pseudonymisierung und ein Ablageort mit Löschdatum sind zu klären, bevor weitergearbeitet wird.
  Nicht im Repository ablegen.
- **WhatsApp** ist ein US-Verarbeiter; Datenfluss dokumentieren und in die Datenschutzerklärung
  des Kunden aufnehmen. Ebenso der KI-Anbieter.

## Technischer Bestand

Domain `sofabelle.ro` · WordPress mit Elementor · Hosting **offen** · Zugänge **offen** ·
Warenwirtschaft und Produktionsplanung **offen** · Figma-Datei „Website" vorhanden

### MEFI — das Zielsystem ist identifiziert

**MEFI ist eine rumänische Plattform: `mefi.ro`** — CRM, ERP, Projektmanagement und Business
Intelligence in einem, mit über 40 Modulen.

Das erste CRM-Modul heißt **„Clienți potențiali"** — exakt der Dateiname des Lead-Exports.
**Der Export stammt also aus MEFI**, es gibt keine parallel geführte Tabelle.

Relevante Module: Clienți potențiali · Clienți finali · **Oferte** · Contracte · Asistență ·
Programări · Facturi · Gestiune Stocuri · Comenzi furnizori · Pâlnie vânzări.
Integrationen laut Anbieter: **WhatsApp Web**, SMS (SMSO), **3CX** (Telefonanlage), Zoom.

**Was das für den Zuschnitt bedeutet:**

- **Es gibt schon ein System für Leads, Angebote und Aufträge.** Der Assistent baut keine
  Parallelwelt, sondern liefert dort ein. Das ist eine deutlich bessere Ausgangslage als
  angenommen.
- **Angebote entstehen bereits in MEFI** (Modul Oferte). Der Qualifizierer erzeugt also einen
  qualifizierten Lead plus Preisspanne in MEFI, das eigentliche Angebot bleibt dort.
- **Der gemeinsame Übergabe-Posteingang ist möglicherweise schon vorhanden** — WhatsApp Web und
  Asistență sind Module. Zu prüfen ist, ob es eine echte Anbindung ist oder nur ein Tab mit
  WhatsApp Web; davon hängt ab, ob wir diese Position noch anbieten müssen.
- **3CX** ist die Telefonanlage — das ist die Antwort für T4 (Sprachassistent), früher als erwartet.
- **Bestellstatus** könnte im ERP-Teil liegen (Comenzi, Gestiune Stocuri). Zu prüfen.

**Risiko:** MEFI veröffentlicht **keine öffentliche API-Dokumentation**. Ein Zugangsschlüssel
liegt vor (Präfix `lrd_`, 76 Zeichen, in `.env` als `MEFI_API`), aber ohne Endpunkt und ohne
Beschreibung ist er nicht nutzbar. Damit hängt der Kern der Lösung an der Mitwirkung eines
Drittanbieters. Das gehört als benanntes Risiko ins Angebot, nicht in eine Fußnote.

Quelle: `mefi.ro`, sowie Trello-Karten „Сделать изменение попаданий лидов в MEFI" und
„Лиды в MEFI связать с продавцами" (beide erledigt).

### Übergaberegel steht bereits fest

Aus derselben Trello-Karte, Zuordnung nach Stadt:

| Stadt | Zuständig |
|---|---|
| Brașov | zwei Verkäuferinnen |
| București | zwei Verkäufer |
| Cluj | zwei Verkäuferinnen |
| andere Stadt | eine Verkäuferin |

**Damit ist die Übergabelogik des Assistenten vorgegeben** und muss nicht erfunden werden: Stadt
erfragen → nach dieser Regel zuordnen. Erklärt zugleich, warum das Feld `Oraș` wichtig ist,
obwohl es nur zu 19 % gefüllt ist — es steuert die Zuteilung. Ein Assistent, der die Stadt immer
erfragt, verbessert die Zuteilung unmittelbar.

*(Namen stehen auf der Trello-Karte des Kunden, nicht in diesem Brief — sieben Personen, eine mehr
als im Lead-Export.)*

## Fakten und Hypothesen

| Aussage | Einordnung | Beleg / zu prüfen |
|---|---|---|
| ~13 Anfragen pro Arbeitstag | **Fakt** | Export, 2.000 in 5 Monaten |
| Kanalverteilung, Trichter, Absagegründe | **Fakt** | Export, Spalten `Sursa`, `Status`, `Ofertat` |
| Kein Preis auf der Website | **Fakt** | Website |
| 6 Personen im Vertrieb | **Fakt** | Export, Spalte `Desemnat` |
| Verkauf ausschließlich in Rumänien | **widerlegt** | UK-Showroom Ramsgate; auf dem Trello-Board läuft aktive Arbeit „Informationen und Fotos UK". |
| Es besteht bereits eine Arbeitsbeziehung | **Fakt** | Trello-Board „Sofabelle" mit sechs erledigten Karten — kein Neukunde, sondern Bestandskunde mit neuem Vorhaben |
| Leads laufen durch ein System (MEFI) | **Fakt** | Trello-Karten; API und Umfang offen |
| Übergabe nach Stadt an feste Verkäufer | **Fakt** | Zuordnungsregel auf der Trello-Karte |
| Die Hälfte der Absagen ist vorab filterbar | **Hypothese** | plausibel aus Statusverteilung, im Pilot zu messen |
| Assistent senkt die Last spürbar | **Hypothese** | nur für 46,4 % der Anfragen überhaupt möglich |
| Preisspannen sind kommunizierbar | **Hypothese** | kaufmännische Entscheidung des Inhabers |
| Bestellstatus ist digital verfügbar | **Hypothese** | unbekannt, entscheidet über Stufe 2 |
| Wissensbasis entsteht beim Vertriebsleiter | **Hypothese** | Zeitbudget nicht zugesagt |

## Riskanteste Annahme

**Dass der Kunde bereit ist, Preisspannen zu nennen.**

Ohne sie bleibt der Assistent ein FAQ-Bot und adressiert die größte Absagegruppe (BUGET, 16,6 %)
nicht. Die erwartete Entlastung fällt dann auf einen Bruchteil zusammen, und das Projekt wird als
Enttäuschung erlebt, obwohl es technisch funktioniert.

**Abbruchkriterium:** Wenn der Inhaber keine Preisspannen freigibt und keine Bestellstatus-Daten
verfügbar sind, bleibt für v1 zu wenig übrig. Dann besser ein kleinerer, ehrlich bepreister
Umfang — oder das Projekt nicht in dieser Form.

## Fehlende Angaben — blockierend für das Angebot

- [ ] Firmierung, Rechtsform, Registerangaben
- [ ] Verkauf an Verbraucher, an Gewerbe (Designer 5,3 % deutet auf beides) oder beides — Anteile
- [ ] **UK: wird in Ramsgate an Verbraucher verkauft?**
- [ ] Ist die Fertigung wirklich individuell oder Kombination aus Standardoptionen?
- [ ] Sind Preisspannen je Modell kommunizierbar? (Inhaber-Entscheidung)
- [ ] Existiert ein digitaler Produktionsstatus, und wer pflegt ihn?
- [ ] Übergabeziel und vorhandenes Posteingangs-Werkzeug
- [ ] Arbeitszeiten und Zeitaufwand je Anfrage (Grundlast)
- [ ] Herkunft der Rechtstexte
- [ ] Export echter Konversationen, nicht nur Leadkarten
- [ ] Budgetrahmen und Wunschtermin
- [ ] Prüfer für Rumänisch, mit Vertretung

## Risiken

| Risiko | Auswirkung | Umgang |
|---|---|---|
| Anforderungsgeber ≠ Zahler | Umfang wächst ohne Freigabe | Scope-Änderungen schriftlich an den Inhaber |
| Wissensbasis hängt an einer Person | Projektstillstand bei Urlaub oder Kündigung | Vertretung benennen, Zeitbudget in den Vertrag |
| 35 % Showroom nicht adressierbar | Erwartung wird enttäuscht | 46,4 % im Angebot ausdrücklich benennen |
| Inhaber gibt keine Preisspannen frei | größter Hebel entfällt | früh entscheiden, sonst Umfang reduzieren |
| Rumänisches Recht außerhalb unserer Zusicherung | Haftung | Einschränkung wörtlich ins Angebot |
| UK-Verkauf ungeklärt | falsche Auskünfte an britische Verbraucher | vor Angebot klären, sonst Übergabe erzwingen |
| Personenbezogene Daten im Export | DSGVO-Verstoß | AVV und Pseudonymisierung vor Weiterarbeit |
| Kein rumänischer Sprachprüfer | Auslieferung blockiert | vor Bauphase benennen |
| Geplanter Shopify-Wechsel | Assistent müsste neu gebaut werden | kanalunabhängiger Kern außerhalb von WordPress |
| Assistent nennt verbindliche Zusagen | Haftung des Kunden | feste Antworten, Verweigerungsfälle im Testset |

## Empfehlung an den Founder

1. **T1 und T2 zusammenlegen** und als ein Produkt anbieten. Getrennt zu verkaufen wäre teurer für
   den Kunden und schlechter für das Ergebnis.
2. **Reihenfolge ändern:** dieses zusammengelegte Produkt zuerst, T3 Shopify danach, T4 zuletzt.
3. **Vor dem Angebot** die fünf entscheidenden Punkte klären: Preisspannen, Bestellstatus, UK,
   Individualität der Fertigung, Konversations-Export. Der Fragebogen dafür liegt fertig auf
   Rumänisch (`t2-chat-assistent/02-chestionar-ro.md`, braucht Gegenlesung).
4. **Paket:** KI-Umsetzung nach CLAUDE.md §4, „ab 3.000 €". Der hier beschriebene Umfang liegt
   deutlich darüber — Aufwandstreiber sind WhatsApp-Anbindung mit laufenden Kosten, der
   gemeinsame Posteingang, die Preislogik und das rumänische Auswertungsset.
   **Keine Zahl vor der Klärung der Punkte oben** — jede Zahl im Angebot muss auf `research/`
   zurückführbar sein.
5. **Wartungsvertrag von Anfang an mitanbieten.** Ein KI-Assistent ohne laufende Kontrolle der
   Antwortqualität ist ein Risiko für den Kunden und für uns.
