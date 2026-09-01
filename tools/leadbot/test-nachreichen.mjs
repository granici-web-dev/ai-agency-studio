#!/usr/bin/env node
// Prüft den Weg, auf dem der Bot einen Ausfall des CRM übersteht.
//
// Warum eigens: das ist die einzige Zusage im Autonomiebetrieb, die man nicht
// sieht, solange alles läuft. Solange Twenty antwortet, sieht ein kaputter
// Rückfallweg genauso aus wie ein heiler — und auffallen würde er an dem Tag,
// an dem der Vertrieb einen echten Kunden aufnimmt und der Rechner gerade
// Docker neu startet.
//
// Läuft ohne Twenty und ohne Telegram: das CRM wird absichtlich auf einen
// geschlossenen Port gezeigt, die Warteschlange in einen Wegwerf-Ordner.
// Es entsteht kein Datensatz im CRM.
//
// Aufruf:  node tools/leadbot/test-nachreichen.mjs

import { mkdtempSync, rmSync, existsSync, readFileSync, readdirSync } from "node:fs";
import { join } from "node:path";
import { tmpdir } from "node:os";

const tempState = mkdtempSync(join(tmpdir(), "leadbot-test-"));

// Vor dem Import setzen — bot.mjs und crm.mjs lesen beides beim Laden.
process.env.LEADBOT_STATE_DIR = tempState;
process.env.TWENTY_URL = "http://127.0.0.1:9";   // Discard-Port, nimmt nie an
process.env.TWENTY_API_KEY ||= "test";
process.env.TELEGRAM_BOT_TOKEN ||= "test";

const { toCrm, offeneAufnahmen, pendingPath } = await import("./bot.mjs");

let ok = 0, fehler = 0;
const pruefe = (bedingung, was) => {
  console.log(`  ${bedingung ? "ok  " : "FEHL"}: ${was}`);
  bedingung ? ok++ : fehler++;
};

const antworten = {
  firma: "Testbetrieb Nachreichen",
  ansprechpartner: "T. Test",
  wunsch: "Website mit Terminbuchung",
  sprache: "Deutsch",
  bestand: "Nichts davon",
};
const slug = "test-nachreichen";

console.log("\nCRM nicht erreichbar\n");

const t0 = Date.now();
const r = await toCrm(antworten, slug, 4242);
const dauer = (Date.now() - t0) / 1000;

pruefe(r.ok === false, "toCrm meldet den Fehlschlag, statt ihn zu verschlucken");
pruefe(dauer < 30, `es hängt nicht — Abbruch nach ${dauer.toFixed(1)} s`);
pruefe(existsSync(pendingPath(slug)), "die Aufnahme liegt in der Nachreichmappe");

const abgelegt = JSON.parse(readFileSync(pendingPath(slug), "utf8"));
pruefe(abgelegt.answers?.firma === antworten.firma, "die Antworten sind vollständig abgelegt");
pruefe(abgelegt.chatId === 4242,
  "der Chat ist mitgespeichert — sonst weiß der Bot später nicht, wem er Bescheid sagt");

const offen = offeneAufnahmen();
pruefe(offen.length === 1 && offen[0].slug === slug, "offeneAufnahmen() findet sie wieder");

// Zweiter Anlauf desselben Vorgangs darf keine zweite Datei erzeugen — sonst
// wächst die Mappe bei jedem Fünf-Minuten-Takt weiter.
await toCrm(antworten, slug, 4242);
pruefe(readdirSync(join(tempState, "crm-offen")).length === 1,
  "ein erneuter Fehlversuch legt keine zweite Datei an");

// Kaputte Datei in der Mappe darf den ganzen Nachreichlauf nicht anhalten.
const { writeFileSync } = await import("node:fs");
writeFileSync(join(tempState, "crm-offen", "kaputt.json"), "{ kein json");
pruefe(offeneAufnahmen().length === 1,
  "eine unlesbare Datei wird übersprungen, nicht zum Absturz gemacht");

rmSync(tempState, { recursive: true, force: true });

console.log(`\n${ok} bestanden, ${fehler} fehlgeschlagen`);
process.exit(fehler ? 1 : 0);
