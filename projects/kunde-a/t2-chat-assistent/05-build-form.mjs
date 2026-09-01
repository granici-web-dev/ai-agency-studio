/**
 * Baut das Google-Formular für Sofa Belle.
 *
 * Ausführen:  node projects/kunde-a/t2-chat-assistent/05-build-form.mjs
 * Erneut ausführen erzeugt ein NEUES Formular — das alte nicht löschen,
 * solange Antworten darin liegen.
 *
 * Fachlicher Hintergrund der einzelnen Fragen: 02-chestionar-ro.md
 */
import { api } from '../../../mcp-servers/google-forms/auth.js';
import { toCreateItem } from '../../../mcp-servers/google-forms/items.js';

const FORMS = 'https://forms.googleapis.com/v1/forms';
const DRIVE = 'https://www.googleapis.com/drive/v3/files';

const TITLE = 'Sofa Belle — asistent AI: chestionar de scop';

const DESCRIPTION =
  'Am analizat exportul de lead-uri și site-ul, așa că multe întrebări au deja răspuns. ' +
  'Prima secțiune sunt doar confirmări — o parcurgeți în câteva minute. Restul sunt întrebările ' +
  'la care datele nu pot răspunde.\n\n' +
  'Nu trebuie completat dintr-o dată: după trimitere primiți un link cu care puteți reveni și ' +
  'edita răspunsurile. Ne este de ajutor și un răspuns parțial.\n\n' +
  'Dacă o întrebare nu este clară sau prea tehnică, scrieți-ne și o reformulăm.';

const CONFIRM = (title, description) => ({
  type: 'CHOICE',
  title,
  description: (description ? description + ' — ' : '') +
    'Dacă nu este corect, alegeți „Altele" și scrieți ce este corect.',
  options: ['Da, este corect'],
  other: true,
  required: true,
});

const ITEMS = [
  // ---------------- A ----------------
  { type: 'SECTION', title: 'A · Confirmări',
    description: 'Lucrurile pe care le-am dedus din datele dumneavoastră.' },

  CONFIRM('Primiți în medie aproximativ 13 solicitări noi pe zi lucrătoare.'),
  CONFIRM('Repartizarea pe surse: Showroom 35 %, Site 22 %, Telefon 15 %, E-mail 13 %, WhatsApp 12 %.',
    'Facebook și Instagram aproape că nu apar ca mesaje directe; reclamele Meta aduc trafic pe site, iar conversația începe apoi pe site sau pe WhatsApp'),
  CONFIRM('Aproximativ jumătate dintre solicitări nu ajung niciodată la o ofertă, iar doar aproximativ o treime primesc una.',
    'Motivele care se repetă: buget insuficient, produs nepotrivit, termen de execuție prea lung, contact irelevant'),
  CONFIRM('Fluxul obișnuit: clientul întreabă de preț sau model, îi trimiteți catalogul (de regulă pe WhatsApp), clientul alege un model, întocmiți oferta, urmează una până la trei reveniri.'),
  CONFIRM('Solicitările sunt preluate de șase colegi din echipa de vânzări.'),
  CONFIRM('Cele mai frecvente motive de refuz sunt termenul de execuție și prețul.'),
  CONFIRM('Lead-urile trec prin MEFI și sunt repartizate pe colegi în funcție de oraș (Brașov, București, Cluj, alt oraș).'),

  // ---------------- B ----------------
  { type: 'SECTION', title: 'B · Cele cinci întrebări care determină proiectul',
    description: 'Dacă apucați să răspundeți doar la o parte, acestea sunt cele importante.' },

  { type: 'PARAGRAPH', required: true,
    title: 'MEFI — există un API? Aveți adresa endpoint-ului și documentația?',
    description:
      'Avem deja o cheie de acces, dar nu și adresa la care se conectează. Este cea mai importantă ' +
      'întrebare tehnică: dacă asistentul poate livra lead-uri calificate direct în MEFI, întregul ' +
      'proiect devine mai simplu și mai ieftin pentru dumneavoastră. Dacă nu știți, ne este de folos ' +
      'și numele persoanei care v-a configurat integrarea.' },

  { type: 'PARAGRAPH', required: true,
    title: 'Pe site nu apare niciun preț. A fost o decizie asumată?',
    description:
      'Aproximativ una din șase solicitări se pierde din cauza bugetului. Un interval de preț pe model ' +
      'ar filtra aceste cazuri înainte să ajungă la un coleg din vânzări. Ne interesează motivul, nu ' +
      'doar răspunsul da/nu.' },

  { type: 'PARAGRAPH', required: true,
    title: '„Când este gata comanda mea?" — unde se află statusul producției astăzi?',
    description:
      'Într-un sistem, într-un fișier, sau trebuie întrebat atelierul de fiecare dată? Cine îl ' +
      'actualizează și cât de curent este? La mobilierul la comandă aceasta este de obicei cea mai ' +
      'frecventă întrebare.' },

  { type: 'PARAGRAPH', required: true,
    title: 'Cât de individuală este în realitate o comandă?',
    description:
      'Dimensiuni complet libere și orice material, sau o alegere dintr-un set definit de modele, ' +
      'dimensiuni și materiale? Această întrebare decide cum trebuie formulate răspunsurile privind ' +
      'dreptul de retur, așa că vă rugăm să fiți cât se poate de exact.' },

  { type: 'PARAGRAPH', required: true,
    title: 'Ne puteți exporta conversațiile propriu-zise cu clienții (e-mail, WhatsApp), nu doar fișele de lead?',
    description:
      'Avem nevoie doar de întrebări și răspunsuri. Numele, numerele de telefon și adresele le eliminăm ' +
      'înainte de analiză. Nu încărcați nimic aici — scrieți doar dacă este posibil și în ce format, ' +
      'canalul de transfer îl stabilim separat.' },

  // ---------------- C ----------------
  { type: 'SECTION', title: 'C · Volumul de muncă',
    description: 'Fără aceste cifre nu putem demonstra mai târziu ce s-a schimbat.' },

  { type: 'TEXT', required: true,
    title: 'Cât timp pe zi îi ia unui coleg răspunsul la solicitări?',
    description: 'O estimare este suficientă — „aproximativ două ore" este util, „mult" nu.' },
  { type: 'PARAGRAPH', required: true,
    title: 'Care este programul de lucru și ce se întâmplă cu un mesaj primit în afara lui?' },
  { type: 'TEXT',
    title: 'Folosiți deja un instrument care adună mesajele din mai multe canale într-un singur loc?',
    description: 'De exemplu Meta Business Suite, un CRM, un sistem de tichete. Dacă da, care?' },
  { type: 'CHOICE', required: true, other: true,
    title: 'Pe care canal v-ar ajuta cel mai mult dacă un asistent ar prelua prima parte a discuției?',
    options: ['Site', 'WhatsApp', 'E-mail', 'Telefon'] },

  // ---------------- D ----------------
  { type: 'SECTION', title: 'D · Ce întreabă clienții' },

  { type: 'PARAGRAPH', required: true,
    title: 'Care sunt cele 10–15 întrebări care se repetă cel mai des?',
    description:
      'Vă rugăm să le formulați așa cum le scriu clienții, nu rezumat. Aceasta este cea mai valoroasă ' +
      'informație din tot chestionarul — din ea se construiește baza de cunoștințe a asistentului. ' +
      'Câte una pe rând.' },
  { type: 'PARAGRAPH', required: true,
    title: 'Care dintre ele par simple, dar sunt riscante dacă primesc un răspuns greșit?' },
  { type: 'PARAGRAPH',
    title: 'Ce nu răspundeți niciodată în scris, ci doar la telefon sau în showroom?' },
  { type: 'PARAGRAPH',
    title: 'Există deja ceva scris — întrebări frecvente, listă de prețuri, catalog de materiale, răspunsuri standard?',
    description: 'Orice, chiar și sub formă de notițe interne.' },

  // ---------------- E ----------------
  { type: 'SECTION', title: 'E · Clienți, predare și partea juridică' },

  { type: 'CHOICE', required: true, other: true,
    title: 'Cine cumpără?',
    description: 'Dacă sunt ambele, alegeți „Altele" și scrieți proporția aproximativă.',
    options: ['În principal persoane fizice',
              'În principal firme (designeri, hoteluri, birouri, revânzători)',
              'Ambele, aproximativ în egală măsură'] },
  { type: 'PARAGRAPH', required: true,
    title: 'Când asistentul nu poate ajuta, unde trebuie să ajungă conversația și la cine anume?',
    description: 'Inclusiv în afara programului de lucru.' },
  { type: 'PARAGRAPH', required: true,
    title: 'Ce nu trebuie să facă niciodată singur?',
    description: 'De exemplu: reduceri, promisiuni de livrare, modificări la o comandă existentă, consultanță privind dimensiunile.' },
  { type: 'CHOICE', required: true,
    title: 'Vindeți și către consumatori din Marea Britanie? (showroom Ramsgate)',
    description: 'Acolo se aplică alte reguli de protecție a consumatorului decât în Uniunea Europeană, iar asistentul va trebui să trateze diferit solicitările de acolo.',
    options: ['Da', 'Nu', 'Nu știu sigur'] },
  { type: 'PARAGRAPH', required: true,
    title: 'De unde provin Termenii și condițiile, politica de retur și politica de confidențialitate?',
    description: 'De la un avocat, de la un furnizor specializat cu serviciu de actualizare, sau au fost redactate intern?' },
  { type: 'CHOICE', required: true,
    title: 'Politica de confidențialitate menționează deja comunicarea prin WhatsApp?',
    options: ['Da', 'Nu', 'Nu știu'] },
];

/* ------------------------------------------------------------------------- */

const form = await api('POST', FORMS, { info: { title: TITLE, documentTitle: TITLE } });
console.log('Formular angelegt:', form.formId);

await api('POST', `${FORMS}/${form.formId}:batchUpdate`, {
  requests: [{ updateFormInfo: { info: { description: DESCRIPTION }, updateMask: 'description' } }],
});
console.log('Beschreibung gesetzt.');

// Antwortende identifizieren — liefert nebenbei die E-Mail-Adresse
await api('POST', `${FORMS}/${form.formId}:batchUpdate`, {
  requests: [{
    updateSettings: {
      settings: { emailCollectionType: 'RESPONDER_INPUT' },
      updateMask: 'emailCollectionType',
    },
  }],
}).catch((e) => console.log('Hinweis: E-Mail-Erfassung nicht gesetzt —', String(e.message).split('\n')[0]));

const requests = ITEMS.map((spec, i) => toCreateItem(spec, i));
await api('POST', `${FORMS}/${form.formId}:batchUpdate`, { requests });
console.log(`${requests.length} Elemente eingefügt.`);

await api('POST', `${FORMS}/${form.formId}:setPublishSettings`, {
  publishSettings: { publishState: { isPublished: true, isAcceptingResponses: true } },
});
console.log('Veröffentlicht.');

const final = await api('GET', `${FORMS}/${form.formId}`);

console.log('\n---');
console.log('LINK FÜR DEN KUNDEN:', final.responderUri);
console.log('BEARBEITEN:          https://docs.google.com/forms/d/' + form.formId + '/edit');
console.log('formId:              ' + form.formId);
console.log('Elemente im Formular:', (final.items || []).length);

export { DRIVE };
