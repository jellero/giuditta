<?php
// Mappa del sito per Google (da indicare in Search Console quando DEMO = false).
declare(strict_types=1);
require __DIR__ . '/inc/config.php';
header('Content-Type: application/xml; charset=utf-8');

$pages = ['', 'shop.php', 'la-nostra-storia.php', 'premi-e-stampa.php', 'aziende.php', 'ristorazione.php', 'contatti.php', 'condizioni-di-vendita.php'];
if ($LOCAL) $pages[] = 'consegna-in-friuli.php';
foreach ($PRODUCTS as $p) if (has_price($p)) $pages[] = product_url($p);

$base = site_url() . '/';
$today = date('Y-m-d');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($pages as $u) echo '<url><loc>' . htmlspecialchars($base . $u, ENT_XML1) . '</loc><lastmod>' . $today . '</lastmod></url>';
echo "</urlset>\n";
