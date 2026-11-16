<?php
// Riceve i moduli (aziende, ristorazione, contatti, newsletter): invia un'email
// a EMAIL_ORDINI e salva sempre una copia in inc/dati/<modulo>.csv.
declare(strict_types=1);
require __DIR__ . '/inc/config.php';

const MODULI = [
    'aziende' => ['titolo' => 'Richiesta aziende', 'campi' => ['azienda', 'partita_iva', 'nome', 'telefono', 'email', 'tipo', 'quantita', 'data_consegna', 'messaggio'], 'obbligatori' => ['azienda', 'nome', 'email', 'tipo', 'privacy']],
    'ristorazione' => ['titolo' => 'Richiesta ristorazione', 'campi' => ['locale', 'tipo', 'comune', 'nome', 'telefono', 'email', 'interesse', 'frequenza', 'messaggio'], 'obbligatori' => ['locale', 'tipo', 'comune', 'nome', 'telefono', 'email', 'privacy']],
    'contatti' => ['titolo' => 'Messaggio dal sito', 'campi' => ['nome', 'email', 'messaggio'], 'obbligatori' => ['nome', 'email', 'messaggio', 'privacy']],
    'newsletter' => ['titolo' => 'Iscrizione newsletter', 'campi' => ['email'], 'obbligatori' => ['email', 'consenso']],
];

function back(string $page): void { header('Location: ' . $page, true, 303); exit; }

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') back('./');

$modulo = (string) ($_POST['modulo'] ?? '');
if (!isset(MODULI[$modulo])) back('./');
$def = MODULI[$modulo];

// Antispam: campo trappola compilato o invio troppo rapido (< 3 secondi) = bot. Fingiamo successo.
$t = (int) ($_POST['t'] ?? 0);
if (!empty($_POST['sito_web']) || $t <= 0 || time() - $t < 3) back('grazie-richiesta.php');

function clean($v): string {
    if (is_array($v)) $v = implode(', ', array_map('strval', $v));
    $v = trim(str_replace(["\r\n", "\r"], "\n", (string) $v));
    return mb_substr($v, 0, 2000);
}

$valori = [];
foreach ($def['campi'] as $c) $valori[$c] = clean($_POST[$c] ?? '');
foreach ($def['obbligatori'] as $c) {
    if (clean($_POST[$c] ?? '') === '') back('grazie-richiesta.php?errore=1');
}
$email = filter_var($valori['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) back('grazie-richiesta.php?errore=1');

// 1) Copia di sicurezza in CSV (la cartella inc/ non è accessibile dal web)
$dir = __DIR__ . '/inc/dati';
if (!is_dir($dir)) @mkdir($dir, 0750, true);
$file = $dir . '/' . $modulo . '.csv';
$nuovo = !is_file($file);
if ($fh = @fopen($file, 'ab')) {
    if (flock($fh, LOCK_EX)) {
        if ($nuovo) fputcsv($fh, array_merge(['data'], $def['campi']), ';');
        // Evita formule Excel malevole: i valori che iniziano con = + - @ vengono preceduti da '
        $riga = array_map(function ($v) { return preg_match('/^[=+\-@\t]/', $v) ? "'" . $v : $v; }, array_values($valori));
        fputcsv($fh, array_merge([date('Y-m-d H:i')], $riga), ';');
        flock($fh, LOCK_UN);
    }
    fclose($fh);
}

// 2) Email al laboratorio
$testo = $def['titolo'] . " — " . date('d/m/Y H:i') . "\n\n";
foreach ($valori as $k => $v) if ($v !== '') $testo .= ucfirst(str_replace('_', ' ', $k)) . ': ' . $v . "\n";
$oggetto = '=?UTF-8?B?' . base64_encode('[Sito] ' . $def['titolo']) . '?=';
$host = preg_replace('/^www\./', '', preg_replace('/[^A-Za-z0-9.\-]/', '', $_SERVER['HTTP_HOST'] ?? 'giudittateresa.com'));
$intest = "From: Sito Giuditta Teresa <noreply@{$host}>\r\nReply-To: {$email}\r\nContent-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0";
@mail(EMAIL_ORDINI, $oggetto, $testo, $intest);

back('grazie-richiesta.php' . ($modulo === 'newsletter' ? '?newsletter=1' : ''));
