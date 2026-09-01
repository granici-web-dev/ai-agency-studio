#!/usr/bin/env node
// Lädt Kunde A und Kunde B aus `projects/` nach Twenty.
// Nur Status und Beziehungen — Inhalt bleibt auf der Platte (`.claude/crm.md`).
// Idempotent über das Feld projektordner.

import { readFileSync, existsSync } from "node:fs";
import { join, dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";

const REPO = resolve(dirname(fileURLToPath(import.meta.url)), "..", "..");
const BASE = process.env.TWENTY_URL || "http://localhost:3000";
for (const line of existsSync(join(REPO, ".env")) ? readFileSync(join(REPO, ".env"), "utf8").split("\n") : []) {
  const m = line.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
  if (m && process.env[m[1]] === undefined) process.env[m[1]] = m[2].trim().replace(/^["']|["']$/g, "");
}
const KEY = process.env.TWENTY_API_KEY;
if (!KEY) { console.error("TWENTY_API_KEY fehlt"); process.exit(1); }

async function rest(method, path, body) {
  const r = await fetch(`${BASE}/rest${path}`, {
    method,
    headers: { "content-type": "application/json", authorization: `Bearer ${KEY}` },
    body: body ? JSON.stringify(body) : undefined,
  });
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(`${r.status} ${JSON.stringify(j).slice(0, 300)}`);
  return j;
}

const filter = (f) => `?filter=${encodeURIComponent(f)}`;
let n = 0;

async function upsert(plural, matchField, matchValue, payload, label) {
  const found = await rest("GET", `/${plural}${filter(`${matchField}[eq]:"${matchValue}"`)}`);
  const existing = found?.data?.[plural]?.[0];
  if (existing) { console.log(`  ·  ${label} existiert`); return existing.id; }
  const res = await rest("POST", `/${plural}`, payload);
  const id = res?.data?.[`create${plural[0].toUpperCase()}${plural.slice(1, -1)}`]?.id
    ?? res?.data?.createCompany?.id ?? res?.data?.createPerson?.id
    ?? res?.data?.createOpportunity?.id ?? res?.data?.createProjekt?.id
    ?? Object.values(res?.data ?? {})[0]?.id;
  console.log(`  +  ${label}`); n++;
  return id;
}

// ============================ Kunde A · Sofa Belle ============================
console.log("\nKunde A — Sofa Belle");

const aId = await upsert("companies", "projektordner", "kunde-a", {
  name: "Sofa Belle",
  projektordner: "kunde-a",
  branche: "PRODUKTION",
  absatzmarkt: ["EU_UEBRIG"],          // Rumänien: EU, aber nicht DACH
  zielprofil: "AUSNAHME_OFFEN",        // CLAUDE.md §1 — Founder-Entscheidung offen
  quelle: "FOUNDER",
  korrespondenzsprache: "RO",
  sprachpruefer: "",                   // leer = Sprache nicht lieferbar (§2.2)
  avv: "OFFEN",
  wartungsvertrag: "KEINER",
}, "Betrieb Sofa Belle");

const maxim = await upsert("people", "name.lastName", "Ciornii", {
  name: { firstName: "Maxim", lastName: "Ciornii" },
  companyId: aId,
  rolle: "ENTSCHEIDER", entscheidetUeberGeld: true,
  anrede: "SIE", sprache: "RU", bevorzugterKanal: "MAIL",
  jobTitle: "Inhaber",
}, "Maxim Ciornii (Inhaber, RU)");

await upsert("people", "name.lastName", "Iordache", {
  name: { firstName: "Răzvan", lastName: "Iordache" },
  companyId: aId,
  rolle: "FACHLICH", entscheidetUeberGeld: false,
  anrede: "SIE", sprache: "RO", bevorzugterKanal: "MAIL",
  jobTitle: "Vertriebsleiter",
}, "Răzvan Iordache (Vertrieb, RO)");

const A = [
  { name: "T2 · Chat-Assistent für Kundenanfragen", stage: "RESEARCH", paket: "KI_UMSETZUNG",
    stackAbweichung: "KEINE", founderFreigabe: "OFFEN" },
  { name: "T1 · Angebots-Assistent mit Sofortpreis", stage: "LEAD", paket: "KI_UMSETZUNG",
    stackAbweichung: "KEINE", founderFreigabe: "OFFEN" },
  { name: "T3 · Shopify-Relaunch inkl. Migration", stage: "LEAD", paket: "SHOP",
    stackAbweichung: "SHOPIFY", founderFreigabe: "OFFEN" },
  { name: "T4 · Sprachassistent", stage: "LEAD", paket: "KI_UMSETZUNG",
    stackAbweichung: "KEINE", founderFreigabe: "OFFEN" },
];
for (const o of A) {
  await upsert("opportunities", "name", o.name, {
    ...o, companyId: aId, pointOfContactId: maxim,
    muendlicheZusagen: "KEINE", zusagenGeprueft: true,
    zusagenWoertlich: "Handoff 2026-08-14: nichts zugesagt, weder Zahl noch Termin.",
  }, o.name);
}

// ============================ Kunde B · Friseursalon ==========================
console.log("\nKunde B — Friseursalon");

const bId = await upsert("companies", "projektordner", "kunde-b", {
  name: "Friseursalon — Name offen",
  projektordner: "kunde-b",
  branche: "FRISEUR",
  absatzmarkt: ["DE"],
  zielprofil: "PASST",
  quelle: "VERTRIEB",
  korrespondenzsprache: "DE",
  sprachpruefer: "",                   // auch für Deutsch ist niemand benannt
  avv: "NICHT_NOETIG",
  wartungsvertrag: "KEINER",
}, "Betrieb Friseursalon");

const inhaberB = await upsert("people", "emails.primaryEmail", "designer.nefele@gmail.com", {
  name: { firstName: "Inhaber", lastName: "(Name offen)" },
  emails: { primaryEmail: "designer.nefele@gmail.com", additionalEmails: [] },
  companyId: bId,
  rolle: "ENTSCHEIDER", entscheidetUeberGeld: true,
  anrede: "UNKLAR", sprache: "DE", bevorzugterKanal: "MAIL",
}, "Inhaber (Name offen)");

await upsert("opportunities", "name", "Website + Onlinebuchung mit Terminverwaltung", {
  name: "Website + Onlinebuchung mit Terminverwaltung",
  companyId: bId, pointOfContactId: inhaberB,
  stage: "LEAD", paket: "SONDER",
  muendlicheZusagen: "UNBEKANNT", zusagenGeprueft: false,
  zusagenWoertlich: "",
  stackAbweichung: "KEINE", founderFreigabe: "NICHT_NOETIG",
}, "Website + Onlinebuchung");

// Kein Projekt-Datensatz: nichts hat das Angebots-Gate passiert (Pipeline-Zustand 3).
console.log("\nKeine Projekte angelegt — nichts hat Zustand 3 · ANGEBOT passiert. Korrekt.");
console.log(`\n${n} Datensätze angelegt.`);
