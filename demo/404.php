<?php
declare(strict_types=1);
require_once __DIR__ . '/inc/layout.php';
if (!headers_sent()) http_response_code(404);
page_start('Pagina non trovata', 'Pagina non trovata.', ['noindex' => true]);
?>
<section class="section"><div class="wrap narrow center">
  <span class="eyebrow">Errore 404</span>
  <h1>Questa pagina non c'è</h1>
  <p class="lead">Forse è stata spostata con il nuovo sito.</p>
  <p><a class="btn" href="shop.php">Vai ai nostri dolci</a></p>
</div></section>
<?php page_end();
