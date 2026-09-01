// Fragenkatalog für den Lead-Bot — Flow A, Schritt A1.
// Diese Datei ist der einzige Ort, an dem Fragen geändert werden. Die Logik in bot.mjs
// kennt keine einzelne Frage.
//
// Sprache: Deutsch, weil der Vertrieb in Deutschland arbeitet.
//
// Feldtypen:
//   text    — freie Antwort
//   choice  — Tastatur mit festen Antworten
//
// followUp greift, wenn die Antwort in `when` steht.
//
// Reihenfolge folgt dem Gespräch, nicht dem Formular: zuerst was der Vertrieb ohnehin
// im Kopf hat, dann das Unangenehme.

export const SKIP = "/skip";

export const QUESTIONS = [
  {
    id: "firma",
    section: 1,
    text: "Wie heißt der Betrieb?",
    type: "text",
    required: true,
  },
  {
    id: "branche",
    section: 1,
    text: "Was für ein Betrieb ist das?",
    type: "choice",
    options: [
      "Friseur",
      "Kosmetik / Nagelstudio",
      "Restaurant / Café",
      "Handwerk",
      "Einzelhandel",
      "Sonstiges",
    ],
    required: true,
  },
  {
    id: "ort",
    section: 1,
    text: "In welcher Stadt?",
    type: "text",
    required: true,
  },
  {
    // CLAUDE.md §1 macht den Absatzmarkt zum Aufnahmekriterium — nicht den Sitz.
    // Ohne diese Frage fällt erst im Angebot auf, dass der Betrieb nicht in unser
    // Profil passt. Genau das ist bei Kunde A passiert.
    id: "absatzmarkt",
    section: 1,
    text:
      "An wen verkauft der Betrieb?\n\n" +
      "Nicht wo er sitzt — wo seine Kunden sind. Ein Friseur bedient seine Stadt, " +
      "ein Hersteller vielleicht das halbe Ausland.",
    type: "choice",
    options: [
      "Nur Deutschland",
      "Deutschland und Nachbarn (AT/CH)",
      "EU, über DACH hinaus",
      "Auch außerhalb der EU",
      "Weiß ich nicht",
    ],
    required: true,
  },
  {
    id: "kontaktweg",
    section: 1,
    text: "Wie ist der Kontakt zustande gekommen?",
    type: "choice",
    options: [
      "Persönlich vor Ort",
      "Empfehlung",
      "Der Betrieb hat sich gemeldet",
      "Anders",
    ],
    required: true,
  },

  // --- Ansprechperson -------------------------------------------------------
  {
    id: "kontakt_name",
    section: 4,
    text: "Name der Ansprechperson — Vor- und Nachname, so wie sie angeschrieben werden will.",
    type: "text",
    required: true,
  },
  {
    id: "kontakt_mail",
    section: 8,
    text: "E-Mail-Adresse dieser Person.",
    type: "text",
    required: true,
    validate: (v) => (/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim())
      ? null
      : "Das sieht nicht nach einer E-Mail-Adresse aus. Nochmal bitte."),
  },
  {
    id: "kontakt_tel",
    section: 8,
    text: "Telefonnummer, falls du sie hast. Sonst /skip.",
    type: "text",
    required: false,
  },
  {
    id: "anrede",
    section: 8,
    text: "Sie oder du?",
    type: "choice",
    options: ["Sie", "Du", "Weiß ich nicht"],
    required: true,
  },
  {
    id: "entscheider",
    section: 4,
    text: "Entscheidet diese Person auch über Auftrag und Geld?",
    type: "choice",
    options: ["Ja", "Nein", "Weiß ich nicht"],
    required: true,
    followUp: {
      when: ["Nein"],
      question: {
        id: "entscheider_wer",
        section: 4,
        text: "Wer entscheidet dann? Name und Rolle.",
        type: "text",
        required: true,
      },
    },
  },

  // --- Der Auftrag ----------------------------------------------------------
  {
    id: "wunsch",
    section: 2,
    text:
      "Was will der Betrieb? Bitte in seinen Worten, nicht in unseren.\n\n" +
      "Lieber der halbe Satz, den er gesagt hat, als eine saubere Übersetzung davon.",
    type: "text",
    required: true,
  },
  {
    id: "bestand",
    section: 6,
    text: "Was läuft heute schon?",
    type: "choice",
    options: [
      "Nichts — Telefon und Papier",
      "Website ohne Buchung",
      "Website mit Buchung",
      "Nur Instagram / WhatsApp",
      "Plattform (Treatwell, Booksy …)",
      "Weiß ich nicht",
    ],
    required: true,
  },

  // --- Der kritische Block: was wurde schon gesagt --------------------------
  // Flow A steht und fällt hiermit. Deshalb drei getrennte Fragen statt einer:
  // auf "Hast du etwas zugesagt?" antwortet jeder Vertrieb mit Nein.
  {
    id: "zugesagt_preis",
    section: 3,
    critical: true,
    text:
      "Jetzt der wichtige Teil.\n\n" +
      "Ist eine Zahl gefallen? Auch ungefähr, auch als Spanne, auch nur „das wird nicht teuer\".",
    type: "choice",
    options: ["Nein, keine Zahl", "Ja"],
    required: true,
    followUp: {
      when: ["Ja"],
      question: {
        id: "zugesagt_preis_was",
        section: 3,
        critical: true,
        text: "Welche Zahl genau, und in welchem Zusammenhang?",
        type: "text",
        required: true,
      },
    },
  },
  {
    id: "zugesagt_termin",
    section: 3,
    critical: true,
    text: "Ist ein Termin gefallen? „Bis zum Sommer\", „in ein paar Wochen\" zählt auch.",
    type: "choice",
    options: ["Nein, kein Termin", "Ja"],
    required: true,
    followUp: {
      when: ["Ja"],
      question: {
        id: "zugesagt_termin_was",
        section: 3,
        critical: true,
        text: "Welcher Termin, und wie verbindlich klang das?",
        type: "text",
        required: true,
      },
    },
  },
  {
    id: "zugesagt_sonst",
    section: 3,
    critical: true,
    text:
      "Hast du etwas zugesagt oder angedeutet, dass es geht?\n\n" +
      "„Telegram ist kein Problem\", „das machen wir mit\", „das ist schnell gemacht\".",
    type: "choice",
    options: ["Nein", "Ja"],
    required: true,
    followUp: {
      when: ["Ja"],
      question: {
        id: "zugesagt_sonst_was",
        section: 3,
        critical: true,
        text: "Was genau? So wörtlich wie du es erinnerst.",
        type: "text",
        required: true,
      },
    },
  },

  // --- Rest -----------------------------------------------------------------
  {
    id: "dringlichkeit",
    section: 5,
    text: "Wie eilig ist es wirklich — und woran liegt das? /skip wenn unklar.",
    type: "text",
    required: false,
  },
  {
    id: "hintergrund",
    section: 6,
    text:
      "Weißt du etwas, das der Betrieb selbst nicht gesagt hat?\n\n" +
      "Ärger mit der letzten Agentur, ein Konkurrent nebenan, Familienbetrieb mit zwei Meinungen. /skip wenn nichts.",
    type: "text",
    required: false,
  },
  {
    id: "tabu",
    section: 9,
    text: "Gibt es etwas, das wir nicht anfassen sollen? /skip wenn nichts.",
    type: "text",
    required: false,
  },
  {
    id: "sprache",
    section: 7,
    text: "Sprache der Korrespondenz?",
    type: "choice",
    options: ["Deutsch", "Deutsch + eine weitere", "Andere"],
    required: true,
    followUp: {
      when: ["Deutsch + eine weitere", "Andere"],
      question: {
        id: "sprache_welche",
        section: 7,
        text: "Welche Sprache? Und weißt du, wer sie bei uns gegenlesen könnte?",
        type: "text",
        required: true,
      },
    },
  },
];

// Sektionsüberschriften des Handoffs (Flow A, Schritt A1)
export const SECTIONS = {
  1: "1 · Wer ist es?",
  2: "2 · Was wurde angefragt?",
  3: "3 · Was wurde bereits zugesagt?",
  4: "4 · Wer entscheidet, wer zahlt?",
  5: "5 · Dringlichkeit",
  6: "6 · Was wissen wir, das der Kunde nicht gesagt hat?",
  7: "7 · Sprache",
  8: "8 · Kontaktweg und Ton",
  9: "9 · Was sollen wir nicht anfassen?",
};
