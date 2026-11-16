<?php
// Configurazione generale. Compatibile con PHP 7.4 e successivi.
declare(strict_types=1);

if (!defined('GT')) define('GT', true);
date_default_timezone_set('Europe/Rome');
mb_internal_encoding('UTF-8');

// --- Impostazioni del sito ---------------------------------------------------
// DEMO = true: le pagine non vengono indicizzate da Google e compare la fascia "anteprima".
// Quando il sito diventa quello ufficiale, mettere false.
const DEMO = true;
const EMAIL_ORDINI = 'info@giudittateresa.com';   // riceve moduli e richieste

// Chiave segreta Stripe: va in inc/segreti.php (vedi segreti.esempio.php), mai su GitHub.
if (is_file(__DIR__ . '/segreti.php')) require __DIR__ . '/segreti.php';
if (!defined('STRIPE_SECRET_KEY')) define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: '');

$BIZ = [
    'name' => 'Giuditta Teresa',
    'street' => 'Via Algida, 43 — Località Azzida',
    'zip' => '33049',
    'city' => 'San Pietro al Natisone',
    'prov' => 'UD',
    'phone' => '+390432727681',
    'phone_display' => '0432 727681',
    'email' => 'info@giudittateresa.com',
    'vat' => '02707840308',
    'facebook' => 'https://www.facebook.com/gubanagiudittateresa/',
    'instagram' => '', // es. https://www.instagram.com/gubanagiudittateresa/
    'maps' => 'https://www.google.com/maps/search/?api=1&query=Giuditta+Teresa+Gubana+Azzida+San+Pietro+al+Natisone',
    'privacy' => 'https://www.iubenda.com/privacy-policy/21456511',
];

// --- Catalogo ----------------------------------------------------------------
$CAT = json_decode((string) file_get_contents(__DIR__ . '/catalog.json'), true);
if (!is_array($CAT)) { http_response_code(500); exit('catalog.json non valido'); }
$PRODUCTS = $CAT['products'];
$SHIP = $CAT['shipping'];
$LOCAL = !empty($CAT['local_delivery']['enabled']) ? $CAT['local_delivery'] : null;
$PICKUP = !empty($CAT['pickup']['enabled']) ? $CAT['pickup'] : null;

const GIORNI = ['domenica', 'lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato'];
const MESI = ['gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];

// --- Funzioni di utilità -----------------------------------------------------
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

function eur(int $cents): string { return number_format($cents / 100, 2, ',', '.') . ' €'; }

function has_price(array $p): bool { return isset($p['price_cents']) && is_int($p['price_cents']); }

function sellable(array $p): bool { return !empty($p['available']) && has_price($p); }

function full_name(array $p): string { return trim($p['name'] . ' ' . ($p['weight'] ?? '')); }

function product_url(array $p): string { return 'prodotto.php?p=' . rawurlencode($p['id']); }

function product_by_id(string $id): ?array {
    global $PRODUCTS;
    foreach ($PRODUCTS as $p) if ($p['id'] === $id) return $p;
    return null;
}

function days_text(array $days): string {
    $n = array_map(function ($d) { return GIORNI[$d]; }, $days);
    if (count($n) === 1) return $n[0];
    $last = array_pop($n);
    return implode(', ', $n) . ' e ' . $last;
}

/** URL assoluto della cartella del sito (funziona anche in sottocartella, es. /demo/). */
function site_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $dir;
}

function current_page(): string { return basename($_SERVER['SCRIPT_NAME'] ?? 'index.php'); }

function tel(): string { global $BIZ; return 'tel:' . $BIZ['phone']; }
