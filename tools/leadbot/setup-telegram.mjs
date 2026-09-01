#!/usr/bin/env node
// Setzt Befehlsliste, Beschreibung und Namen des Bots über die Bot-API.
//
// Warum nicht über BotFather: dort wird die Liste als Text eingetippt, und ein
// unsichtbares Zeichen aus der Zwischenablage lässt sie stillschweigend scheitern.
// Hier steht sie als Code, ist wiederholbar und wird mitversioniert.
//
// Aufruf: node setup-telegram.mjs

import { readFileSync, existsSync } from "node:fs";
import { join, dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const REPO = resolve(dirname(fileURLToPath(import.meta.url)), "..", "..");
for (const line of existsSync(join(REPO, ".env")) ? readFileSync(join(REPO, ".env"), "utf8").split("\n") : []) {
  const m = line.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
  if (m && process.env[m[1]] === undefined) process.env[m[1]] = m[2].trim().replace(/^["']|["']$/g, "");
}
const TOKEN = process.env.TELEGRAM_BOT_TOKEN;
if (!TOKEN) { console.error("TELEGRAM_BOT_TOKEN fehlt in .env"); process.exit(1); }

const COMMANDS = [
  { command: "new_client", description: "Neuen Kunden aufnehmen" },
  { command: "cancel", description: "Aufnahme abbrechen" },
  { command: "status", description: "Stand der letzten Aufnahme" },
  { command: "sync", description: "Offene Aufnahmen ins CRM nachtragen" },
  { command: "help", description: "Hilfe" },
];

// Die Beschreibung ist öffentlich — jeder, der den Benutzernamen kennt, sieht sie.
// Deshalb steht dort nichts über Kunden, Projekte oder die Agentur selbst.
const DESCRIPTION =
  "Interner Aufnahmebot. Nur für berechtigte Mitarbeiter. " +
  "Anfragen von außen werden nicht bearbeitet.";
const SHORT = "Interner Aufnahmebot.";

async function call(method, payload) {
  const r = await fetch(`https://api.telegram.org/bot${TOKEN}/${method}`, {
    method: "POST",
    headers: { "content-type": "application/json" },
    body: JSON.stringify(payload ?? {}),
  });
  const j = await r.json();
  if (!j.ok) throw new Error(`${method}: ${j.description}`);
  return j.result;
}

const me = await call("getMe");
console.log(`Bot: @${me.username} (${me.first_name}), id ${me.id}\n`);

// Erst die alte Liste löschen. Sonst bleiben Reste stehen, wenn ein Befehl entfällt.
await call("deleteMyCommands", { scope: { type: "default" } });
await call("setMyCommands", { commands: COMMANDS, scope: { type: "default" }, language_code: "de" });
await call("setMyCommands", { commands: COMMANDS, scope: { type: "default" } });
console.log("Befehle gesetzt:");
for (const c of COMMANDS) console.log(`  /${c.command.padEnd(11)} ${c.description}`);

await call("setMyDescription", { description: DESCRIPTION, language_code: "de" });
await call("setMyDescription", { description: DESCRIPTION });
await call("setMyShortDescription", { short_description: SHORT, language_code: "de" });
await call("setMyShortDescription", { short_description: SHORT });
console.log(`\nBeschreibung: „${DESCRIPTION}"`);

const check = await call("getMyCommands", { scope: { type: "default" } });
console.log(`\nGegenprobe: ${check.length} Befehle beim Server hinterlegt.`);
if (check.length !== COMMANDS.length) { console.error("Anzahl weicht ab."); process.exit(1); }
