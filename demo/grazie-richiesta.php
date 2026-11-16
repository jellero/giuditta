<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
page_start('Richiesta inviata', 'Richiesta inviata.', ['noindex' => true]);
$errore = !empty($_GET['errore']);
$news = !empty($_GET['newsletter']);
?>
<section class="section"><div class="wrap narrow center">
<?php if ($errore): ?>
  <span class="eyebrow">Qualcosa non va</span>
  <h1>Mancano alcuni dati</h1>
  <p class="lead">Controlla di aver compilato i campi obbligatori e un indirizzo email valido.</p>
  <p style="margin-top:28px"><a class="btn" href="javascript:history.back()">Torna al modulo</a></p>
<?php elseif ($news): ?>
  <span class="eyebrow">Iscrizione ricevuta</span>
  <h1>Grazie, ci sentiamo presto</h1>
  <p class="lead">Ti scriveremo poche volte all'anno, solo quando abbiamo qualcosa di buono da raccontarti.</p>
  <p style="margin-top:28px"><a class="btn" href="shop.php">Scopri i nostri dolci</a></p>
<?php else: ?>
  <span class="eyebrow">Messaggio inviato</span>
  <h1>Grazie, ti risponderemo presto</h1>
  <p class="lead">Leggiamo personalmente ogni messaggio e rispondiamo di solito entro due giorni lavorativi.</p>
  <p style="margin-top:28px"><a class="btn" href="shop.php">Intanto, scopri i nostri dolci</a></p>
<?php endif; ?>
</div></section>
<?php page_end();
