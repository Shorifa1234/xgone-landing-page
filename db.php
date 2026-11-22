<?php
/*
 * Database access for the XGTECH website (MySQL / MariaDB, database set in config.php).
 *   demo_requests      – contact form / demo requests
 *   visitor_locations  – locations visitors chose to share (with consent)
 * Tables are created automatically. Old rows are deleted after the retention period.
 */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) { http_response_code(404); exit; }
require_once __DIR__ . '/config.php';

const LOC_RETENTION_DAYS  = 30;      // shared locations are deleted after this many days
const ENQ_RETENTION_DAYS  = 365;     // demo requests are deleted after this many days
const LOC_MAX_ROWS        = 10000;   // safety limit
const LOC_CONSENT_VERSION = 'v1-2026-11';

function db() {
  static $pdo = null;
  if ($pdo) return $pdo;
  $pdo = new PDO(
    'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
  );
  $pdo->exec("CREATE TABLE IF NOT EXISTS demo_requests (
      id          CHAR(32)     NOT NULL PRIMARY KEY,
      name        VARCHAR(200) NOT NULL,
      company     VARCHAR(200) NOT NULL DEFAULT '',
      email       VARCHAR(254) NOT NULL,
      phone       VARCHAR(50)  NOT NULL DEFAULT '',
      industry    VARCHAR(100) NOT NULL DEFAULT '',
      message     TEXT         NOT NULL,
      mail_status VARCHAR(500) NOT NULL DEFAULT 'not configured',
      created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS visitor_locations (
      id              CHAR(32)     NOT NULL PRIMARY KEY,
      lat             DECIMAL(9,6) NOT NULL,
      lng             DECIMAL(9,6) NOT NULL,
      accuracy_m      INT UNSIGNED NULL,
      consent_version VARCHAR(20)  NOT NULL,
      created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  // retention: remove expired rows
  $pdo->exec('DELETE FROM visitor_locations WHERE created_at < NOW() - INTERVAL ' . (int)LOC_RETENTION_DAYS . ' DAY');
  $pdo->exec('DELETE FROM demo_requests WHERE created_at < NOW() - INTERVAL ' . (int)ENQ_RETENTION_DAYS . ' DAY');
  return $pdo;
}

function new_id() { return bin2hex(random_bytes(16)); }

/* ---------- Demo requests ---------- */
function enq_add(array $f) {
  $id = new_id();
  db()->prepare('INSERT INTO demo_requests (id, name, company, email, phone, industry, message) VALUES (?,?,?,?,?,?,?)')
      ->execute([$id, $f['name'], $f['company'], $f['email'], $f['phone'], $f['industry'], $f['message']]);
  return $id;
}
function enq_set_mail($id, $status) {
  db()->prepare('UPDATE demo_requests SET mail_status = ? WHERE id = ?')->execute([mb_substr($status, 0, 500), $id]);
}
function enq_all() {
  return db()->query('SELECT id, name, company, email, phone, industry, message, mail_status AS mail, UNIX_TIMESTAMP(created_at) AS ts
                      FROM demo_requests ORDER BY created_at DESC')->fetchAll();
}
function enq_delete($id) { db()->prepare('DELETE FROM demo_requests WHERE id = ?')->execute([$id]); }

/* ---------- Shared visitor locations ---------- */
function loc_count() { return (int)db()->query('SELECT COUNT(*) FROM visitor_locations')->fetchColumn(); }
function loc_add($lat, $lng, $acc) {
  $id = new_id();
  db()->prepare('INSERT INTO visitor_locations (id, lat, lng, accuracy_m, consent_version) VALUES (?,?,?,?,?)')
      ->execute([$id, round($lat, 6), round($lng, 6), $acc === null ? null : (int)round($acc), LOC_CONSENT_VERSION]);
  return $id;
}
function loc_all() {
  return db()->query('SELECT id, CAST(lat AS DOUBLE) AS lat, CAST(lng AS DOUBLE) AS lng, accuracy_m AS acc, UNIX_TIMESTAMP(created_at) AS ts
                      FROM visitor_locations ORDER BY created_at DESC')->fetchAll();
}
function loc_delete_ids(array $ids) {
  if (!$ids) return 0;
  $st = db()->prepare('DELETE FROM visitor_locations WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
  $st->execute(array_values($ids));
  return $st->rowCount();
}
function loc_delete_all() { db()->exec('DELETE FROM visitor_locations'); }
