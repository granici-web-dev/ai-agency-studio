# Mail 01 an den Vertriebsleiter — versandfertig

Spiegelt den Gmail-Entwurf `r-1440033116825949714`. Bei Änderungen **beides** aktualisieren.

**Absender:** `granici.design@gmail.com` (Founder-Entscheidung 2026-08-14)
**Empfänger:** Iordache Razvan — [[E-MAIL FEHLT NOCH]], im Entwurf steht die Testadresse
**Sprache:** Rumänisch (Founder-Entscheidung 2026-08-14)
**Kanal:** ausschließlich E-Mail, kein Telefontermin (Founder-Entscheidung)
**Versand:** durch den Menschen (CLAUDE.md §2.5)

**Fragebogen:** als Google-Formular, nicht als Anlage.
Antwortlink: `https://docs.google.com/forms/d/e/1FAIpQLSezaSU9LnRTpAQEhPRZlkiVPc_bGmm_Jypv1L62kAs74JMf2w/viewform`
Bearbeiten: `https://docs.google.com/forms/d/1osPQL6T67iSKuepxEVhNTHa8R0RH6dvzteZ0KFnks-4/edit`
Aufgebaut von `05-build-form.mjs`, Inhalt fachlich in `02-chestionar-ro.md`.

**Sprachprüfung:** ohne muttersprachliche Gegenlesung. Bewusste Entscheidung des Founders,
hier protokolliert, damit später nachvollziehbar ist, warum kein Prüfvermerk vorliegt.

---

**Subiect:** `Sofa Belle — asistent AI: câteva întrebări înainte de a stabili scopul`

---

Stimate domnule Iordache,

Serghei m-a rugat să preiau coordonarea proiectului pentru asistentul AI, așa că de acum înainte
eu voi fi persoana dumneavoastră de contact.

Înainte să stabilim scopul și prețul, am analizat exportul de lead-uri pe care ni l-ați pus la
dispoziție și am parcurs site-ul. O bună parte din ce aveam nevoie este deja acoperită de acestea,
așa că în loc de o listă lungă de întrebări prin e-mail am pregătit un chestionar scurt:

https://docs.google.com/forms/d/e/1FAIpQLSezaSU9LnRTpAQEhPRZlkiVPc_bGmm_Jypv1L62kAs74JMf2w/viewform

Prima secțiune sunt doar confirmări ale lucrurilor pe care le-am dedus din datele dumneavoastră —
o parcurgeți în câteva minute. Restul sunt întrebările la care datele nu pot răspunde.

Ca să știți de unde pornim: aproximativ 13 solicitări noi pe zi lucrătoare, venite în principal
prin showroom-uri, site, telefon, e-mail și WhatsApp. Aproximativ jumătate dintre ele nu ajung
niciodată la o ofertă — buget, produs nepotrivit, termen de execuție sau contact irelevant. Exact
aici credem că un asistent poate prelua o parte reală din munca echipei, iar asta este altceva
decât simplul răspuns la întrebări care se repetă.

Nu trebuie completat dintr-o dată — după trimitere primiți un link cu care puteți reveni și edita
răspunsurile. Ne este de ajutor și un răspuns parțial.

Dacă vreo întrebare nu este clară sau formulată prea tehnic, scrieți-mi și o reformulez.

Cu stimă,
Alex

—
[!] SEMNĂTURĂ LIPSĂ — de completat înainte de trimitere

---

## Interne Anmerkungen

- **Keine Zahl, kein Termin, keine Zusage.** Bewusst — es wurde bisher nichts zugesagt, und dieser
  Zustand wird bis zum Angebot gehalten (`entscheidungen.md`).
- Der vierte Absatz ist der wichtigste: er gibt ihm seine eigenen Zahlen zurück und zeigt, dass wir
  gearbeitet haben, bevor wir gefragt haben. Das unterscheidet diese Mail von jeder anderen
  Agenturmail in seinem Postfach.
- Die fünf entscheidenden Fragen stehen jetzt **im Formular**, nicht mehr in der Mail
  (Founder-Entscheidung). Der Kontext, der sonst im Mailtext gestanden hätte, liegt als Hilfetext
  unter den jeweiligen Feldern — ohne ihn kippen die Antworten ins Einsilbige.
- **Kein Telefonat angeboten.** Ersatz ist der Schlusssatz: er darf zurückfragen. Ohne dieses
  Ventil wirkt ein reiner Fragenkatalog abweisend.
- Das Formular erfasst die E-Mail-Adresse des Antwortenden — damit bekommen wir nebenbei den
  Kontakt, der uns bisher fehlt.
- Zweite Mail an den **Inhaber** (Maxim Ciornii), auf Russisch, über den Founder: nur Kurzfassung
  des Stands und die eine Entscheidung, die ihm gehört — Preisspannen. Getrennt, weil er nicht in
  einem Fachfragebogen ertrinken soll. Noch nicht entworfen.

## Geklärt: „Redirect Notice" beim Formularlink (2026-08-14)

Beim Testen erschien nach dem Klick auf den Formularlink eine Google-Zwischenseite
„Uведomление о переадресации". Untersucht und **kein echtes Problem**:

- Die Zwischenseite trat nur im **Gmail-Webinterface** auf, und nur bei einer Mail, die an die
  eigene Adresse geschickt wurde. In der Gmail-App und im Mail.ru-Client öffnet der Link direkt.
- Die `google.com/url?q=…`-Umhüllung ist Gmails Darstellung im Browser. Sie steht **nicht** in der
  versendeten Nachricht — ein Abruf über die API liefert die von Gmail gerenderte Fassung, nicht
  das Original. Diese Verwechslung hat hier eine Weile Zeit gekostet.
- **Merke:** E-Mail-Verhalten nie durch Versand an die eigene Adresse prüfen. Der Zustellweg ist
  ein anderer.

Zwei echte Fehler wurden dabei gefunden und behoben: eine CDATA-Klammer landete sichtbar als
`]]>` am Mailende, und die Nur-Text-Fassung wurde teils statt des HTML dargestellt.
**Kundenmails gehen als HTML raus**, mit echtem Anker statt nackter URL.

## Vor dem Versand

- [x] Absender-Name: **Alex**
- [x] Fragebogen als Formular verlinkt
- [ ] **E-Mail-Adresse von Iordache Razvan** — fehlt, blockiert den Versand
- [ ] **Signatur mit Pflichtangaben** — Founder reicht nach (Trello, Liste *Bereit*).
      Bis dahin steht im Entwurf ein sichtbarer Warnhinweis statt einer Signatur, damit die Mail
      nicht versehentlich unvollständig rausgeht.
- [ ] Kein `[!]` und keine `[[…]]` mehr im versendeten Text
