#!/usr/bin/env node
// Lead-Bot — Flow A, Schritt A1 (`.claude/flows/lead-a-founder.md`).
//
// Der Vertrieb schickt /new_client, beantwortet den Fragenkatalog auf dem Telefon und der Bot
// legt den Projektordner auf der Platte an. Zweck ist nicht Bequemlichkeit: die Übergabe passiert
// damit Minuten nach dem Gespräch statt Tage danach, und genau daran scheitert Flow A sonst.
//
// Keine Abhängigkeiten. Node >= 18 (fetch eingebaut). Start: npm run bot

import { readFileSync, writeFileSync, mkdirSync, existsSync, readdirSync, unlinkSync } from "node:fs";
import { join, dirname, resolve } from "node:path";
import { fileURLToPath } from "node:url";
import { QUESTIONS, SECTIONS, SKIP } from "./questions.mjs";
import { pushToCrm } from "./crm.mjs";
import { bestandsart, ZUGANG, avvAnlass, zweitsprache } from "./regeln.mjs";

const HERE = dirname(fileURLToPath(import.meta.url));
const REPO = resolve(HERE, "..", "..");

// --- Konfiguration ----------------------------------------------------------

function loadEnv() {
  const f = join(REPO, ".env");
  if (!existsSync(f)) return;
  for (const line of readFileSync(f, "utf8").split("\n")) {
    const m = line.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
    if (!m) continue;
    let v = m[2].trim().replace(/\s+#.*$/, "");
    if ((v.startsWith('"') && v.endsWith('"')) || (v.startsWith("'") && v.endsWith("'"))) {
      v = v.slice(1, -1);
    }
    if (process.env[m[1]] === undefined) process.env[m[1]] = v;
  }
}
loadEnv();

const TOKEN = process.env.TELEGRAM_BOT_TOKEN;
const ALLOWED = (process.env.TELEGRAM_ALLOWED_IDS || "")
  .split(",").map((s) => s.trim()).filter(Boolean);

// Angefangene Formulare enthalten Kontaktdaten. Sie liegen deshalb außerhalb des Repos
// (CLAUDE.md §2.7) und werden nach dem Abschluss gelöscht.
const STATE_DIR = process.env.LEADBOT_STATE_DIR
  || join(process.env.HOME, "Documents", "Kundendaten-vertraulich", "_leadbot-state");

const invokedDirectly = process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url);
if (invokedDirectly) {
  if (!TOKEN) {
    console.error("TELEGRAM_BOT_TOKEN fehlt. Siehe tools/leadbot/README.md");
    process.exit(1);
  }
  mkdirSync(STATE_DIR, { recursive: true, mode: 0o700 });
  mkdirSync(join(STATE_DIR, "crm-offen"), { recursive: true, mode: 0o700 });
}

const API = `https://api.telegram.org/bot${TOKEN}`;

// --- Telegram ---------------------------------------------------------------

async function api(method, body) {
  const r = await fetch(`${API}/${method}`, {
    method: "POST",
    headers: { "content-type": "application/json" },
    body: JSON.stringify(body),
  });
  const j = await r.json();
  if (!j.ok) throw new Error(`${method}: ${j.description}`);
  return j.result;
}

function send(chatId, text, options = []) {
  const reply_markup = options.length
    ? { keyboard: options.map((o) => [{ text: o }]), one_time_keyboard: true, resize_keyboard: true }
    : { remove_keyboard: true };
  return api("sendMessage", { chat_id: chatId, text, reply_markup }).catch((e) =>
    console.error("send:", e.message));
}

// --- Sitzungen --------------------------------------------------------------

const sessions = new Map();

const statePath = (chatId) => join(STATE_DIR, `${chatId}.json`);

function saveSession(chatId) {
  const s = sessions.get(chatId);
  if (s) writeFileSync(statePath(chatId), JSON.stringify(s), { mode: 0o600 });
}

function dropSession(chatId) {
  sessions.delete(chatId);
  try { unlinkSync(statePath(chatId)); } catch {}
}

function restoreSessions() {
  if (!existsSync(STATE_DIR)) return;
  for (const f of readdirSync(STATE_DIR).filter((f) => f.endsWith(".json"))) {
    try {
      sessions.set(Number(f.replace(".json", "")), JSON.parse(readFileSync(join(STATE_DIR, f), "utf8")));
    } catch {}
  }
  if (sessions.size) console.log(`${sessions.size} angefangene Aufnahme(n) wiederhergestellt`);
}

// --- Ablauf -----------------------------------------------------------------

async function askCurrent(chatId) {
  const s = sessions.get(chatId);
  const q = s.queue[s.idx];
  const total = s.queue.length;
  const head = `Frage ${s.idx + 1} von ${total}`;
  const hint = !q.required && q.type === "text" ? "\n\n(/skip überspringt)" : "";
  await send(chatId, `${head}\n\n${q.text}${hint}`, q.type === "choice" ? q.options : []);
}

async function handleAnswer(chatId, raw) {
  const s = sessions.get(chatId);
  const q = s.queue[s.idx];
  const value = raw.trim();

  if (value === SKIP) {
    if (q.required) {
      await send(chatId, "Diese Angabe brauche ich. Ohne sie kann der Erstkontakt nicht raus.",
        q.type === "choice" ? q.options : []);
      return;
    }
    s.answers[q.id] = null;
  } else if (q.type === "choice" && !q.options.includes(value)) {
    await send(chatId, "Bitte einen der Knöpfe benutzen.", q.options);
    return;
  } else if (q.validate) {
    const err = q.validate(value);
    if (err) { await send(chatId, err); return; }
    s.answers[q.id] = value;
  } else {
    s.answers[q.id] = value;
  }

  // Nachfrage einhängen, direkt hinter der aktuellen Frage
  if (q.followUp && q.followUp.when.includes(s.answers[q.id])) {
    s.queue.splice(s.idx + 1, 0, q.followUp.question);
  }

  s.idx += 1;
  saveSession(chatId);

  if (s.idx >= s.queue.length) return finish(chatId);
  await askCurrent(chatId);
}

// --- Ausgabe ----------------------------------------------------------------

export function slugify(name) {
  return name.toLowerCase()
    .replace(/ä/g, "ae").replace(/ö/g, "oe").replace(/ü/g, "ue").replace(/ß/g, "ss")
    .normalize("NFD").replace(/[̀-ͯ]/g, "")
    .replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "").slice(0, 48) || "kunde";
}

const gap = (v, text) => (v ? v : "`[!]` " + text);
// Ortszeit, nicht UTC: eine Aufnahme um halb eins nachts trüge sonst das Datum
// des Vortages — in Briefdateien und Statuszeilen, die später jemand liest.
const today = () => {
  const d = new Date(), p = (n) => String(n).padStart(2, "0");
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
};

function promises(a) {
  const out = [];
  if (a.zugesagt_preis === "Ja") out.push(["Zahl", a.zugesagt_preis_was]);
  if (a.zugesagt_termin === "Ja") out.push(["Termin", a.zugesagt_termin_was]);
  if (a.zugesagt_sonst === "Ja") out.push(["Zusage", a.zugesagt_sonst_was]);
  return out;
}

// „Blockiert: nichts" war die alte Vorgabe und war fast immer falsch — im selben
// Dokument standen vier Fragen mit „blockierend: ja". Hier steht jetzt, was
// tatsächlich hängt, und wenn nichts hängt, steht das auch da.
function blockiert(a) {
  const z = [];
  if (promises(a).length) {
    z.push("Mündliche Zusagen liegen vor. Sie werden vor dem Erstkontakt geprüft (`entscheidungen.md`).");
  }
  if (bestandsart(a.bestand) === "UNBEKANNT") {
    z.push("Ist-Zustand unbekannt. Ohne ihn ist keine Schätzung belastbar.");
  }
  if (zweitsprache(a)) {
    z.push(`Für ${zweitsprache(a)} ist kein Prüfer benannt. Bis dahin ist die Sprache nicht lieferbar (§2.2).`);
  }
  if (avvAnlass(a).length) {
    z.push("AVV nach Art. 28 DSGVO steht aus. Bis dahin keine Verarbeitung von Kundendaten (§2.7).");
  }
  return z.length ? z : ["Nichts. Der Erstkontakt kann heute raus."];
}

// Was der Ist-Zustand für den nächsten Schritt bedeutet. „Nichts" ist die
// angenehmste Antwort — kein Altsystem, keine Migration, keine Abhängigkeit.
function bestandNotiz(a) {
  const art = bestandsart(a.bestand);
  if (art === "KEINER") {
    return "\n\nKein Bestandssystem. Es gibt keine Zugänge zu erfragen und nichts zu migrieren — " +
      "wir bauen auf der grünen Wiese. Das ist der schnellste Fall, nicht der schwierigste.";
  }
  if (art === "UNBEKANNT") {
    return "\n\n`[!]` Der Ist-Zustand ist nicht erfasst. Bevor irgendetwas geschätzt wird, muss " +
      "geklärt sein, was läuft und wer die Zugänge hat.";
  }
  return `\n\nDaraus folgt eine Rückfrage: ${ZUGANG[art]} (Flow A, Schritt A4 — fast immer der ` +
    "langsamste Punkt, deshalb am selben Tag stellen).";
}

// Der AVV entscheidet nicht über die Auslieferung, sondern darüber, ob wir die
// Daten überhaupt anfassen dürfen (CLAUDE.md §2.7). Deshalb steht er im Handoff
// und nicht erst im Angebot.
function avvBlock(a) {
  const anlass = avvAnlass(a);
  if (!anlass.length) return "";
  return "**Auftragsverarbeitung:** Das Vorhaben berührt personenbezogene Daten der Kunden des " +
    `Betriebs (${anlass.join(", ")}). Damit ist ein AVV nach Art. 28 DSGVO Voraussetzung, ` +
    "nicht Beiwerk: bis er unterzeichnet ist, werden Daten allenfalls gespeichert, nicht " +
    "verarbeitet (CLAUDE.md §2.7). Gehört als Bedingung ins Angebot, nicht in die Übergabe.\n";
}

export function renderHandoff(a, meta) {
  const p = promises(a);
  const section3 = p.length
    ? "**Es wurde etwas gesagt.** Wörtlich so erfasst, wie der Vertrieb es erinnert:\n\n" +
      p.map(([k, v]) => `- **${k}:** „${v}"`).join("\n") +
      "\n\nJeder dieser Punkte steht in `entscheidungen.md` als *mündlich zugesagt, noch nicht " +
      "geprüft*. Was wir nicht halten können, wird **jetzt** geklärt, nicht im Angebot " +
      "(Flow A, Schritt A1)."
    : "**Nichts.** Keine Zahl, kein Termin, keine Zusage, kein Ausschluss.\n\n" +
      "Bester Ausgangspunkt: wir sind in Preis, Umfang und Zeitplan frei. Dieser Zustand wird " +
      "geschützt — bis Research und Angebot stehen, nennt niemand eine Zahl, auch nicht ungefähr " +
      "und auch nicht mündlich.";

  const entscheider = a.entscheider === "Ja"
    ? "Ja — Ansprechperson und Entscheider sind dieselbe Person. Vereinfacht alles."
    : a.entscheider === "Nein"
      ? `Nein. Entscheidung und Geld liegen bei: **${a.entscheider_wer}**\n\n` +
        "Damit gilt die Trennregel: **jede Änderung an Umfang, Preis oder Termin geht schriftlich " +
        "an den Entscheider**, nie nur an die Ansprechperson. Sonst wächst der Umfang im " +
        "Tagesgeschäft und niemand hat ihn bezahlt."
      : "`[!]` Unklar — vor dem Angebot zu klären. Wer die Anforderungen nennt, ist nicht immer wer zahlt.";

  const sprache = a.sprache === "Deutsch"
    ? "**Deutsch**, Korrespondenz und Produkt. Prüfer: `german-language-tone` plus PM-Human " +
      "(CLAUDE.md §2.2). Kein offener Sprachpunkt."
    : `**${a.sprache}** — ${a.sprache_welche || "`[!]` welche, offen"}\n\n` +
      "CLAUDE.md §2.2: jede ausgelieferte Sprache braucht einen **benannten** " +
      "muttersprachlichen Prüfer. Ohne ihn wird die Sprache eingepreist oder gestrichen.";

  return `# Handoff — ${a.firma}

Flow A, Schritt A1 (\`.claude/flows/lead-a-founder.md\`). Aufgenommen per Lead-Bot.

**Status:** ${p.length ? "beantwortet, mündliche Zusagen vorhanden" : "beantwortet"} · **Datum:** ${meta.date}
**Übergeben von:** ${meta.from} · **Erfasst von:** \`leadbot\`

---

## ${SECTIONS[1]}

**${a.firma}** — ${a.branche}, ${a.ort}.

Kontakt zustande gekommen: ${a.kontaktweg}.

## ${SECTIONS[2]}

In den Worten des Betriebs:

> ${a.wunsch.split("\n").join("\n> ")}

**Ist-Zustand:** ${a.bestand}${bestandNotiz(a)}

${avvBlock(a)}
## ${SECTIONS[3]}

${section3}

## ${SECTIONS[4]}

| Rolle | Person | Kontakt |
|---|---|---|
| Ansprechperson | ${a.kontakt_name} | ${a.kontakt_mail}${a.kontakt_tel ? " · " + a.kontakt_tel : ""} |

${entscheider}

## ${SECTIONS[5]}

${gap(a.dringlichkeit, "Offen.")}

## ${SECTIONS[6]}

${gap(a.hintergrund, "Nichts gemeldet.")}

## ${SECTIONS[7]}

${sprache}

## ${SECTIONS[8]}

**E-Mail an ${a.kontakt_mail}**${a.kontakt_tel ? `, Telefon ${a.kontakt_tel}` : ""}.
Anrede: **${a.anrede}**.

Ab dem Erstkontakt ist \`pm-client-lead\` der einzige Kanal zum Kunden (Flow A, Übergabe an die
Projektleitung). Der Vertrieb stellt vor und tritt zurück.

## ${SECTIONS[9]}

${gap(a.tabu, "Nichts benannt.")}

---

## Nächster Schritt

1. \`co-founder-orchestrator\` liest diesen Handoff und prüft Punkt 3 gegen das, was wir halten können
2. \`pm-client-lead\` entwirft den Erstkontakt (\`.claude/flows/kundenmails.md\`)
${bestandsart(a.bestand) === "KEINER"
  ? "3. Keine Zugänge nötig — direkt in Research (Zustand 2)"
  : `3. Am selben Tag anfragen: ${ZUGANG[bestandsart(a.bestand)]} (Flow A, Schritt A4)`}
4. Aufgaben stehen im CRM, angelegt beim Aufnehmen (\`.claude/crm.md\`). Die Trello-Spiegelung ist
   nur Anzeige — gearbeitet wird im CRM

**Vor dem Versand:** offene \`[!]\`-Stellen prüfen. Ein leerer Platz sieht aus wie Absicht.
`;
}

export function renderStatus(a, slug, meta) {
  return `# Status — ${a.firma}

**Phase:** 0 · LEAD (Flow A, Schritt A1 abgeschlossen)
**Stand:** ${meta.date}
**Orchestrator:** \`co-founder-orchestrator\` · **Kundenkontakt:** \`pm-client-lead\`

## Auftrag in einem Satz

${a.branche} in ${a.ort}. ${a.wunsch.split("\n")[0]}

## Beteiligte

| Rolle | Sprache | Zuständig |
|---|---|---|
| ${a.kontakt_name} | ${a.sprache} | Ansprechperson${a.entscheider === "Ja" ? ", entscheidet und zahlt" : ""} |
${a.entscheider === "Nein" ? `| ${a.entscheider_wer} | ${a.sprache} | Entscheidung, Geld |\n` : ""}
## Läuft gerade

- Handoff aufgenommen, siehe \`00-handoff.md\`
- Erstkontakt in Vorbereitung

## Blockiert

${blockiert(a).map((z) => `- ${z}`).join("\n")}

## Exit-Kriterium Zustand 0

- [x] \`00-handoff.md\` liegt vor
- [x] Kontaktdaten und Kanal erfasst
- [x] Aufgaben im CRM angelegt
- [ ] Punkt 3 in \`entscheidungen.md\` geprüft
- [ ] Erstkontakt versendet (durch einen Menschen, CLAUDE.md §2.5)
${bestandsart(a.bestand) === "KEINER" ? "" : "- [ ] Zugänge angefragt\n"}${zweitsprache(a) ? `- [ ] Prüfer für ${zweitsprache(a)} benannt oder Sprache gestrichen (§2.2)\n` : ""}${avvAnlass(a).length ? "- [ ] AVV nach Art. 28 DSGVO entworfen\n" : ""}

→ danach Zustand 1 · BRIEF
`;
}

export function renderEntscheidungen(a, meta) {
  const p = promises(a);
  const rows = p.length
    ? p.map(([k, v]) =>
        `| ${meta.date} | ${k} im Erstgespräch genannt | „${v}" | Vertrieb | **Mündlich zugesagt, noch nicht geprüft.** Vor dem Erstkontakt gegen unsere Kalkulation halten. Im Erstkontakt weder bestätigen noch dementieren |`)
        .join("\n")
    : `| ${meta.date} | Mündliche Zusagen | **Keine** | Vertrieb | Bestätigt im Handoff. Preis, Umfang und Termin sind frei |`;

  return `# Entscheidungen — ${a.firma}

Was einmal hier steht, wird nicht stillschweigend geändert. Korrekturen werden als Korrektur
kenntlich gemacht.

| Datum | Thema | Entscheidung | Von | Folge |
|---|---|---|---|---|
${rows}
| ${meta.date} | Korrespondenzsprache | ${a.sprache} | Vertrieb | ${a.sprache === "Deutsch" ? "`german-language-tone` + PM-Human als Prüfer" : "Benannter muttersprachlicher Prüfer erforderlich (CLAUDE.md §2.2)"} |
| ${meta.date} | Kanal zum Kunden | E-Mail an ${a.kontakt_mail} | Vertrieb | Ab Erstkontakt führt \`pm-client-lead\` |

## Noch zu entscheiden

${p.length
  ? "- **Halten wir die mündlichen Zusagen oben?** Wenn nicht, wird das jetzt geklärt, nicht im Angebot.\n"
  : ""}- Paketzuschnitt und Preisrahmen nach Research (CLAUDE.md §4)
- Wartungsvertrag — immer mit anbieten
`;
}

export function renderFragen(a, meta) {
  const rows = [];

  // Nur fragen, was es geben kann. Die Frage nach der Website an einen Betrieb mit
  // Telefon und Papier ist der schnellste Weg, im Erstkontakt unaufmerksam zu wirken.
  const zugang = ZUGANG[bestandsart(a.bestand)];
  if (zugang) rows.push([zugang, "Orchestrator", "**ja**"]);

  rows.push(
    ["Genauer Leistungsumfang und Dauer je Leistung", "Orchestrator", "ja"],
    ["Öffnungszeiten, Pausen, Urlaub, Feiertage", "Orchestrator", "ja"],
    ["Pflichtangaben für Impressum (Firmierung, Adresse, Registerdaten)", "`german-legal-compliance`", "**ja**"],
    ["Mitarbeiterzahl und Umsatz — BFSG-Kleinstunternehmensausnahme prüfen", "`accessibility-auditor`", "nein"],
  );
  if (avvAnlass(a).length) {
    rows.push(["Bereitschaft zur Unterzeichnung eines AVV (Art. 28 DSGVO) — Voraussetzung, kein Beiwerk",
      "`german-legal-compliance`", "**ja**"]);
  }
  if (zweitsprache(a)) {
    rows.push([`Wer liest ${zweitsprache(a)} auf Kundenseite gegen? (§2.2 — sonst wird die Sprache eingepreist oder gestrichen)`,
      "`co-founder-orchestrator`", "ja"]);
  }
  if (!a.dringlichkeit) rows.push(["Gewünschter Fertigstellungstermin", "Orchestrator", "nein"]);

  return `# Offene Fragen — ${a.firma}

Gepflegt von \`pm-client-lead\`. Jede Frage aus dem Team landet hier, bevor sie den Kunden
erreicht. Gebündelt versenden — maximal einmal pro Woche, außer bei blockierenden Fragen.

| # | Frage | von | an | seit | blockierend | Status |
|---|---|---|---|---|---|---|
${rows.map(([f, v, b], i) => `| ${i + 1} | ${f} | ${v} | Kunde | ${meta.date} | ${b} | offen |`).join("\n")}
`;
}

export function writeProject(a, meta) {
  const slug = slugify(a.firma);
  const dir = join(REPO, "projects", slug);
  if (existsSync(dir)) return { error: `\`projects/${slug}/\` existiert schon.` };

  mkdirSync(join(dir, "research"), { recursive: true });
  mkdirSync(join(dir, "reviews"), { recursive: true });
  mkdirSync(join(dir, "nachweise"), { recursive: true });

  writeFileSync(join(dir, "00-handoff.md"), renderHandoff(a, meta));
  writeFileSync(join(dir, "status.md"), renderStatus(a, slug, meta));
  writeFileSync(join(dir, "entscheidungen.md"), renderEntscheidungen(a, meta));
  writeFileSync(join(dir, "offene-fragen.md"), renderFragen(a, meta));

  return { slug, dir };
}

// Das CRM darf die Aufnahme nicht gefährden. Deshalb erst die Dateien, dann das
// CRM — und wenn das CRM nicht antwortet, wandert die Aufnahme in die Nachreichmappe
// statt verloren zu gehen. Nachholen mit /sync.
export const pendingPath = (slug) => join(STATE_DIR, "crm-offen", `${slug}.json`);

export async function toCrm(a, slug, chatId) {
  try {
    const r = await pushToCrm(a, slug);
    try { unlinkSync(pendingPath(slug)); } catch {}
    return { ok: true, ...r };
  } catch (e) {
    // Den Ordner hier anlegen, nicht nur beim Start. Wer sich darauf verlässt,
    // dass er existiert, verliert genau die Aufnahme, die dieser Zweig retten
    // soll: das Schreiben wirft, der Wurf verlässt toCrm, und der Vertrieb
    // bekommt gar keine Antwort mehr. Zweimal mkdir kostet nichts.
    try {
      mkdirSync(join(STATE_DIR, "crm-offen"), { recursive: true, mode: 0o700 });
      writeFileSync(pendingPath(slug), JSON.stringify({ slug, answers: a, chatId }), { mode: 0o600 });
    } catch (schreibfehler) {
      console.error(`CRM: ${slug} konnte nicht einmal zurückgestellt werden — ${schreibfehler.message}`);
      return { ok: false, error: e.message, verloren: true };
    }
    console.error(`CRM: ${slug} zurückgestellt — ${e.message}`);
    return { ok: false, error: e.message };
  }
}

export function offeneAufnahmen() {
  const dir = join(STATE_DIR, "crm-offen");
  if (!existsSync(dir)) return [];
  return readdirSync(dir).filter((f) => f.endsWith(".json")).map((f) => {
    try { return JSON.parse(readFileSync(join(dir, f), "utf8")); } catch { return null; }
  }).filter(Boolean);
}

async function syncPending(chatId) {
  const offen = offeneAufnahmen();
  if (!offen.length) return send(chatId, "Nichts nachzureichen. Das CRM ist auf Stand.");
  const out = [];
  for (const { slug, answers } of offen) {
    const r = await toCrm(answers, slug, chatId);
    out.push(r.ok ? `✓ ${slug}` : `✗ ${slug} — ${r.error.slice(0, 90)}`);
  }
  return send(chatId, "Nachgereicht:\n" + out.join("\n"));
}

// Auf localhost ist das CRM regelmäßig weg — Docker aus, Rechner zugeklappt. Der
// Vertrieb soll deshalb nicht /sync tippen müssen: er weiß nicht, dass es Docker
// gibt, und er soll es auch nicht wissen müssen. Der Bot versucht es allein und
// meldet sich nur, wenn es geklappt hat. Schweigen heißt: es hängt noch.
export async function nachreichenAutomatisch() {
  for (const { slug, answers, chatId } of offeneAufnahmen()) {
    const r = await toCrm(answers, slug, chatId);
    if (!r.ok) continue;
    console.log(`[${today()}] Nachgereicht: ${slug}`);
    if (chatId) await send(chatId, `Nachgetragen: ${slug} steht jetzt im CRM.\n${r.url}`);
  }
}

async function finish(chatId) {
  const s = sessions.get(chatId);
  const a = s.answers;
  const res = writeProject(a, { date: today(), from: s.from });

  if (res.error) {
    await send(chatId, `Nicht angelegt: ${res.error}\nBitte anderen Namen benutzen oder im Repo nachsehen.`);
    dropSession(chatId);
    return;
  }

  const p = promises(a);
  const lines = [
    `Aufgenommen. Ordner: projects/${res.slug}/`,
    "",
    `${a.firma} — ${a.branche}, ${a.ort}`,
    `Kontakt: ${a.kontakt_name}, ${a.kontakt_mail}`,
    "",
  ];
  if (p.length) {
    lines.push("Mündlich zugesagt (steht jetzt in entscheidungen.md):");
    p.forEach(([k, v]) => lines.push(`  • ${k}: ${v}`));
    lines.push("", "Das wird geprüft, bevor der Erstkontakt rausgeht.");
  } else {
    lines.push("Keine mündlichen Zusagen. Wir sind frei in Preis, Umfang und Termin.");
  }
  const crm = await toCrm(a, res.slug, chatId);
  lines.push("");
  if (crm.ok) {
    lines.push(`CRM: Betrieb, Ansprechpartner, Anfrage und ${crm.tasks} Aufgaben angelegt.`);
    lines.push(crm.url);
  } else {
    // Kein "/sync schicken" mehr: der Bot holt das seit dem 2026-08-16 allein
    // nach und meldet sich hier, sobald es durch ist. Wer dem Vertrieb eine
    // Aufgabe gibt, die er nicht braucht, bekommt sie irgendwann vergessen —
    // und dann fehlt der Lead im CRM, ohne dass jemand es merkt.
    lines.push("[!] CRM gerade nicht erreichbar — die Aufnahme ist gesichert.");
    lines.push("Wird automatisch nachgetragen. Du bekommst hier Bescheid, sobald es steht.");
  }
  lines.push("", "Nächster Schritt: der Erstkontakt wird entworfen. Versendet wird von einem Menschen.");

  await send(chatId, lines.join("\n"));
  dropSession(chatId);
  console.log(`[${today()}] Neuer Lead: ${a.firma} → projects/${res.slug}/`);
}

// --- Kommandos --------------------------------------------------------------

const HELP = `Lead-Bot — Übergabe eines neuen Kontakts.

/new_client — neuen Kontakt aufnehmen
/cancel — laufende Aufnahme abbrechen
/status — wo stehe ich gerade\n/sync — ausstehende Aufnahmen ins CRM nachreichen
/help — diese Übersicht

Nimm dir die zwei Minuten direkt nach dem Gespräch. Was du heute Abend erinnerst, ist schon
die aufgeräumte Fassung.`;

async function handleUpdate(u) {
  const msg = u.message;
  if (!msg || !msg.text) return;

  const chatId = msg.chat.id;
  const userId = String(msg.from.id);
  const text = msg.text.trim();

  if (!ALLOWED.includes(userId)) {
    await send(chatId, `Kein Zugang. Deine Telegram-ID: ${userId}\nSie muss in TELEGRAM_ALLOWED_IDS stehen.`);
    console.warn(`Abgelehnt: ${userId} (${msg.from.username || "?"})`);
    return;
  }

  if (text === "/start" || text === "/help") return send(chatId, HELP);

  if (text === "/cancel") {
    if (!sessions.has(chatId)) return send(chatId, "Es läuft gerade nichts.");
    dropSession(chatId);
    return send(chatId, "Abgebrochen. Nichts gespeichert.");
  }

  if (text === "/sync") return syncPending(chatId);

  if (text === "/status") {
    const s = sessions.get(chatId);
    return send(chatId, s
      ? `Aufnahme läuft: Frage ${s.idx + 1} von ${s.queue.length}.`
      : "Keine laufende Aufnahme. /new_client startet eine.");
  }

  if (text === "/new_client") {
    if (sessions.has(chatId)) {
      return send(chatId, "Es läuft schon eine Aufnahme. Erst /cancel, dann neu.");
    }
    sessions.set(chatId, {
      idx: 0,
      answers: {},
      queue: QUESTIONS.map((q) => ({ ...q })),
      from: msg.from.first_name || msg.from.username || userId,
      started: new Date().toISOString(),
    });
    saveSession(chatId);
    await send(chatId, "Neuer Kontakt. Ein paar Fragen — die meisten mit Knopfdruck.");
    return askCurrent(chatId);
  }

  if (!sessions.has(chatId)) {
    return send(chatId, "Ich warte auf nichts. /new_client startet eine Aufnahme.");
  }
  return handleAnswer(chatId, text);
}

// --- Long polling -----------------------------------------------------------

async function main() {
  restoreSessions();
  const me = await api("getMe", {});
  console.log(`Lead-Bot läuft als @${me.username}`);
  console.log(`Repo:  ${REPO}`);
  console.log(`State: ${STATE_DIR}`);
  console.log(ALLOWED.length ? `Zugelassen: ${ALLOWED.join(", ")}` : "WARNUNG: TELEGRAM_ALLOWED_IDS leer — niemand kommt rein");

  const offen = offeneAufnahmen().length;
  if (offen) console.log(`${offen} Aufnahme(n) warten aufs CRM — wird alle 5 Minuten versucht`);
  // Sofort beim Start: der häufigste Fall ist, dass der Rechner gerade wieder da
  // ist und das CRM mit ihm.
  nachreichenAutomatisch().catch((e) => console.error("nachreichen:", e.message));
  setInterval(() => nachreichenAutomatisch().catch((e) => console.error("nachreichen:", e.message)),
    5 * 60 * 1000);

  let offset = 0;
  for (;;) {
    try {
      const updates = await api("getUpdates", { offset, timeout: 50 });
      for (const u of updates) {
        offset = u.update_id + 1;
        await handleUpdate(u).catch((e) => console.error("update:", e.message));
      }
    } catch (e) {
      console.error("poll:", e.message);
      await new Promise((r) => setTimeout(r, 5000));
    }
  }
}

if (invokedDirectly) main().catch((e) => { console.error(e); process.exit(1); });
