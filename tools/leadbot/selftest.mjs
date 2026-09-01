#!/usr/bin/env node
// Prüft die Ausgabe des Lead-Bots ohne Telegram und ohne ins Repo zu schreiben.
// Aufruf: npm run selftest

import { renderHandoff, renderStatus, renderEntscheidungen, renderFragen, slugify } from "./bot.mjs";
import { QUESTIONS } from "./questions.mjs";
import { BRANCHE, MARKT, QUELLE, ANREDE, zielprofil, splitName, anfrageName, zusagenText,
         standardTasks } from "./crm.mjs";
import { bestandsart, ZUGANG, avvAnlass, avvStatus, zweitsprache } from "./regeln.mjs";

let fehler = 0;
const pruefe = (bedingung, was) => {
  if (!bedingung) { console.error("  FEHLT: " + was); fehler++; }
  else console.log("  ok: " + was);
};

// --- 1 · Slugs --------------------------------------------------------------
console.log("\n1 · Ordnernamen");
const slugs = [
  ["Salon Schnittstelle", "salon-schnittstelle"],
  ["Friseur Müller & Söhne", "friseur-mueller-soehne"],
  ["Café Weiß", "cafe-weiss"],
  ["   ...   ", "kunde"],
];
for (const [ein, aus] of slugs) pruefe(slugify(ein) === aus, `"${ein}" → ${slugify(ein)} (erwartet ${aus})`);

// --- 2 · Fragenkatalog ------------------------------------------------------
console.log("\n2 · Fragenkatalog");
const ids = QUESTIONS.map((q) => q.id);
pruefe(new Set(ids).size === ids.length, "keine doppelten Fragen-IDs");
pruefe(QUESTIONS.filter((q) => q.critical).length === 3, "drei kritische Fragen zu Punkt 3");
pruefe(QUESTIONS.every((q) => q.type !== "choice" || (q.options && q.options.length)),
  "jede Auswahlfrage hat Optionen");
pruefe(QUESTIONS.every((q) => !q.followUp || q.followUp.when.every((w) => !q.options || q.options.includes(w))),
  "jede Nachfrage hängt an einer wirklich möglichen Antwort");
pruefe(QUESTIONS.filter((q) => q.required).length >= 10, "genug Pflichtfelder");

const meta = { date: "2026-08-15", from: "Vertrieb" };

// --- 3 · Fall ohne Zusagen --------------------------------------------------
console.log("\n3 · Fall A — nichts zugesagt, Idealfall");
const sauber = {
  firma: "Salon Schnittstelle", branche: "Friseur", ort: "Siegburg",
  kontaktweg: "Persönlich vor Ort",
  kontakt_name: "Anna Vogt", kontakt_mail: "designer.nefele@gmail.com", kontakt_tel: null,
  anrede: "Sie", entscheider: "Ja",
  wunsch: "Website und Onlinebuchung, Benachrichtigung nach Telegram",
  bestand: "Website ohne Buchung",
  zugesagt_preis: "Nein, keine Zahl", zugesagt_termin: "Nein, kein Termin", zugesagt_sonst: "Nein",
  dringlichkeit: null, hintergrund: null, tabu: null, sprache: "Deutsch",
};
const hA = renderHandoff(sauber, meta);
pruefe(hA.includes("**Nichts.** Keine Zahl"), "Punkt 3 meldet ausdrücklich: nichts zugesagt");
pruefe(hA.includes("`[!]` Offen."), "übersprungene Felder werden als [!] markiert, nicht leer gelassen");
pruefe(hA.includes("designer.nefele@gmail.com"), "Kontakt steht im Handoff");
pruefe(!renderEntscheidungen(sauber, meta).includes("Halten wir die mündlichen Zusagen"),
  "ohne Zusagen keine Scheinaufgabe in entscheidungen.md");

// --- 4 · Fall mit Zusagen ---------------------------------------------------
console.log("\n4 · Fall B — der Vertrieb hat geredet");
const geredet = {
  ...sauber,
  entscheider: "Nein", entscheider_wer: "Herr Vogt, Mitinhaber",
  zugesagt_preis: "Ja", zugesagt_preis_was: "so um die 2000 Euro",
  zugesagt_termin: "Ja", zugesagt_termin_was: "bis Ende September",
  zugesagt_sonst: "Ja", zugesagt_sonst_was: "Telegram ist kein Problem",
  sprache: "Deutsch + eine weitere", sprache_welche: "Türkisch, Prüfer unbekannt",
  kontakt_tel: "+49 170 0000000",
};
const hB = renderHandoff(geredet, meta);
pruefe(hB.includes("so um die 2000 Euro"), "die genannte Zahl steht wörtlich drin");
pruefe(hB.includes("bis Ende September") && hB.includes("Telegram ist kein Problem"),
  "Termin und technische Zusage ebenfalls wörtlich");
pruefe(hB.includes("Herr Vogt"), "abweichender Entscheider wird benannt");
pruefe(hB.includes("jede Änderung an Umfang, Preis oder Termin geht schriftlich"),
  "Trennregel greift automatisch, wenn Entscheider ≠ Ansprechperson");
pruefe(hB.includes("benannten") && hB.includes("§2.2"),
  "zweite Sprache löst die Prüfer-Regel aus");

const eB = renderEntscheidungen(geredet, meta);
pruefe((eB.match(/Mündlich zugesagt, noch nicht geprüft/g) || []).length === 3,
  "alle drei Zusagen landen einzeln in entscheidungen.md");
pruefe(eB.includes("Halten wir die mündlichen Zusagen"), "und erzeugen eine offene Entscheidung");

// --- 5 · Die übrigen Dateien ------------------------------------------------
console.log("\n5 · Restliche Projektdateien");
const st = renderStatus(geredet, "salon-schnittstelle", meta);
pruefe(st.includes("Phase:** 0 · LEAD"), "status.md startet in Zustand 0");
pruefe(st.includes("[ ] Erstkontakt versendet (durch einen Menschen"), "§2.5 steht im Exit-Kriterium");
const of = renderFragen(geredet, meta);
pruefe(of.includes("Pflichtangaben für Impressum"), "Impressum-Frage wird automatisch angelegt");
pruefe(of.includes("BFSG"), "BFSG-Prüfung wird automatisch angelegt");

// --- 6 · Abbildung Bot → CRM -------------------------------------------------
console.log("\n6 · Abbildung auf das CRM");

// Jede Auswahlantwort muss drüben ankommen. Eine nicht übersetzte Option wird sonst
// still zu "Sonstiges" und niemand merkt es.
const q = (id) => QUESTIONS.find((x) => x.id === id).options;
pruefe(q("branche").every((o) => BRANCHE[o]), "jede Branche hat eine CRM-Entsprechung");
pruefe(q("absatzmarkt").every((o) => MARKT[o] !== undefined), "jeder Absatzmarkt ist übersetzt");
pruefe(q("kontaktweg").every((o) => QUELLE[o]), "jeder Kontaktweg ist übersetzt");
pruefe(q("anrede").every((o) => ANREDE[o]), "jede Anrede ist übersetzt");

pruefe(zielprofil(["DE"]) === "PASST", "Deutschland → passt §1");
pruefe(zielprofil(["DE","AT","CH"]) === "PASST", "DACH → passt §1");
pruefe(zielprofil(["EU_UEBRIG","NICHT_EU"]) === "AUSNAHME_OFFEN", "ohne DACH → Ausnahme offen");
pruefe(zielprofil([]) === "AUSNAHME_OFFEN", "unbekannter Markt ist nicht \"passt\"");

pruefe(splitName("Anna Vogt").lastName === "Vogt", "Name wird geteilt");
pruefe(splitName("Anna von der Vogt").lastName === "von der Vogt", "mehrteiliger Nachname bleibt zusammen");
pruefe(splitName("Cher").firstName === "Cher", "einteiliger Name bricht nicht");

pruefe(anfrageName("Website und Buchung. Und noch mehr Text dahinter.") === "Website und Buchung.",
  "Anfragename nimmt den ersten Satz");
pruefe(anfrageName("x".repeat(200)).length <= 70, "Anfragename wird gekappt");
pruefe(anfrageName("") === "Anfrage", "leerer Wunsch ergibt keinen leeren Namen");

pruefe(zusagenText(sauber) === "", "ohne Zusagen bleibt das Feld leer");
const zt = zusagenText(geredet);
pruefe(zt.includes("2000") && zt.includes("September") && zt.includes("Telegram"),
  "alle drei Zusagen landen wörtlich im CRM-Feld");

// --- 7 · Ableitungen --------------------------------------------------------
// Diese vier Regeln sind aus einem echten Fehllauf entstanden (Sunshine, 2026-08-16):
// der Bot hat Zugänge zu einer Website erfragt, die es nicht gab, den AVV als
// „nicht nötig" behauptet und die zweite Sprache unterwegs verloren.
console.log("\n7 · Ableitungen aus den Antworten");

pruefe(bestandsart("Nichts — Telefon und Papier") === "KEINER", "Telefon und Papier → kein Bestand");
pruefe(bestandsart("Website ohne Buchung") === "WEBSITE", "Website erkannt");
pruefe(bestandsart("Plattform (Treatwell, Booksy …)") === "PLATTFORM", "Plattform erkannt");
pruefe(bestandsart("Nur Instagram / WhatsApp") === "KANAELE", "Kanäle erkannt");
pruefe(bestandsart("Weiß ich nicht") === "UNBEKANNT", "unbekannt bleibt unbekannt");
pruefe(ZUGANG.KEINER === null, "ohne Bestand gibt es nichts zu erfragen");
pruefe(Object.keys(ZUGANG).every((k) => k === "KEINER" || ZUGANG[k]),
  "für jede andere Bestandsart steht eine konkrete Frage bereit");

const ohneBestand = { ...sauber, bestand: "Nichts — Telefon und Papier" };
pruefe(!renderFragen(ohneBestand, meta).includes("bestehenden Website"),
  "ohne Bestand wird nicht nach der bestehenden Website gefragt");
pruefe(renderFragen(sauber, meta).includes("bestehenden Website"),
  "mit Website wird sehr wohl danach gefragt");
pruefe(!standardTasks(ohneBestand, "x").some((t) => t.title.includes("Zugänge zum Bestandssystem")),
  "ohne Bestand entsteht keine Zugangs-Aufgabe");
pruefe(standardTasks({ ...sauber, bestand: "Weiß ich nicht" }, "x")
  .some((t) => t.title.includes("Klären, was heute schon läuft")),
  "bei unbekanntem Bestand wird geklärt statt Zugang erfragt");

pruefe(avvAnlass(sauber).length > 0, "Onlinebuchung löst den AVV aus");
pruefe(avvStatus(sauber) === "OFFEN", "mit Anlass ist der AVV offen, nicht erledigt");
pruefe(avvStatus({ wunsch: "Nur eine Visitenkarte mit drei Seiten" }) === "UNGEPRUEFT",
  "ohne Anlass behauptet der Bot nicht, der AVV sei nicht nötig");
pruefe(renderHandoff(sauber, meta).includes("Art. 28"), "der AVV steht im Handoff, nicht erst im Angebot");

pruefe(zweitsprache({ sprache: "Deutsch" }) === null, "reines Deutsch braucht keine Zweitsprache");
pruefe(zweitsprache({ sprache: "Deutsch + eine weitere", sprache_welche: "Englisch" }) === "Englisch",
  "die zweite Sprache wird festgehalten");
pruefe(zweitsprache({ sprache: "Andere" }) === "unbenannt",
  "eine Sprache ohne Namen verschwindet nicht stillschweigend");
const zweisprachig = { ...sauber, sprache: "Deutsch + eine weitere", sprache_welche: "Englisch" };
pruefe(standardTasks(zweisprachig, "x").some((t) => t.wartetAuf === "FOUNDER" && t.bereich === "SPRACHE"),
  "die zweite Sprache erzeugt eine Founder-Entscheidung");
pruefe(renderFragen(zweisprachig, meta).includes("Englisch"),
  "und eine offene Frage, wer sie gegenliest");

// Nicht „kein Trello" — die Spiegelung wird ja erwähnt. Sondern: kein Verweis auf
// das abgeschaltete Board als Ablageort (`.claude/trello.md`, Liste Triage).
const vorlagen = renderHandoff(sauber, meta) + renderStatus(sauber, "x", meta);
pruefe(!vorlagen.includes(".claude/trello.md") && !vorlagen.includes("Agentur — Leads"),
  "kein Verweis mehr auf das abgeschaltete Board");
pruefe(renderStatus(zweisprachig, "x", meta).includes("nicht lieferbar"),
  "\"Blockiert\" nennt echte Blocker statt pauschal \"nichts\"");

// --- Ergebnis ---------------------------------------------------------------
console.log("\n" + (fehler ? `${fehler} Prüfung(en) fehlgeschlagen` : "Alle Prüfungen bestanden"));
if (process.env.SHOW_HANDOFF) console.log("\n" + "=".repeat(70) + "\n" + hB);
process.exit(fehler ? 1 : 0);
