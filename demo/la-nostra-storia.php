<?php
declare(strict_types=1);
require __DIR__ . '/inc/layout.php';
page_start('La nostra storia: la Gubana di famiglia dal 1981',
    'Dal 1981 la famiglia di Silvana Chiabai produce a mano la Gubana delle Valli del Natisone ad Azzida, con la ricetta segreta del ripieno tramandata da generazioni.',
    ['image' => 'lab-composizione.jpg']);
?>
<section class="page-hero"><div class="wrap split">
  <div>
    <span class="eyebrow">La nostra storia</span>
    <h1>Una Gubana nel cuore</h1>
    <p class="lead">Ci sono mestieri che nascono dalla passione: fare dolci è uno di questi.</p>
    <p>La tradizione della Gubana si unisce alla storia della nostra famiglia dal 1981, quando rilevammo la ditta Giuditta Teresa ad Azzida, nelle Valli del Natisone. Un'avventura che è diventata la nostra missione, come se la Gubana stessa ci avesse chiamati a proseguire il percorso di bontà e generosità che ancora oggi caratterizza queste valli.</p>
    <p>Il cuore del laboratorio è Silvana Chiabai, pasticcera e titolare: è lei a custodire la ricetta di famiglia ed è lei ad aver inventato la Colombana.</p>
  </div>
  <img src="img/gubana-scontornata.jpg" alt="Gubana Giuditta Teresa" width="500" height="369">
</div></section>

<section class="section section-alt"><div class="wrap split reverse">
  <div>
    <span class="eyebrow">La promessa</span>
    <h2>Il segreto del ripieno</h2>
    <p>L'abbondanza di farcitura è il segno distintivo della Gubana. Se oggi ne apprezziamo la complessità e la quantità, immaginiamo cosa significasse in tempi in cui imbandire una tavola non era facile come oggi: la generosità del ripieno era un augurio di buona fortuna, una festa per il palato e per la famiglia.</p>
    <p>La nostra ricetta si tramanda da generazioni e si basa su una promessa fatta a mamma Lidia: <em>mantenere fede alla qualità</em>. Per questo le noci le puliamo ancora a mano, e la bagnatura dei liquori la dosiamo noi, come abbiamo sempre fatto.</p>
  </div>
  <img src="img/pelatura-noci.jpg" alt="Pulizia a mano delle noci" width="500" height="369" loading="lazy">
</div></section>

<section class="section section-dark"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Le fasi della lavorazione</span><h2>Come nasce una Gubana</h2></div>
  <?= steps_html() ?>
</div></section>

<section class="section"><div class="wrap">
  <div class="section-head"><span class="eyebrow">Nel laboratorio</span><h2>Mani, forno e pazienza</h2></div>
  <div class="gallery">
    <figure><img src="img/lab-noci.jpg" alt="Pulizia manuale delle noci" loading="lazy"><figcaption>Le noci, pulite a mano</figcaption></figure>
    <figure><img src="img/lab-impasto.jpg" alt="Impasto di nocciole" loading="lazy"><figcaption>Il ripieno</figcaption></figure>
    <figure><img src="img/lab-frittura.jpg" alt="Frittura degli Strucchi" loading="lazy"><figcaption>La frittura degli Strucchi</figcaption></figure>
    <figure><img src="img/lab-strucchi.jpg" alt="Asciugatura degli Strucchi" loading="lazy"><figcaption>Strucchi ad asciugare</figcaption></figure>
    <figure><img src="img/lab-composizione.jpg" alt="Composizione con Gubana" loading="lazy"><figcaption>La Gubana</figcaption></figure>
    <figure><img src="img/tronco.jpg" alt="Tronco di Gubana tagliato" loading="lazy"><figcaption>Il Tronco di Gubana</figcaption></figure>
  </div>
  <p class="small" style="margin-top:14px">Fotografie di Ennio Calice.</p>
</div></section>

<section class="section section-alt"><div class="wrap narrow center">
  <h2>Assaggiala a casa tua</h2>
  <p class="lead">Spediamo in tutta Italia<?= $LOCAL ? ' e in Friuli te la portiamo noi' : '' ?>.</p>
  <a class="btn" href="shop.php">Scegli i tuoi dolci</a>
</div></section>
<?php page_end();
