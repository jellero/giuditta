<?php
// POST checkout.php  { items: [{id, qty}], delivery: {mode, cap, date} }  ->  { url }
// Crea una sessione Stripe Checkout ricalcolando prezzi e consegna dal catalogo.
declare(strict_types=1);
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/order.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function reply(int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') reply(405, ['error' => 'Metodo non consentito.']);

$raw = (string) file_get_contents('php://input', false, null, 0, 20000);
$body = json_decode($raw, true);
if (!is_array($body)) reply(400, ['error' => 'Richiesta non valida.']);

$d = is_array($body['delivery'] ?? null) ? $body['delivery'] : [];
try {
    $priced = price_cart($CAT, $body['items'] ?? null, [
        'mode' => is_string($d['mode'] ?? null) ? $d['mode'] : 'ship',
        'cap' => is_string($d['cap'] ?? null) ? $d['cap'] : '',
        'date' => is_string($d['date'] ?? null) ? $d['date'] : '',
    ]);
} catch (OrderError $ex) {
    reply(400, ['error' => $ex->getMessage()]);
}

if (STRIPE_SECRET_KEY === '' || strpos(STRIPE_SECRET_KEY, 'INSERISCI') !== false) {
    reply(503, ['error' => 'Demo: il pagamento non è ancora collegato a Stripe.']);
}

$payload = encode_pairs(checkout_params($CAT, $priced, site_url()));
$headers = ['Authorization: Bearer ' . STRIPE_SECRET_KEY, 'Content-Type: application/x-www-form-urlencoded'];

if (function_exists('curl_init')) {
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
    ]);
    // Su Windows PHP spesso non ha i certificati: usa quelli del sistema
    if (PHP_OS_FAMILY === 'Windows' && defined('CURLSSLOPT_NATIVE_CA')) curl_setopt($ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA);
    $resp = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($resp === false) error_log('Stripe curl: ' . curl_error($ch));
    curl_close($ch);
} else {
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $payload, 'timeout' => 20, 'ignore_errors' => true]]);
    $resp = @file_get_contents('https://api.stripe.com/v1/checkout/sessions', false, $ctx);
    $status = 0;
    foreach ($http_response_header ?? [] as $h) if (preg_match('#^HTTP/\S+ (\d{3})#', $h, $m)) $status = (int) $m[1];
}

$data = is_string($resp) ? json_decode($resp, true) : null;
if ($status !== 200 || empty($data['url'])) {
    error_log('Stripe error ' . $status . ' ' . substr((string) $resp, 0, 500));
    reply(502, ['error' => 'Il servizio di pagamento non risponde.']);
}
reply(200, ['url' => $data['url']]);
