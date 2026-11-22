<?php
/* Minimal SMTP client (no external libraries). Uses settings from config.php. */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) { http_response_code(404); exit; }
require_once __DIR__ . '/config.php';

function smtp_configured() { return SMTP_HOST !== '' && SMTP_USER !== '' && SMTP_PASS !== ''; }

function smtp_header_encode($s) { return '=?UTF-8?B?' . base64_encode(str_replace(["\r", "\n"], ' ', $s)) . '?='; }

/**
 * Send a plain-text email. Throws RuntimeException on failure.
 * $replyTo is optional.
 */
function smtp_send($to, $subject, $body, $replyTo = null) {
  $remote = (SMTP_SECURE === 'ssl' ? 'ssl://' : 'tcp://') . SMTP_HOST . ':' . (int)SMTP_PORT;
  $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => SMTP_HOST]]);
  $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
  if (!$fp) throw new RuntimeException("Could not connect to " . SMTP_HOST . ":" . SMTP_PORT . " ($errstr)");
  stream_set_timeout($fp, 20);

  $read = function () use ($fp) {
    $data = '';
    while (($line = fgets($fp, 1024)) !== false) {
      $data .= $line;
      if (strlen($line) < 4 || $line[3] === ' ') break;   // last line of a multi-line reply
    }
    return $data;
  };
  $cmd = function ($line, $expect, $label = null) use ($fp, $read) {
    if ($line !== null) fwrite($fp, $line . "\r\n");
    $reply = $read();
    if (!in_array((int)substr($reply, 0, 3), (array)$expect, true)) {
      throw new RuntimeException('SMTP error at ' . ($label ?: ($line ?? 'connect')) . ': ' . trim($reply));
    }
    return $reply;
  };

  try {
    $cmd(null, 220);
    $helo = 'xgtech-website';
    $cmd("EHLO $helo", 250);
    if (SMTP_SECURE === 'tls') {
      $cmd('STARTTLS', 220);
      if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
        throw new RuntimeException('Could not start TLS encryption');
      }
      $cmd("EHLO $helo", 250);
    }
    $cmd('AUTH LOGIN', 334);
    $cmd(base64_encode(SMTP_USER), 334, 'username');
    $cmd(base64_encode(SMTP_PASS), 235, 'password (login rejected, check username/password)');
    $cmd('MAIL FROM:<' . MAIL_FROM . '>', 250);
    $cmd('RCPT TO:<' . $to . '>', [250, 251]);
    $cmd('DATA', 354);

    $headers = [
      'Date: ' . date('r'),
      'From: ' . smtp_header_encode('XGTECH Website') . ' <' . MAIL_FROM . '>',
      'To: <' . $to . '>',
      'Subject: ' . smtp_header_encode($subject),
      'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (explode('@', MAIL_FROM)[1] ?? 'xgtech.co.uk') . '>',
      'MIME-Version: 1.0',
      'Content-Type: text/plain; charset=UTF-8',
      'Content-Transfer-Encoding: base64',
    ];
    if ($replyTo) $headers[] = 'Reply-To: <' . str_replace(["\r", "\n", '<', '>'], '', $replyTo) . '>';
    $msg = implode("\r\n", $headers) . "\r\n\r\n" . rtrim(chunk_split(base64_encode($body), 76, "\r\n"));
    $cmd($msg . "\r\n.", 250, 'sending message');
    try { $cmd('QUIT', 221); } catch (Exception $e) { /* ignore */ }
  } finally {
    fclose($fp);
  }
}
