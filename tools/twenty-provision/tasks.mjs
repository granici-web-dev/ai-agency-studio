#!/usr/bin/env node
// Rüstet Twenty-Tasks für unsere Pipeline nach und holt die Trello-Karten herüber.
// Trello wird dabei nicht verändert — abgeschaltet wird erst nach der Kontrolle.
//
// Aufruf: node tasks.mjs [--dry]

import { readFileSync, existsSync } from "node:fs";
import { join, dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const REPO = resolve(dirname(fileURLToPath(import.meta.url)), "..", "..");
const BASE = process.env.TWENTY_URL || "http://localhost:3000";
const DRY = process.argv.includes("--dry");
for (const line of existsSync(join(REPO, ".env")) ? readFileSync(join(REPO, ".env"), "utf8").split("\n") : []) {
  const m = line.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
  if (m && process.env[m[1]] === undefined) process.env[m[1]] = m[2].trim().replace(/^["']|["']$/g, "");
}
const { TWENTY_API_KEY: KEY, TRELLO_KEY, TRELLO_TOKEN } = process.env;
const BOARD = process.env.TRELLO_BOARD || "696f5042578d457ae240329d";
if (!KEY) { console.error("TWENTY_API_KEY fehlt"); process.exit(1); }

const H = { "content-type": "application/json", authorization: `Bearer ${KEY}` };
async function meta(query, variables) {
  const r = await fetch(`${BASE}/metadata`, { method: "POST", headers: H, body: JSON.stringify({ query, variables }) });
  const j = await r.json();
  if (j.errors) throw new Error(j.errors.map((e) => e.message).join(" | "));
  return j.data;
}
async function rest(method, path, body) {
  const r = await fetch(`${BASE}/rest${path}`, { method, headers: H, body: body ? JSON.stringify(body) : undefined });
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(`${r.status} ${JSON.stringify(j).slice(0, 250)}`);
  return j;
}
const uid = () => crypto.randomUUID();
const opts = (p) => p.map(([value, label, color], i) => ({ id: uid(), value, label, color: color || "gray", position: i }));

// ============================ 1 · Schema =====================================

const { objects } = await meta(`{ objects(paging:{first:200}){ edges { node { id nameSingular fieldsList { id name options } } } } }`);
const byName = Object.fromEntries(objects.edges.map((e) => [e.node.nameSingular, e.node]));
const task = byName.task;
let created = 0, skipped = 0, failed = 0;

console.log("Task-Schema");

// status erweitern — die Trello-Listen werden zu Zuständen. TODO/IN_PROGRESS/DONE
// bleiben erhalten, weil die Oberfläche daran hängt.
const status = task.fieldsList.find((f) => f.name === "status");
if (!status.options.some((o) => o.value === "BLOCKIERT")) {
  const merged = opts([
    ["TODO", "Backlog", "gray"],
    ["BEREIT", "Bereit", "sky"],
    ["IN_PROGRESS", "In Arbeit", "blue"],
    ["REVIEW", "Review", "purple"],
    ["BLOCKIERT", "Blockiert", "red"],
    ["FREIGABE", "Freigabe Founder", "orange"],
    ["DONE", "Fertig", "green"],
  ]);
  if (!DRY) await meta(`mutation($i: UpdateOneFieldMetadataInput!){ updateOneField(input:$i){ id } }`,
    { i: { id: status.id, update: { options: merged } } });
  console.log("  ~  status → Backlog / Bereit / In Arbeit / Review / Blockiert / Freigabe / Fertig"); created++;
} else { console.log("  ·  status schon erweitert"); skipped++; }

async function field(spec) {
  if (task.fieldsList.some((f) => f.name === spec.name)) { console.log(`  ·  task.${spec.name} existiert`); skipped++; return; }
  try {
    if (!DRY) await meta(`mutation($i: CreateOneFieldMetadataInput!){ createOneField(input:$i){ id } }`,
      { i: { field: { ...spec, objectMetadataId: task.id } } });
    console.log(`  +  task.${spec.name}`); created++;
  } catch (e) { console.log(`  !  task.${spec.name} — ${e.message}`); failed++; }
}

await field({ name: "bereich", label: "Bereich", type: "SELECT", icon: "IconCategory",
  options: opts([["AUFNAHME","Aufnahme","gray"],["RESEARCH","Research","sky"],["ANGEBOT","Angebot","purple"],
    ["SETUP","Setup","blue"],["BUILD","Build","turquoise"],["CONTENT","Content","yellow"],
    ["SPRACHE","Sprache","orange"],["RECHT","Recht","red"],["QA","QA","green"],
    ["HANDOVER","Handover","green"],["ENTSCHEIDUNG","Entscheidung","pink"]]) });
await field({ name: "agent", label: "Owner (Agent)", type: "TEXT", icon: "IconRobot",
  description: "Zuständiger Agent. assignee bleibt für Menschen." });
await field({ name: "wartetAuf", label: "Wartet auf", type: "SELECT", icon: "IconHourglass",
  defaultValue: "'NICHTS'",
  description: "Der häufigste Grund für Stillstand ist nicht Arbeit, sondern Warten. Deshalb ein eigenes Feld.",
  options: opts([["NICHTS","nichts","green"],["KUNDE","Kunde","red"],["FOUNDER","Founder","orange"],
    ["GATE","Gate","purple"],["INTERN","intern","gray"]]) });
await field({ name: "abnahme", label: "Abnahme", type: "TEXT", icon: "IconChecks",
  description: "Woran erkennt der Orchestrator, dass es fertig ist." });
await field({ name: "datei", label: "Datei", type: "TEXT", icon: "IconFile",
  description: "Pfad auf der Platte. Dort steht der Inhalt, hier nur der Stand." });
await field({ name: "blockiertDurch", label: "Blockiert durch", type: "TEXT", icon: "IconLock" });

// ============================ 2 · Trello lesen ===============================

if (!TRELLO_KEY || !TRELLO_TOKEN) { console.log("\nKeine Trello-Zugangsdaten — Migration übersprungen."); process.exit(0); }

const lists = await (await fetch(
  `https://api.trello.com/1/boards/${BOARD}/lists?filter=open&cards=open&card_fields=name,desc,due&key=${TRELLO_KEY}&token=${TRELLO_TOKEN}`)).json();

const STATUS = { "Backlog":"TODO", "Bereit":"BEREIT", "In Arbeit":"IN_PROGRESS", "Review":"REVIEW",
                 "Blockiert":"BLOCKIERT", "Freigabe Founder":"FREIGABE", "Fertig":"DONE" };
const BEREICH = { "T1+T2":"BUILD", "Angebot":"ANGEBOT", "Setup":"SETUP", "Recht":"RECHT",
                  "Research":"RESEARCH", "Aufnahme":"AUFNAHME", "Entscheidung":"ENTSCHEIDUNG" };

// Alle Felder der Karten sind einzeilig. Ein mehrzeiliger Fang lief in die
// Folgezeile "Blockiert seinerseits den Versand …" — die hat keinen Doppelpunkt
// und wurde deshalb nicht als neues Feld erkannt.
const grab = (d, label) => {
  const re = new RegExp(`^\\s*${label}:\\s*(.*)$`, "im");
  return ((d || "").match(re)?.[1] || "").trim().slice(0, 400);
};

// Reihenfolge ist hier die ganze Logik. "nichts" zuerst zu prüfen war falsch:
// "nichts — läuft ohne diese Klärung weiter" und "Angaben vom Founder" landeten
// dann beide auf NICHTS. Erst die konkreten Wartepartner, dann die Verneinung.
function wartetAuf(txt, list) {
  const t = (txt || "").toLowerCase();
  if (list === "Freigabe Founder") return "FOUNDER";          // die Liste sticht den Text
  if (/founder/.test(t)) return "FOUNDER";
  if (/kunde|inhaber|vertriebsleiter|fragebogen|export/.test(t)) return "KUNDE";
  if (/\bgate\b/.test(t)) return "GATE";
  if (/^(nichts|keine)\b/.test(t)) return "NICHTS";
  return t ? "INTERN" : "NICHTS";                              // wartet auf eigene Vorarbeit
}

// Zielobjekte
const comp = (await rest("GET", `/companies?filter=${encodeURIComponent('projektordner[eq]:"kunde-a"')}`))
  .data.companies[0];
const t2 = (await rest("GET", `/opportunities?filter=${encodeURIComponent('name[ilike]:"%T2 %"')}`))
  .data.opportunities[0];
if (!comp) { console.error("Sofa Belle nicht gefunden — erst seed.mjs laufen lassen."); process.exit(1); }

const existing = (await rest("GET", "/tasks?limit=200")).data.tasks.map((t) => t.title);

console.log(`\nTrello → Twenty  (Board ${BOARD})`);
let mig = 0;
for (const list of lists) {
  for (const card of list.cards || []) {
    if (existing.includes(card.name)) { console.log(`  ·  ${card.name.slice(0, 60)}`); skipped++; continue; }
    const tag = card.name.match(/^\[([^\]]+)\]/)?.[1] || "";
    const blocked = grab(card.desc, "Blockiert durch");
    const payload = {
      title: card.name,
      status: STATUS[list.name] || "TODO",
      bereich: BEREICH[tag] || null,
      agent: grab(card.desc, "Owner") || null,
      abnahme: grab(card.desc, "Abnahme") || null,
      datei: grab(card.desc, "Datei") || null,
      blockiertDurch: blocked || null,
      wartetAuf: wartetAuf(blocked, list.name),
      ...(card.due ? { dueAt: card.due } : {}),
    };
    try {
      if (DRY) { console.log(`  →  [${payload.status}/${payload.wartetAuf}] ${card.name.slice(0, 58)}`); mig++; continue; }
      const t = await rest("POST", "/tasks", payload);
      const tid = Object.values(t.data)[0].id;
      await rest("POST", "/taskTargets", { taskId: tid, targetCompanyId: comp.id });
      if (tag === "T1+T2" && t2) await rest("POST", "/taskTargets", { taskId: tid, targetOpportunityId: t2.id });
      console.log(`  +  [${payload.status}] ${card.name.slice(0, 58)}`); mig++;
    } catch (e) { console.log(`  !  ${card.name.slice(0, 50)} — ${e.message}`); failed++; }
  }
}

console.log(`\nSchema: ${created} neu, ${skipped} vorhanden · Karten migriert: ${mig} · Fehler: ${failed}`);
console.log("Trello ist unverändert. Abschalten erst nach der Sichtkontrolle.");
process.exit(failed ? 1 : 0);
