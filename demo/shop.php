<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';

$list = [];
$i = 0;
foreach ($PRODUCTS as $p) {
    if (has_price($p)) $list[] = ['@type' => 'ListItem', 'position' => ++$i, 'url' => site_url() . '/' . product_url($p), 'name' => full_name($p)];
}

page_start('Acquista Gubana online',
    'Acquista online Gubana, Strucchi, Gemme e Cuor di Gubana fatti a mano ad Azzida. Spedizione in tutta Italia, consegna diretta in Friuli.',
    ['ld' => [['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $list]]]);
?>
<?php if (!empty($_GET['annullato'])): ?>
<div class="wrap"><p class="notice" style="margin-top:24px">Pagamento annullato: il tuo carrello è ancora qui, puoi riprovare quando vuoi.</p></div>
<?php endif; ?>
<section class="page-hero"><div class="wrap">
  <span class="eyebrow">Shop</span>
  <h1>I nostri dolci</h1>
  <p class="lead narrow">Tutto quello che esce dal nostro forno, preparato a mano. In tutta Italia con corriere, gratis sopra i <?= intdiv($SHIP['free_over_cents'], 100) ?> €<?php if ($LOCAL): ?>; in Friuli te li portiamo noi, gratis sopra i <?= intdiv($LOCAL['free_over_cents'], 100) ?> €<?php endif; ?>.</p>
</div></section>
<section style="padding-bottom:80px"><div class="wrap">
  <div class="grid"><?= cards(national(), 'h2') ?></div>
</div></section>
<section class="section section-alt" id="freschi"><div class="wrap">
  <div class="section-head">
    <span class="eyebrow">Freschi di forno · solo in Friuli</span>
    <h2>Su prenotazione</h2>
    <p class="muted">Dolci che non viaggiano con il corriere: li prepariamo su prenotazione.
      <?= $LOCAL ? 'Te li portiamo noi in Friuli (' . e(implode(', ', array_column($LOCAL['zones'], 'name'))) . ') oppure li ritiri in laboratorio.' : 'Da ritirare in laboratorio.' ?>
      Per prenotarli chiamaci al <a href="<?= tel() ?>"><?= e($BIZ['phone_display']) ?></a>.</p>
  </div>
  <div class="grid"><?= cards(fresh(), 'h2') ?></div>
</div></section>
<?php page_end();
