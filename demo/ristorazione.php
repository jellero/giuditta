<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
page_start('Gubana per bar, alberghi e ristoranti in Friuli',
    'Gubana artigianale premiata per ristoranti, alberghi, bar, agriturismi ed enoteche in Friuli: consegna diretta settimanale, listino dedicato, materiali per la sala.',
    ['image' => 'tronco.jpg']);
?>
<section class="page-hero"><div class="wrap split">
  <div>
    <span class="eyebrow">Bar · Alberghi · Ristoranti · Agriturismi · Enoteche</span>
    <h1>Il dolce del territorio, sul vostro menù</h1>
    <p class="lead">I vostri clienti cercano il Friuli vero. Offrite loro una Gubana artigianale, premiata, fatta a mano nelle Valli del Natisone, e raccontatene la storia.</p>
    <div class="hero-actions"><a class="btn" href="#richiesta-horeca">Chiedi il listino ristorazione</a><a class="btn btn-ghost" href="<?= tel() ?>">Chiamaci</a></div>
  </div>
  <img src="img/tronco.jpg" alt="Tronco di Gubana tagliato a fette" width="855" height="570">
</div></section>

<section class="section section-alt"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Pochi formati, pensati per voi</span><h2>Cosa vi proponiamo</h2></div>
  <div class="steps">
    <div class="step"><img src="img/tronco.jpg" alt="Tronco di Gubana" loading="lazy"><h3>Ristoranti: il fine pasto friulano</h3><p>Il <b>Tronco di Gubana</b> si taglia a fette regolari, senza sprechi. Servito con un goccio di grappa o slivovitz è il dessert della tradizione, da mettere in carta con il nostro nome.</p></div>
    <div class="step"><img src="img/torta-silvana.jpg" alt="Torta Silvana" loading="lazy"><h3>Alberghi: la colazione del territorio</h3><p><b>Torta Silvana</b>, <b>Mini Gubana</b> e Focaccia per il buffet. Su richiesta, un piccolo espositore per vendere le nostre confezioni alla reception.</p></div>
    <div class="step"><img src="img/strucchi-scatola.jpg" alt="Strucchi" loading="lazy"><h3>Bar, enoteche, cantine</h3><p><b>Strucchi</b> e <b>Gemme di Gubana</b> da abbinare al caffè o ai vini dolci dei Colli Orientali: Picolit, Ramandolo, Verduzzo.</p></div>
  </div>
</div></section>

<section class="section"><div class="wrap split">
  <div>
    <span class="eyebrow">Perché conviene</span>
    <h2>Un prodotto che si racconta da solo</h2>
    <ul class="pdp-notes">
      <li><b>Consegna diretta in Friuli</b>, a giorni fissi: niente corriere, dolci appena fatti</li>
      <li><b>Ordine fisso settimanale</b>: lo stesso giorno, senza doverci pensare</li>
      <li><b>Listino riservato</b> alla ristorazione, con fattura</li>
      <li><b>Materiali per la sala</b>: cartoncino con la nostra storia e QR code, perché i vostri ospiti sappiano cosa stanno assaggiando</li>
      <li>Primo premio al <b>Gubana Day</b>: un argomento in più per i vostri camerieri</li>
    </ul>
  </div>
  <img src="img/lab-noci.jpg" alt="Pulizia a mano delle noci nel laboratorio" loading="lazy">
</div></section>

<section class="section section-alt" id="richiesta-horeca"><div class="wrap narrow">
  <div class="section-head"><h2>Chiedi il listino ristorazione</h2><p class="muted">Vi rispondiamo personalmente. Se volete, passiamo noi a farvi assaggiare.</p></div>
  <form class="form" method="POST" action="invia.php">
    <?= form_guard('ristorazione') ?>
    <div><label for="h-locale">Nome del locale *</label><input id="h-locale" name="locale" required autocomplete="organization"></div>
    <div><label for="h-tipo">Tipo di locale *</label>
      <select id="h-tipo" name="tipo" required><option value="">Scegli…</option><option>Ristorante / trattoria</option><option>Albergo / B&amp;B</option><option>Bar / caffè / pasticceria</option><option>Agriturismo</option><option>Enoteca / cantina</option><option>Altro</option></select></div>
    <div><label for="h-comune">Comune *</label><input id="h-comune" name="comune" required></div>
    <div><label for="h-nome">Referente *</label><input id="h-nome" name="nome" required autocomplete="name"></div>
    <div><label for="h-tel">Telefono *</label><input id="h-tel" name="telefono" type="tel" required autocomplete="tel"></div>
    <div><label for="h-email">Email *</label><input id="h-email" name="email" type="email" required autocomplete="email"></div>
    <div class="full"><label>Cosa vi interessa</label>
      <label class="check"><input type="checkbox" name="interesse[]" value="Tronco di Gubana (fine pasto)"> Tronco di Gubana per il fine pasto</label>
      <label class="check"><input type="checkbox" name="interesse[]" value="Colazione"> Torta Silvana, Mini Gubana, Focaccia per la colazione</label>
      <label class="check"><input type="checkbox" name="interesse[]" value="Strucchi e Gemme"> Strucchi e Gemme di Gubana</label>
      <label class="check"><input type="checkbox" name="interesse[]" value="Confezioni da rivendere"> Confezioni da rivendere ai clienti</label></div>
    <div class="full"><label for="h-freq">Frequenza</label>
      <select id="h-freq" name="frequenza"><option>Ordine fisso settimanale</option><option>Ogni due settimane</option><option>Solo per eventi o stagioni</option><option>Da valutare</option></select></div>
    <div class="full"><label for="h-msg">Note</label><textarea id="h-msg" name="messaggio" placeholder="Coperti, stagionalità, giorni di chiusura…"></textarea></div>
    <label class="check full"><input type="checkbox" name="privacy" value="sì" required> <span>Acconsento al trattamento dei dati per ricevere una risposta, secondo l'<a href="<?= e($BIZ['privacy']) ?>" target="_blank" rel="noopener">informativa privacy</a>. *</span></label>
    <div class="full"><button class="btn" type="submit">Invia richiesta</button></div>
  </form>
</div></section>
<?php page_end();
