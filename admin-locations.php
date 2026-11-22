<?php
/* Private admin page: view locations visitors chose to share. */
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/mailer.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'login') {
    $_SESSION['tries'] = ($_SESSION['tries'] ?? 0) + 1;
    if ($_SESSION['tries'] > 10) { $loginError = 'Too many attempts. Close the browser and try again later.'; }
    elseif (password_verify((string)($_POST['password'] ?? ''), ADMIN_PASSWORD_HASH)) {
      session_regenerate_id(true);
      $_SESSION['admin'] = true; $_SESSION['tries'] = 0;
      header('Location: admin-locations.php'); exit;
    } else { usleep(500000); $loginError = 'Wrong password.'; }
  } elseif (!empty($_SESSION['admin']) && hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
    if ($action === 'logout') { session_destroy(); header('Location: admin-locations.php'); exit; }
    if ($action === 'delete')     loc_delete_ids([(string)($_POST['id'] ?? '')]);
    if ($action === 'delete_all') loc_delete_all();
    if ($action === 'delete_enq') enq_delete((string)($_POST['id'] ?? ''));
    header('Location: admin-locations.php'); exit;
  }
}

$isAdmin = !empty($_SESSION['admin']);
$rows = []; $enqs = []; $dbError = '';
if ($isAdmin) {
  try { $rows = loc_all(); $enqs = enq_all(); }
  catch (PDOException $ex) { $dbError = 'Could not connect to the database "' . DB_NAME . '". Check that MySQL is running and the DB settings in config.php are correct.'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Shared Locations | XGTECH Admin</title>
  <?php if ($isAdmin): ?><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"><?php endif; ?>
  <style>
    :root{--navy:#06265d;--navy2:#03183e;--green:#65b51c;--ink:#14213b;--muted:#586579;--line:#e4e9f1;--pale:#f4f7fb}
    *{box-sizing:border-box}body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Arial,sans-serif;background:var(--pale);color:var(--ink);line-height:1.5}
    .wrap{width:min(1180px,100% - 32px);margin:auto}
    header{background:var(--navy2);color:#fff;padding:16px 0}
    header .wrap{display:flex;justify-content:space-between;align-items:center;gap:16px}
    header b span{color:#8bd13e}
    .btn{background:var(--green);color:#fff;border:0;border-radius:8px;padding:10px 16px;font-weight:700;cursor:pointer;font:inherit;font-weight:700;text-decoration:none}
    .btn.ghost{background:transparent;border:1px solid rgba(255,255,255,.4)}
    .btn.danger{background:#c53030}.btn.small{padding:6px 10px;font-size:13px}
    .card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:22px;margin:22px 0}
    .login{max-width:380px;margin:80px auto}
    input{width:100%;font:inherit;padding:12px;border:1px solid #d5dce7;border-radius:8px;margin:8px 0 14px}
    .err{color:#9b1c1c;font-size:14px}
    #map{height:420px;border-radius:12px}
    .top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    h1{font-size:24px;color:var(--navy);margin:0}
    .muted{color:var(--muted);font-size:14px}
    table{width:100%;border-collapse:collapse;font-size:14px}
    th,td{text-align:left;padding:10px 8px;border-bottom:1px solid var(--line)}
    th{color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:1px}
    .scroll{overflow-x:auto}
    a{color:var(--navy)}
  </style>
</head>
<body>
<?php if (!$isAdmin): ?>
  <div class="wrap">
    <form class="card login" method="post">
      <h1>XGTECH Admin</h1>
      <p class="muted">Shared visitor locations</p>
      <?php if ($loginError): ?><p class="err"><?= e($loginError) ?></p><?php endif; ?>
      <label for="pw">Password</label>
      <input id="pw" type="password" name="password" autocomplete="current-password" required autofocus>
      <input type="hidden" name="action" value="login">
      <button class="btn" type="submit" style="width:100%">Log in</button>
    </form>
  </div>
<?php else: ?>
  <header><div class="wrap"><b style="display:flex;align-items:center;gap:12px"><img src="logo-1.png" alt="XGTECH" height="36" style="border-radius:6px"> Admin</b>
    <form method="post"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><button class="btn ghost small" name="action" value="logout">Log out</button></form>
  </div></header>
  <div class="wrap">
    <?php if ($dbError): ?><div class="card"><p class="err">⚠ <?= e($dbError) ?></p></div><?php endif; ?>
    <div class="card scroll" id="requests">
      <h1>📩 <?= count($enqs) ?> demo request<?= count($enqs) === 1 ? '' : 's' ?></h1>
      <p class="muted">Every request from the contact form is saved here, even if the email could not be sent. Kept for <?= ENQ_RETENTION_DAYS ?> days.</p>
      <?php if (!smtp_configured()): ?><p class="err">⚠ Email is not set up yet. Requests are only saved here. Add your SMTP details in config.php to receive them by email.</p><?php endif; ?>
      <?php if ($enqs): ?>
      <table>
        <thead><tr><th>Date</th><th>Name / Company</th><th>Contact</th><th>Industry</th><th>Message</th><th>Email</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($enqs as $q): ?>
          <tr>
            <td style="white-space:nowrap"><?= e(date('d M Y, H:i', $q['ts'])) ?></td>
            <td><b><?= e($q['name']) ?></b><br><span class="muted"><?= e($q['company']) ?></span></td>
            <td><a href="mailto:<?= e($q['email']) ?>?subject=<?= rawurlencode('Your XGOne demo request') ?>"><?= e($q['email']) ?></a><br><?php if ($q['phone'] !== ''): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $q['phone'])) ?>"><?= e($q['phone']) ?></a><?php endif; ?></td>
            <td><?= e($q['industry']) ?></td>
            <td style="max-width:320px;white-space:pre-wrap"><?= e($q['message']) ?></td>
            <td><?= $q['mail'] === 'sent' ? '✅ Sent' : '<span class="err" title="' . e($q['mail']) . '">⚠ ' . e(ucfirst(strtok($q['mail'], ':'))) . '</span>' ?></td>
            <td><form method="post" onsubmit="return confirm('Delete this request?')"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id" value="<?= e($q['id']) ?>"><button class="btn danger small" name="action" value="delete_enq">Delete</button></form></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?><p class="muted">No demo requests yet.</p><?php endif; ?>
    </div>

    <div class="card">
      <div class="top">
        <div><h1>📍 <?= count($rows) ?> shared location<?= count($rows) === 1 ? '' : 's' ?></h1>
          <p class="muted">Only visitors who ticked the consent box appear here. Entries are deleted automatically after <?= LOC_RETENTION_DAYS ?> days.</p></div>
        <?php if ($rows): ?>
        <form method="post" onsubmit="return confirm('Delete ALL shared locations? This cannot be undone.')">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><button class="btn danger small" name="action" value="delete_all">Delete all</button>
        </form>
        <?php endif; ?>
      </div>
      <?php if ($rows): ?><div id="map" style="margin-top:16px"></div><?php else: ?><p class="muted">No locations have been shared yet.</p><?php endif; ?>
    </div>

    <?php if ($rows): ?>
    <div class="card scroll">
      <table>
        <thead><tr><th>#</th><th>Date &amp; time</th><th>Latitude</th><th>Longitude</th><th>Accuracy</th><th>Map</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
          <tr>
            <td><?= count($rows) - $i ?></td>
            <td><?= e(date('d M Y, H:i', $r['ts'])) ?></td>
            <td><?= e($r['lat']) ?></td>
            <td><?= e($r['lng']) ?></td>
            <td><?= $r['acc'] === null ? '—' : '±' . e($r['acc']) . ' m' ?></td>
            <td><a href="https://www.openstreetmap.org/?mlat=<?= e($r['lat']) ?>&amp;mlon=<?= e($r['lng']) ?>#map=16/<?= e($r['lat']) ?>/<?= e($r['lng']) ?>" target="_blank" rel="noopener noreferrer">Open ↗</a></td>
            <td><form method="post" onsubmit="return confirm('Delete this entry?')"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn danger small" name="action" value="delete">Delete</button></form></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
      const pts = <?= json_encode(array_map(function ($r) { return [$r['lat'], $r['lng'], date('d M Y, H:i', $r['ts']), $r['acc']]; }, $rows)) ?>;
      const map = L.map('map');
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
      const markers = pts.map(p => L.marker([p[0], p[1]]).bindPopup('<b>' + p[2] + '</b><br>' + p[0] + ', ' + p[1] + (p[3] !== null ? '<br>±' + p[3] + ' m' : '')).addTo(map));
      map.fitBounds(L.featureGroup(markers).getBounds().pad(0.2), { maxZoom: 15 });
    </script>
    <?php endif; ?>
  </div>
<?php endif; ?>
</body>
</html>
