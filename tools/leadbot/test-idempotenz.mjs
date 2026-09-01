#!/usr/bin/env node
// Prüft, dass ein zweiter Anlauf derselben Aufnahme nichts verdoppelt.
//
// Warum eigens: der Nachreichweg versucht es alle fünf Minuten erneut. Bricht
// ein Lauf in der Mitte ab — Docker geht aus, das Netz weg —, dann ist der
// Betrieb angelegt und der Rest nicht. Ohne Idempotenz entstünde bei jedem
// Versuch ein weiterer Ansprechpartner, eine weitere Anfrage und ein weiterer
// Satz Aufgaben; über Nacht rund 280 Versuche, und niemand sieht zu.
//
// Der Test schreibt echte Datensätze und räumt sie danach restlos weg — auch
// aus der Papierkorbebene, damit im CRM nichts Erfundenes stehen bleibt.
//
// Aufruf:  node tools/leadbot/test-idempotenz.mjs

import { readFileSync } from "node:fs";
import { execFileSync } from "node:child_process";

for (const l of readFileSync(".env", "utf8").split("\n")) {
  const m = l.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
  if (m && process.env[m[1]] === undefined)
    process.env[m[1]] = m[2].trim().replace(/^["']|["']$/g, "");
}

const { pushToCrm } = await import("./crm.mjs");
const BASE = process.env.TWENTY_URL || "http://localhost:3000";
const H = { "content-type": "application/json",
            authorization: `Bearer ${process.env.TWENTY_API_KEY}` };

const rest = async (method, path, body) => {
  const r = await fetch(`${BASE}/rest${path}`, { method, headers: H,
    body: body ? JSON.stringify(body) : undefined });
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(`${method} ${path} → ${r.status} ${JSON.stringify(j).slice(0, 200)}`);
  return j;
};

let ok = 0, fehler = 0;
const pruefe = (b, was) => { console.log(`  ${b ? "ok  " : "FEHL"}: ${was}`); b ? ok++ : fehler++; };

const SLUG = "zz-test-idempotenz";
const antworten = {
  firma: "ZZ Testbetrieb (automatischer Test)",
  branche: "Friseur",
  ort: "Siegburg",
  absatzmarkt: "Nur Deutschland",
  kontaktweg: "Persönlich vor Ort",
  kontakt_name: "Test Person",
  kontakt_mail: "test@example.invalid",
  anrede: "Sie",
  entscheider: "Ja",
  sprache: "Deutsch",
  bestand: "Nichts davon",
  wunsch: "Website mit Terminbuchung",
};

const zaehle = async () => {
  const c = (await rest("GET",
    `/companies?filter=${encodeURIComponent(`projektordner[eq]:"${SLUG}"`)}`)).data.companies[0];
  if (!c) return null;
  const p = (await rest("GET", `/people?filter=${encodeURIComponent(`companyId[eq]:"${c.id}"`)}`)).data.people;
  const o = (await rest("GET", `/opportunities?filter=${encodeURIComponent(`companyId[eq]:"${c.id}"`)}`)).data.opportunities;
  const tt = (await rest("GET", `/taskTargets?filter=${encodeURIComponent(`targetCompanyId[eq]:"${c.id}"`)}&limit=200`)).data.taskTargets;
  return { c, personen: p.length, anfragen: o.length, aufgaben: tt.length, taskIds: tt.map((x) => x.taskId) };
};

let stand;
try {
  console.log("\nErster Lauf\n");
  const r1 = await pushToCrm(antworten, SLUG);
  console.log("  " + r1.log.join(" · "));
  const s1 = await zaehle();
  pruefe(s1 !== null, "Betrieb liegt im CRM");
  pruefe(s1.personen === 1, `ein Ansprechpartner (${s1.personen})`);
  pruefe(s1.anfragen === 1, `eine Anfrage (${s1.anfragen})`);
  pruefe(s1.aufgaben > 0, `${s1.aufgaben} Aufgaben`);

  console.log("\nZweiter Lauf mit denselben Antworten — darf nichts hinzufügen\n");
  const r2 = await pushToCrm(antworten, SLUG);
  console.log("  " + r2.log.join(" · "));
  const s2 = await zaehle();
  stand = s2;
  pruefe(s2.c.id === s1.c.id, "derselbe Betrieb, kein zweiter");
  pruefe(s2.personen === s1.personen, `weiterhin ${s1.personen} Ansprechpartner (${s2.personen})`);
  pruefe(s2.anfragen === s1.anfragen, `weiterhin ${s1.anfragen} Anfrage (${s2.anfragen})`);
  pruefe(s2.aufgaben === s1.aufgaben, `weiterhin ${s1.aufgaben} Aufgaben (${s2.aufgaben})`);
  pruefe(r2.tasks === 0, "der zweite Lauf legt keine Aufgabe an");
} catch (e) {
  console.error("  FEHL: " + e.message);
  fehler++;
  stand = await zaehle().catch(() => null);
}

// --- Aufräumen ---------------------------------------------------------------

console.log("\nAufräumen\n");
if (stand) {
  for (const id of stand.taskIds) await rest("DELETE", `/tasks/${id}`).catch(() => {});
  const o = (await rest("GET", `/opportunities?filter=${encodeURIComponent(`companyId[eq]:"${stand.c.id}"`)}`)).data.opportunities;
  for (const x of o) await rest("DELETE", `/opportunities/${x.id}`).catch(() => {});
  const p = (await rest("GET", `/people?filter=${encodeURIComponent(`companyId[eq]:"${stand.c.id}"`)}`)).data.people;
  for (const x of p) await rest("DELETE", `/people/${x.id}`).catch(() => {});
  await rest("DELETE", `/companies/${stand.c.id}`).catch(() => {});

  // REST löscht nur weich. Ein Testdatensatz, der im Papierkorb liegen bleibt,
  // taucht in drei Monaten in einer Auswertung wieder auf und sieht dann aus
  // wie ein echter Lead.
  const sql = `
    delete from "workspace_8xesuj4yovj5w65frutaud4tv"."taskTarget"
      where "taskId" in (select id from "workspace_8xesuj4yovj5w65frutaud4tv"."task"
                         where title like '%ZZ Testbetrieb%');
    delete from "workspace_8xesuj4yovj5w65frutaud4tv"."task"
      where title like '%ZZ Testbetrieb%';
    delete from "workspace_8xesuj4yovj5w65frutaud4tv"."opportunity"
      where "companyId" = '${stand.c.id}';
    delete from "workspace_8xesuj4yovj5w65frutaud4tv"."person"
      where "companyId" = '${stand.c.id}';
    delete from "workspace_8xesuj4yovj5w65frutaud4tv"."company"
      where id = '${stand.c.id}';`;
  execFileSync("docker", ["exec", "-i", "twenty-db-1", "psql", "-U", "postgres", "-d", "default",
    "-v", "ON_ERROR_STOP=1", "-c", sql], { stdio: "pipe" });

  const rest_c = (await rest("GET",
    `/companies?filter=${encodeURIComponent(`projektordner[eq]:"${SLUG}"`)}`)).data.companies;
  pruefe(rest_c.length === 0, "der Testbetrieb ist restlos weg");

  const uebrig = execFileSync("docker", ["exec", "twenty-db-1", "psql", "-U", "postgres",
    "-d", "default", "-tAc",
    `select count(*) from "workspace_8xesuj4yovj5w65frutaud4tv"."company" where "projektordner" = '${SLUG}'`],
    { encoding: "utf8" }).trim();
  pruefe(uebrig === "0", "auch in der Datenbank steht nichts mehr (auch nicht gelöscht-markiert)");
}

console.log(`\n${ok} bestanden, ${fehler} fehlgeschlagen`);
process.exit(fehler ? 1 : 0);
