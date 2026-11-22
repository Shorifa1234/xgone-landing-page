<?php
/* Receives a visitor's location ONLY when they ticked the consent box, and lets them delete it again. */
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

function reply($code, $data) { http_response_code($code); echo json_encode($data); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') reply(405, ['ok' => false, 'error' => 'Method not allowed']);

// Only accept requests from our own pages
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) {
  reply(403, ['ok' => false, 'error' => 'Forbidden']);
}

$in = json_decode(file_get_contents('php://input', false, null, 0, 4096), true);
if (!is_array($in)) reply(400, ['ok' => false, 'error' => 'Bad request']);
$action = $in['action'] ?? '';

try {

if ($action === 'save') {
  if (($in['consent'] ?? false) !== true) reply(400, ['ok' => false, 'error' => 'Consent is required']);
  $lat = filter_var($in['lat'] ?? null, FILTER_VALIDATE_FLOAT);
  $lng = filter_var($in['lng'] ?? null, FILTER_VALIDATE_FLOAT);
  $acc = filter_var($in['acc'] ?? null, FILTER_VALIDATE_FLOAT);
  if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    reply(400, ['ok' => false, 'error' => 'Invalid location']);
  }
  $acc = ($acc === false || $acc < 0) ? null : min($acc, 100000);

  if (loc_count() >= LOC_MAX_ROWS) reply(503, ['ok' => false, 'error' => 'Storage full']);
  $id = loc_add($lat, $lng, $acc);
  reply(200, ['ok' => true, 'id' => $id, 'retention_days' => LOC_RETENTION_DAYS]);
}

if ($action === 'delete') {
  $ids = array_filter((array)($in['ids'] ?? []), function ($x) { return is_string($x) && preg_match('/^[a-f0-9]{32}$/', $x); });
  if (!$ids) reply(400, ['ok' => false, 'error' => 'Nothing to delete']);
  $removed = loc_delete_ids(array_slice(array_values($ids), 0, 100));
  reply(200, ['ok' => true, 'removed' => $removed]);
}

reply(400, ['ok' => false, 'error' => 'Unknown action']);
} catch (PDOException $ex) {
  error_log('Location DB error: ' . $ex->getMessage());
  reply(500, ['ok' => false, 'error' => 'Database unavailable']);
}
