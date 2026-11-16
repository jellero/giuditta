<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
page_start('Regali aziendali e fornitura per negozi',
    'Regali aziendali di Natale e Pasqua con Gubana artigianale delle Valli del Natisone: biglietto personalizzato, consegna a più indirizzi, listino dedicato. Fornitura per botteghe ed enoteche.',
    ['image' => 'colombana.jpg']);
?>
<section class="page-hero"><div class="wrap split">
  <div>
    <span class="eyebrow">Aziende e negozi</span>
    <h1>Un regalo che racconta un territorio</h1>
    <p class="lead">Un dolce fatto a mano nelle Valli del Natisone è un regalo che si ricorda. Lavoriamo con aziende, botteghe ed enoteche che cercano un prodotto autentico.</p>
    <div class="hero-actions"><a class="btn" href="#richiesta">Richiedi il listino</a><a class="btn btn-ghost" href="ristorazione.php">Sei un bar o un ristorante?</a></div>
  </div>
  <img src="img/colombana.jpg" alt="Colombana Giuditta Teresa" width="855" height="570">
</div></section>

<section class="section section-dark"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Regali aziendali</span><h2>Per Natale, Pasqua e ogni occasione</h2></div>
  <ul class="b2b-points">
    <li><h3>Biglietto personalizzato</h3><p>Il vostro messaggio, scritto a mano su ogni confezione, con il vostro logo se lo desiderate.</p></li>
    <li><h3>Consegna a più indirizzi</h3><p>Ci mandate l'elenco dei destinatari, pensiamo noi a confezionare e spedire. In Friuli consegniamo di persona.</p></li>
    <li><h3>Confezioni su misura</h3><p>Gubana, Strucchi, Colombana: componiamo insieme la confezione giusta per il vostro budget.</p></li>
    <li><h3>Fattura e listino dedicato</h3><p>Prezzi riservati per quantità, fattura elettronica e un unico referente.</p></li>
  </ul>
  <p style="margin-top:28px" class="muted">Per Natale consigliamo di contattarci entro fine ottobre: ogni dolce è fatto a mano e la produzione non è infinita.</p>
</div></section>

<section class="section" id="rivenditori"><div class="wrap split">
  <div>
    <span class="eyebrow">Rivenditori</span>
    <h2>Vendi la nostra Gubana</h2>
    <p>Botteghe di prodotti tipici, enoteche, gastronomie e agriturismi: se cercate una Gubana artigianale premiata, con una storia vera da raccontare ai vostri clienti, scriveteci. Vi invieremo condizioni di fornitura e calendario delle consegne.</p>
    <p class="muted">Per hotel, ristoranti e bar abbiamo un'<a href="ristorazione.php">offerta dedicata</a>.</p>
  </div>
  <img src="img/gemme.jpg" alt="Gemme di Gubana" width="855" height="570" loading="lazy">
</div></section>

<section class="section section-alt" id="richiesta"><div class="wrap narrow">
  <div class="section-head"><h2>Richiedi il listino</h2><p class="muted">Rispondiamo entro due giorni lavorativi. Preferisci il telefono? Chiamaci al <a href="<?= tel() ?>"><?= e($BIZ['phone_display']) ?></a>.</p></div>
  <form class="form" method="POST" action="invia.php">
    <?= form_guard('aziende') ?>
    <div><label for="f-azienda">Azienda *</label><input id="f-azienda" name="azienda" required autocomplete="organization"></div>
    <div><label for="f-piva">Partita IVA</label><input id="f-piva" name="partita_iva"></div>
    <div><label for="f-nome">Nome e cognome *</label><input id="f-nome" name="nome" required autocomplete="name"></div>
    <div><label for="f-tel">Telefono</label><input id="f-tel" name="telefono" type="tel" autocomplete="tel"></div>
    <div class="full"><label for="f-email">Email *</label><input id="f-email" name="email" type="email" required autocomplete="email"></div>
    <div><label for="f-tipo">Di cosa avete bisogno? *</label>
      <select id="f-tipo" name="tipo" required>
        <option value="">Scegli…</option><option>Regali aziendali di Natale</option><option>Regali aziendali di Pasqua</option>
        <option>Rivendita in negozio</option><option>Evento o matrimonio</option><option>Altro</option>
      </select></div>
    <div><label for="f-qta">Quantità indicativa</label>
      <select id="f-qta" name="quantita"><option>10–30 confezioni</option><option>30–100 confezioni</option><option>Oltre 100 confezioni</option><option>Fornitura continuativa</option></select></div>
    <div class="full"><label for="f-data">Entro quando?</label><input id="f-data" name="data_consegna" placeholder="es. entro il 15 dicembre"></div>
    <div class="full"><label for="f-msg">Raccontateci di più</label><textarea id="f-msg" name="messaggio"></textarea></div>
    <label class="check full"><input type="checkbox" name="privacy" value="sì" required> <span>Acconsento al trattamento dei dati per ricevere una risposta, secondo l'<a href="<?= e($BIZ['privacy']) ?>" target="_blank" rel="noopener">informativa privacy</a>. *</span></label>
    <div class="full"><button class="btn" type="submit">Invia richiesta</button></div>
  </form>
</div></section>
<?php page_end();
