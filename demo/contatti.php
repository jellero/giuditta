<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
page_start('Contatti', 'Giuditta Teresa, Via Algida 43, Azzida — San Pietro al Natisone (UD). Tel. 0432 727681, info@giudittateresa.com.');
?>
<section class="page-hero"><div class="wrap split" style="align-items:start">
  <div>
    <span class="eyebrow">Contatti</span>
    <h1>Venite a trovarci</h1>
    <p class="lead">Il nostro laboratorio è ad Azzida, alle porte delle Valli del Natisone, a pochi minuti da Cividale del Friuli.</p>
    <p><strong>Giuditta Teresa</strong><br><?= e($BIZ['street']) ?><br><?= e($BIZ['zip'] . ' ' . $BIZ['city'] . ' (' . $BIZ['prov'] . ')') ?></p>
    <p>Telefono: <a href="<?= tel() ?>"><?= e($BIZ['phone_display']) ?></a><br>Email: <a href="mailto:<?= e($BIZ['email']) ?>"><?= e($BIZ['email']) ?></a></p>
    <p><a class="btn btn-ghost btn-sm" href="<?= e($BIZ['maps']) ?>" rel="noopener" target="_blank">Apri in Google Maps</a></p>
    <p class="small">Prima di passare per ritirare un dolce su ordinazione, chiamaci per conferma.</p>
  </div>
  <form class="form" method="POST" action="invia.php">
    <?= form_guard('contatti') ?>
    <div class="full"><label for="c-nome">Nome *</label><input id="c-nome" name="nome" required autocomplete="name"></div>
    <div class="full"><label for="c-email">Email *</label><input id="c-email" name="email" type="email" required autocomplete="email"></div>
    <div class="full"><label for="c-msg">Messaggio *</label><textarea id="c-msg" name="messaggio" required></textarea></div>
    <label class="check full"><input type="checkbox" name="privacy" value="sì" required> <span>Acconsento al trattamento dei dati secondo l'<a href="<?= e($BIZ['privacy']) ?>" target="_blank" rel="noopener">informativa privacy</a>. *</span></label>
    <div class="full"><button class="btn" type="submit">Invia</button></div>
  </form>
</div></section>
<?php page_end();
