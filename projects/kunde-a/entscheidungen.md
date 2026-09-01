# Entscheidungsprotokoll

Jede Entscheidung, die den Scope, den Stack, den Preis, den Termin oder ein Risiko betrifft.
Gepflegt von `co-founder-orchestrator`, ergänzt von `pm-client-lead` nach Kundengesprächen.

| Datum | Frage | Entscheidung | Entschieden von | Konsequenz |
|---|---|---|---|---|
| 2026-08-14 | Reihenfolge der vier Teilprojekte | T1 Angebots-Assistent zuerst, dann T2 Chat, T3 Shopify, T4 Sprache | Founder | T1 dient zugleich als Test des Prozesses; Shopify-Migration startet erst nach abgeschlossenem Research |

| 2026-08-14 | Wurde im Erstgespräch etwas zugesagt? | **Nein** — keine Zahl, kein Termin, keine Zusage, kein Ausschluss | Founder | Wir sind in Preis, Umfang und Zeitplan frei. Bis Research und Angebot stehen, nennt niemand eine Zahl, auch nicht mündlich |
| 2026-08-14 | Wer entscheidet, wer zahlt? | Inhaber: Angebot, Unterschrift, Zahlung, Scope. Vertriebsleiter: fachliche Abstimmung, Wissensbasis | Founder | Scope-Änderungen immer schriftlich an den Inhaber, nie nur an den Vertriebsleiter. Angebot geht an den Inhaber |
| 2026-08-14 | Korrespondenzsprachen | Inhaber Russisch (Founder direkt), Vertriebsleiter Rumänisch/Englisch | Founder | Arbeitssprache mit dem Vertriebsleiter ausdrücklich vereinbaren. Rumänisch-Gegenlesung durch den Vertriebsleiter als Mitwirkungspflicht ins Angebot |

| 2026-08-14 | Kunden außerhalb des DACH-Raums annehmen? | **Ja, wenn sie in den DACH-Raum verkaufen.** Kriterium ist der Absatzmarkt, nicht der Sitz des Kunden | Founder | CLAUDE.md §1 entsprechend geändert. DACH-Markt prüfen wir vollständig, weitere Märkte nur strukturell — Einschränkung gehört ins Angebot |
| 2026-08-14 | Absatzmarkt | ~~GUS, Europa, Amerika~~ → **korrigiert: ausschließlich Rumänien** | Founder | Rumänisches Recht, rumänische Sprache, ein Markt. Vereinfacht den Umfang erheblich — und stellt die Frage, ob wir den Kunden nach der eben beschlossenen Regel überhaupt annehmen |
| 2026-08-14 | Arbeitssprache / Freelancer | **offen, Entscheidung zurückgestellt** | Founder | Frühere Festlegung „rumänischer Freelancer" beruhte auf der falschen Marktannahme. Bei Rumänien-only ist Rumänisch die einzige Produktsprache — damit wird ein rumänischer Sprachprüfer strukturell nötig, nicht optional |

| 2026-08-14 | Kommunikationskanal mit dem Kunden | **Ausschliesslich E-Mail.** Kein WhatsApp, keine Telefontermine anbieten | Founder | WhatsApp Business API entfaellt damit vorerst; Kanal passt ohnehin besser zu Fragebogen und Dateianlagen |
| 2026-08-14 | Absenderadresse | `granici.design@gmail.com` | Founder | Hinweis auf eigene Domain wurde gegeben, Entscheidung bewusst getroffen. Pflichtangaben in der Signatur bleiben erforderlich |
| 2026-08-14 | Sprache der Korrespondenz mit dem Vertriebsleiter | **Rumaenisch** (nicht Englisch) | Founder | Versand ohne muttersprachliche Gegenlesung, bewusst. Rumaenischer Korrektor bleibt fuer das Produkt erforderlich |

| 2026-08-15 | Ablage des Lead-Exports | **Aus dem Repo entfernt**, liegt unter `~/Documents/Kundendaten-vertraulich/kunde-a/`, Rechte 700/600 | Founder | Verschoben, nicht kopiert; SHA-256 identisch. Ins Repo kommen nur Aggregate. Regeln und offener AVV-Punkt: `DATEN-EXTERN.md` |

## Noch zu entscheiden

- **AVV mit dem Kunden für den Lead-Export.** Wir verarbeiten 2.904 Verbraucherdatensätze
  ohne schriftliche Grundlage nach Art. 28 DSGVO. Vor jeder weiteren Auswertung zu unterzeichnen,
  mit Zweckbindung und Löschfrist. Siehe `DATEN-EXTERN.md`.
- **Der Kunde verkauft nicht in den DACH-Raum.** Nach der am 2026-08-14 beschlossenen Regel
  (CLAUDE.md §1: Kriterium ist der Absatzmarkt) fällt er damit aus dem Zielprofil. Founder muss
  entscheiden: Ausnahme mit eingeschränkter Zusicherung, oder Regel wieder öffnen. Ohne diese
  Entscheidung kein Angebot.
- **Widerrufsrecht bei Maßanfertigung** — Ausnahme greift, aber eng. Feste geprüfte Antwort je
  Markt statt freier Formulierung durch das Modell. Details in
  `t2-chat-assistent/00-vorueberlegungen.md`.

- **Shopify als Stack** — Abweichung von CLAUDE.md §3. Vor Projektstart erforderlich:
  schriftliche Begründung, dokumentierter Datenfluss, Shopify-DPA in der Projektakte, Freigabe des
  Founders. Ohne diese drei Punkte kein Build.
- **Verbindlichkeit der Preise aus dem Angebots-Assistenten.** Ein automatisch erzeugter,
  verbindlicher Preis ist ein Angebot im Rechtssinn. Vor dem Bau zu klären, mit
  `german-legal-compliance`.
- **Aufzeichnung von Telefonaten** beim Sprachassistenten — nur mit Einwilligung und Ansage.
  Entscheidung des Kunden, Umsetzung von uns.
- **Wartungsvertrag** für Shop und Assistenten. KI-Assistenten ohne laufende Kontrolle der
  Antwortqualität sind ein Risiko für den Kunden und für uns.
