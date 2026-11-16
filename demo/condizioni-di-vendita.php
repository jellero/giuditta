<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
page_start('Spedizioni e condizioni di vendita', 'Spedizioni, consegna in Friuli, ritiro, pagamenti e condizioni di vendita del negozio online Giuditta Teresa.');
?>
<section class="page-hero"><div class="wrap prose">
  <span class="eyebrow">Informazioni</span>
  <h1>Spedizioni e condizioni di vendita</h1>
  <p class="notice">Testo di base da far verificare al proprio consulente prima della pubblicazione.</p>

  <h2>Venditore</h2>
  <p>Giuditta Teresa — <?= e($BIZ['street']) ?>, <?= e($BIZ['zip'] . ' ' . $BIZ['city'] . ' (' . $BIZ['prov'] . ')') ?> — P. IVA <?= e($BIZ['vat']) ?> — <?= e($BIZ['email']) ?> — tel. <?= e($BIZ['phone_display']) ?>.</p>

  <h2>Prodotti e prezzi</h2>
  <p>I prezzi sono in euro e comprensivi di IVA. Le spese di consegna sono indicate nel carrello prima del pagamento. Le immagini dei prodotti sono indicative: ogni dolce è fatto a mano e può differire leggermente nell'aspetto. Ingredienti e allergeni sono riportati nella scheda di ogni prodotto e sull'etichetta.</p>

  <h2>Ordine e pagamento</h2>
  <p>Il contratto si conclude con la conferma del pagamento, effettuato su pagina sicura di Stripe Payments Europe Ltd. con carta, Apple Pay, Google Pay o PayPal. Riceverai la conferma via email. Non riceviamo né conserviamo i dati della tua carta.</p>

  <h2>Spedizione con corriere</h2>
  <ul>
    <li>Spediamo in Italia, San Marino e Città del Vaticano con corriere espresso.</li>
    <li>Costo: <?= eur($SHIP['flat_cents']) ?> per ordine, gratuita per ordini da <?= eur($SHIP['free_over_cents']) ?> in su.</li>
    <li>Spediamo dal lunedì al mercoledì per evitare soste nel fine settimana. Consegna di solito in <?= (int) $SHIP['days_min'] ?>–<?= (int) $SHIP['days_max'] ?> giorni lavorativi dalla spedizione.</li>
    <li>Nei periodi di Natale e Pasqua i tempi possono allungarsi: ti consigliamo di ordinare con anticipo.</li>
  </ul>

  <?php if ($LOCAL): ?>
  <h2>Consegna diretta in Friuli</h2>
  <ul>
    <li>Nelle zone indicate nella pagina <a href="consegna-in-friuli.php">Consegna in Friuli</a> consegniamo personalmente, su prenotazione, nel giorno scelto al momento dell'ordine.</li>
    <li>Costo: <?= eur($LOCAL['fee_cents']) ?> per consegna, gratuita per ordini da <?= eur($LOCAL['free_over_cents']) ?> in su. Prenotazione con almeno <?= (int) $LOCAL['lead_days'] ?> giorni di anticipo.</li>
    <li>L'indirizzo di consegna deve corrispondere al CAP indicato nel carrello. Il giorno della consegna ti contattiamo per concordare l'orario; se non sei raggiungibile, concordiamo una nuova data.</li>
    <li>I dolci freschi contrassegnati "Solo in Friuli" sono disponibili esclusivamente con consegna diretta o ritiro.</li>
  </ul>
  <?php endif; ?>

  <?php if ($PICKUP): ?>
  <h2>Ritiro in laboratorio</h2>
  <p>Puoi ritirare gratuitamente il tuo ordine nel nostro laboratorio ad Azzida, Via Algida 43, il giorno scelto (<?= e(days_text($PICKUP['days'])) ?>).</p>
  <?php endif; ?>

  <h2>Diritto di recesso</h2>
  <p>Trattandosi di prodotti alimentari deperibili, il diritto di recesso è escluso ai sensi dell'art. 59, comma 1, lett. d) ed e) del Codice del Consumo (D.Lgs. 206/2005).</p>

  <h2>Prodotti danneggiati o non conformi</h2>
  <p>Se il pacco arriva danneggiato, accettalo con riserva specifica al corriere e scrivici entro 48 ore a <?= e($BIZ['email']) ?> con alcune foto. Sostituiremo il prodotto o ti rimborseremo.</p>

  <h2>Reclami e controversie</h2>
  <p>Per qualsiasi problema scrivici: rispondiamo personalmente. Piattaforma europea ODR: <a href="https://ec.europa.eu/consumers/odr" rel="noopener">ec.europa.eu/consumers/odr</a>. Si applica la legge italiana.</p>

  <h2>Privacy</h2>
  <p>I dati sono trattati solo per evadere l'ordine, secondo la nostra <a href="<?= e($BIZ['privacy']) ?>" rel="noopener">informativa privacy</a>.</p>
</div></section>
<?php page_end();
