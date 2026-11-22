<?php
require __DIR__ . '/db.php';
$static = defined('XG_STATIC');            // GitHub Pages (HTML-only) build
$home   = $static ? 'index.html' : 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Privacy Policy | XGTECH Ltd</title>
  <meta name="description" content="How XGTECH Ltd collects, uses and protects your personal data.">
  <style>
    :root{--navy:#06265d;--navy2:#03183e;--green:#65b51c;--ink:#14213b;--muted:#586579;--line:#e4e9f1;--pale:#f4f7fb}
    *{box-sizing:border-box}body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Arial,sans-serif;color:var(--ink);line-height:1.7;background:#fff}
    .wrap{width:min(820px,100% - 32px);margin:auto}
    header{background:var(--navy2);color:#fff;padding:18px 0}
    header a{color:#fff;text-decoration:none;font-weight:800;font-size:20px}header a span{color:#8bd13e}
    main{padding:48px 0 72px}
    h1{color:var(--navy);font-size:38px;line-height:1.15;margin:0 0 8px}
    h2{color:var(--navy);font-size:22px;margin:40px 0 10px;scroll-margin-top:20px}
    p,li{color:#34425a}
    .muted{color:var(--muted);font-size:14px}
    table{width:100%;border-collapse:collapse;font-size:15px;margin:12px 0}
    th,td{text-align:left;vertical-align:top;padding:10px;border:1px solid var(--line)}
    th{background:var(--pale);color:var(--navy)}
    .box{background:var(--pale);border-left:4px solid var(--green);padding:14px 18px;border-radius:6px}
    a{color:#2f6b0c}
    footer{background:var(--navy2);color:#b9c6dc;padding:20px 0;font-size:13px;text-align:center}
  </style>
</head>
<body>
  <header><div class="wrap"><a href="<?= $home ?>" aria-label="XGTECH home"><img src="logo-1.png" alt="XGTECH" width="158" height="44" style="display:block;height:44px;width:auto;border-radius:8px"></a></div></header>
  <main class="wrap">
    <h1>Privacy Policy</h1>
    <p class="muted">Last updated: 22 November 2026</p>

    <p>This policy explains how XGTECH Ltd ("XGTECH", "we", "us") collects and uses personal data when you use this website. We are the data controller for this data.</p>

    <h2>Who we are</h2>
    <p>XGTECH Ltd<br>
      Address: <strong>[ADD REGISTERED ADDRESS]</strong><br>
      Company number: <strong>[ADD COMPANY NUMBER]</strong><br>
      ICO registration number: <strong>[ADD ICO NUMBER]</strong><br>
      Email: <a href="mailto:info@xgtech.co.uk">info@xgtech.co.uk</a> · Phone: <a href="tel:+441512461103">0151 246 1103</a></p>

    <h2 id="location">Live GPS demo: location data</h2>
    <p>Our website has a live GPS tracking demo. When you click <em>"Show my location"</em>, your browser asks for permission to use your location.</p>
    <?php if ($static): ?>
    <p>Your location is shown only on your own screen. It is <strong>never sent to us or stored anywhere</strong>, and it is gone when you close the page or click <em>"Stop tracking"</em>.</p>
    <?php else: ?>
    <ul>
      <li><strong>If you do not tick the sharing box,</strong> your location is shown only on your own screen. It is never sent to us.</li>
      <li><strong>If you tick "Share my location with XGTECH",</strong> we store your location once for that session.</li>
    </ul>
    <table>
      <tr><th>What we store</th><td>Latitude, longitude, location accuracy (in metres), the date and time, and the version of the consent you gave. We do not store your name, IP address or device details with your location.</td></tr>
      <tr><th>Why</th><td>To understand which areas our visitors are in, so we can plan where we offer our services.</td></tr>
      <tr><th>Lawful basis</th><td>Your consent (UK GDPR Article 6(1)(a)). Sharing is optional and the demo works fully without it.</td></tr>
      <tr><th>How long we keep it</th><td><?= LOC_RETENTION_DAYS ?> days. After that it is deleted automatically.</td></tr>
      <tr><th>Who can see it</th><td>Only authorised XGTECH staff, through a password-protected page.</td></tr>
    </table>
    <div class="box"><strong>Withdrawing consent:</strong> you can delete your shared location at any time using the <em>"Delete my shared location"</em> button in the demo on the same device and browser, or by emailing <a href="mailto:info@xgtech.co.uk">info@xgtech.co.uk</a> with the approximate date and time you shared it.</div>
    <?php endif; ?>

    <h2>Contact form</h2>
    <p>When you send an enquiry we receive the details you enter: your name, company, email, phone, industry and message. We use them to reply to you and arrange a demo you have asked for. The lawful basis is our legitimate interest in responding to enquiries and taking steps at your request before entering into a contract.
    <?php if ($static): ?>
    The form opens your own email app with your request filled in. Nothing is sent until you press Send in your email app, and the details are not stored on this website.</p>
    <?php else: ?>
    Your enquiry is stored securely on our website (in a password-protected area) and sent to us by email. We also send you an email confirming we received your request. Stored enquiries are deleted automatically after <?= ENQ_RETENTION_DAYS ?> days.</p>
    <?php endif; ?>

    <h2>Third-party services</h2>
    <p>To display this website, your browser connects to these services, which receive your IP address as part of normal web traffic:</p>
    <ul>
      <?php if ($static): ?><li><strong>GitHub Pages</strong> (GitHub): hosts this website</li><?php endif; ?>
      <li><strong>Google Fonts</strong> (Google): website fonts</li>
      <li><strong>cdnjs</strong> (Cloudflare): map software, loaded only when you use the GPS demo</li>
      <li><strong>OpenStreetMap</strong>: map images, loaded only when you use the GPS demo</li>
    </ul>
    <p>We do not use advertising or tracking cookies.</p>

    <h2>Your rights</h2>
    <p>Under UK data protection law you have the right to access, correct or delete your personal data, to restrict or object to how we use it, to data portability, and to withdraw consent at any time. To use any of these rights, email <a href="mailto:info@xgtech.co.uk">info@xgtech.co.uk</a>.</p>
    <p>If you are unhappy with how we handle your data, you can complain to the Information Commissioner's Office (ICO) at <a href="https://ico.org.uk/make-a-complaint/" target="_blank" rel="noopener">ico.org.uk</a> or on 0303 123 1113.</p>

    <h2>Security</h2>
    <p>We take reasonable technical and organisational measures to protect your data<?= $static ? '' : ', including restricted, password-protected access to stored locations and enquiries' ?>.</p>

    <h2>Changes to this policy</h2>
    <p>We may update this policy from time to time. The latest version will always be on this page.</p>

    <p style="margin-top:40px"><a href="<?= $home ?>">← Back to home</a></p>
  </main>
  <footer>© <?= date('Y') ?> XGTECH Ltd. All rights reserved.</footer>
</body>
</html>
