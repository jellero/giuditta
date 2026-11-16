<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';

$p = product_by_id((string) ($_GET['p'] ?? ''));
if (!$p || !has_price($p)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$others = array_slice(array_values(array_filter($PRODUCTS, function ($o) use ($p) { return $o['id'] !== $p['id'] && sellable($o); })), 0, 4);

if (!empty($p['local_only'])) {
    $notes = ['Dolce fresco: solo consegna diretta in Friuli o ritiro in laboratorio'];
} else {
    $notes = ['Corriere in tutta Italia ' . eur($SHIP['flat_cents']) . ', gratis sopra ' . eur($SHIP['free_over_cents']),
              'Spediamo dal lunedì al mercoledì, per farla arrivare fresca'];
}
if (!empty($p['seasonal'])) array_unshift($notes, 'Dolce di stagione: ' . $p['seasonal']);
if ($LOCAL) $notes[] = 'In Friuli te la portiamo noi: ' . eur($LOCAL['fee_cents']) . ', gratis sopra ' . eur($LOCAL['free_over_cents']);
if ($PICKUP) $notes[] = 'Ritiro gratuito nel nostro laboratorio di Azzida';
$notes[] = "Puoi aggiungere un biglietto d'auguri al pagamento";

$sections = [['Descrizione', $p['description'] ?? ''], ['Come gustarla', $p['serve'] ?? '']];
if (!empty($p['ingredients'])) $sections[] = ['Ingredienti', $p['ingredients']];
if (!empty($p['allergens'])) $sections[] = ['Allergeni', $p['allergens']];
if (!empty($p['shelf'])) $sections[] = ['Conservazione', $p['shelf']];
$sections[] = ['Produttore', 'Giuditta Teresa — ' . $BIZ['street'] . ', ' . $BIZ['zip'] . ' ' . $BIZ['city'] . ' (' . $BIZ['prov'] . ')'];

$meta = implode(' · ', array_filter([$p['subtitle'] ?? '', $p['weight'] ?? '', $p['pack'] ?? '']));
$site = site_url();
$prodLd = [
    '@context' => 'https://schema.org', '@type' => 'Product', 'name' => full_name($p), 'description' => $p['short'],
    'image' => $site . '/img/' . $p['image'], 'sku' => $p['id'], 'brand' => ['@type' => 'Brand', 'name' => 'Giuditta Teresa'],
    'offers' => ['@type' => 'Offer', 'url' => $site . '/' . product_url($p), 'priceCurrency' => 'EUR',
        'price' => number_format($p['price_cents'] / 100, 2, '.', ''),
        'availability' => sellable($p) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'itemCondition' => 'https://schema.org/NewCondition'],
];
$crumbs = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $site . '/'],
    ['@type' => 'ListItem', 'position' => 2, 'name' => 'I nostri dolci', 'item' => $site . '/shop.php'],
    ['@type' => 'ListItem', 'position' => 3, 'name' => $p['name'], 'item' => $site . '/' . product_url($p)],
]];

page_start(full_name($p) . ' artigianale',
    mb_substr(full_name($p) . ': ' . $p['short'] . ' Fatto a mano ad Azzida, Valli del Natisone. ' . eur($p['price_cents']) . '.', 0, 300),
    ['image' => $p['image'], 'ld' => [$prodLd, $crumbs]]);
?>
<div class="wrap">
  <nav class="crumbs" aria-label="Percorso"><a href="./">Home</a> / <a href="shop.php">I nostri dolci</a> / <?= e($p['name']) ?></nav>
  <div class="pdp">
    <div class="pdp-img"><img src="img/<?= e($p['image']) ?>" alt="<?= e(full_name($p)) ?> Giuditta Teresa" width="855" height="570" fetchpriority="high"></div>
    <div>
      <span class="eyebrow"><?= e($p['badge'] ?? (!empty($p['local_only']) ? 'Solo in Friuli' : 'Fatto a mano ad Azzida')) ?></span>
      <h1><?= e($p['name']) ?></h1>
      <div class="muted"><?= e($meta) ?></div>
      <span class="price"><?= eur($p['price_cents']) ?></span>
      <p><?= e($p['short']) ?></p>
      <?php if (sellable($p)): ?>
      <div class="pdp-buy">
        <div class="qty"><button type="button" data-step="-1" aria-label="Diminuisci">−</button><input id="qty" type="number" min="1" max="50" value="1" aria-label="Quantità"><button type="button" data-step="1" aria-label="Aumenta">+</button></div>
        <button class="btn" data-add="<?= e($p['id']) ?>" data-qty-from="#qty">Aggiungi al carrello</button>
      </div>
      <?php else: ?>
      <p class="notice">Al momento non disponibile. <a href="contatti.php">Scrivici</a> per sapere quando tornerà.</p>
      <?php endif; ?>
      <ul class="pdp-notes"><?php foreach ($notes as $n): ?><li><?= e($n) ?></li><?php endforeach; ?></ul>
      <div class="details">
        <?php $first = true; foreach ($sections as [$t, $v]): if ($v === '') continue; ?>
        <details<?= $first ? ' open' : '' ?>><summary><?= e($t) ?></summary><div><?= e($v) ?></div></details>
        <?php $first = false; endforeach; ?>
      </div>
    </div>
  </div>
</div>
<section class="section section-alt"><div class="wrap">
  <div class="section-head"><h2>Potrebbero piacerti anche</h2></div>
  <div class="grid"><?= cards($others) ?></div>
</div></section>
<?php page_end();
