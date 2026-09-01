#!/usr/bin/env node
// Lagert die lokalen Sicherungsstände auf eine Hetzner Storage Box aus.
//
// Warum: `sichern.mjs` legt alle Stände auf dieselbe Platte, auf der auch die
// Datenbank liegt. Das schützt gegen Fehlbedienung — gegen Plattenschaden,
// Diebstahl und Feuer nicht. Erst eine Kopie außer Haus macht daraus eine
// Sicherung im eigentlichen Sinn.
//
// Warum restic und nicht rsync: die Stände enthalten personenbezogene Daten und
// verlassen unseren Rechner. restic verschlüsselt sie, bevor sie ihn verlassen —
// Hetzner sieht verschlüsselte Blöcke, nicht die Namen unserer Ansprechpartner.
// Dazu spart die Deduplizierung den Löwenanteil: die Stände unterscheiden sich
// kaum voneinander.
//
// Warum trotzdem ein AVV: verschlüsselt oder nicht, wir beauftragen einen
// Dienstleister mit der Speicherung personenbezogener Daten. Art. 28 DSGVO
// fragt nicht danach, ob er sie lesen kann. Ohne Vertrag lädt dieses Skript
// nichts hoch — siehe HETZNER_AVV weiter unten.
//
// Aufruf:  node auslagern.mjs [--init] [--status] [--pruefen] [--trocken]
//   --init      Ablage anlegen (einmalig, nach dem Bestellen)
//   (ohne)      hochladen, aufräumen, prüfen
//   --status    zeigen, was oben liegt
//   --pruefen   Ablage auf Vollständigkeit prüfen (restic check)
//   --trocken   zeigen, was passieren würde

import { execFileSync, spawnSync } from "node:child_process";
import { readFileSync, existsSync, readdirSync } from "node:fs";
import { join, resolve, dirname } from "node:path";
import { fileURLToPath } from "node:url";

const REPO = resolve(dirname(fileURLToPath(import.meta.url)), "..", "..");
const QUELLE = process.env.TWENTY_BACKUP_DIR ||
  join(process.env.HOME, "Documents", "Kundendaten-vertraulich", "_twenty-backup");

for (const l of existsSync(join(REPO, ".env")) ? readFileSync(join(REPO, ".env"), "utf8").split("\n") : []) {
  const m = l.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
  if (m && process.env[m[1]] === undefined)
    process.env[m[1]] = m[2].trim().replace(/^["']|["']$/g, "");
}

const BENUTZER = process.env.HETZNER_BOX_USER;          // z. B. u123456
const HOST = process.env.HETZNER_BOX_HOST || (BENUTZER ? `${BENUTZER}.your-storagebox.de` : null);
const PFAD = process.env.HETZNER_BOX_PATH || "/twenty";
const AVV = process.env.HETZNER_AVV;                    // Datum der Unterzeichnung
const SCHLUESSELDIENST = ["security", "find-generic-password", "-a", "twenty-crm",
                          "-s", "restic-repo-password", "-w"];

const INIT = process.argv.includes("--init");
const STATUS = process.argv.includes("--status");
const PRUEFEN = process.argv.includes("--pruefen");
const TROCKEN = process.argv.includes("--trocken");

const p2 = (n) => String(n).padStart(2, "0");
const zeitstempel = () => {
  const d = new Date();
  return `${d.getFullYear()}-${p2(d.getMonth() + 1)}-${p2(d.getDate())} ${p2(d.getHours())}:${p2(d.getMinutes())}`;
};

function abbruch(text, ...zeilen) {
  console.error(text);
  for (const z of zeilen) console.error(z);
  process.exit(1);
}

// --- Die Reihenfolge, in der etwas fehlen darf ---------------------------------

if (!BENUTZER) {
  abbruch(
    "Keine Storage Box hinterlegt — es ist noch nichts eingerichtet.",
    "",
    "In die .env gehören drei Zeilen:",
    "  HETZNER_BOX_USER=u123456",
    "  HETZNER_BOX_PATH=/twenty",
    "  HETZNER_AVV=2026-08-17          # Datum der Unterzeichnung",
    "",
    "Die Bestellschritte stehen in tools/twenty-backup/README.md.");
}

// Der harte Riegel. Er steht bewusst vor allem anderen: einmal hochgeladen ist
// nicht rückgängig zu machen, und "wir holen den Vertrag nach" ist genau der
// Satz, den man später einer Aufsichtsbehörde nicht sagen möchte.
if (!/^\d{4}-\d{2}-\d{2}$/.test(AVV || "")) {
  abbruch(
    "Kein AVV mit Hetzner hinterlegt — es wird nichts hochgeladen.",
    "",
    "Die Stände enthalten Namen, Mailadressen und Telefonnummern unserer",
    "Ansprechpartner bei Kunden. Sie einem Dienstleister zu übergeben, ist eine",
    "Auftragsverarbeitung nach Art. 28 DSGVO — auch verschlüsselt.",
    "",
    "Hetzner stellt den Vertrag im Kundenkonto bereit (Rechtliches → AVV).",
    "Nach der Unterzeichnung in die .env:  HETZNER_AVV=JJJJ-MM-TT");
}

let passwort;
try {
  passwort = execFileSync(SCHLUESSELDIENST[0], SCHLUESSELDIENST.slice(1), { encoding: "utf8" }).trim();
} catch {
  abbruch(
    "Kein Ablage-Passwort im Schlüsselbund.",
    "",
    "Ohne dieses Passwort ist die ausgelagerte Sicherung nicht zu entschlüsseln —",
    "auch von uns nicht. Es gehört wie der ENCRYPTION_KEY in den Passwortmanager.",
    "",
    "Anlegen:",
    "  PW=$(openssl rand -base64 32)",
    "  security add-generic-password -U -a twenty-crm -s restic-repo-password \\",
    "    -l 'Twenty CRM — restic-Ablage Hetzner' -w \"$PW\"",
    "  echo \"$PW\"   # sofort in Passwords.app, dann Fenster schließen");
}

const ABLAGE = `sftp:${BENUTZER}@${HOST}:${PFAD}`;
// Storage Boxen sprechen SSH auf Port 23, nicht auf 22.
const SFTP_ARGS = `-p 23 -o BatchMode=yes -o StrictHostKeyChecking=accept-new`;

function restic(args, { still = false } = {}) {
  const r = spawnSync("restic", ["-r", ABLAGE, "-o", `sftp.args=${SFTP_ARGS}`, ...args], {
    encoding: "utf8",
    env: { ...process.env, RESTIC_PASSWORD: passwort },
    stdio: still ? "pipe" : ["ignore", "pipe", "pipe"],
  });
  if (r.error) throw r.error;
  return r;
}

// --- Trockenlauf ---------------------------------------------------------------

if (TROCKEN) {
  const staende = existsSync(QUELLE)
    ? readdirSync(QUELLE).filter((n) => /^\d{4}-\d{2}-\d{2}_\d{4}$/.test(n)) : [];
  console.log(`Quelle   ${QUELLE}  (${staende.length} Stände)`);
  console.log(`Ablage   ${ABLAGE}`);
  console.log(`AVV      unterzeichnet am ${AVV}`);
  console.log(`Passwort im Schlüsselbund vorhanden (${passwort.length} Zeichen)`);
  console.log(`\nAufbewahrung oben: 14 tägliche, 6 monatliche Stände.`);
  process.exit(0);
}

// --- Anlegen -------------------------------------------------------------------

if (INIT) {
  const vorhanden = restic(["cat", "config"], { still: true });
  if (vorhanden.status === 0) {
    console.log(`Ablage besteht bereits: ${ABLAGE}`);
    process.exit(0);
  }
  const r = restic(["init"]);
  process.stdout.write(r.stdout || "");
  if (r.status !== 0) abbruch(r.stderr || "restic init fehlgeschlagen");
  console.log(`Ablage angelegt: ${ABLAGE}`);
  console.log("Erster Lauf:  node tools/twenty-backup/auslagern.mjs");
  process.exit(0);
}

// --- Status --------------------------------------------------------------------

if (STATUS) {
  const r = restic(["snapshots", "--compact"]);
  process.stdout.write(r.stdout || "");
  if (r.status !== 0) abbruch(r.stderr || "restic snapshots fehlgeschlagen");
  process.exit(0);
}

if (PRUEFEN) {
  const r = restic(["check"]);
  process.stdout.write(r.stdout || "");
  if (r.status !== 0) abbruch(r.stderr || "restic check meldet Fehler");
  console.log("Ablage vollständig.");
  process.exit(0);
}

// --- Auslagern -----------------------------------------------------------------

if (!existsSync(QUELLE) || !readdirSync(QUELLE).some((n) => /^\d{4}-\d{2}-\d{2}_\d{4}$/.test(n))) {
  console.log("Nichts auszulagern — es gibt noch keinen lokalen Stand.");
  process.exit(0);
}

const hoch = restic(["backup", QUELLE, "--tag", "twenty", "--host", "agentur-mac"]);
process.stdout.write(hoch.stdout || "");
if (hoch.status !== 0) {
  // Nicht laut scheitern, wenn nur das Netz weg ist: der nächste Lauf holt es
  // nach, und ein Dienst, der wöchentlich rot leuchtet, wird bald ignoriert.
  const grund = (hoch.stderr || "").trim().split("\n").slice(0, 2).join(" ");
  console.error(`${zeitstempel()}  Auslagern fehlgeschlagen — ${grund}`);
  process.exit(1);
}

// Dieselbe Aufbewahrung wie lokal. Oben ist Platz genug (1 TB gegen ein paar
// Megabyte je Stand), aber unbegrenzt aufheben heißt, personenbezogene Daten
// unbegrenzt aufheben — und das ist keine Sparsamkeitsfrage, sondern Art. 5.
const raeumen = restic(["forget", "--tag", "twenty",
  "--keep-daily", "14", "--keep-monthly", "6", "--prune"]);
process.stdout.write(raeumen.stdout || "");
if (raeumen.status !== 0) console.error((raeumen.stderr || "").trim().split("\n")[0]);

const geprueft = restic(["check"]);
if (geprueft.status !== 0) {
  console.error(`${zeitstempel()}  ACHTUNG: restic check meldet Fehler in der Ablage`);
  process.stderr.write(geprueft.stderr || "");
  process.exit(1);
}

const liste = restic(["snapshots", "--compact"], { still: true });
const zeilen = (liste.stdout || "").trim().split("\n");
console.log(`${zeitstempel()}  ausgelagert und geprüft · ${zeilen.length ? zeilen[zeilen.length - 1] : ""}`);
