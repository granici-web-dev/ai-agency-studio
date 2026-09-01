/**
 * Item-Spezifikation → Forms-API `createItem`.
 * Geteilt zwischen MCP-Server und Aufbau-Skripten.
 */
export const ITEM_TYPES = [
  'SECTION',   // page break
  'TEXT',      // short answer
  'PARAGRAPH', // long answer
  'CHOICE',    // radio
  'CHECKBOX',
  'DROPDOWN',
  'SCALE',
  'DATE',
  'INFO',      // text block, no answer
];

/** Turn one flat item spec into a Forms API `createItem` request. */
export function toCreateItem(spec, index) {
  const type = String(spec.type || 'PARAGRAPH').toUpperCase();
  if (!ITEM_TYPES.includes(type)) {
    throw new Error(`Unknown item type "${spec.type}". Allowed: ${ITEM_TYPES.join(', ')}`);
  }
  if (!spec.title) throw new Error(`Item at position ${index} has no title.`);

  const item = { title: String(spec.title) };
  if (spec.description) item.description = String(spec.description);

  if (type === 'SECTION') {
    item.pageBreakItem = {};
  } else if (type === 'INFO') {
    item.textItem = {};
  } else {
    const question = { required: !!spec.required };

    if (type === 'TEXT' || type === 'PARAGRAPH') {
      question.textQuestion = { paragraph: type === 'PARAGRAPH' };
    } else if (type === 'CHOICE' || type === 'CHECKBOX' || type === 'DROPDOWN') {
      const options = spec.options || [];
      if (!options.length) throw new Error(`"${spec.title}": ${type} needs options.`);
      question.choiceQuestion = {
        type: type === 'CHOICE' ? 'RADIO' : type === 'CHECKBOX' ? 'CHECKBOX' : 'DROP_DOWN',
        options: [
          ...options.map((v) => ({ value: String(v) })),
          ...(spec.other ? [{ isOther: true }] : []),
        ],
        shuffle: false,
      };
    } else if (type === 'SCALE') {
      question.scaleQuestion = {
        low: spec.low ?? 1,
        high: spec.high ?? 5,
        lowLabel: spec.lowLabel,
        highLabel: spec.highLabel,
      };
    } else if (type === 'DATE') {
      question.dateQuestion = { includeYear: spec.includeYear !== false, includeTime: !!spec.includeTime };
    }

    item.questionItem = { question };
  }

  return { createItem: { item, location: { index } } };
}

