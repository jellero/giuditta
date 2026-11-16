<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
page_start('Premi e stampa',
    'Primo premio al Gubana Day 2012 e 2016, il Corriere della Sera, la rivista Quanto Basta: cosa dicono della Gubana Giuditta Teresa.',
    ['image' => 'premio-gubana-day.jpg']);
?>
<section class="page-hero"><div class="wrap">
  <span class="eyebrow">Premi e stampa</span>
  <h1>Dicono di noi</h1>
  <p class="lead narrow">Non facciamo concorsi per collezionare medaglie, ma quando la nostra Gubana viene scelta a caso dalla produzione di ogni giorno e vince, ne siamo orgogliosi.</p>
</div></section>
<section style="padding-bottom:80px"><div class="wrap">
  <div class="awards"><?= awards_html(AWARDS) ?></div>
  <p class="muted" style="margin-top:36px">Siamo parte delle <strong>Strade del Vino e dei Sapori del Friuli Venezia Giulia</strong> e di <strong>Colli Orientali — Terre da vivere insieme</strong>. Di noi hanno scritto anche Il Golosario, LiveFVG, Vino e Vacanze e Prodotti-regionali.com.</p>
</div></section>
<?php page_end();
