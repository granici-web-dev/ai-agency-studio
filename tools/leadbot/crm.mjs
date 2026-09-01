// Schreibt eine abgeschlossene Aufnahme ins CRM (`.claude/crm.md`).
//
// Grundsatz: **die Aufnahme darf nicht am CRM scheitern.** Der Bot legt zuerst die
// Dateien an; erst danach kommt das hier. Schlägt es fehl, wird die Aufnahme nicht
// verworfen, sondern zum Nachreichen abgelegt (`/sync`).
//
// Es wandert nur Zustand hierher, kein Inhalt. Der Wunsch des Betriebs steht als
// Kurzfassung im Namen der Anfrage — ausformuliert steht er in `00-handoff.md`.

import { bestandsart, avvAnlass, avvStatus, zweitsprache } from "./regeln.mjs";

const BASE = () => process.env.TWENTY_URL || "http://localhost:3000";

// --- Übersetzungstabellen Bot → CRM -----------------------------------------

export const BRANCHE = {
  "Friseur": "FRISEUR",
  "Kosmetik / Nagelstudio": "KOSMETIK",
  "Restaurant / Café": "GASTRONOMIE",
  "Handwerk": "HANDWERK",
  "Einzelhandel": "EINZELHANDEL",
  "Sonstiges": "SONSTIGES",
};

export const MARKT = {
  "Nur Deutschland": ["DE"],
  "Deutschland und Nachbarn (AT/CH)": ["DE", "AT", "CH"],
  "EU, über DACH hinaus": ["DE", "EU_UEBRIG"],
  "Auch außerhalb der EU": ["DE", "EU_UEBRIG", "NICHT_EU"],
  "Weiß ich nicht": [],
};

export const QUELLE = {
  "Persönlich vor Ort": "VERTRIEB",
  "Empfehlung": "EMPFEHLUNG",
  "Der Betrieb hat sich gemeldet": "FORMULAR",
  "Anders": "SONSTIGES",
};

export const SPRACHE = { "Deutsch": "DE", "Deutsch + eine weitere": "DE", "Andere": "ANDERE" };
export const ANREDE = { "Sie": "SIE", "Du": "DU", "Weiß ich nicht": "UNKLAR" };

// Das Aufnahmekriterium aus CLAUDE.md §1, hier einmal als Code statt als Erinnerung.
export function zielprofil(markt) {
  if (!markt || !markt.length) return "AUSNAHME_OFFEN";      // unbekannt ist nicht "passt"
  if (markt.some((m) => ["DE", "AT", "CH"].includes(m))) return "PASST";
  return "AUSNAHME_OFFEN";
}

export function splitName(full) {
  const parts = (full || "").trim().split(/\s+/);
  return parts.length < 2
    ? { firstName: parts[0] || "", lastName: "" }
    : { firstName: parts[0], lastName: parts.slice(1).join(" ") };
}

// Der Name der Anfrage ist eine Kurzfassung, keine Zusammenfassung: erster Satz,
// gekappt. Der volle Wortlaut steht im Handoff und wird hier nicht verdoppelt.
export function anfrageName(wunsch) {
  const s = (wunsch || "").replace(/\s+/g, " ").trim();
  const erster = s.split(/(?<=[.!?])\s/)[0] || s;
  return (erster.length > 70 ? erster.slice(0, 67).trimEnd() + "…" : erster) || "Anfrage";
}

export function zusagenText(a) {
  const t = [];
  if (a.zugesagt_preis === "Ja") t.push(`Zahl: „${a.zugesagt_preis_was}"`);
  if (a.zugesagt_termin === "Ja") t.push(`Termin: „${a.zugesagt_termin_was}"`);
  if (a.zugesagt_sonst === "Ja") t.push(`Zusage: „${a.zugesagt_sonst_was}"`);
  return t.join(" · ");
}

// --- HTTP --------------------------------------------------------------------

async function rest(method, path, body) {
  const r = await fetch(`${BASE()}/rest${path}`, {
    method,
    headers: { "content-type": "application/json", authorization: `Bearer ${process.env.TWENTY_API_KEY}` },
    body: body ? JSON.stringify(body) : undefined,
    signal: AbortSignal.timeout(15000),
  });
  const j = await r.json().catch(() => ({}));
  if (!r.ok) throw new Error(`${method} ${path} → ${r.status} ${JSON.stringify(j).slice(0, 200)}`);
  return j;
}
const first = (res, plural) => res?.data?.[plural]?.[0];
const idOf = (res) => Object.values(res?.data ?? {})[0]?.id;

// --- Standardaufgaben ---------------------------------------------------------

// Was in jedem Flow-A-Lead anfällt und am häufigsten vergessen wird. Bedingte
// Aufgaben entstehen nur, wenn die Antworten sie tragen — eine Aufgabe, die auf
// nichts zeigt, wird nach zwei Wochen mit allen anderen zusammen ignoriert.
export function standardTasks(a, slug) {
  const t = [
    { title: `[Aufnahme] Erstkontakt entwerfen — ${a.firma}`, status: "BEREIT", bereich: "AUFNAHME",
      agent: "pm-client-lead", wartetAuf: "NICHTS",
      abnahme: "Entwurf liegt vor, Sprachprüfung erfolgt, Versand durch einen Menschen",
      datei: `projects/${slug}/00-handoff.md`, blockiertDurch: "nichts" },
    { title: `[Recht] Pflichtangaben für das Impressum erfragen — ${a.firma}`, status: "BEREIT", bereich: "RECHT",
      agent: "german-legal-compliance", wartetAuf: "KUNDE",
      abnahme: "Firmierung, Anschrift, Vertretung, Register und USt-IdNr. liegen schriftlich vor",
      datei: `projects/${slug}/00-handoff.md`, blockiertDurch: "Rückfrage an den Kunden" },
  ];

  // Zugänge nur, wenn es etwas gibt, wozu man Zugang haben könnte. Bei „Telefon und
  // Papier" ist die Aufgabe nicht bloß überflüssig — der Kunde liest die Frage nach
  // seiner Website als Beleg, dass wir nicht zugehört haben.
  const art = bestandsart(a.bestand);
  if (art === "UNBEKANNT") {
    t.push({ title: `[Setup] Klären, was heute schon läuft — ${a.firma}`, status: "BEREIT", bereich: "SETUP",
      agent: "pm-client-lead", wartetAuf: "KUNDE",
      abnahme: "Bestand benannt: Website, Plattform, Kanäle oder nichts",
      datei: `projects/${slug}/00-handoff.md`, blockiertDurch: "Vertrieb hat den Ist-Zustand nicht erfasst" });
  } else if (art !== "KEINER") {
    t.push({ title: `[Setup] Zugänge zum Bestandssystem anfragen — ${a.firma}`, status: "BEREIT", bereich: "SETUP",
      agent: "pm-client-lead", wartetAuf: "KUNDE",
      abnahme: "Lesezugang vorhanden oder schriftlich abgelehnt",
      datei: `projects/${slug}/00-handoff.md`, blockiertDurch: "Rückfrage an den Kunden" });
  }

  // Art. 28 DSGVO. Der AVV muss stehen, bevor wir Daten anfassen — nicht bevor wir
  // liefern. Wer ihn erst beim Go-live bemerkt, hat die Daten schon verarbeitet.
  const anlass = avvAnlass(a);
  if (anlass.length) {
    t.push({ title: `[Recht] AVV nach Art. 28 DSGVO vorbereiten — ${a.firma}`, status: "BEREIT",
      bereich: "RECHT", agent: "german-legal-compliance", wartetAuf: "NICHTS",
      abnahme: "AVV-Entwurf liegt vor und ist im Angebot als Voraussetzung benannt",
      datei: `projects/${slug}/00-handoff.md`, blockiertDurch: `Anlass: ${anlass.join(", ")}` });
  }

  // §2.2: ohne benannten Prüfer wird die Sprache eingepreist oder gestrichen.
  // Das entscheidet der Founder, nicht der Vertrieb und nicht der Bot.
  const zweit = zweitsprache(a);
  if (zweit) {
    t.push({ title: `[Sprache] Prüfer für ${zweit} benennen oder Sprache streichen — ${a.firma}`,
      status: "FREIGABE", bereich: "SPRACHE", agent: "co-founder-orchestrator", wartetAuf: "FOUNDER",
      abnahme: "Person namentlich benannt und in Sprachprüfer eingetragen — oder Sprache gestrichen",
      datei: `projects/${slug}/entscheidungen.md`,
      blockiertDurch: `Kein benannter Prüfer für ${zweit} (CLAUDE.md §2.2)` });
  }

  // Nur wenn wirklich etwas zugesagt wurde. Sonst entsteht eine Scheinaufgabe.
  if (zusagenText(a)) {
    t.push({ title: `[Entscheidung] Mündliche Zusagen prüfen — ${a.firma}`, status: "FREIGABE",
      bereich: "ENTSCHEIDUNG", agent: "co-founder-orchestrator", wartetAuf: "FOUNDER",
      abnahme: "Je Zusage entschieden: halten wir sie? Ergebnis in entscheidungen.md",
      datei: `projects/${slug}/entscheidungen.md`, blockiertDurch: zusagenText(a) });
  }
  return t;
}

// --- Hauptweg ------------------------------------------------------------------

export async function pushToCrm(a, slug) {
  if (!process.env.TWENTY_API_KEY) throw new Error("TWENTY_API_KEY fehlt");
  const log = [];

  const markt = MARKT[a.absatzmarkt] ?? [];
  const vorhanden = first(
    await rest("GET", `/companies?filter=${encodeURIComponent(`projektordner[eq]:"${slug}"`)}`), "companies");

  let companyId;
  if (vorhanden) { companyId = vorhanden.id; log.push("Betrieb war schon da"); }
  else {
    companyId = idOf(await rest("POST", "/companies", {
      name: a.firma,
      projektordner: slug,
      branche: BRANCHE[a.branche] ?? "SONSTIGES",
      address: { addressCity: a.ort ?? "" },
      absatzmarkt: markt,
      zielprofil: zielprofil(markt),
      quelle: QUELLE[a.kontaktweg] ?? "SONSTIGES",
      korrespondenzsprache: SPRACHE[a.sprache] ?? "ANDERE",
      sprachpruefer: "",                 // absichtlich leer: niemand ist benannt (§2.2)
      weitereSprachen: zweitsprache(a) ?? "",
      avv: avvStatus(a),
      wartungsvertrag: "KEINER",
    }));
    log.push("Betrieb angelegt");
  }

  // Ab hier gilt: jeder Schritt sieht erst nach, ob es ihn schon gibt.
  //
  // Der Grund steht in der Nachreichmappe. Bricht der Lauf nach dem Betrieb ab
  // — Docker geht mitten hinein aus, das Netz weg —, dann liegt die Aufnahme
  // wieder in der Warteschlange und der Bot versucht es alle fünf Minuten neu.
  // Ohne diese Abfragen entstünde bei jedem Versuch ein weiterer
  // Ansprechpartner, eine weitere Anfrage, ein weiterer Satz Aufgaben. Über
  // Nacht sind das rund 280 Versuche, und niemand sieht zu — das ist der Preis
  // des Autonomiebetriebs. Der Betrieb selbst war schon immer so abgesichert;
  // alles dahinter war es nicht.
  const personen = (await rest("GET",
    `/people?filter=${encodeURIComponent(`companyId[eq]:"${companyId}"`)}`))?.data?.people ?? [];
  const wunschName = splitName(a.kontakt_name);
  const personVorhanden = personen.find((p) =>
    p.name?.firstName === wunschName.firstName && p.name?.lastName === wunschName.lastName);

  let personId;
  if (personVorhanden) { personId = personVorhanden.id; log.push("Ansprechpartner war schon da"); }
  else {
    personId = idOf(await rest("POST", "/people", {
      name: wunschName,
      emails: { primaryEmail: a.kontakt_mail, additionalEmails: [] },
      ...(a.kontakt_tel ? { phones: { primaryPhoneNumber: a.kontakt_tel } } : {}),
      companyId,
      rolle: a.entscheider === "Ja" ? "ENTSCHEIDER" : "FACHLICH",
      entscheidetUeberGeld: a.entscheider === "Ja",
      anrede: ANREDE[a.anrede] ?? "UNKLAR",
      sprache: SPRACHE[a.sprache] ?? "ANDERE",
      bevorzugterKanal: "MAIL",
    }));
    log.push("Ansprechpartner angelegt");
  }

  const zus = zusagenText(a);
  const anfragen = (await rest("GET",
    `/opportunities?filter=${encodeURIComponent(`companyId[eq]:"${companyId}"`)}`))?.data?.opportunities ?? [];
  const anfrageTitel = anfrageName(a.wunsch);
  const anfrageVorhanden = anfragen.find((o) => o.name === anfrageTitel);

  let opportunityId;
  if (anfrageVorhanden) { opportunityId = anfrageVorhanden.id; log.push("Anfrage war schon da"); }
  else {
    opportunityId = idOf(await rest("POST", "/opportunities", {
      name: anfrageTitel,
      companyId, pointOfContactId: personId,
      stage: "LEAD",
      muendlicheZusagen: zus ? "VORHANDEN" : "KEINE",
      zusagenWoertlich: zus,
      zusagenGeprueft: false,
      stackAbweichung: "KEINE",
      founderFreigabe: zielprofil(markt) === "PASST" ? "NICHT_NOETIG" : "OFFEN",
    }));
    log.push("Anfrage angelegt");
  }

  // Aufgaben über ihre Verknüpfung zum Betrieb, nicht über eine Titelsuche im
  // ganzen CRM: zwei Betriebe können denselben Aufgabentitel tragen.
  const ziele = (await rest("GET",
    `/taskTargets?filter=${encodeURIComponent(`targetCompanyId[eq]:"${companyId}"`)}&limit=200`))
    ?.data?.taskTargets ?? [];
  const bekannteTitel = new Set();
  for (const z of ziele) {
    const t = (await rest("GET", `/tasks/${z.taskId}`))?.data?.task;
    if (t?.title) bekannteTitel.add(t.title);
  }

  let n = 0, uebersprungen = 0;
  for (const t of standardTasks(a, slug)) {
    if (bekannteTitel.has(t.title)) { uebersprungen++; continue; }
    const taskId = idOf(await rest("POST", "/tasks", t));
    await rest("POST", "/taskTargets", { taskId, targetCompanyId: companyId });
    n++;
  }
  log.push(uebersprungen ? `${n} Aufgaben angelegt, ${uebersprungen} waren schon da`
                         : `${n} Aufgaben angelegt`);

  return { companyId, personId, opportunityId, tasks: n, log,
           url: `${BASE()}/object/company/${companyId}` };
}
