# Giuditta Teresa — sito e negozio online (PHP)

Sito in **PHP semplice**, senza database e senza Shopify. Funziona su qualsiasi hosting
con PHP 7.4 o superiore (Aruba, Register, SiteGround…), anche dentro una sottocartella.
I pagamenti passano da **Stripe Checkout**.

```
demo/                         ← tutto il sito: si carica così com'è sull'hosting
  index.php, shop.php, prodotto.php?p=…, consegna-in-friuli.php, ristorazione.php,
  aziende.php, la-nostra-storia.php, premi-e-stampa.php, contatti.php,
  condizioni-di-vendita.php, grazie.php, grazie-richiesta.php, 404.php, sitemap.php
  checkout.php                ← crea il pagamento Stripe (ricalcola prezzi e consegna)
  invia.php                   ← riceve i moduli: email + copia in inc/dati/*.csv
  assets/                     ← style.css, cart.js
  img/                        ← fotografie
  inc/                        ← NON accessibile dal web (.htaccess)
    catalog.json              ← prodotti, prezzi, spedizione, zone   ← si modifica qui
    config.php                ← dati azienda, DEMO on/off, email che riceve i moduli
    segreti.esempio.php       ← modello per la chiave Stripe
    order.php, layout.php, content.php
```

## 1. Mettere online la demo (senza toccare il sito attuale)

1. Con FTP (FileZilla) o il file manager dell'hosting, carica la cartella `demo/` nella
   cartella principale del sito (di solito `public_html/` o `www/`).
2. Apri **https://www.giudittateresa.com/demo/** — il sito vecchio resta dov'è.
3. In modalità demo (`DEMO = true` in `inc/config.php`) le pagine non vengono indicizzate da
   Google e in alto compare la fascia "Anteprima".

Senza chiave Stripe tutto funziona tranne il pagamento, che mostra "Demo: pagamento non collegato".

## 2. Collegare Stripe

1. Crea l'account su stripe.com (dati aziendali, IBAN).
2. *Impostazioni → Metodi di pagamento*: attiva Carte, Apple Pay, Google Pay, **PayPal**, Klarna.
3. *Impostazioni → Email clienti*: ricevuta per i pagamenti riusciti.
   *Notifiche*: email a ogni pagamento (avviso di nuovo ordine). App Stripe sul telefono.
4. Copia `inc/segreti.esempio.php` come **`inc/segreti.php`** e incolla la chiave `sk_test_…`.
   Caricalo via FTP: **non va mai su GitHub** (è già escluso).
5. Prova un ordine con la carta `4242 4242 4242 4242` (data futura, CVC qualsiasi).
6. Quando tutto è pronto, sostituisci con la chiave `sk_live_…`.

## 3. Quando la demo diventa il sito ufficiale

1. In `inc/config.php` metti `DEMO = false`.
2. Sposta il contenuto di `demo/` nella cartella principale (dopo aver salvato il vecchio sito).
3. Su Google Search Console indica la mappa: `https://www.giudittateresa.com/sitemap.php`.
4. Aggiungi nel `.htaccess` principale i redirect dai vecchi indirizzi Drupal, per non perdere
   posizionamento:
   ```
   RewriteEngine On
   RewriteRule ^Chi%20siamo$ /la-nostra-storia.php [R=301,L,NE]
   RewriteRule ^lavorazione$ /la-nostra-storia.php [R=301,L]
   RewriteRule ^prodotti$ /shop.php [R=301,L]
   RewriteRule ^rassegna$ /premi-e-stampa.php [R=301,L]
   RewriteRule ^blog/ /premi-e-stampa.php [R=301,L]
   RewriteRule ^contatti$ /contatti.php [R=301,L]
   ```

## Modificare prodotti, prezzi, zone

Apri `demo/inc/catalog.json` con un editor di testo (es. Notepad++), salva, ricarica via FTP.
Nessuna compilazione: la modifica è subito online.

- Prezzi in centesimi: 24,00 € = `2400`.
- `available`: `true` in vendita, `false` nascosto dal carrello.
- `local_only: true`: dolce fresco, solo consegna in Friuli o ritiro.
- `local_delivery.zones`: CAP e giorni (0 = domenica … 6 = sabato).
- `closed_dates`: festivi e ferie.

Il prezzo pagato è **sempre** quello di `catalog.json`, ricalcolato da `checkout.php`:
chi manomette il browser non può cambiarlo.

## Prima di andare online (obbligatorio)

- [ ] **Prezzi da confermare**: Gubana 850 g, Strucchi 300 g e 200 g, Mandorlini (`_verificare_prezzo`).
- [ ] **Prezzi da inserire**: Tronco, Mini Gubana, Focaccia, Torta Silvana (finché mancano: "su prenotazione").
- [ ] **Ingredienti, allergeni, conservazione** per ogni prodotto (Reg. UE 1169/2011).
- [ ] **Zone, CAP, giorni, costi** della consegna in Friuli; giorni di ritiro; `closed_dates`.
- [ ] Rileggere le promesse: spedizione lun–mer, biglietto a mano, offerta aziende e ristorazione.
- [ ] Far verificare le condizioni di vendita al consulente; aggiornare l'informativa iubenda (Stripe, moduli, newsletter).
- [ ] Link Instagram in `inc/config.php`.

## Gestire ordini e richieste

- **Ordini**: pannello o app Stripe → *Pagamenti*. Per ogni ordine: prodotti, indirizzo, telefono,
  biglietto d'auguri, note e, nei *metadati*, tipo di consegna, data, CAP e zona.
- **Moduli** (ristorazione, aziende, contatti, newsletter): arrivano per email a `EMAIL_ORDINI`
  e sono sempre salvati anche in `inc/dati/*.csv` (scaricabili via FTP, si aprono con Excel).
  Se le email non arrivano, chiedi all'hosting di abilitare la funzione `mail()` di PHP.
- **Codici sconto per i social**: Stripe → *Catalogo prodotti → Coupon* → codice promozionale.

## Provare sul proprio PC

Con PHP installato:
```
php -S localhost:8081 -t demo
```
