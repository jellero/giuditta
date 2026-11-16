# Giuditta Teresa — sito e negozio online

Sito statico + un'unica funzione serverless che crea il pagamento su **Stripe Checkout**.
Niente Shopify, niente database, niente dipendenze npm.

```
catalog.json                 prodotti, prezzi, spedizione  ← si modifica qui
src/assets/                  style.css, cart.js
src/img/                     fotografie
tools/build.py               genera il sito in public/
public/                      sito generato (da pubblicare, non modificare a mano)
netlify/functions/checkout.mjs   POST /api/checkout → sessione Stripe
netlify/shared/order.mjs         validazione carrello e prezzi (lato server)
netlify.toml                 configurazione + redirect 301 dai vecchi indirizzi
```

## Modificare prodotti e prezzi

1. Apri `catalog.json` e cambia `price_cents` (es. 24,00 € = `2400`), testi, `available` (true/false).
2. Esegui `python tools/build.py` — stampa un avviso per tutto ciò che manca.
3. Commit e push: Netlify pubblica in automatico.

Il prezzo pagato è **sempre** quello di `catalog.json` letto dal server: chi manomette il browser non può cambiarlo.

## Prima di andare online (obbligatorio)

- [ ] **Prezzi da confermare**: Gubana 850 g, Strucchi 300 g e 200 g, Mandorlini (non erano visibili su Shopify; ora valori provvisori, segnati `_verificare_prezzo`).
- [ ] **Ingredienti, allergeni e conservazione** per ogni prodotto (`ingredients`, `allergens`, `shelf`): per il Reg. UE 1169/2011 devono essere visibili *prima* dell'acquisto.
- [ ] Rileggere descrizioni dei prodotti (specialmente Mandorlini, Gemme, Cuor di Gubana) e le promesse ai clienti: spedizione lun–mer, biglietto scritto a mano, risposta entro 2 giorni, offerta aziende (logo, più indirizzi, fattura elettronica).
- [ ] Far verificare `Spedizioni e condizioni di vendita` al consulente.
- [ ] Aggiornare l'informativa privacy iubenda (nuovi trattamenti: Stripe, Netlify Forms).
- [ ] Inserire il link Instagram in `tools/build.py` (`BIZ["instagram"]`).

## Messa online (una volta sola)

### 1. Stripe
1. Crea l'account su stripe.com (dati aziendali, IBAN per gli accrediti).
2. *Impostazioni → Metodi di pagamento*: attiva Carte, Apple Pay, Google Pay, **PayPal**, Klarna.
3. *Impostazioni → Email clienti*: attiva la ricevuta per i pagamenti riusciti.
4. *Impostazioni → Notifiche*: attiva l'email per ogni pagamento riuscito (avviso di nuovo ordine). Installa l'app Stripe sul telefono per le notifiche push.
5. *Sviluppatori → Chiavi API*: copia la chiave segreta (`sk_test_…` per le prove).

### 2. Netlify
1. netlify.com → *Add new site → Import from GitHub* → repository `jellero/giuditta`.
2. Le impostazioni di build sono già in `netlify.toml`.
3. *Site configuration → Environment variables*: `STRIPE_SECRET_KEY` = chiave Stripe; `SITE_URL` = `https://www.giudittateresa.com`.
4. *Forms*: attiva il rilevamento dei form e le notifiche email verso info@giudittateresa.com (richieste aziende e contatti).
5. *Domain management*: collega `www.giudittateresa.com` e aggiorna i DNS presso il registrar.

### 3. Prova completa
Con la chiave `sk_test_…` fai un ordine con la carta di prova `4242 4242 4242 4242` (qualsiasi data futura, qualsiasi CVC). Controlla: ordine nel pannello Stripe, email di conferma, pagina /grazie/. Poi sostituisci con la chiave `sk_live_…`.

## Gestire gli ordini

Pannello o app Stripe → *Pagamenti*: per ogni ordine trovi prodotti, indirizzo di spedizione, telefono,
testo del biglietto d'auguri, note di consegna e l'eventuale Partita IVA. Rimborsi con un clic.

**Codici sconto per i social**: *Catalogo prodotti → Coupon* → crea il coupon e un *codice promozionale*
(es. `NATALE10`). Il campo codice compare già nel checkout.

## Anteprima in locale

```
python tools/build.py
python -m http.server 8080 --directory public
```
Il pagamento funziona solo su Netlify (o con `netlify dev`), perché serve la funzione serverless.
