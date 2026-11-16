<?php
// Testi condivisi: premi e domande frequenti.
declare(strict_types=1);

const AWARDS = [
    ['year' => '2016', 'title' => 'Primo premio al Gubana Day', 'img' => 'premio-gubana-day.jpg',
     'text' => 'Tra 14 dolci dei principali gubanifici della regione e dei migliori produttori di panforte senese, scelti a caso dalla produzione normale, la giuria tecnica e quella popolare hanno premiato la nostra Gubana.'],
    ['year' => '2012', 'title' => 'Vittoria al Gubana Day', 'img' => 'stampa-quantobasta.jpg',
     'text' => 'La prima vittoria al Gubana Day, raccontata dalla rivista Quanto Basta, che ha dedicato la copertina a Silvana Chiabai.'],
    ['year' => '2018', 'title' => 'Corriere della Sera', 'img' => 'stampa-corriere.jpg',
     'text' => 'Il Corriere Extra racconta la Colombana, il dolce di Pasqua inventato da Silvana con il ripieno della Gubana.'],
    ['year' => '2016', 'title' => 'Gusti di Frontiera, Gorizia', 'img' => 'premio-raspelli.jpg',
     'text' => 'Abbiamo omaggiato con una nostra Gubana Edoardo Raspelli, premiato per aver fatto conoscere in Italia e nel mondo i prodotti di questa terra.'],
];

function faq(): array {
    global $SHIP, $LOCAL, $PICKUP;
    $f = [
        ['Dove spedite?', 'In tutta Italia, compresi San Marino e Città del Vaticano, con corriere espresso. La consegna richiede di solito ' . $SHIP['days_min'] . '–' . $SHIP['days_max'] . " giorni lavorativi dalla spedizione. Per l'estero scrivici: valutiamo ogni richiesta."],
        ['Quanto costa la spedizione?', intdiv($SHIP['flat_cents'], 100) . ' € per ordine, gratuita sopra i ' . intdiv($SHIP['free_over_cents'], 100) . ' €.'],
        ['Quando spedite?', 'Spediamo dal lunedì al mercoledì, così i dolci non restano fermi in magazzino nel fine settimana e arrivano freschi come escono dal nostro forno.'],
        ['Posso mandarla in regalo con un biglietto?', "Sì. Durante il pagamento puoi indicare un indirizzo di consegna diverso dal tuo e scrivere il testo del biglietto d'auguri: lo scriviamo a mano e lo mettiamo nel pacco."],
    ];
    if ($LOCAL) {
        $f[] = ['Consegnate voi a domicilio?', 'Sì, in Friuli te la portiamo noi, su prenotazione: ' . intdiv($LOCAL['fee_cents'], 100) . ' € a consegna, gratuita sopra i ' . intdiv($LOCAL['free_over_cents'], 100) . ' €. Inserisci il CAP nel carrello e scegli il giorno: consegniamo a giri fissi, con almeno ' . $LOCAL['lead_days'] . ' giorni di preavviso.'];
    }
    if ($PICKUP) {
        $f[] = ['Posso ritirare in laboratorio?', 'Certo: scegli "Ritiro in laboratorio" nel carrello e il giorno che preferisci. Ti aspettiamo ad Azzida, in Via Algida 43.'];
    }
    $f[] = ['Come si conserva la Gubana?', "In un luogo fresco e asciutto, lontano da fonti di calore, nella sua confezione. La data di scadenza è riportata sull'etichetta."];
    $f[] = ['Come posso pagare?', 'Con carta di credito o debito, Apple Pay, Google Pay e PayPal, su pagina sicura Stripe. Non vediamo né conserviamo i dati della tua carta.'];
    $f[] = ['Serve la fattura?', 'Durante il pagamento puoi inserire la Partita IVA e i dati di fatturazione. Per ordini aziendali consulta la pagina dedicata alle aziende.'];
    return $f;
}
