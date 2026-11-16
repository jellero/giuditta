<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
page_start('Grazie per il tuo ordine', 'Ordine ricevuto.', ['noindex' => true, 'body' => 'data-clear-cart']);
?>
<section class="section"><div class="wrap narrow center">
  <span class="eyebrow">Ordine ricevuto</span>
  <h1>Grazie!</h1>
  <p class="lead">Abbiamo ricevuto il tuo ordine e ti abbiamo inviato una email di conferma. Prepareremo i tuoi dolci a mano: se hai scelto la consegna diretta ti chiameremo il giorno stabilito per concordare l'orario.</p>
  <p>Per qualsiasi domanda: <a href="mailto:<?= e($BIZ['email']) ?>"><?= e($BIZ['email']) ?></a> · <?= e($BIZ['phone_display']) ?></p>
  <p style="margin-top:28px"><a class="btn btn-ghost" href="./">Torna alla home</a></p>
</div></section>
<?php page_end();
