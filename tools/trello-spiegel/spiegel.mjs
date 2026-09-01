#!/usr/bin/env node
// Spiegelt die Aufgaben aus Twenty auf ein Trello-Board — in eine Richtung.
//
// Warum überhaupt: Twenty läuft auf `localhost`. Vom Telefon aus ist es nicht zu
// sehen, Trello schon. Das Board ist deshalb eine Anzeige, kein Arbeitsplatz.
//
// Warum nur in eine Richtung: zwei Quellen der Wahrheit gehen in der zweiten Woche
// auseinander und danach weiß niemand mehr, welche stimmt. Wer eine Karte hier
// verschiebt, sieht sie beim nächsten Lauf zurückwandern — mit Vermerk im Protokoll.
//
// Datenschutz: Trello gehört Atlassian, also Drittland. Deshalb wandert hier
// bewusst wenig hinüber — Titel, Zustand, Wartepartner, sonst nichts. Keine
// Ansprechpartner, keine Telefonnummern, keine wörtlichen Kundenzitate. Wo der
// Betriebsname selbst schon personenbezogen ist (Einzelunternehmen „Friseur
// Müller"), hilft TRELLO_ANONYM=1: dann steht der Projektordner-Slug im Titel.
//
// Aufruf:  node spiegel.mjs [--init] [--dry] [--watch[=Sekunden]]
//   --init   legt das Board an und trägt TRELLO_BOARD in .env ein
//   --dry    zeigt nur, was passieren würde
//   --watch  gleicht dauerhaft ab (Vorgabe alle 300 s), damit erledigte Aufgaben
//            von selbst auf „Fertig" wandern und nicht erst, wenn jemand daran denkt

import { readFileSync, writeFileSync, existsSync, appendFileSync } from "node:fs";
import { join, dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const REPO = resolve(dirname(fileURLToPath(import.meta.url)), "..", "..");
const ENV = join(REPO, ".env");
const TWENTY = process.env.TWENTY_URL || "http://localhost:3000";
const INIT = process.argv.includes("--init");
const DRY = process.argv.includes("--dry");
const WATCH = process.argv.some((a) => a.startsWith("--watch"));
const p2 = (n) => String(n).padStart(2, "0");

for (const line of existsSync(ENV) ? readFileSync(ENV, "utf8").split("\n") : []) {
  const m = line.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
  if (m && process.env[m[1]] === undefined) process.env[m[1]] = m[2].trim().replace(/^["']|["']$/g, "");
}
const { TRELLO_KEY: K, TRELLO_TOKEN: T, TWENTY_API_KEY: TK } = process.env;
const ANONYM = process.env.TRELLO_ANONYM === "1";
if (!K || !T) { console.error("TRELLO_KEY/TRELLO_TOKEN fehlen in .env"); process.exit(1); }
if (!TK) { console.error("TWENTY_API_KEY fehlt in .env"); process.exit(1); }

// --- HTTP ---------------------------------------------------------------------

async function trello(method, path, params = {}) {
  const u = new URL(`https://api.trello.com/1${path}`);
  for (const [k, v] of Object.entries({ ...params, key: K, token: T })) u.searchParams.set(k, v);
  const r = await fetch(u, { method });
  const body = await r.text();
  if (!r.ok) throw new Error(`${method} ${path} → ${r.status} ${body.slice(0, 200)}`);
  return body ? JSON.parse(body) : null;
}

async function twenty(path, plural) {
  const r = await fetch(`${TWENTY}/rest${path}`, { headers: { authorization: `Bearer ${TK}` } });
  if (!r.ok) throw new Error(`Twenty ${path} → ${r.status}`);
  return (await r.json()).data[plural];
}

// --- Spalten ------------------------------------------------------------------

// Dieselben Zustände wie im CRM, in derselben Reihenfolge. Weicht das ab, liest
// man auf dem Telefon einen anderen Prozess als im CRM.
const SPALTEN = [
  ["TODO", "Backlog"],
  ["BEREIT", "Bereit"],
  ["IN_PROGRESS", "In Arbeit"],
  ["REVIEW", "Review"],
  ["BLOCKIERT", "Blockiert"],
  ["FREIGABE", "Freigabe Founder"],
  ["DONE", "Fertig"],
];

// Die Marke trägt den Zustand mit, den wir zuletzt gespiegelt haben. Ohne ihn sind
// zwei Fälle nicht unterscheidbar: das CRM ist weitergerückt (richtig so) oder
// jemand hat die Karte hier verschoben (wird zurückgeholt). Sie sehen gleich aus.
const MARKE = (t) => `[crm:${t.id}|${t.status}]`;

// --- Board sicherstellen -------------------------------------------------------

async function boardAnlegen() {
  const b = await trello("POST", "/boards/", {
    name: "Agentur — Aufgaben (Spiegel)",
    defaultLists: "false",
    desc: "Anzeige des CRM. Gearbeitet wird in Twenty — Änderungen hier werden überschrieben.",
    prefs_permissionLevel: "private",
  });
  for (const [i, [, name]] of SPALTEN.entries()) {
    await trello("POST", "/lists", { name, idBoard: b.id, pos: (i + 1) * 100 });
  }
  appendFileSync(ENV, `\nTRELLO_BOARD=${b.id}\n`);
  console.log(`Board angelegt: ${b.shortUrl}`);
  console.log(`TRELLO_BOARD=${b.id} in .env eingetragen.`);
  return b.id;
}

const BOARD = INIT ? await boardAnlegen() : process.env.TRELLO_BOARD;
if (!BOARD) { console.error("TRELLO_BOARD fehlt. Einmalig mit --init anlegen."); process.exit(1); }

// --- Ist-Stand ----------------------------------------------------------------

async function abgleich() {

const listen = await trello("GET", `/boards/${BOARD}/lists`, { filter: "open", fields: "name" });
const listeFuer = {};
for (const [status, name] of SPALTEN) {
  const l = listen.find((x) => x.name === name);
  if (!l) throw new Error(`Spalte "${name}" fehlt auf dem Board. Board von Hand verändert?`);
  listeFuer[status] = l.id;
}

const karten = await trello("GET", `/boards/${BOARD}/cards`, { filter: "open", fields: "name,desc,idList" });
const kartenNach = new Map();
for (const c of karten) {
  const m = c.desc.match(/\[crm:([0-9a-f-]+)\|([A-Z_]+)\]/i);
  if (m) kartenNach.set(m[1], { ...c, gespiegelt: m[2] });
  else {
    // Karten aus einem älteren Lauf ohne Zustand in der Marke.
    const alt = c.desc.match(/\[crm:([0-9a-f-]+)\]/i);
    if (alt) kartenNach.set(alt[1], { ...c, gespiegelt: null });
  }
}

const tasks = await twenty("/tasks?limit=200", "tasks");
const ziele = await twenty("/taskTargets?limit=400", "taskTargets");
const firmen = await twenty("/companies?limit=100", "companies");
const firmaFuer = Object.fromEntries(firmen.map((c) => [c.id, ANONYM ? (c.projektordner || "?") : c.name]));

const betriebVon = {};
for (const z of ziele) {
  if (z.targetCompanyId && firmaFuer[z.targetCompanyId]) betriebVon[z.taskId] = firmaFuer[z.targetCompanyId];
}

// --- Abgleich ------------------------------------------------------------------

// Was hinüberwandert. Bewusst knapp: das Board soll die Frage „wo hängt es"
// beantworten, nicht den Vorgang ersetzen. Details stehen im CRM und auf der Platte.
function beschreibung(t) {
  const z = [];
  if (t.wartetAuf && t.wartetAuf !== "NICHTS") z.push(`Wartet auf: ${t.wartetAuf}`);
  if (t.agent) z.push(`Owner: ${t.agent}`);
  if (t.abnahme) z.push(`Fertig, wenn: ${t.abnahme}`);
  z.push("", "Gearbeitet wird im CRM. Änderungen an dieser Karte werden beim nächsten",
    "Abgleich überschrieben.", "", MARKE(t));
  return z.join("\n");
}

function titel(t) {
  const betrieb = betriebVon[t.id];
  // Der Betriebsname steht meist schon im Titel („… — Sunshine"). Im anonymen
  // Modus muss er da raus, sonst ist die Anonymisierung reine Behauptung.
  let name = t.title || "(ohne Titel)";
  if (ANONYM && betrieb) name = name.replace(/\s+—\s+.*$/, ` — ${betrieb}`);
  return name.slice(0, 250);
}

let neu = 0, verschoben = 0, geaendert = 0, zurueck = 0, archiviert = 0, gleich = 0;

for (const t of tasks) {
  const soll = listeFuer[t.status] ?? listeFuer.TODO;
  const karte = kartenNach.get(t.id);
  const name = titel(t);
  const desc = beschreibung(t);

  if (!karte) {
    if (!DRY) await trello("POST", "/cards", { idList: soll, name, desc, pos: "bottom" });
    console.log(`  +  ${name}`);
    neu++;
    continue;
  }
  kartenNach.delete(t.id);

  const falscheSpalte = karte.idList !== soll;
  const andererText = karte.name !== name || karte.desc !== desc;
  if (!falscheSpalte && !andererText) { gleich++; continue; }

  if (!DRY) await trello("PUT", `/cards/${karte.id}`, { name, desc, idList: soll });

  if (falscheSpalte) {
    const von = listen.find((l) => l.id === karte.idList)?.name ?? "?";
    const nach = SPALTEN.find(([s]) => s === t.status)?.[1] ?? "Backlog";
    // Lag die Karte dort, wo wir sie zuletzt hingelegt haben, dann ist das CRM
    // weitergerückt. Lag sie woanders, hat jemand sie hier verschoben — und das
    // wird still zurückgeholt, wenn es nicht ausgesprochen wird.
    const wieGespiegelt = karte.gespiegelt && karte.idList === listeFuer[karte.gespiegelt];
    if (wieGespiegelt || karte.gespiegelt === null) {
      console.log(`  →  ${von} → ${nach}   ${name}`);
      verschoben++;
    } else {
      const handVon = SPALTEN.find(([s]) => s === karte.gespiegelt)?.[1] ?? "?";
      console.log(`  ↩  von Hand nach "${von}" geschoben, zurück nach "${nach}"   ${name}`);
      console.log(`       (zuletzt gespiegelt als "${handVon}" — im CRM ändern, nicht hier)`);
      zurueck++;
    }
  } else {
    console.log(`  ~  ${name}`);
    geaendert++;
  }
}

// Karten ohne Aufgabe im CRM: gelöscht oder umbenannt. Archivieren, nicht löschen —
// falls jemand doch etwas Eigenes auf dem Board angelegt hat, ist es wiederholbar.
for (const [id, c] of kartenNach) {
  if (!DRY) await trello("PUT", `/cards/${c.id}`, { closed: "true" });
  console.log(`  ×  archiviert (im CRM nicht mehr vorhanden): ${c.name}`);
  archiviert++;
}

if (!WATCH) {
  console.log(`\n${tasks.length} Aufgaben im CRM · ${neu} neu · ${verschoben} verschoben · ` +
    `${zurueck} zurückgeholt · ${geaendert} Text geändert · ${archiviert} archiviert · ${gleich} unverändert`);
  if (ANONYM) console.log("Anonymer Modus: statt Betriebsnamen steht der Projektordner auf den Karten.");
  if (DRY) console.log("Probelauf — nichts geschrieben.");
}
if (zurueck) console.log(`${zurueck} Karte(n) von Hand verschoben und zurückgesetzt. ` +
  "Das Board ist Anzeige — der Zustand wird im CRM geändert.");

return { neu, verschoben, zurueck, geaendert, archiviert };
}

// --- Lauf ----------------------------------------------------------------------

const watchArg = process.argv.find((a) => a.startsWith("--watch"));
if (!watchArg) {
  await abgleich();
} else {
  const takt = Math.max(60, Number(watchArg.split("=")[1]) || 300);
  console.log(`Spiegel läuft, Abgleich alle ${takt} s. Beenden mit Strg-C.\n`);
  for (;;) {
    // Ortszeit, nicht UTC. Wer im Protokoll nachsieht, vergleicht mit der Uhr an
    // der Wand — im Sommer lag toISOString() zwei Stunden daneben.
    const d = new Date();
    const stempel = `${d.getFullYear()}-${p2(d.getMonth() + 1)}-${p2(d.getDate())} ` +
      `${p2(d.getHours())}:${p2(d.getMinutes())}`;
    try {
      const r = await abgleich();
      const bewegt = r.neu + r.verschoben + r.zurueck + r.geaendert + r.archiviert;
      // Ruhige Durchläufe erzeugen sonst hunderte identische Zeilen und man
      // übersieht die eine, in der wirklich etwas passiert ist.
      if (!bewegt) process.stdout.write(`\r${stempel}  keine Änderung          `);
      else console.log(`${stempel}  ${bewegt} Änderung(en)\n`);
    } catch (e) {
      // Twenty aus (Docker gestoppt, Rechner geschlafen) darf den Spiegel nicht
      // beenden — er soll weiterlaufen und sich beim nächsten Takt wieder fangen.
      console.error(`\n${stempel}  Abgleich fehlgeschlagen: ${e.message}`);
    }
    await new Promise((r) => setTimeout(r, takt * 1000));
  }
}
