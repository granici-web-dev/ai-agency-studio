#!/usr/bin/env node
// Richtet Twenty nach `.claude/crm.md` ein: Felder auf Company/Person/Opportunity,
// das Objekt Projekt, und die Opportunity-Phasen auf unsere Pipeline.
//
// Idempotent: was es schon gibt, wird übersprungen, nicht überschrieben.
// Aufruf: node provision.mjs   (TWENTY_API_KEY aus ../../.env)

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
if (!KEY) { console.error("TWENTY_API_KEY fehlt in .env"); process.exit(1); }

async function meta(query, variables) {
  const r = await fetch(`${BASE}/metadata`, {
    method: "POST",
    headers: { "content-type": "application/json", authorization: `Bearer ${KEY}` },
    body: JSON.stringify({ query, variables }),
  });
  const j = await r.json();
  if (j.errors) throw new Error(j.errors.map((e) => e.message).join(" | "));
  return j.data;
}

const uid = () => crypto.randomUUID();
const opts = (pairs) => pairs.map(([value, label, color], i) =>
  ({ id: uid(), value, label, color: color || "gray", position: i }));

// --- Ist-Zustand -------------------------------------------------------------

const { objects } = await meta(`{ objects(paging:{first:200}) {
  edges { node { id nameSingular fieldsList { id name type options } } } } }`);
const byName = Object.fromEntries(objects.edges.map((e) => [e.node.nameSingular, e.node]));
const has = (obj, field) => byName[obj]?.fieldsList.some((f) => f.name === field);

let created = 0, skipped = 0, failed = 0;

async function field(objName, spec) {
  const obj = byName[objName];
  if (!obj) { console.log(`  ?  ${objName}.${spec.name} — Objekt fehlt`); failed++; return; }
  if (obj.fieldsList.some((f) => f.name === spec.name)) {
    console.log(`  ·  ${objName}.${spec.name} existiert`); skipped++; return;
  }
  try {
    await meta(
      `mutation($input: CreateOneFieldMetadataInput!){ createOneField(input:$input){ id name } }`,
      { input: { field: { ...spec, objectMetadataId: obj.id } } });
    console.log(`  +  ${objName}.${spec.name}`); created++;
  } catch (e) {
    console.log(`  !  ${objName}.${spec.name} — ${e.message}`); failed++;
  }
}

// --- Company -----------------------------------------------------------------

console.log("\nCompany · Betrieb");
await field("company", { name: "projektordner", label: "Projektordner", type: "TEXT",
  icon: "IconFolder", description: "Slug auf der Platte, z. B. kunde-b. Bei Widerspruch gewinnt die Platte." });
await field("company", { name: "branche", label: "Branche", type: "SELECT", icon: "IconBuildingStore",
  options: opts([["FRISEUR","Friseur","pink"],["KOSMETIK","Kosmetik / Nagelstudio","purple"],
    ["GASTRONOMIE","Gastronomie","orange"],["HANDWERK","Handwerk","yellow"],
    ["EINZELHANDEL","Einzelhandel","green"],["PRODUKTION","Produktion","blue"],
    ["DIENSTLEISTUNG","Dienstleistung","sky"],["SONSTIGES","Sonstiges","gray"]]) });
await field("company", { name: "absatzmarkt", label: "Absatzmarkt", type: "MULTI_SELECT", icon: "IconMap",
  description: "CLAUDE.md §1: Aufnahmekriterium ist der Absatzmarkt, nicht der Sitz.",
  options: opts([["DE","Deutschland","green"],["AT","Österreich","green"],["CH","Schweiz","green"],
    ["EU_UEBRIG","EU übrig","yellow"],["NICHT_EU","Nicht-EU","red"]]) });
await field("company", { name: "zielprofil", label: "Zielprofil", type: "SELECT", icon: "IconTarget",
  options: opts([["PASST","passt §1","green"],["AUSNAHME_ERTEILT","Ausnahme erteilt","blue"],
    ["AUSNAHME_OFFEN","Ausnahme offen","orange"],["PASST_NICHT","passt nicht","red"]]) });
await field("company", { name: "quelle", label: "Quelle", type: "SELECT", icon: "IconRoute",
  options: opts([["FOUNDER","Founder / Netzwerk","blue"],["VERTRIEB","Vertrieb vor Ort","purple"],
    ["FORMULAR","Website-Formular","sky"],["EMPFEHLUNG","Empfehlung","green"],["SONSTIGES","Sonstiges","gray"]]) });
await field("company", { name: "korrespondenzsprache", label: "Korrespondenzsprache", type: "SELECT",
  icon: "IconLanguage",
  options: opts([["DE","Deutsch","green"],["RU","Russisch","blue"],["RO","Rumänisch","purple"],
    ["EN","Englisch","sky"],["ANDERE","andere","gray"]]) });
await field("company", { name: "sprachpruefer", label: "Sprachprüfer", type: "TEXT", icon: "IconUserCheck",
  description: "CLAUDE.md §2.2: leer bei Sprache ≠ Deutsch heißt, die Sprache ist nicht lieferbar." });
await field("company", { name: "weitereSprachen", label: "Weitere Sprachen", type: "TEXT",
  icon: "IconLanguageHiragana",
  description: "Sprachen neben der Korrespondenzsprache. Ohne Eintrag in Sprachprüfer nicht lieferbar." });
await field("company", { name: "avv", label: "AVV", type: "SELECT", icon: "IconFileCertificate",
  options: opts([["UNGEPRUEFT","ungeprüft","gray"],["NICHT_NOETIG","nicht nötig","blue"],
    ["OFFEN","offen","red"],["UNTERZEICHNET","unterzeichnet","green"]]) });
await field("company", { name: "wartungsvertrag", label: "Wartungsvertrag", type: "SELECT", icon: "IconTool",
  options: opts([["KEINER","keiner","gray"],["ANGEBOTEN","angeboten","orange"],
    ["AKTIV","aktiv","green"],["ABGELEHNT","abgelehnt","red"]]) });

// Eine Auswahlliste erweitern, ohne die schon erfassten Werte zu verlieren.
//
// Das ist teuer gelernt: beim ersten Versuch wurde die Optionsliste komplett durch
// eine neue mit frischen UUIDs ersetzt. Twenty hängt die gespeicherten Werte an der
// Options-ID, nicht am Text — also stand danach bei Sofa Belle kein „AVV offen"
// mehr, sondern gar nichts. Stillschweigend, ohne Fehlermeldung.
// Deshalb: vorhandene Optionen behalten wie sie sind, nur fehlende anhängen.
async function auswahlErweitern(objName, feldName, neueWerte) {
  const f = byName[objName]?.fieldsList.find((x) => x.name === feldName);
  if (!f) { console.log(`  ?  ${objName}.${feldName} fehlt`); failed++; return; }
  const fehlend = neueWerte.filter(([v]) => !f.options.some((o) => o.value === v));
  if (!fehlend.length) { console.log(`  ·  ${objName}.${feldName} vollständig`); skipped++; return; }
  const zusammen = [
    ...f.options.map((o) => ({ id: o.id, value: o.value, label: o.label, color: o.color, position: o.position })),
    ...fehlend.map(([value, label, color], i) =>
      ({ id: uid(), value, label, color: color || "gray", position: f.options.length + i })),
  ];
  try {
    await meta(`mutation($i: UpdateOneFieldMetadataInput!){ updateOneField(input:$i){ id } }`,
      { i: { id: f.id, update: { options: zusammen } } });
    console.log(`  ~  ${objName}.${feldName} + ${fehlend.map(([v]) => v).join(", ")}`); created++;
  } catch (e) { console.log(`  !  ${objName}.${feldName} — ${e.message}`); failed++; }
}

// „nicht nötig" ist eine Feststellung, die jemand getroffen hat. Beim Lead hat sie
// niemand getroffen — dafür der vierte Wert.
await auswahlErweitern("company", "avv", [["UNGEPRUEFT", "ungeprüft", "gray"]]);

// Die Pakete aus CLAUDE.md §4, Fassung vom 2026-08-16: die drei Pakete für kleine
// Betriebe (einmalig, plus Betreuung) und die aufgeteilten KI-Leistungen.
// Ergänzt, nicht ersetzt — sonst stünde bei den bestehenden Anfragen wieder
// nichts (siehe Kommentar an auswahlErweitern).
await auswahlErweitern("opportunity", "paket", [
  ["KLEIN_BASIS", "Basis (499)", "turquoise"],
  ["KLEIN_TERMIN", "Termin (999)", "turquoise"],
  ["KLEIN_KOMPLETT", "Komplett (1499)", "turquoise"],
  ["BETREUUNG_WEB", "Betreuung Website (79)", "green"],
  ["KI_ANALYSE", "KI-Analyse (490)", "yellow"],
  ["KI_COMPLIANCE", "KI-Compliance", "red"],
  ["KI_SCHULUNG", "KI-Schulung", "pink"],
  ["KI_BETREUUNG", "KI-Betreuung", "green"],
]);

// --- Person ------------------------------------------------------------------

console.log("\nPerson · Ansprechpartner");
await field("person", { name: "rolle", label: "Rolle", type: "SELECT", icon: "IconId",
  options: opts([["ENTSCHEIDER","Inhaber / Entscheider","green"],["FACHLICH","fachlich","blue"],
    ["BUCHHALTUNG","Buchhaltung","yellow"],["TECHNIK","Technik","purple"]]) });
await field("person", { name: "entscheidetUeberGeld", label: "Entscheidet über Geld", type: "BOOLEAN",
  icon: "IconCoin", description: "Trennt Anforderungsgeber vom Zahler." });
await field("person", { name: "anrede", label: "Anrede", type: "SELECT", icon: "IconMessage",
  options: opts([["SIE","Sie","blue"],["DU","Du","green"],["UNKLAR","unklar","gray"]]) });
await field("person", { name: "sprache", label: "Sprache", type: "SELECT", icon: "IconLanguage",
  options: opts([["DE","Deutsch","green"],["RU","Russisch","blue"],["RO","Rumänisch","purple"],
    ["EN","Englisch","sky"],["ANDERE","andere","gray"]]) });
await field("person", { name: "bevorzugterKanal", label: "Bevorzugter Kanal", type: "SELECT", icon: "IconSend",
  options: opts([["MAIL","E-Mail","blue"],["WHATSAPP","WhatsApp","green"],["TELEFON","Telefon","yellow"],
    ["SIGNAL","Signal","sky"],["TELEGRAM","Telegram","purple"],["LINKEDIN","LinkedIn","gray"]]) });

// --- Opportunity -------------------------------------------------------------

console.log("\nOpportunity · Anfrage → Angebot");
const STAGES = opts([["LEAD","0 · Lead","gray"],["BRIEF","1 · Brief","sky"],["RESEARCH","2 · Research","blue"],
  ["ANGEBOT","3 · Angebot","purple"],["GEWONNEN","Gewonnen","green"],["VERLOREN","Verloren","red"]]);
const stageField = byName.opportunity.fieldsList.find((f) => f.name === "stage");
if (stageField && JSON.stringify(stageField.options).includes("SCREENING")) {
  try {
    await meta(`mutation($input: UpdateOneFieldMetadataInput!){ updateOneField(input:$input){ id } }`,
      { input: { id: stageField.id, update: { options: STAGES, defaultValue: "'LEAD'" } } });
    console.log("  ~  stage → LEAD / BRIEF / RESEARCH / ANGEBOT / GEWONNEN / VERLOREN"); created++;
  } catch (e) { console.log(`  !  stage — ${e.message}`); failed++; }
} else { console.log("  ·  stage schon angepasst"); skipped++; }

await field("opportunity", { name: "paket", label: "Paket", type: "SELECT", icon: "IconPackage",
  options: opts([["STARTER","Starter","sky"],["BUSINESS","Business","blue"],["SHOP","Shop","purple"],
    ["KI_AUDIT","KI-Audit","yellow"],["KI_UMSETZUNG","KI-Umsetzung","orange"],
    ["WARTUNG","Wartung","green"],["SONDER","Sonderprojekt","gray"]]) });
await field("opportunity", { name: "muendlicheZusagen", label: "Mündliche Zusagen", type: "SELECT",
  icon: "IconAlertTriangle", defaultValue: "'UNBEKANNT'",
  description: "Vorgabe unbekannt: niemand hat den Vertrieb gefragt. Das ist nicht dasselbe wie keine.",
  options: opts([["UNBEKANNT","unbekannt","orange"],["KEINE","keine","green"],["VORHANDEN","vorhanden","red"]]) });
await field("opportunity", { name: "zusagenWoertlich", label: "Zusagen wörtlich", type: "TEXT",
  icon: "IconQuote", description: "So, wie der Vertrieb es erinnert. Nicht geglättet." });
await field("opportunity", { name: "zusagenGeprueft", label: "Zusagen geprüft", type: "BOOLEAN",
  icon: "IconChecks" });
await field("opportunity", { name: "stackAbweichung", label: "Stack-Abweichung", type: "SELECT",
  icon: "IconGitFork", description: "CLAUDE.md §3 — Abweichung erlaubt, aber nie stillschweigend.",
  options: opts([["KEINE","keine","green"],["SHOPIFY","Shopify","purple"],["STRAPI","Strapi","blue"],
    ["ANDERE","andere","orange"]]) });
await field("opportunity", { name: "founderFreigabe", label: "Founder-Freigabe", type: "SELECT",
  icon: "IconStamp",
  options: opts([["NICHT_NOETIG","nicht nötig","gray"],["OFFEN","offen","orange"],
    ["ERTEILT","erteilt","green"],["VERWEIGERT","verweigert","red"]]) });
await field("opportunity", { name: "verlustgrund", label: "Verlustgrund", type: "SELECT", icon: "IconMoodSad",
  options: opts([["PREIS","Preis","red"],["ZEIT","Zeit","orange"],["KEIN_BUDGET","kein Budget","yellow"],
    ["WETTBEWERB","Wettbewerb","purple"],["KEIN_BEDARF","kein Bedarf","gray"],
    ["WIR_ABGELEHNT","wir abgelehnt","blue"]]) });

// --- Projekt -----------------------------------------------------------------

console.log("\nProjekt · Lieferung");
let projekt = byName.projekt;
if (!projekt) {
  try {
    const d = await meta(`mutation($input: CreateOneObjectInput!){ createOneObject(input:$input){ id nameSingular } }`,
      { input: { object: {
        nameSingular: "projekt", namePlural: "projekte",
        labelSingular: "Projekt", labelPlural: "Projekte",
        icon: "IconRocket",
        description: "Lieferung ab angenommenem Angebot — Zustände 4–11 der Pipeline.",
      } } });
    projekt = { id: d.createOneObject.id, nameSingular: "projekt", fieldsList: [] };
    byName.projekt = projekt;
    console.log(`  +  Objekt projekt (${projekt.id})`); created++;
  } catch (e) { console.log(`  !  Objekt projekt — ${e.message}`); failed++; }
} else { console.log("  ·  Objekt projekt existiert"); skipped++; }

if (projekt) {
  await field("projekt", { name: "phase", label: "Phase", type: "SELECT", icon: "IconProgress",
    defaultValue: "'SETUP'",
    options: opts([["SETUP","4 · Setup","gray"],["BUILD","5 · Build","sky"],["CONTENT","6 · Content","blue"],
      ["GATE_SPRACHE","7 · Gate Sprache","purple"],["GATE_RECHT","8 · Gate Recht","orange"],
      ["QA","9 · QA / Staging","yellow"],["PRODUKTION","10 · Produktion","green"],
      ["HANDOVER","11 · Handover","green"],["WARTUNG","Wartung","turquoise"]]) });
  await field("projekt", { name: "projektordner", label: "Projektordner", type: "TEXT", icon: "IconFolder" });
  await field("projekt", { name: "gateSprache", label: "Gate Sprache", type: "SELECT", icon: "IconLanguage",
    options: opts([["OFFEN","offen","orange"],["FREIGABE","FREIGABE","green"],["NACHARBEIT","NACHARBEIT","red"]]) });
  await field("projekt", { name: "gateRecht", label: "Gate Recht", type: "SELECT", icon: "IconGavel",
    options: opts([["OFFEN","offen","orange"],["JA","JA","green"],["NEIN","NEIN","red"],
      ["RISIKO","Risiko bewusst getragen","purple"]]) });
  await field("projekt", { name: "qaSechs", label: "QA §6", type: "SELECT", icon: "IconChecklist",
    options: opts([["OFFEN","offen","orange"],["BESTANDEN","bestanden","green"],["NACHARBEIT","Nacharbeit","red"]]) });
  await field("projekt", { name: "stagingUrl", label: "Staging", type: "LINKS", icon: "IconWorld" });
  await field("projekt", { name: "liveUrl", label: "Live", type: "LINKS", icon: "IconWorldCheck" });
  await field("projekt", { name: "naechsterMeilenstein", label: "Nächster Meilenstein", type: "DATE_TIME",
    icon: "IconCalendar" });
  await field("projekt", { name: "blockiertDurch", label: "Blockiert durch", type: "TEXT", icon: "IconLock",
    description: "Eine Zeile. Ausführung gehört in projects/<slug>/status.md." });

  for (const [name, label, target, plural] of [
    ["betrieb", "Betrieb", "company", "Projekte"],
    ["anfrage", "Anfrage", "opportunity", "Projekte"],
  ]) {
    if (has("projekt", name)) { console.log(`  ·  projekt.${name} existiert`); skipped++; continue; }
    try {
      await meta(`mutation($input: CreateOneFieldMetadataInput!){ createOneField(input:$input){ id } }`,
        { input: { field: {
          name, label, type: "RELATION", icon: "IconLink",
          objectMetadataId: projekt.id,
          relationCreationPayload: {
            targetObjectMetadataId: byName[target].id,
            targetFieldLabel: plural,
            targetFieldIcon: "IconRocket",
            type: "MANY_TO_ONE",
          },
        } } });
      console.log(`  +  projekt.${name} → ${target}`); created++;
    } catch (e) { console.log(`  !  projekt.${name} — ${e.message}`); failed++; }
  }
}

console.log(`\nAngelegt ${created} · übersprungen ${skipped} · fehlgeschlagen ${failed}`);
process.exit(failed ? 1 : 0);
