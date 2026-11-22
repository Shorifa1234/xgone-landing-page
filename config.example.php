<?php
/* Copy this file to config.php and fill in your own values.
   config.php is NOT committed to git because it contains passwords. */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) { http_response_code(404); exit; }

/* Admin login for admin-locations.php.
   Create a hash with:  php -r "echo password_hash('YOUR-PASSWORD', PASSWORD_DEFAULT);"  */
const ADMIN_PASSWORD_HASH = 'PASTE-HASH-HERE';

/* ---------- Database (MySQL / MariaDB) ----------
   Tables are created automatically on first use. */
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'xgone';
const DB_USER = 'root';
const DB_PASS = '';

/* ---------- Email (SMTP) for demo requests ----------
     Microsoft 365 / Outlook : host smtp.office365.com, port 587, secure 'tls'
     Gmail / Google Workspace: host smtp.gmail.com,    port 587, secure 'tls'  (use an App Password)
     cPanel hosting          : host mail.xgtech.co.uk, port 465, secure 'ssl'
   Leave SMTP_HOST empty to skip email (requests are still saved in the database). */
const SMTP_HOST   = '';
const SMTP_PORT   = 587;
const SMTP_SECURE = 'tls';
const SMTP_USER   = '';
const SMTP_PASS   = '';
const MAIL_FROM   = 'info@xgtech.co.uk';
const MAIL_TO     = 'info@xgtech.co.uk';
const MAIL_CONFIRM_VISITOR = true;
