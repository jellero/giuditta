<?php
// Logica dell'ordine: valida carrello e consegna contro il catalogo e prepara
// i parametri per Stripe Checkout. Nessun I/O: testabile da riga di comando.
declare(strict_types=1);

const MAX_QTY = 50;
const MAX_LINES = 20;

class OrderError extends Exception {}

// ---------- Date (fuso di Roma) ----------

function rome_today(?DateTimeImmutable $now = null): string {
    $now = $now ?: new DateTimeImmutable('now');
    return $now->setTimezone(new DateTimeZone('Europe/Rome'))->format('Y-m-d');
}

function iso_valid(string $iso): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $iso)) return false;
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $iso, new DateTimeZone('UTC'));
    return $d && $d->format('Y-m-d') === $iso;
}

function days_between(string $a, string $b): int {
    $tz = new DateTimeZone('UTC');
    $da = DateTimeImmutable::createFromFormat('!Y-m-d', $a, $tz);
    $db = DateTimeImmutable::createFromFormat('!Y-m-d', $b, $tz);
    return (int) round(($db->getTimestamp() - $da->getTimestamp()) / 86400);
}

function weekday(string $iso): int {
    return (int) DateTimeImmutable::createFromFormat('!Y-m-d', $iso, new DateTimeZone('UTC'))->format('w');
}

function format_date(string $iso): string {
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $iso, new DateTimeZone('UTC'));
    return GIORNI[(int) $d->format('w')] . ' ' . (int) $d->format('j') . ' ' . MESI[(int) $d->format('n') - 1];
}

/** $rule: days, lead_days, max_days */
function is_bookable(array $cat, string $iso, array $rule, ?DateTimeImmutable $now = null): bool {
    if (!iso_valid($iso)) return false;
    $diff = days_between(rome_today($now), $iso);
    if ($diff < $rule['lead_days'] || $diff > $rule['max_days']) return false;
    if (in_array($iso, $cat['closed_dates'] ?? [], true)) return false;
    return in_array(weekday($iso), $rule['days'], true);
}

function next_dates(array $cat, array $rule, int $count = 6, ?DateTimeImmutable $now = null): array {
    $out = [];
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', rome_today($now), new DateTimeZone('UTC'));
    for ($i = $rule['lead_days']; $i <= $rule['max_days'] && count($out) < $count; $i++) {
        $iso = $d->modify("+$i day")->format('Y-m-d');
        if (is_bookable($cat, $iso, $rule, $now)) $out[] = $iso;
    }
    return $out;
}

function zone_for_cap(array $cat, $cap): ?array {
    $ld = $cat['local_delivery'] ?? null;
    $cap = (string) $cap;
    if (!$ld || empty($ld['enabled']) || !preg_match('/^\d{5}$/', $cap)) return null;
    foreach ($ld['zones'] as $z) if (in_array($cap, $z['caps'], true)) return $z;
    return null;
}

// ---------- Prezzi ----------

/**
 * $items:    [['id' => ..., 'qty' => ...], ...]
 * $delivery: ['mode' => 'ship'|'local'|'pickup', 'cap' => ?, 'date' => ?]
 */
function price_cart(array $cat, $items, array $delivery = ['mode' => 'ship'], ?DateTimeImmutable $now = null): array {
    if (!is_array($items) || count($items) === 0) throw new OrderError('Il carrello è vuoto.');
    if (count($items) > MAX_LINES) throw new OrderError('Troppi articoli nel carrello.');
    $byId = [];
    foreach ($cat['products'] as $p) $byId[$p['id']] = $p;

    $merged = [];
    foreach ($items as $it) {
        $id = is_array($it) && isset($it['id']) && is_string($it['id']) ? $it['id'] : '';
        $p = $byId[$id] ?? null;
        if (!$p || empty($p['available']) || !isset($p['price_cents']) || !is_int($p['price_cents'])) {
            throw new OrderError('Un prodotto nel carrello non è più disponibile. Aggiorna la pagina.');
        }
        $qty = $it['qty'] ?? null;
        if (!is_int($qty) || $qty < 1) throw new OrderError('Quantità non valida.');
        $merged[$id] = min(MAX_QTY, ($merged[$id] ?? 0) + $qty);
    }
    $lines = [];
    $subtotal = 0;
    foreach ($merged as $id => $qty) {
        $lines[] = ['product' => $byId[$id], 'qty' => $qty];
        $subtotal += $byId[$id]['price_cents'] * $qty;
    }

    $mode = $delivery['mode'] ?? 'ship';
    if ($mode === 'ship') {
        foreach ($lines as $l) {
            if (!empty($l['product']['local_only'])) {
                throw new OrderError($l['product']['name'] . ' è disponibile solo con consegna in Friuli o ritiro in laboratorio.');
            }
        }
        $s = $cat['shipping'];
        $shipping = $subtotal >= $s['free_over_cents'] ? 0 : $s['flat_cents'];
        $info = ['mode' => 'ship'];
    } elseif ($mode === 'local') {
        $ld = $cat['local_delivery'] ?? null;
        if (!$ld || empty($ld['enabled'])) throw new OrderError('La consegna in Friuli non è attiva.');
        $zone = zone_for_cap($cat, $delivery['cap'] ?? '');
        if (!$zone) throw new OrderError('Per questo CAP non abbiamo ancora un giro di consegna. Scegli la spedizione con corriere o il ritiro.');
        $date = (string) ($delivery['date'] ?? '');
        if (!is_bookable($cat, $date, ['days' => $zone['days'], 'lead_days' => $ld['lead_days'], 'max_days' => $ld['max_days']], $now)) {
            throw new OrderError("La data di consegna scelta non è più disponibile. Scegline un'altra.");
        }
        $shipping = $subtotal >= $ld['free_over_cents'] ? 0 : $ld['fee_cents'];
        $info = ['mode' => 'local', 'cap' => (string) $delivery['cap'], 'zone' => $zone, 'date' => $date];
    } elseif ($mode === 'pickup') {
        $pk = $cat['pickup'] ?? null;
        if (!$pk || empty($pk['enabled'])) throw new OrderError('Il ritiro in laboratorio non è attivo.');
        $date = (string) ($delivery['date'] ?? '');
        if (!is_bookable($cat, $date, $pk, $now)) throw new OrderError('Il giorno di ritiro scelto non è più disponibile. Scegline un altro.');
        $shipping = 0;
        $info = ['mode' => 'pickup', 'date' => $date];
    } else {
        throw new OrderError('Modalità di consegna non valida.');
    }
    return ['lines' => $lines, 'subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping, 'delivery' => $info];
}

// ---------- Stripe ----------

/** Restituisce coppie [chiave, valore] per POST /v1/checkout/sessions. */
function checkout_params(array $cat, array $priced, string $siteUrl): array {
    $p = [];
    $add = function (string $k, $v) use (&$p) { $p[] = [$k, (string) $v]; };
    $d = $priced['delivery'];
    $add('mode', 'payment');
    $add('locale', 'it');
    $add('success_url', $siteUrl . '/grazie.php?ordine={CHECKOUT_SESSION_ID}');
    $add('cancel_url', $siteUrl . '/shop.php?annullato=1');
    $add('allow_promotion_codes', 'true');
    $add('billing_address_collection', 'auto');
    $add('phone_number_collection[enabled]', 'true');
    $add('tax_id_collection[enabled]', 'true'); // Partita IVA per la fattura
    $add('customer_creation', 'always');

    foreach ($priced['lines'] as $i => $l) {
        $pr = $l['product'];
        $k = "line_items[$i]";
        $add("{$k}[quantity]", $l['qty']);
        $add("{$k}[price_data][currency]", 'eur');
        $add("{$k}[price_data][unit_amount]", $pr['price_cents']);
        $add("{$k}[price_data][product_data][name]", full_name($pr));
        $add("{$k}[price_data][product_data][description]", !empty($pr['pack']) ? 'Confezione: ' . $pr['pack'] : $pr['short']);
        $add("{$k}[price_data][product_data][images][0]", $siteUrl . '/img/' . $pr['image']);
        $add("{$k}[price_data][product_data][metadata][sku]", $pr['id']);
    }

    $so = 'shipping_options[0][shipping_rate_data]';
    $free = $priced['shipping'] === 0;
    if ($d['mode'] === 'ship') {
        $s = $cat['shipping'];
        foreach ($s['countries'] as $i => $c) $add("shipping_address_collection[allowed_countries][$i]", $c);
        $add("{$so}[type]", 'fixed_amount');
        $add("{$so}[display_name]", $free ? $s['label'] . ' — gratuita' : $s['label']);
        $add("{$so}[fixed_amount][amount]", $priced['shipping']);
        $add("{$so}[fixed_amount][currency]", 'eur');
        $add("{$so}[delivery_estimate][minimum][unit]", 'business_day');
        $add("{$so}[delivery_estimate][minimum][value]", $s['days_min']);
        $add("{$so}[delivery_estimate][maximum][unit]", 'business_day');
        $add("{$so}[delivery_estimate][maximum][value]", $s['days_max']);
        $add('custom_text[submit][message]', 'Ogni dolce viene confezionato a mano nel nostro laboratorio di Azzida.');
    } elseif ($d['mode'] === 'local') {
        $add('shipping_address_collection[allowed_countries][0]', 'IT');
        $add("{$so}[type]", 'fixed_amount');
        $add("{$so}[display_name]", 'Consegna diretta ' . format_date($d['date']) . ($free ? ' — gratuita' : ''));
        $add("{$so}[fixed_amount][amount]", $priced['shipping']);
        $add("{$so}[fixed_amount][currency]", 'eur');
        $add('custom_text[shipping_address][message]', 'Te la portiamo noi ' . format_date($d['date']) . '. Consegniamo al CAP ' . $d['cap'] . ' (' . $d['zone']['name'] . ') indicato nel carrello.');
        $add('custom_text[submit][message]', "Ti chiameremo il giorno della consegna per concordare l'orario.");
    } else {
        $add('custom_text[submit][message]', 'Ritiro in laboratorio ad Azzida, Via Algida 43, ' . format_date($d['date']) . '.');
    }

    $add('custom_fields[0][key]', 'biglietto');
    $add('custom_fields[0][label][type]', 'custom');
    $add('custom_fields[0][label][custom]', "Biglietto d'auguri (facoltativo)");
    $add('custom_fields[0][type]', 'text');
    $add('custom_fields[0][optional]', 'true');
    $add('custom_fields[0][text][maximum_length]', 200);
    $add('custom_fields[1][key]', 'note');
    $add('custom_fields[1][label][type]', 'custom');
    $add('custom_fields[1][label][custom]', $d['mode'] === 'pickup' ? 'Orario preferito di ritiro (facoltativo)' : 'Note per la consegna (facoltativo)');
    $add('custom_fields[1][type]', 'text');
    $add('custom_fields[1][optional]', 'true');
    $add('custom_fields[1][text][maximum_length]', 200);

    $tipo = ['ship' => 'Corriere', 'local' => 'Consegna diretta Friuli', 'pickup' => 'Ritiro in laboratorio'][$d['mode']];
    $add('metadata[consegna]', $tipo);
    if (!empty($d['date'])) $add('metadata[data]', $d['date'] . ' (' . format_date($d['date']) . ')');
    if ($d['mode'] === 'local') {
        $add('metadata[cap]', $d['cap']);
        $add('metadata[zona]', $d['zone']['name']);
    }
    $summary = implode(', ', array_map(function ($l) { return $l['qty'] . 'x ' . $l['product']['id']; }, $priced['lines']));
    $add('metadata[ordine]', mb_substr($summary, 0, 490));
    return $p;
}

function encode_pairs(array $pairs): string {
    return implode('&', array_map(function ($kv) { return rawurlencode($kv[0]) . '=' . rawurlencode($kv[1]); }, $pairs));
}
