<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
if (!$LOCAL) { header('Location: shop.php', true, 302); exit; }

page_start('Consegna a domicilio in Friuli',
    'Gubana e dolci delle Valli del Natisone consegnati a domicilio in Friuli da noi, su prenotazione: ' . implode(', ', array_column($LOCAL['zones'], 'name')) . '. Gratis sopra ' . intdiv($LOCAL['free_over_cents'], 100) . ' €.');
?>
<section class="page-hero hero-local"><div class="wrap split">
  <div>
    <span class="pill">Novità · su prenotazione</span>
    <h1>In Friuli te la portiamo noi</h1>
    <p class="lead">Dal nostro forno di Azzida direttamente a casa tua, in ufficio o al tuo locale. Niente corriere, niente magazzini: la consegniamo noi, di persona.</p>
    <?= cap_checker() ?>
  </div>
  <img src="img/hero.jpg" alt="Gubana e Strucchi sulla tavola" width="1400" height="779">
</div></section>

<section class="section"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Come funziona</span><h2>Tre passi, e arriva fresca</h2></div>
  <div class="steps">
    <div class="step"><img src="img/lab-composizione.jpg" alt="" loading="lazy"><h3>Scegli i dolci</h3><p>Dal nostro shop, compresi i dolci freschi che con il corriere non possiamo mandare.</p></div>
    <div class="step"><img src="img/fase-forno.jpg" alt="" loading="lazy"><h3>Prenota il giorno</h3><p>Nel carrello inserisci il CAP e scegli il giorno di consegna, con almeno <?= (int) $LOCAL['lead_days'] ?> giorni di anticipo: prepariamo tutto per te.</p></div>
    <div class="step"><img src="img/gubana-1000.jpg" alt="" loading="lazy"><h3>Te la portiamo noi</h3><p>Il giorno della consegna ti chiamiamo per concordare l'orario e arriviamo noi, con i dolci appena fatti.</p></div>
  </div>
</div></section>

<section class="section section-alt"><div class="wrap">
  <div class="section-head">
    <span class="eyebrow">Dove e quando</span>
    <h2>I nostri giri di consegna</h2>
    <p class="muted">Consegniamo a giorni fissi per zona: così possiamo garantire puntualità e dolci appena sfornati. Le zone crescono: se la tua non c'è, <a href="contatti.php">scrivici</a>.</p>
  </div>
  <?= zones_table() ?>
  <ul class="pdp-notes" style="margin-top:24px">
    <li>Consegna <?= eur($LOCAL['fee_cents']) ?>, <b>gratuita sopra <?= eur($LOCAL['free_over_cents']) ?></b></li>
    <li>Prenotazione con almeno <?= (int) $LOCAL['lead_days'] ?> giorni di anticipo</li>
    <li>Ti chiamiamo il giorno della consegna per concordare l'orario</li>
    <?php if ($PICKUP): ?><li>Preferisci passare tu? Il <b>ritiro in laboratorio</b> ad Azzida è gratuito, <?= e(days_text($PICKUP['days'])) ?></li><?php endif; ?>
  </ul>
</div></section>

<section class="section"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Solo in Friuli</span><h2>I dolci freschi che non spediamo</h2>
  <p class="muted">Con la consegna diretta o il ritiro puoi avere anche i dolci più delicati, preparati su prenotazione.</p></div>
  <div class="grid"><?= cards(fresh()) ?></div>
</div></section>

<section class="section section-dark"><div class="wrap split">
  <div>
    <span class="eyebrow">Per bar, alberghi e ristoranti</span>
    <h2>Consegna settimanale al vostro locale</h2>
    <p class="lead">Un ordine fisso, lo stesso giorno ogni settimana, con un listino dedicato.</p>
  </div>
  <div><a class="btn btn-light" href="ristorazione.php">Scopri l'offerta per la ristorazione</a></div>
</div></section>
<?php page_end();
