#!/usr/bin/env node
// Sichert das selbst gehostete Twenty — Datenbank und hochgeladene Dateien.
//
// Warum überhaupt: in der Datenbank stehen die Ansprechpartner unserer Kunden.
// Bisher lagen sie in genau einem Docker-Volume. Ein `docker compose down -v`,
// ein kaputtes Volume, ein Fehlgriff beim Aufräumen — und sie sind weg, ohne
// dass irgendetwas kaputt aussieht.
//
// Warum die Sicherung geprüft wird: eine Sicherung, die nie zurückgespielt
// wurde, ist eine Vermutung. Deshalb liest jeder Lauf den frischen Dump sofort
// wieder ein (`pg_restore --list`) und `--test` spielt ihn testweise in eine
// Wegwerf-Datenbank zurück und zählt nach.
//
// Wo die Sicherung liegt: im Ordner für Kundendaten, nicht im Repository.
// CLAUDE.md §2.7 — der Dump enthält personenbezogene Daten, das Repository
// nimmt so etwas nicht auf. Verschlüsselt ist er dadurch, dass die Platte es
// ist (FileVault); ein zweiter Schlüssel wäre ein zweiter Schlüssel, den man
// verlieren kann.
//
// Was NICHT hineingehört: der ENCRYPTION_KEY. Läge er neben den Daten, die er
// schützt, wäre die Trennung aufgehoben. Stattdessen steht sein Fingerabdruck
// im Manifest — damit man beim Zurückspielen merkt, ob man den richtigen hat,
// bevor man sich über unlesbare Felder wundert.
//
// Aufruf:  node sichern.mjs [--test] [--status] [--trocken]
//   (ohne)     sichern, prüfen, alte Stände aufräumen
//   --test     zusätzlich in eine Wegwerf-Datenbank zurückspielen und zählen
//   --status   nur berichten, wie alt der jüngste Stand ist
//   --trocken  zeigen, was passieren würde

import { spawn, execFileSync } from "node:child_process";
import { createWriteStream, mkdirSync, readdirSync, statSync, rmSync,
         writeFileSync, readFileSync, existsSync, createReadStream } from "node:fs";
import { join, resolve, dirname } from "node:path";
import { fileURLToPath } from "node:url";
import { createHash } from "node:crypto";
import { pipeline } from "node:stream/promises";

const REPO = resolve(dirname(fileURLToPath(import.meta.url)), "..", "..");
const ZIEL = process.env.TWENTY_BACKUP_DIR ||
  join(process.env.HOME, "Documents", "Kundendaten-vertraulich", "_twenty-backup");
const COMPOSE = process.env.TWENTY_DIR ||
  join(process.env.HOME, "Documents", "PROJECTS", "twenty-crm", "twenty");

const DB_CONTAINER = "twenty-db-1";
const DATEN_VOLUME = "twenty_server-local-data";
const DATENBANK = "default";

// Aufbewahrung. 30 MB je Stand — großzügig sein kostet hier nichts, und der
// Fehler, den man wirklich fürchtet (jemand löscht etwas und merkt es in drei
// Wochen), braucht Stände, die älter sind als die letzte Woche.
const TAEGLICH = 14;   // die letzten 14 Tage vollständig
const MONATLICH = 6;   // dazu der jeweils erste Stand der letzten 6 Monate

const TEST = process.argv.includes("--test");
const STATUS = process.argv.includes("--status");
const TROCKEN = process.argv.includes("--trocken");

const p2 = (n) => String(n).padStart(2, "0");
const jetzt = () => {
  const d = new Date();
  return {
    tag: `${d.getFullYear()}-${p2(d.getMonth() + 1)}-${p2(d.getDate())}`,
    zeit: `${p2(d.getHours())}${p2(d.getMinutes())}`,
    lesbar: `${d.getFullYear()}-${p2(d.getMonth() + 1)}-${p2(d.getDate())} ` +
            `${p2(d.getHours())}:${p2(d.getMinutes())}`,
  };
};

function sh(cmd, args) {
  return execFileSync(cmd, args, { encoding: "utf8", maxBuffer: 64 * 1024 * 1024 }).trim();
}

// Einen Befehl laufen lassen und seine Ausgabe in eine Datei schreiben. Über
// eine Pipe, nicht über einen Puffer — sonst liegt die ganze Sicherung im
// Speicher, und das geht so lange gut, bis sie groß wird.
// Auf beides warten: dass der Prozess sauber endet UND dass die Datei
// vollständig geschrieben ist. Wer nur auf „close" wartet, schneidet bei
// großen Dumps den Rest ab; wer den „finish"-Horcher erst danach anhängt,
// verpasst ihn bei kleinen — die Rollen-Datei ist so klein, dass sie fertig
// war, bevor der Prozess als beendet gemeldet wurde, und der Lauf hing.
async function inDatei(cmd, args, ziel) {
  const strom = createWriteStream(ziel);
  const p = spawn(cmd, args, { stdio: ["ignore", "pipe", "pipe"] });
  let stderr = "";
  p.stderr.on("data", (d) => (stderr += d));

  const geschrieben = pipeline(p.stdout, strom);
  const beendet = new Promise((ok, fehler) => {
    p.on("error", fehler);
    p.on("close", ok);
  });

  const [, code] = await Promise.all([geschrieben, beendet]);
  if (code !== 0) throw new Error(`${cmd} endete mit ${code}\n${stderr.trim()}`);
}

function sha256(datei) {
  return new Promise((ok) => {
    const h = createHash("sha256");
    createReadStream(datei).on("data", (d) => h.update(d)).on("end", () => ok(h.digest("hex")));
  });
}

const mb = (bytes) => (bytes / 1024 / 1024).toFixed(1) + " MB";

function staende() {
  if (!existsSync(ZIEL)) return [];
  return readdirSync(ZIEL)
    .filter((n) => /^\d{4}-\d{2}-\d{2}_\d{4}$/.test(n))
    .filter((n) => existsSync(join(ZIEL, n, "MANIFEST.txt")))  // halbe Läufe zählen nicht
    .sort();
}

// --- Status -------------------------------------------------------------------

if (STATUS) {
  const alle = staende();
  if (!alle.length) { console.log("Keine Sicherung vorhanden."); process.exit(1); }
  const neuster = alle[alle.length - 1];
  const alter = (Date.now() - statSync(join(ZIEL, neuster, "MANIFEST.txt")).mtimeMs) / 3600e3;
  const gesamt = alle.reduce((s, n) => s + Number(sh("du", ["-sk", join(ZIEL, n)]).split("\t")[0]), 0);
  console.log(`Jüngster Stand: ${neuster}  (vor ${alter.toFixed(1)} Stunden)`);
  console.log(`Stände: ${alle.length}  ·  belegt: ${(gesamt / 1024).toFixed(0)} MB  ·  ${ZIEL}`);
  if (alter > 48) {
    console.log("\nÄlter als 48 Stunden. Läuft der Dienst noch?");
    console.log("  launchctl print gui/$(id -u)/de.agentur.twenty-backup | grep -E 'state|last exit'");
    process.exit(1);
  }
  process.exit(0);
}

// --- Vorbedingungen -----------------------------------------------------------

try {
  const zustand = sh("docker", ["inspect", DB_CONTAINER, "--format", "{{.State.Status}}"]);
  if (zustand !== "running") throw new Error(`Container ist ${zustand}`);
} catch (e) {
  // Kein Drama und kein Fehlschlag im lauten Sinn: auf diesem Rechner ist Docker
  // regelmäßig aus. Der nächste Lauf holt es nach.
  console.log(`Übersprungen — ${DB_CONTAINER} nicht erreichbar (${e.message.split("\n")[0]})`);
  process.exit(0);
}

// Der Dienst läuft auch bei jeder Anmeldung, nicht nur zur festen Uhrzeit —
// sonst fällt der Tag aus, an dem der Rechner mittags zu war. Damit daraus
// nicht fünf Stände am selben Tag werden, hält ein junger Stand den Lauf an.
const alle0 = staende();
if (alle0.length && !TROCKEN) {
  const alter = (Date.now() - statSync(join(ZIEL, alle0[alle0.length - 1], "MANIFEST.txt")).mtimeMs) / 3600e3;
  if (alter < 12 && !process.argv.includes("--erzwingen")) {
    console.log(`Übersprungen — jüngster Stand ist ${alter.toFixed(1)} h alt (${alle0[alle0.length - 1]}).`);
    process.exit(0);
  }
}

const t = jetzt();
const ordner = join(ZIEL, `${t.tag}_${t.zeit}`);

if (TROCKEN) {
  console.log(`Würde anlegen: ${ordner}`);
  console.log(`  datenbank.dump      pg_dump -Fc aus ${DB_CONTAINER}`);
  console.log(`  rollen.sql          pg_dumpall --globals-only`);
  console.log(`  dateien.tar.gz      Volume ${DATEN_VOLUME}`);
  console.log(`  MANIFEST.txt        Prüfsummen, Version, Schlüssel-Fingerabdruck`);
  console.log(`\nAufbewahrung: ${TAEGLICH} Tage + ${MONATLICH} Monatserste`);
  console.log(`Vorhanden: ${staende().length} Stände`);
  process.exit(0);
}

mkdirSync(ordner, { recursive: true, mode: 0o700 });

// --- Sichern ------------------------------------------------------------------

const dump = join(ordner, "datenbank.dump");
const rollen = join(ordner, "rollen.sql");
const dateien = join(ordner, "dateien.tar.gz");

// -Fc statt reinem SQL: erlaubt selektives Zurückspielen einzelner Tabellen und
// lässt sich ohne Postgres-Instanz auf Vollständigkeit prüfen.
await inDatei("docker", ["exec", DB_CONTAINER,
  "pg_dump", "-U", "postgres", "-d", DATENBANK, "-Fc", "--no-owner"], dump);

// Rollen sind zwei Kilobyte und beantworten beim Zurückspielen die Frage
// "welche Benutzer gab es eigentlich", bevor sie jemand stellt.
await inDatei("docker", ["exec", DB_CONTAINER,
  "pg_dumpall", "-U", "postgres", "--globals-only"], rollen);

// Anhänge und Profilbilder. Liegen im Volume, nicht in der Datenbank — wer nur
// die Datenbank sichert, stellt später eine Oberfläche voller toter Bilder her.
await inDatei("docker", ["run", "--rm", "-v", `${DATEN_VOLUME}:/d:ro`, "-w", "/d",
  "alpine", "tar", "czf", "-", "."], dateien);

// --- Sofort prüfen ------------------------------------------------------------

const eintraege = sh("docker", ["run", "--rm", "-i", "-v", `${ordner}:/b:ro`,
  `postgres:16`, "pg_restore", "--list", "/b/datenbank.dump"])
  .split("\n").filter((l) => l && !l.startsWith(";"));

// Schemata aus dem Dump selbst, nicht aus der laufenden Datenbank — sonst
// prüft man am Ende, ob die Quelle vollständig ist, statt der Sicherung.
// Zeilenformat: `8; 2615 16385 SCHEMA - core postgres`
const datenzeilen = eintraege.filter((l) => / TABLE DATA /.test(l));
const tabellen = datenzeilen.length;
const schemata = [...new Set(datenzeilen.map((l) => l.split(/\s+/)[5]).filter(Boolean))].sort();

const dateiliste = sh("tar", ["-tzf", dateien]).split("\n").filter((l) => l && !l.endsWith("/"));

if (!tabellen || !eintraege.length) {
  rmSync(ordner, { recursive: true, force: true });
  console.error("Dump ist leer oder unlesbar — Stand verworfen, nicht als Sicherung gezählt.");
  process.exit(1);
}

// --- Manifest -----------------------------------------------------------------

// Der Fingerabdruck, nicht der Schlüssel. Beim Zurückspielen vergleicht man ihn
// mit `shasum -a 256 <<< "$ENCRYPTION_KEY"` und weiß vorher, ob die Felder
// lesbar sein werden — statt es an unlesbaren Zugangsdaten zu merken.
let fingerabdruck = "unbekannt (twenty/.env nicht gefunden)";
let version = "unbekannt";
if (existsSync(join(COMPOSE, ".env"))) {
  const env = Object.fromEntries(readFileSync(join(COMPOSE, ".env"), "utf8").split("\n")
    .map((l) => l.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/)).filter(Boolean)
    .map((m) => [m[1], m[2].trim().replace(/^["']|["']$/g, "")]));
  if (env.ENCRYPTION_KEY)
    fingerabdruck = createHash("sha256").update(env.ENCRYPTION_KEY).digest("hex").slice(0, 16);
  version = env.TAG || version;
}

const groesse = (f) => statSync(f).size;
const manifest = [
  `Twenty-Sicherung`,
  `Zeitpunkt        ${t.lesbar}`,
  `Twenty-Version   ${version}`,
  `Postgres         ${sh("docker", ["exec", DB_CONTAINER, "psql", "-U", "postgres", "-tAc",
                          "show server_version"])}`,
  `Datenbank        ${DATENBANK}`,
  ``,
  `datenbank.dump   ${mb(groesse(dump))}   ${await sha256(dump)}`,
  `rollen.sql       ${mb(groesse(rollen))}   ${await sha256(rollen)}`,
  `dateien.tar.gz   ${mb(groesse(dateien))}   ${await sha256(dateien)}`,
  ``,
  `Schemata         ${schemata.join(", ") || "—"}`,
  `Tabellen mit Daten ${tabellen}`,
  `Dateien im Archiv  ${dateiliste.length}`,
  ``,
  `ENCRYPTION_KEY   sha256[0:16] = ${fingerabdruck}`,
  `                 Der Schlüssel selbst liegt NICHT hier. Passwortmanager,`,
  `                 Anmelde-Schlüsselbund: twenty-crm / twenty-encryption-key.`,
  ``,
  `Zurückspielen    tools/twenty-backup/README.md`,
].join("\n") + "\n";

writeFileSync(join(ordner, "MANIFEST.txt"), manifest, { mode: 0o600 });

console.log(`${t.lesbar}  gesichert nach ${ordner}`);
console.log(`  Datenbank  ${mb(groesse(dump))}  ·  ${tabellen} Tabellen mit Daten  ·  ${schemata.length} Schemata`);
console.log(`  Dateien    ${mb(groesse(dateien))}  ·  ${dateiliste.length} Stück`);

// --- Rückspieltest ------------------------------------------------------------

if (TEST) {
  const testdb = "wiederherstellung_probe";
  const psql = (sql, db = "postgres") =>
    sh("docker", ["exec", DB_CONTAINER, "psql", "-U", "postgres", "-d", db, "-tAc", sql]);
  try {
    psql(`drop database if exists ${testdb}`);
    psql(`create database ${testdb}`);
    sh("docker", ["run", "--rm", "--network", "container:" + DB_CONTAINER,
      "-v", `${ordner}:/b:ro`, "postgres:16",
      "pg_restore", "-h", "localhost", "-U", "postgres", "-d", testdb,
      "--no-owner", "--no-privileges", "/b/datenbank.dump"]);
    const echt = psql(`select count(*) from information_schema.tables
                       where table_schema not in ('pg_catalog','information_schema')`, DATENBANK);
    const probe = psql(`select count(*) from information_schema.tables
                        where table_schema not in ('pg_catalog','information_schema')`, testdb);
    const ok = echt === probe;
    console.log(`  Rückspieltest  ${probe} von ${echt} Tabellen wiederhergestellt  ${ok ? "— in Ordnung" : "— UNVOLLSTÄNDIG"}`);
    if (!ok) process.exitCode = 1;
  } catch (e) {
    console.error(`  Rückspieltest fehlgeschlagen: ${e.message.split("\n").slice(0, 3).join(" ")}`);
    process.exitCode = 1;
  } finally {
    try { psql(`drop database if exists ${testdb}`); } catch { /* Wegwerfdatenbank */ }
  }
}

// --- Aufräumen ----------------------------------------------------------------

const alle = staende();
const behalten = new Set(alle.slice(-TAEGLICH));
// Dazu je Monat den ersten Stand — damit ein Fehler, der erst nach Wochen
// auffällt, noch einen Stand von davor findet.
const monate = new Map();
for (const s of alle) {
  const monat = s.slice(0, 7);
  if (!monate.has(monat)) monate.set(monat, s);
}
for (const s of [...monate.values()].slice(-MONATLICH)) behalten.add(s);

const weg = alle.filter((s) => !behalten.has(s));
for (const s of weg) rmSync(join(ZIEL, s), { recursive: true, force: true });
if (weg.length) console.log(`  aufgeräumt: ${weg.length} alte Stände entfernt`);
console.log(`  Stände gesamt: ${staende().length}`);
