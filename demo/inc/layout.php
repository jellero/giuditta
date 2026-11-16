<?php
// Intestazione, piè di pagina e componenti comuni a tutte le pagine.
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/content.php';

const ICON = [
    'cart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 8h14l-1.2 11.1a2 2 0 0 1-2 1.9H8.2a2 2 0 0 1-2-1.9L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>',
    'menu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
    'award' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="9" r="6"/><path d="m8.5 14-1.5 7 5-3 5 3-1.5-7"/></svg>',
    'hand' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 3c3 3 5 5.5 5 9a5 5 0 0 1-10 0c0-3.5 2-6 5-9Z"/><path d="M12 21v-6"/></svg>',
    'home' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/></svg>',
    'truck' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/></svg>',
    'fb' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.4H8v3h2.6V21h2.9Z"/></svg>',
    'ig' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r=".8" fill="currentColor"/></svg>',
];

function nav_items(): array {
    global $LOCAL;
    $n = [
        ['shop.php', 'I nostri dolci'],
        ['consegna-in-friuli.php', 'Consegna in Friuli'],
        ['la-nostra-storia.php', 'La nostra storia'],
        ['ristorazione.php', 'Bar e ristoranti'],
        ['aziende.php', 'Aziende'],
        ['contatti.php', 'Contatti'],
    ];
    if (!$LOCAL) $n = array_values(array_filter($n, function ($i) { return $i[0] !== 'consegna-in-friuli.php'; }));
    return $n;
}

function ld(array $obj): string {
    return '<script type="application/ld+json">' . str_replace('</', '<\/', json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</script>';
}

function org_ld(): array {
    global $BIZ;
    $site = site_url();
    return [
        '@context' => 'https://schema.org', '@type' => 'Bakery', '@id' => $site . '/#azienda',
        'name' => $BIZ['name'], 'description' => 'Gubana artigianale delle Valli del Natisone, prodotta ad Azzida dal 1981.',
        'url' => $site . '/', 'logo' => $site . '/img/logo.png', 'image' => $site . '/img/hero.jpg',
        'telephone' => $BIZ['phone'], 'email' => $BIZ['email'], 'vatID' => 'IT' . $BIZ['vat'], 'foundingDate' => '1981',
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Via Algida, 43, Località Azzida', 'postalCode' => $BIZ['zip'],
            'addressLocality' => $BIZ['city'], 'addressRegion' => $BIZ['prov'], 'addressCountry' => 'IT'],
        'areaServed' => [['@type' => 'Country', 'name' => 'Italia'], ['@type' => 'AdministrativeArea', 'name' => 'Friuli Venezia Giulia']],
        'sameAs' => array_values(array_filter([$BIZ['facebook'], $BIZ['instagram']])),
    ];
}

/** Dati per il carrello (solo visualizzazione: il server ricontrolla tutto). */
function cart_data(): array {
    global $PRODUCTS, $SHIP, $LOCAL, $PICKUP, $CAT;
    $prods = [];
    foreach ($PRODUCTS as $p) {
        if (!sellable($p)) continue;
        $prods[] = ['id' => $p['id'], 'name' => $p['name'], 'weight' => $p['weight'] ?? '', 'pack' => $p['pack'] ?? '',
            'price_cents' => $p['price_cents'], 'image' => $p['image'], 'available' => true, 'local_only' => !empty($p['local_only'])];
    }
    $local = null;
    if ($LOCAL) {
        $local = ['fee_cents' => $LOCAL['fee_cents'], 'free_over_cents' => $LOCAL['free_over_cents'],
            'lead_days' => $LOCAL['lead_days'], 'max_days' => $LOCAL['max_days'], 'zones' => []];
        foreach ($LOCAL['zones'] as $z) $local['zones'][] = ['id' => $z['id'], 'name' => $z['name'], 'days' => $z['days'], 'caps' => $z['caps']];
    }
    return [
        'products' => $prods,
        'shipping' => ['flat_cents' => $SHIP['flat_cents'], 'free_over_cents' => $SHIP['free_over_cents']],
        'local' => $local,
        'pickup' => $PICKUP ? ['days' => $PICKUP['days'], 'lead_days' => $PICKUP['lead_days'], 'max_days' => $PICKUP['max_days']] : null,
        'closed_dates' => $CAT['closed_dates'] ?? [],
    ];
}

/**
 * Apre la pagina.  $o: image, ld (array di oggetti schema.org), body (attributi), noindex (bool)
 */
function page_start(string $title, string $desc, array $o = []): void {
    global $SHIP, $LOCAL;
    $site = site_url();
    $page = current_page();
    $canon = $site . '/' . ($page === 'index.php' ? '' : $page) . (!empty($_GET['p']) ? '?p=' . rawurlencode((string) $_GET['p']) : '');
    $full = mb_strpos($title, 'Giuditta Teresa') !== false ? $title : $title . ' | Giuditta Teresa';
    $image = $site . '/img/' . ($o['image'] ?? 'hero.jpg');
    $noindex = DEMO || !empty($o['noindex']);
    $lds = ld(org_ld());
    foreach ($o['ld'] ?? [] as $x) $lds .= ld($x);
    $topbar = $LOCAL
        ? '<a href="consegna-in-friuli.php"><strong>Novità:</strong> in Friuli te la portiamo noi, su prenotazione →</a>'
        : 'Spedizione <strong>gratuita</strong> sopra i ' . intdiv($SHIP['free_over_cents'], 100) . ' € · Fatta a mano ad Azzida, Valli del Natisone';
    ?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($full) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<?php if ($noindex): ?><meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:locale" content="it_IT">
<meta property="og:site_name" content="Giuditta Teresa">
<meta property="og:title" content="<?= e($full) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta property="og:image" content="<?= e($image) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#2b3521">
<link rel="icon" href="img/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;1,9..144,400;1,9..144,500&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=3">
<?= $lds ?>
</head>
<body<?= !empty($o['body']) ? ' ' . $o['body'] : '' ?>>
<a class="skip" href="#main">Vai al contenuto</a>
<?php if (DEMO): ?><div class="demo-bar">Anteprima del nuovo sito · i pagamenti sono in modalità di prova</div>
<?php endif; ?>
<div class="topbar"><?= $topbar ?></div>
<header class="header">
  <div class="wrap">
    <button class="menu-btn" aria-label="Menu" aria-expanded="false"><?= ICON['menu'] ?></button>
    <a class="brand" href="./">Giuditta Teresa<small>Gubana dal 1981</small></a>
    <nav class="nav" aria-label="Principale">
<?php foreach (nav_items() as [$u, $t]): ?>      <a href="<?= $u ?>"<?= $page === $u ? ' aria-current="page"' : '' ?>><?= e($t) ?></a>
<?php endforeach; ?>
    </nav>
    <button class="cart-btn" aria-label="Carrello"><?= ICON['cart'] ?><span class="cart-count" data-n="0">0</span></button>
  </div>
</header>
<main id="main">
<?php
}

function page_end(): void {
    global $BIZ, $LOCAL;
    $social = '';
    foreach ([[$BIZ['facebook'], 'Facebook', 'fb'], [$BIZ['instagram'], 'Instagram', 'ig']] as [$u, $n, $i]) {
        if ($u) $social .= '<a href="' . e($u) . '" rel="noopener" aria-label="' . $n . '">' . ICON[$i] . '</a>';
    }
    ?>
</main>
<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <img class="footer-logo" src="img/logo.png" alt="Giuditta Teresa" width="309" height="80" loading="lazy">
        <p>La Gubana delle Valli del Natisone, fatta a mano dalla nostra famiglia dal 1981.</p>
        <div class="social"><?= $social ?></div>
        <form method="POST" action="invia.php" style="margin-top:22px">
          <?= form_guard('newsletter') ?>
          <h4 style="margin-bottom:6px">Le nostre novità</h4>
          <span class="small" style="color:#b8a88f">Poche email all'anno: Natale, Pasqua e i dolci di stagione.</span>
          <div class="newsletter"><input type="email" name="email" required placeholder="La tua email" aria-label="Email"><button class="btn btn-sm btn-light" type="submit">Iscriviti</button></div>
          <label class="newsletter-consent"><input type="checkbox" name="consenso" value="sì" required> <span>Acconsento a ricevere le email di Giuditta Teresa (<a href="<?= e($BIZ['privacy']) ?>" rel="noopener">privacy</a>).</span></label>
        </form>
      </div>
      <div>
        <h4>Negozio</h4>
        <ul>
          <li><a href="shop.php">Tutti i dolci</a></li>
          <li><a href="prodotto.php?p=gubana-1000">La Gubana</a></li>
          <?php if ($LOCAL): ?><li><a href="consegna-in-friuli.php">Consegna in Friuli</a></li><?php endif; ?>
          <li><a href="ristorazione.php">Bar, alberghi e ristoranti</a></li>
          <li><a href="aziende.php">Regali aziendali</a></li>
          <li><a href="aziende.php#rivenditori">Rivenditori</a></li>
        </ul>
      </div>
      <div>
        <h4>Informazioni</h4>
        <ul>
          <li><a href="la-nostra-storia.php">La nostra storia</a></li>
          <li><a href="premi-e-stampa.php">Premi e stampa</a></li>
          <li><a href="condizioni-di-vendita.php">Spedizioni e condizioni</a></li>
          <li><a href="<?= e($BIZ['privacy']) ?>" rel="noopener">Privacy</a></li>
        </ul>
      </div>
      <div>
        <h4>Laboratorio</h4>
        <ul>
          <li><?= e($BIZ['street']) ?></li>
          <li><?= e($BIZ['zip'] . ' ' . $BIZ['city'] . ' (' . $BIZ['prov'] . ')') ?></li>
          <li><a href="<?= tel() ?>"><?= e($BIZ['phone_display']) ?></a></li>
          <li><a href="mailto:<?= e($BIZ['email']) ?>"><?= e($BIZ['email']) ?></a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> Giuditta Teresa · P. IVA <?= e($BIZ['vat']) ?></span>
      <span>Pagamenti sicuri con Stripe</span>
    </div>
  </div>
</footer>

<div class="drawer-backdrop"></div>
<aside class="drawer" id="cart" aria-hidden="true" aria-label="Carrello">
  <div class="drawer-head"><h2>Il tuo carrello</h2><button class="drawer-close" aria-label="Chiudi">×</button></div>
  <div class="drawer-body"></div>
  <div class="drawer-foot" hidden>
    <div class="delivery"></div>
    <div class="ship-bar"></div>
    <div class="totals small"><span>Subtotale</span><span class="js-subtotal"></span></div>
    <div class="totals small"><span class="js-shipping-label">Spedizione</span><span class="js-shipping"></span></div>
    <div class="totals grand"><span>Totale</span><b class="js-total"></b></div>
    <label class="terms"><input type="checkbox" id="accept-terms"> <span>Ho letto e accetto le <a href="condizioni-di-vendita.php" target="_blank">condizioni di vendita</a>.</span></label>
    <button class="btn btn-block" id="checkout-btn">Vai al pagamento sicuro</button>
    <div class="cart-error" role="alert"></div>
    <div class="pay-icons"><span>Carta</span><span>Apple Pay</span><span>Google Pay</span><span>PayPal</span></div>
  </div>
</aside>
<script>window.GT=<?= json_encode(cart_data(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;</script>
<script src="assets/cart.js?v=3" defer></script>
</body>
</html>
<?php
}

/** Campi nascosti antispam per i moduli: nome modulo, trappola per bot, orario. */
function form_guard(string $name): string {
    return '<input type="hidden" name="modulo" value="' . e($name) . '">'
        . '<input type="hidden" name="t" value="' . time() . '">'
        . '<p class="hp"><label>Non compilare: <input name="sito_web" tabindex="-1" autocomplete="off"></label></p>';
}

// ---------- Componenti ----------

function product_card(array $p, string $h = 'h3'): string {
    if (!empty($p['local_only'])) $badge = '<span class="badge badge-local">Solo in Friuli</span>';
    elseif (!empty($p['badge']) || !empty($p['seasonal'])) $badge = '<span class="badge">' . e($p['badge'] ?? $p['seasonal']) . '</span>';
    else $badge = '';
    $meta = implode(' · ', array_filter([$p['subtitle'] ?? '', $p['weight'] ?? '', $p['pack'] ?? '']));
    if (sellable($p)) {
        $cta = '<button class="btn btn-sm" data-add="' . e($p['id']) . '">Aggiungi</button>';
        $price = '<span class="price">' . eur($p['price_cents']) . '</span>';
    } elseif (!empty($p['local_only'])) {
        $cta = '<a class="btn btn-sm btn-ghost" href="' . tel() . '">Prenota</a>';
        $price = '<span class="muted small">Su prenotazione</span>';
    } else {
        $cta = '<span class="muted small">Non disponibile ora</span>';
        $price = has_price($p) ? '<span class="price">' . eur($p['price_cents']) . '</span>' : '';
    }
    $title = has_price($p) ? '<a href="' . e(product_url($p)) . '">' . e($p['name']) . '</a>' : e($p['name']);
    return '<article class="product">'
        . '<div class="product-img">' . $badge . '<img src="img/' . e($p['image']) . '" alt="' . e(full_name($p)) . ' Giuditta Teresa" width="855" height="570" loading="lazy"></div>'
        . '<div class="product-body"><' . $h . '>' . $title . '</' . $h . '>'
        . '<div class="product-meta">' . e($meta) . '</div>'
        . '<p class="product-short">' . e($p['short']) . '</p>'
        . '<div class="product-foot">' . $price . $cta . '</div></div></article>';
}

function cards(array $list, string $h = 'h3'): string {
    return implode('', array_map(function ($p) use ($h) { return product_card($p, $h); }, $list));
}

function national(): array { global $PRODUCTS; return array_values(array_filter($PRODUCTS, function ($p) { return empty($p['local_only']); })); }

function fresh(): array { global $PRODUCTS; return array_values(array_filter($PRODUCTS, function ($p) { return !empty($p['local_only']); })); }

function zones_table(): string {
    global $LOCAL;
    $rows = '';
    foreach ($LOCAL['zones'] as $z) {
        $rows .= '<tr><td><b>' . e($z['name']) . '</b><small>' . e($z['places']) . '</small></td><td>'
            . e(ucfirst(days_text($z['days']))) . '</td><td>' . e(implode(', ', $z['caps'])) . '</td></tr>';
    }
    return '<table class="zones"><thead><tr><th>Zona</th><th>Giorno di consegna</th><th>CAP</th></tr></thead><tbody>' . $rows . '</tbody></table>';
}

function awards_html(array $items): string {
    $h = '';
    foreach ($items as $a) {
        $h .= '<article class="award"><img src="img/' . e($a['img']) . '" alt="' . e($a['title']) . '" loading="lazy"><div><span class="year">'
            . e($a['year']) . '</span><h3>' . e($a['title']) . '</h3><p>' . e($a['text']) . '</p></div></article>';
    }
    return $h;
}

function faq_html(): string {
    $h = '';
    foreach (faq() as [$q, $a]) $h .= '<details><summary>' . e($q) . '</summary><div>' . e($a) . '</div></details>';
    return '<div class="details faq">' . $h . '</div>';
}

function faq_ld(): array {
    $items = [];
    foreach (faq() as [$q, $a]) $items[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
    return ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items];
}

function steps_html(): string {
    return '<div class="steps">
  <div class="step"><img src="img/fase-ripieno.jpg" alt="Preparazione del ripieno con i liquori" width="340" height="340" loading="lazy">
    <h3>Il ripieno</h3><p>Gli ingredienti, selezionati e puliti a mano, vengono mescolati e bagnati con i liquori secondo la nostra ricetta, fino alla giusta umidità e consistenza.</p></div>
  <div class="step"><img src="img/fase-formatura.jpg" alt="Formatura a mano delle gubane" width="340" height="340" loading="lazy">
    <h3>La formatura</h3><p>La pasta viene stesa, farcita e arrotolata su se stessa, poi acciambellata nella formella. Ultima carezza prima del forno: l\'impasto zuccherino.</p></div>
  <div class="step"><img src="img/fase-forno.jpg" alt="Gubane appena sfornate" width="340" height="340" loading="lazy">
    <h3>La cottura</h3><p>Solo all\'uscita dal forno si scopre se l\'impasto era ben lievitato e il ripieno al punto giusto. Dopo tanti anni, ogni infornata è ancora una sfida.</p></div>
</div>';
}

function cap_checker(string $id = 'cap-input'): string {
    $idE = e($id);
    return '<form class="cap-check" onsubmit="return false">
  <label class="sr" for="' . $idE . '">Il tuo CAP</label>
  <input id="' . $idE . '" inputmode="numeric" maxlength="5" autocomplete="postal-code" placeholder="Il tuo CAP, es. 33043">
  <button class="btn" type="button" data-cap-check="' . $idE . '">Verifica</button>
</form>
<div class="cap-result" id="' . $idE . '-result" aria-live="polite"></div>';
}
