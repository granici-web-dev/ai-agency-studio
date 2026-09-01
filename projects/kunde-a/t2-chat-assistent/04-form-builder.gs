/**
 * Sofa Belle — Chestionar de scop pentru asistentul AI
 *
 * Erzeugt das Google-Formular und eine verknüpfte Antwort-Tabelle.
 *
 * Ausführen:
 *   1. script.google.com → Neues Projekt
 *   2. Diesen Code einfügen, speichern
 *   3. Funktion `createSofaBelleForm` ausführen, Berechtigungen bestätigen
 *   4. Die drei URLs aus dem Ausführungsprotokoll kopieren (Ansicht → Protokoll)
 *
 * Änderungen am Fragebogen: hier ändern und erneut ausführen — es entsteht ein
 * neues Formular. Das alte nicht löschen, solange Antworten darin liegen.
 */

function createSofaBelleForm() {
  var form = FormApp.create('Sofa Belle — asistent AI: chestionar de scop');

  form.setDescription(
    'Am analizat exportul de lead-uri și site-ul, așa că multe întrebări au deja răspuns. ' +
    'Secțiunea A sunt doar confirmări — o parcurgeți în câteva minute. ' +
    'Restul sunt întrebările la care datele nu pot răspunde.\n\n' +
    'Puteți reveni și completa mai târziu: după trimitere primiți un link de editare. ' +
    'Dacă o întrebare nu este clară sau prea tehnică, scrieți-ne și o reformulăm.'
  );

  form.setCollectEmail(true);
  form.setProgressBar(true);
  form.setAllowResponseEdits(true);
  form.setLimitOneResponsePerUser(false);

  // ---------- helpers ----------
  function section(title, desc) {
    var p = form.addPageBreakItem().setTitle(title);
    if (desc) p.setHelpText(desc);
    return p;
  }
  function confirm_(title, help) {
    form.addMultipleChoiceItem()
      .setTitle(title)
      .setHelpText(help ? help + ' — dacă nu este corect, scrieți la „Altele" ce este corect.'
                        : 'Dacă nu este corect, scrieți la „Altele" ce este corect.')
      .setChoiceValues(['Da, este corect'])
      .showOtherOption(true)
      .setRequired(true);
  }
  function para(title, help, required) {
    form.addParagraphTextItem()
      .setTitle(title)
      .setHelpText(help || '')
      .setRequired(!!required);
  }
  function short_(title, help, required) {
    form.addTextItem()
      .setTitle(title)
      .setHelpText(help || '')
      .setRequired(!!required);
  }
  function choice(title, options, help, required) {
    form.addMultipleChoiceItem()
      .setTitle(title)
      .setHelpText(help || '')
      .setChoiceValues(options)
      .showOtherOption(true)
      .setRequired(!!required);
  }

  // ---------- A · Confirmări ----------
  section('A · Confirmări',
    'Acestea sunt lucrurile pe care le-am dedus din datele dumneavoastră. Bifați dacă este corect.');

  confirm_('Primiți în medie aproximativ 13 solicitări noi pe zi lucrătoare.');
  confirm_('Repartizarea pe surse: Showroom 35 %, Site 22 %, Telefon 15 %, E-mail 13 %, WhatsApp 12 %.',
    'Facebook și Instagram aproape că nu apar ca mesaje directe — reclamele Meta aduc trafic pe site, iar conversația începe apoi pe site sau pe WhatsApp.');
  confirm_('Aproximativ jumătate dintre solicitări nu ajung niciodată la o ofertă, iar doar aproximativ o treime primesc una.',
    'Motivele care se repetă: buget insuficient, produs nepotrivit, termen de execuție prea lung, contact irelevant.');
  confirm_('Fluxul obișnuit: clientul întreabă de preț sau model → îi trimiteți catalogul, de regulă pe WhatsApp → clientul alege un model → întocmiți oferta → urmează una până la trei reveniri.');
  confirm_('Solicitările sunt preluate de șase colegi din echipa de vânzări.');
  confirm_('Cele mai frecvente motive de refuz sunt termenul de execuție și prețul.');
  confirm_('Lead-urile trec prin MEFI și sunt repartizate pe colegi în funcție de oraș (Brașov, București, Cluj, alt oraș).');

  // ---------- B · Întrebările decisive ----------
  section('B · Cele cinci întrebări care determină proiectul',
    'Dacă apucați să răspundeți doar la o parte, acestea sunt cele importante.');

  para('1. MEFI — există un API? Aveți adresa endpoint-ului și documentația?',
    'Avem deja o cheie de acces, dar nu și adresa la care se conectează. Este cea mai importantă întrebare tehnică: ' +
    'dacă asistentul poate livra lead-uri calificate direct în MEFI, întregul proiect devine mai simplu și mai ieftin ' +
    'pentru dumneavoastră. Dacă nu știți, ne este de folos și numele persoanei care v-a configurat integrarea.', true);

  para('2. Pe site nu apare niciun preț. A fost o decizie asumată?',
    'Aproximativ una din șase solicitări se pierde din cauza bugetului. Un interval de preț pe model ar filtra aceste ' +
    'cazuri înainte să ajungă la un coleg din vânzări. Ne interesează motivul, nu doar răspunsul da/nu.', true);

  para('3. „Când este gata comanda mea?" — unde se află statusul producției astăzi?',
    'Într-un sistem, într-un fișier, sau trebuie întrebat atelierul de fiecare dată? Cine îl actualizează și cât de ' +
    'curent este? La mobilierul la comandă aceasta este de obicei cea mai frecventă întrebare.', true);

  para('4. Cât de individuală este în realitate o comandă?',
    'Dimensiuni complet libere și orice material, sau o alegere dintr-un set definit de modele, dimensiuni și ' +
    'materiale? Această întrebare decide cum trebuie formulate răspunsurile privind dreptul de retur, așa că vă ' +
    'rugăm să fiți cât se poate de exact.', true);

  para('5. Ne puteți exporta conversațiile propriu-zise cu clienții (e-mail, WhatsApp), nu doar fișele de lead?',
    'Avem nevoie doar de întrebări și răspunsuri. Numele, numerele de telefon și adresele le eliminăm înainte de ' +
    'analiză. Nu încărcați nimic aici în formular — stabilim împreună un canal potrivit. Scrieți doar dacă este ' +
    'posibil și în ce format.', true);

  // ---------- C · Volumul de muncă ----------
  section('C · Volumul de muncă', 'Fără aceste cifre nu putem demonstra mai târziu ce s-a schimbat.');

  short_('Cât timp pe zi îi ia unui coleg răspunsul la solicitări?',
    'O estimare este suficientă — „aproximativ două ore" este util, „mult" nu.', true);
  para('Care este programul de lucru și ce se întâmplă cu un mesaj primit în afara lui?', '', true);
  short_('Folosiți deja un instrument care adună mesajele din mai multe canale într-un singur loc?',
    'De exemplu Meta Business Suite, un CRM, un sistem de tichete. Dacă da, care?');
  choice('Pe care canal v-ar ajuta cel mai mult dacă un asistent ar prelua prima parte a discuției?',
    ['Site', 'WhatsApp', 'E-mail', 'Telefon'], '', true);

  // ---------- D · Întrebările clienților ----------
  section('D · Ce întreabă clienții');

  para('Care sunt cele 10–15 întrebări care se repetă cel mai des?',
    'Vă rugăm să le formulați așa cum le scriu clienții, nu rezumat. Aceasta este cea mai valoroasă informație din ' +
    'tot chestionarul — din ea se construiește baza de cunoștințe a asistentului. Câte una pe rând.', true);
  para('Care dintre ele par simple, dar sunt riscante dacă primesc un răspuns greșit?', '', true);
  para('Ce nu răspundeți niciodată în scris, ci doar la telefon sau în showroom?');
  para('Există deja ceva scris — întrebări frecvente, listă de prețuri, catalog de materiale, răspunsuri standard?',
    'Orice, chiar și sub formă de notițe interne.');

  // ---------- E · Clienți, predare, juridic ----------
  section('E · Clienți, predare și partea juridică');

  choice('Cine cumpără?',
    ['În principal persoane fizice', 'În principal firme (designeri, hoteluri, birouri, revânzători)', 'Ambele, aproximativ în egală măsură'],
    'Dacă sunt ambele, scrieți la „Altele" proporția aproximativă.', true);
  para('Când asistentul nu poate ajuta, unde trebuie să ajungă conversația și la cine anume?',
    'Inclusiv în afara programului de lucru.', true);
  para('Ce nu trebuie să facă niciodată singur?',
    'De exemplu: reduceri, promisiuni de livrare, modificări la o comandă existentă, consultanță privind dimensiunile.', true);
  choice('Vindeți și către consumatori din Marea Britanie? (showroom Ramsgate)',
    ['Da', 'Nu', 'Nu știu sigur'],
    'Acolo se aplică alte reguli de protecție a consumatorului decât în Uniunea Europeană, iar asistentul va trebui ' +
    'să trateze diferit solicitările de acolo.', true);
  para('De unde provin Termenii și condițiile, politica de retur și politica de confidențialitate?',
    'De la un avocat, de la un furnizor specializat cu serviciu de actualizare, sau au fost redactate intern?', true);
  choice('Politica de confidențialitate menționează deja comunicarea prin WhatsApp?',
    ['Da', 'Nu', 'Nu știu'], '', true);

  // ---------- Antwort-Tabelle ----------
  var ss = SpreadsheetApp.create('Sofa Belle — răspunsuri chestionar');
  form.setDestination(FormApp.DestinationType.SPREADSHEET, ss.getId());

  Logger.log('FORMULAR (link de trimis):  ' + form.getPublishedUrl());
  Logger.log('EDITARE formular:           ' + form.getEditUrl());
  Logger.log('TABEL răspunsuri:           ' + ss.getUrl());
}
