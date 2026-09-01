# T2 · KI-Assistent für Kundenanfragen — Vorüberlegungen

**Status:** Vorüberlegungen vor dem Brief. **Alles hier ist Hypothese**, nicht Befund. Wird im
Zustand 2 · RESEARCH geprüft und dann nach `research.md` überführt.
**Angelegt:** 2026-08-14 · `co-founder-orchestrator`

## Auftrag, wie er bisher verstanden ist

Ein Assistent beantwortet Standardfragen von Kundinnen und Kunden, damit die Verkäufer entlastet
werden. Heutiger Stand: Shop auf WordPress mit Elementor.

## Der Punkt, der das Angebot verändert

Der Kunde will **denselben Shop später auf Shopify neu bauen** (T3). Ein Assistent, der als
WordPress-Plugin oder als Elementor-Widget gebaut wird, ist bei der Migration wertlos und muss
bezahlt neu gebaut werden.

**Empfehlung:** Assistent als eigenständiger Dienst plus eingebettetes Widget — ein Skript-Schnipsel,
den man in WordPress genauso einbindet wie später in ein Shopify-Theme. Die Wissensbasis, die
Prompts, die Übergabelogik und das Auswertungsset liegen außerhalb des Shops und überleben den
Wechsel.

Kostet jetzt etwas mehr als die schnellste Lösung, spart aber den zweiten Aufbau. Das gehört so in
den Angebotstext, damit der Kunde die Entscheidung bewusst trifft.

Nebeneffekt: Der Assistent kann **vor** dem Relaunch live gehen und Nutzen bringen, während T3 noch
läuft. Er ist damit der richtige erste Baustein, unabhängig vom Migrationsplan.

## Was vor dem Angebot geklärt sein muss

### Die Wissensbasis

„Standardfragen" ist keine Anforderung. Nötig ist ein Export echter Anfragen der letzten Wochen —
E-Mails, Formularnachrichten, Chatverläufe. Ziel: 20 bis 50 Stück. Daraus entsteht beides, die
Wissensbasis **und** das Auswertungsset (`.claude/standards/TESTING.md`).

Ohne diesen Export ist jede Aufwandsschätzung geraten. Das ist die blockierende Frage in diesem
Teilprojekt.

### Die Messgröße — vor dem Start, nicht danach

Das Ziel ist Entlastung der Verkäufer. Messbar nur, wenn der Ausgangswert **vor** dem Livegang
bekannt ist: wie viele Anfragen pro Tag, wie viel Zeit pro Anfrage, welche Themen.

Wer das versäumt, kann hinterher nicht belegen, dass die Investition sich gelohnt hat — und
verliert die Verlängerung des Wartungsvertrags. Baseline-Messung gehört in den Scope.

### Reichweite der Antworten

Zwei völlig verschiedene Projekte, die gern in einem Satz zusammengeworfen werden:

1. **Nur allgemeines Wissen** — Versand, Rückgabe, Zahlarten, Öffnungszeiten, Produktfragen.
   Kein Zugriff auf Bestelldaten, kein Login, deutlich einfacher und schneller live.
2. **Mit Bestelldaten** — „Wo ist meine Lieferung?". Braucht Authentifizierung, Zugriff auf den
   Shop, ein Löschkonzept und eine deutlich strengere Datenschutzprüfung.

Empfehlung: mit 1 starten, 2 als zweite Stufe anbieten. Der Nutzen von 1 ist sofort da, das Risiko
klein.

### Übergabe an den Menschen

Der Assistent muss aufgeben können, und die Übergabe muss tatsächlich bei jemandem ankommen — mit
Verlauf, in der Sprache des Kunden, innerhalb der Geschäftszeiten mit klarer Erwartung außerhalb.
Wird regelmäßig vergessen und ist der häufigste Grund, warum solche Projekte als Ärgernis enden.

### Sprachen

Neu und noch offen (`.claude/standards/LANGUAGE.md`). Zu klären: in welchen Sprachen verkauft der
Kunde, welche Sprachen schalten wir frei, **wer liest jede davon gegen**. Ein Assistent, der in
einer Sprache antwortet, die niemand im Projekt prüfen kann, ist kein Feature, sondern ein
unkontrolliertes Risiko. Außerhalb der freigeschalteten Sprachen: höfliche Übergabe an einen
Menschen, in der Sprache der Anfrage.

### Recht

- **Transparenzpflicht** nach der EU-KI-Verordnung, anwendbar seit 02.08.2026: erkennbar, dass es
  eine KI ist. Als Testfall im Auswertungsset, nicht nur als Satz im Prompt.
  → Aktuellen Stand der Regelung vor dem Angebot bestätigen lassen (`german-legal-compliance`).
- **Keine verbindlichen Zusagen** durch den Assistenten: keine Preisnachlässe, keine Lieferzusagen,
  keine Rechtsauskünfte. Als Verweigerungsfälle testen.
- **Datenschutz:** Welcher Anbieter, wo verarbeitet, AVV vorhanden, EU-Option (CLAUDE.md §2.4).
  Chatverläufe sind personenbezogene Daten — Zweck, Speicherdauer, Löschroutine festlegen.
- **Consent:** Das Widget lädt erst nach Einwilligung, wenn es nicht-essenzielle Daten verarbeitet.
- **Barrierefreiheit:** Das Chatfenster ist Teil des Shops. Tastaturbedienbar, Fokus sichtbar,
  Screenreader-tauglich (`accessibility-auditor`).

### Technische Randbedingungen WordPress/Elementor

- Einbindung als Skript, kein Eingriff ins Theme — überlebt Theme- und Elementor-Updates
- Caching-Plugins können eingebettete Widgets stören; früh auf dem echten System testen
- Elementor ist schwer; das Widget darf das Lighthouse-Ziel aus §6 nicht weiter drücken
- Kein Zugriff auf die Live-Umgebung ohne Staging und Backup (`.claude/standards/STACK.md`)

---

## Maßanfertigung — der fachlich heikelste Punkt dieses Projekts

Ergänzt am 2026-08-14 nach dem Handoff. Der Kunde fertigt **Polstermöbel nach Maß**. Das ist keine
Randnotiz, das bestimmt den Kern der Wissensbasis.

### Widerrufsrecht

Für Waren, die nach Kundenspezifikation angefertigt oder eindeutig auf persönliche Bedürfnisse
zugeschnitten sind, besteht **kein Widerrufsrecht** — EU-weit aus der
Verbraucherrechte-Richtlinie 2011/83/EU Art. 16 lit. c, in Deutschland §312g Abs. 2 Nr. 1 BGB, in
Rumänien über die dortige Umsetzung (Artikel vor dem Angebot durch eine rumänische Quelle
bestätigen lassen).

**Die Ausnahme ist eng, und genau hier liegt das Risiko.** Sie greift nicht automatisch, nur weil
ein Konfigurator existiert. Entscheidend ist, ob die Anfertigung wirklich individuell ist oder ob
aus vorgegebenen Standardoptionen kombiniert wird und der Händler die Ware ohne erheblichen
Nachteil weiterverkaufen könnte. Die Rechtsprechung ist streng, und Möbelhändler liegen hier
regelmäßig falsch — in beide Richtungen.

**Für den Assistenten heißt das:** „Kann ich das zurückgeben?" ist mit Sicherheit unter den zehn
häufigsten Fragen, und beide möglichen Fehler sind teuer:

- Er sagt „kein Rückgaberecht", obwohl eines besteht → unzulässig, abmahnfähig, irreführend
- Er sagt „14 Tage Rückgabe", obwohl keines besteht → der Kunde darf sich darauf berufen, das
  Unternehmen muss es einlösen

**Konsequenz:** Diese Frage ist **kein Fall für freie Formulierung durch das Modell.** Sie bekommt
je Markt eine feste, rechtlich geprüfte Antwort, die wortgetreu ausgegeben wird, plus Angebot der
Übergabe an einen Menschen. Als Pflichtfall im Auswertungsset, in jeder freigeschalteten Sprache.

### Weitere Fragen mit derselben Eigenschaft

Alles, wo eine beiläufige Antwort zur Zusage wird. Gleiche Behandlung: feste Antwort oder Übergabe,
nie freie Formulierung.

- **Lieferzeit.** Bei Maßanfertigung lang und schwankend. Eine genannte Woche ist eine Zusage.
  Der Assistent nennt Spannen und den Vorbehalt, oder er übergibt.
- **Anzahlung und Vorkasse.** Bei Maßanfertigung üblich, rechtlich sensibel in der Ausgestaltung.
- **Transportschäden und Speditionslieferung.** Was der Kunde bei Anlieferung prüfen und wie er
  einen Schaden melden muss — falsche Auskunft kostet direkt Geld.
- **Beratung zu Maßen, Stoffen, Belastbarkeit.** Eine Empfehlung, die nicht passt, ist ein
  Reklamationsgrund. Beratung gehört zum Vertriebsleiter, nicht zum Assistenten.
- **Preisauskunft.** Bei Maßanfertigung immer konfigurationsabhängig — sauber abgegrenzt zu T1.
- **Gewährleistung**, Pflegehinweise, Nachbestellung von Stoff.

Die gute Nachricht: Das Themenfeld ist klein und stabil. Ein Möbelhersteller bekommt im Kern
zehn bis fünfzehn wiederkehrende Fragen. Ein Assistent, der diese fünfzehn wirklich zuverlässig
beantwortet und alles andere sauber übergibt, entlastet den Vertrieb spürbar — und ist
ungleich sicherer als einer, der alles versucht.

### Die Wissensbasis kommt vom Vertrieb — das hat eine Nebenwirkung

Der Vertriebsleiter baut die Wissensbasis auf. Fachlich ist das ideal, er kennt die Fragen. Aber
Vertrieb formuliert optimistisch: „Lieferung in vier bis sechs Wochen", „das lässt sich problemlos
ändern", „Stoffmuster kostenlos". Sobald der Assistent das sagt, ist es eine Aussage des
Unternehmens gegenüber dem Verbraucher.

**Deshalb ein zusätzlicher Durchgang über die Wissensbasis vor dem Livegang**, der nach Zusagen
sucht und sie entweder absichert oder in Spannen mit Vorbehalt umformuliert. Das ist Aufwand und
gehört in den Angebotstext, nicht ins Kleingedruckte.

### Fünf Monate Anfragen — Datenschutz vor Nutzen

Der zugesagte Export ist eine sehr gute Grundlage, aber er besteht aus **personenbezogenen Daten**:
Namen, Adressen, Maße von Wohnungen, teils Gesundheitsangaben (orthopädische Anforderungen an
Sitzmöbel). Vor jeder Verarbeitung:

1. Rechtsgrundlage klären — Verarbeitung zu einem anderen Zweck als der ursprünglichen Anfrage
2. **Pseudonymisieren vor der Analyse**: Namen, Anschriften, Telefonnummern, Bestellnummern raus.
   Für die Themenanalyse braucht niemand die Identität
3. AVV mit dem KI-Anbieter, EU-Verarbeitung bevorzugt
4. Kein Rohexport in ein Werkzeug ohne AVV, auch nicht „nur zum Anschauen"
5. Aufbewahrung und Löschung des Exports festlegen

Das kostet einen halben bis einen Tag und ist nicht verhandelbar.

---

## Markt und Sprache: **nur Rumänien, nur Rumänisch**

Korrigiert 2026-08-14. Frühere Annahme „GUS, Europa, Amerika" war falsch. Der Kunde verkauft
ausschließlich in Rumänien.

Das vereinfacht T2 erheblich: **ein Markt, eine Rechtsordnung, eine Sprache.** Kein
Mehrsprachigkeitsaufwand, kein Marktvergleich bei Widerruf und Gewährleistung, ein Prüfer.

### Was das für die Rechtslage heißt

Unser Rechts-Gate (`german-legal-compliance`) ist auf deutsches Recht gebaut und gilt hier
**nicht**. Was sich trotzdem überträgt, weil es EU-Recht ist und in Rumänien nur anders umgesetzt
wurde:

| Thema | Grundlage | überträgt sich? |
|---|---|---|
| Datenschutz | DSGVO | ja, identisch |
| KI-Transparenzpflicht | KI-VO Art. 50 | ja, identisch |
| Widerruf, Ausnahme für Maßanfertigung | RL 2011/83/EU Art. 16 lit. c | ja, rumänische Umsetzung |
| „Zahlungspflichtig bestellen" | RL 2011/83/EU Art. 8 Abs. 2 | ja, andere Formulierung |
| Preisangaben | RL 98/6/EG | ja, andere Umsetzung |
| Cookies | ePrivacy-RL | ja, rumänische Umsetzung |
| Barrierefreiheit | RL (EU) 2019/882 | ja, rumänische Umsetzung |
| Impressumspflichten | §5 DDG | **nein** — rumänische Regelung, andere Pflichtangaben |
| Aufsicht, Streitschlichtung | — | **nein** — ANPC statt deutscher Stellen |

**Die Struktur unserer Prüfliste überträgt sich, die Fundstellen nicht.** Praktische Folge: Wir
können die Prüfliste anwenden, aber **keine Rechtskonformität für Rumänien zusichern**. Die
Rechtstexte kommen von einer rumänischen Quelle mit Haftung; wir prüfen strukturell auf
Vorhandensein und Plausibilität. Diese Einschränkung gehört wörtlich ins Angebot.

### Prüfer

Rumänisch ist die einzige Produktsprache. Damit ist ein rumänischer Gegenleser nicht optional,
sondern strukturell nötig — für Fakten **und** für Sprache. Der Vertriebsleiter deckt beides
fachlich ab; ob zusätzlich ein externer Sprachprüfer nötig ist, entscheidet sich daran, ob wir
unseren Zeitplan von einem Mitarbeiter des Kunden abhängig machen wollen. Offen.

---

## Kanäle: alle vier — das bestimmt die Architektur

Ergänzt 2026-08-14. Anfragen kommen über **Formular/E-Mail, WhatsApp, Facebook/Instagram und
Telefon**.

### Die zentrale Folgerung

**Ein Widget auf der Website entlastet den Vertrieb nicht.** Wenn der überwiegende Teil der
Anfragen über WhatsApp und Meta kommt — bei rumänischen KMU der Normalfall, bei Möbeln besonders
ausgeprägt, weil Leute unter das Foto eines Sofas schreiben — dann beantwortet ein Website-Widget
den kleineren Teil und das Ziel des Projekts wird verfehlt.

Bauweise deshalb: **kanalunabhängiger Kern plus Adapter.** Wissensbasis, Prompts, Übergabelogik,
Protokollierung und Auswertungsset liegen einmal in der Mitte; jeder Kanal ist ein austauschbarer
Anschluss. Das ist derselbe Schnitt, der den Assistenten auch den Shopify-Wechsel überleben lässt.

### Und die Falle, an der solche Projekte scheitern

> **Ein Assistent auf vier Kanälen ohne gemeinsamen Posteingang erhöht die Belastung, statt sie
> zu senken.**

Wenn die Übergabe an den Menschen in vier verschiedenen Oberflächen landet, prüft der Vertrieb
danach fünf Orte statt vier. Deshalb ist ein **einheitlicher Übergabe-Posteingang** kein Extra,
sondern Voraussetzung. Zu klären: Nutzt der Kunde bereits ein Werkzeug dafür (Meta Business Suite,
ein Helpdesk, ein CRM)? Wenn ja, bauen wir dorthin. Wenn nein, ist das eine eigene
Angebotsposition.

### Aufwand und Risiken je Kanal

| Kanal | Aufwand | Zu beachten |
|---|---|---|
| Website-Widget | gering | Einbindung als Skript, Caching-Plugins testen, Consent |
| E-Mail | gering–mittel | Zuordnung zu Vorgängen, Signatur, Zustellbarkeit |
| WhatsApp | **hoch** | WhatsApp Business API über Meta Cloud API oder BSP; Unternehmensverifizierung; eigene Rufnummer; **laufende Kosten pro Konversation**; 24-Stunden-Fenster für freie Antworten, danach nur genehmigte Vorlagen |
| Facebook / Instagram | mittel–hoch | Meta-App mit Berechtigungsprüfung (`pages_messaging`, `instagram_manage_messages`); **App Review dauert und kann scheitern** — früh beantragen, Terminrisiko |
| Telefon | — | nicht T2, sondern T4. Zählt aber in die Grundlast: wir dürfen keine Entlastung versprechen, die nur die Textkanäle betrifft |

**Terminrisiko benennen:** Meta-Verifizierung und App Review liegen nicht in unserer Hand. Sie
gehören als Abhängigkeit mit eigenem Datum in den Projektplan, nicht in eine Fußnote.

### Datenschutz

WhatsApp und Meta sind US-Verarbeiter. Nachrichteninhalte sind personenbezogene Daten. Der
Datenfluss ist zu dokumentieren, in die Datenschutzerklärung des Kunden aufzunehmen und mit den
jeweiligen Auftragsverarbeitungsbedingungen zu unterlegen (DSGVO gilt in Rumänien identisch).
Zusätzlich der KI-Anbieter selbst. Diese Kette wird einmal aufgeschrieben und dem Kunden erklärt.

### Empfehlung für v1

Nicht alle Kanäle gleichzeitig. Die Auszählung des Exports zeigt, welche zwei den größten Teil
der Last erzeugen — diese in v1, der Rest als bepreistes Inkrement. Der Kern wird von Anfang an so
gebaut, dass ein weiterer Kanal ein Anschluss ist und kein Umbau.

## Offene Fragen, die daraus folgen

Ergänzt in `../offene-fragen.md`: Anfragen-Export, Baseline-Zahlen, Reichweite 1 oder 2,
Zielsprachen und Prüfer je Sprache, Übergabeempfänger und Geschäftszeiten, KI-Anbieter mit
EU-Verarbeitung.
