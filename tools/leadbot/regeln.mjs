// Ableitungen aus den Antworten des Vertriebs.
//
// Sie stehen hier einmal, weil die Dateien auf der Platte und das CRM dieselben
// Schlüsse brauchen. Eine Regel an zwei Orten ist nach dem zweiten Umbau an einem
// der beiden Orte falsch — und niemand merkt, an welchem.

// --- Was läuft heute schon ---------------------------------------------------

// „Nichts" ist eine Antwort, kein fehlender Wert. Der Unterschied ist teuer: der
// Bot hat für Sunshine Zugänge zu einem Bestandssystem erfragt, das es nicht gibt.
// Der Kunde liest das als „die haben nicht zugehört".
export function bestandsart(bestand) {
  const b = bestand || "";
  if (/^Nichts/i.test(b)) return "KEINER";
  if (/Plattform/i.test(b)) return "PLATTFORM";
  if (/Instagram|WhatsApp/i.test(b)) return "KANAELE";
  if (/Website/i.test(b)) return "WEBSITE";
  return "UNBEKANNT";
}

// Wonach genau gefragt wird, hängt davon ab, was es gibt. Bei UNBEKANNT fragen wir
// nicht nach Zugängen, sondern erst danach, ob überhaupt etwas existiert.
export const ZUGANG = {
  KEINER: null,
  WEBSITE: "Adresse der bestehenden Website und Lesezugang",
  PLATTFORM: "Zugang zur genutzten Plattform und Export der bestehenden Termine",
  KANAELE: "Zugang zu den genutzten Kanälen (Instagram, WhatsApp Business)",
  UNBEKANNT: "Was läuft heute tatsächlich — Website, Plattform, Kanäle? Und wer hat die Zugänge?",
};

// --- Auftragsverarbeitung ----------------------------------------------------

// Art. 28 DSGVO: sobald wir im Auftrag des Betriebs personenbezogene Daten seiner
// Kunden verarbeiten, braucht es einen AVV. CLAUDE.md §2.7 macht daraus eine harte
// Schranke — ohne AVV lagern wir die Daten und tun nichts damit.
//
// Ein Buchungssystem ist genau dieser Fall: Name, Telefon, Termin, teils Anlass.
const AVV_ANLASS = [
  [/buchung|bucht|termin|reservier|kalender|anmeld/i, "Termin- oder Buchungsdaten"],
  [/newsletter|mailing|e-?mail-?marketing|verteiler/i, "Newsletter-Verteiler"],
  [/chatbot|assistent|\bki\b|\bai\b|sprachassist/i, "KI-Assistent im Kundendialog"],
  [/crm|kundendaten|kundenliste|bestandskunden|migration|import/i, "Übernahme bestehender Kundendaten"],
  [/shop|bestell|warenkorb|zahlung/i, "Bestell- und Zahlungsdaten"],
];

// Gelesen wird der Wunsch in den Worten des Betriebs — dort steht „Buchungsfunktion",
// nicht in unserer Paketbezeichnung.
export function avvAnlass(a) {
  const t = `${a.wunsch || ""} ${a.hintergrund || ""} ${a.dringlichkeit || ""}`;
  return AVV_ANLASS.filter(([re]) => re.test(t)).map(([, label]) => label);
}

// Drei Werte, nicht zwei — dieselbe Überlegung wie bei den mündlichen Zusagen:
// „nicht nötig" ist eine Feststellung, die jemand getroffen hat. Der Bot trifft sie
// nicht. Er sagt entweder „hier liegt ein Anlass vor" oder „das hat niemand geprüft".
export function avvStatus(a) {
  return avvAnlass(a).length ? "OFFEN" : "UNGEPRUEFT";
}

// --- Sprache -----------------------------------------------------------------

// §2.2: jede ausgelieferte Sprache braucht einen benannten Prüfer. Bisher ist die
// zweite Sprache zwischen Handoff und CRM verschwunden — im Handoff stand
// „Deutsch + eine weitere: английский", im CRM stand DE und sonst nichts.
export function zweitsprache(a) {
  if (a.sprache === "Deutsch") return null;
  return (a.sprache_welche || "").trim() || "unbenannt";
}
