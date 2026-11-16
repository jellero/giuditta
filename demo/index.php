<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';

$featured = array_slice(array_values(array_filter($PRODUCTS, function ($p) { return !empty($p['featured']) && sellable($p); })), 0, 4);

page_start('Gubana artigianale delle Valli del Natisone dal 1981 | Giuditta Teresa',
    'Gubana, Strucchi e Colombana fatti a mano ad Azzida, Valli del Natisone, dal 1981. Primo premio al Gubana Day. Spedizione in tutta Italia e consegna diretta in Friuli.',
    ['ld' => [faq_ld()]]);
?>
<section class="hero">
  <img src="img/hero.jpg" alt="Gubana e Strucchi Giuditta Teresa sulla tavola" width="1400" height="779" fetchpriority="high">
  <div class="wrap">
    <span class="eyebrow">Azzida · Valli del Natisone · dal 1981</span>
    <h1>La Gubana, come si fa da noi.</h1>
    <p>Un ripieno generoso, una ricetta di famiglia mai cambiata, ogni dolce formato a mano nel nostro piccolo laboratorio.</p>
    <div class="hero-actions">
      <a class="btn btn-light" href="shop.php">Scegli i tuoi dolci</a>
      <a class="btn btn-ghost" href="la-nostra-storia.php">La nostra storia</a>
    </div>
  </div>
</section>

<section class="trust" aria-label="Perché sceglierci">
  <ul class="wrap">
    <li><?= ICON['award'] ?><span><b>Primo premio</b>Gubana Day 2012 e 2016</span></li>
    <li><?= ICON['hand'] ?><span><b>Fatta a mano</b>Un dolce alla volta</span></li>
    <li><?= ICON['home'] ?><span><b>Ricetta di famiglia</b>Dal 1981 ad Azzida</span></li>
    <?php if ($LOCAL): ?>
    <li><?= ICON['truck'] ?><span><b>In Friuli consegniamo noi</b>su prenotazione</span></li>
    <?php else: ?>
    <li><?= ICON['truck'] ?><span><b>Spedizione gratuita</b>sopra i <?= intdiv($SHIP['free_over_cents'], 100) ?> €</span></li>
    <?php endif; ?>
  </ul>
</section>

<section class="section">
  <div class="wrap split">
    <div>
      <span class="eyebrow">Noi crediamo nel</span>
      <h2>Segreto del ripieno</h2>
      <p class="lead">La ricetta si tramanda nella nostra famiglia da generazioni, e ancora oggi si basa su una promessa fatta a mamma Lidia: mantenere fede alla qualità.</p>
      <p>Perché è il ripieno a dare alla Gubana quel sapore ricco e pieno. Un tempo la sua abbondanza era un augurio di buona fortuna per la famiglia; per noi è una regola che non si discute.</p>
      <p><a href="la-nostra-storia.php">Scopri come nasce una Gubana →</a></p>
    </div>
    <img src="img/lab-composizione.jpg" alt="Gubana tagliata con il ripieno in vista" width="1400" height="953" loading="lazy">
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Dal nostro forno</span>
      <h2>I nostri dolci</h2>
      <p class="muted">Pochi dolci, fatti bene. Abbiamo cominciato con la Gubana e negli anni abbiamo aggiunto solo ciò che nasce dallo stesso ripieno e dalla stessa cura.</p>
    </div>
    <div class="grid"><?= cards($featured) ?></div>
    <p class="center" style="margin-top:36px"><a class="btn btn-ghost" href="shop.php">Vedi tutti i dolci</a></p>
  </div>
</section>

<section class="section section-dark">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">Nel laboratorio</span><h2>Come nasce la regina dei dolci friulani</h2></div>
    <?= steps_html() ?>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">Premi e stampa</span><h2>Scelta dalle giurie, e da chi la assaggia</h2></div>
    <div class="awards"><?= awards_html(array_slice(AWARDS, 0, 3)) ?></div>
  </div>
</section>

<?php if ($LOCAL): ?>
<section class="section hero-local">
  <div class="wrap split">
    <div>
      <span class="pill">Novità</span>
      <h2>In Friuli te la portiamo noi</h2>
      <p class="lead">Da oggi consegniamo personalmente, su prenotazione, a casa tua o in ufficio. Niente corriere: dal nostro forno alla tua tavola.</p>
      <p><?= eur($LOCAL['fee_cents']) ?> a consegna, gratis sopra <?= eur($LOCAL['free_over_cents']) ?>. E in più i dolci freschi che non spediamo.</p>
      <?= cap_checker('cap-home') ?>
    </div>
    <img src="img/focaccia.jpg" alt="Focaccia Giuditta Teresa fresca di forno" width="855" height="570" loading="lazy">
  </div>
</section>
<?php endif; ?>

<section class="section section-alt">
  <div class="wrap split reverse">
    <div>
      <span class="eyebrow">Bar, alberghi, ristoranti e aziende</span>
      <h2>Un dolce che racconta un territorio</h2>
      <p class="lead">Il fine pasto friulano per il vostro menù, la colazione del vostro albergo, i regali di Natale per clienti e collaboratori.</p>
      <p>In Friuli consegniamo noi, a giorni fissi. Per i regali aziendali spediamo anche a più indirizzi.</p>
      <div class="hero-actions"><a class="btn" href="ristorazione.php">Per bar e ristoranti</a><a class="btn btn-ghost" href="aziende.php">Regali aziendali</a></div>
    </div>
    <img src="img/tronco.jpg" alt="Tronco di Gubana a fette" width="855" height="570" loading="lazy">
  </div>
</section>

<section class="section">
  <div class="wrap narrow center">
    <blockquote class="quote">“I dolci si mangiano alla fine del pasto, perché dopo il corpo è necessario nutrire l'anima.”<cite>Il nostro motto</cite></blockquote>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="section-head center"><span class="eyebrow">Domande frequenti</span><h2>Prima di ordinare</h2></div>
    <?= faq_html() ?>
  </div>
</section>
<?php page_end();
