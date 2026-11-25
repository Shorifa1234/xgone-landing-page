<?php
/* ---------- Contact form handling ---------- */
$to      = 'info@xgtech.co.uk';
$sent    = false;
$errors  = [];
$fields  = ['name' => '', 'company' => '', 'email' => '', 'phone' => '', 'industry' => '', 'message' => ''];

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function oneline($s) { return trim(str_replace(["\r", "\n", "%0a", "%0d"], ' ', (string)$s)); }

// XG_STATIC is defined by build-static.php when building the GitHub Pages (HTML-only) version
$static     = defined('XG_STATIC');
$privacyUrl = $static ? 'privacy.html' : 'privacy.php';
$bookingUrl = 'https://www.xgtech.co.uk/booking.html';   // "Book a Demo" buttons go to the demo scheduling page

if (!$static && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  foreach ($fields as $k => $v) { $fields[$k] = trim((string)($_POST[$k] ?? '')); }

  if (!empty($_POST['website'])) {            // honeypot: bots fill this, people don't
    $sent = true;
  } else {
    if ($fields['name'] === '')                                  $errors[] = 'Please enter your name.';
    if (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL))     $errors[] = 'Please enter a valid email address.';
    if (mb_strlen($fields['message']) < 5)                       $errors[] = 'Please tell us a little about what you need.';
    if (mb_strlen($fields['message']) > 5000)                    $errors[] = 'Your message is too long.';

    if (!$errors) {
      require_once __DIR__ . '/db.php';
      require_once __DIR__ . '/mailer.php';
      $limits = ['name' => 200, 'company' => 200, 'email' => 254, 'phone' => 50, 'industry' => 100];
      $clean = [];
      foreach ($limits as $k => $max) $clean[$k] = mb_substr(oneline($fields[$k]), 0, $max);
      $clean['message'] = $fields['message'];

      // 1) Always save the request in the database first, so it is never lost
      $saved = false; $rowId = null;
      try { $rowId = enq_add($clean); $saved = true; } catch (Exception $ex) { error_log('Enquiry save failed: ' . $ex->getMessage()); }

      // 2) Email it to XGTECH (and a confirmation to the visitor)
      $mailOk = false;
      if (smtp_configured()) {
        $body  = "New demo request from the XGTECH website\n\n";
        $body .= "Name:     {$clean['name']}\nCompany:  {$clean['company']}\nEmail:    {$clean['email']}\n";
        $body .= "Phone:    {$clean['phone']}\nIndustry: {$clean['industry']}\nDate:     " . date('d M Y, H:i') . "\n\n";
        $body .= "Message:\n{$clean['message']}\n\n— Reply to this email to answer {$clean['name']} directly.\n";
        try {
          smtp_send(MAIL_TO, 'New demo request: ' . $clean['name'] . ($clean['company'] ? " ({$clean['company']})" : ''), $body, $clean['email']);
          $mailOk = true;
          if (MAIL_CONFIRM_VISITOR) {
            $thanks  = "Hi {$clean['name']},\n\nThank you for your interest in XGOne. We have received your demo request and a member of our team will be in touch shortly.\n\n";
            $thanks .= "If you need us sooner, call 0151 246 1103 or reply to this email.\n\nKind regards,\nThe XGTECH team\nwww.xgone.co.uk\n";
            try { smtp_send($clean['email'], 'We received your XGOne demo request', $thanks, MAIL_TO); } catch (Exception $ex) { error_log('Confirmation email failed: ' . $ex->getMessage()); }
          }
        } catch (Exception $ex) {
          error_log('Enquiry email failed: ' . $ex->getMessage());
          $mailStatus = 'failed: ' . $ex->getMessage();
        }
        if ($saved) {
          try { enq_set_mail($rowId, $mailOk ? 'sent' : ($mailStatus ?? 'failed')); } catch (Exception $ex) { error_log($ex->getMessage()); }
        }
      }

      if ($saved || $mailOk) {
        $sent = true;
        $fields = array_map(function () { return ''; }, $fields);
      } else {
        $errors[] = 'Sorry, we could not send your message right now. Please email us at info@xgtech.co.uk or call 0151 246 1103.';
      }
    }
  }
}
$industries = ['Construction', 'Property management', 'Field service', 'Engineering', 'Facilities management', 'Other'];
require_once __DIR__ . '/db.php';   // for LOC_RETENTION_DAYS
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>XGTECH Ltd | Technology That Empowers Your Business</title>
  <meta name="description" content="XGTECH Ltd builds XGOne, an all-in-one business management platform for construction, property management, field service, engineering and facilities management businesses.">
  <meta property="og:title" content="XGTECH Ltd | Technology That Empowers Your Business">
  <meta property="og:description" content="Manage your people, projects, stock, customers and finances in one place with XGOne by XGTECH.">
  <meta property="og:type" content="website">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --navy:#06265d;--navy2:#03183e;--navy3:#021230;--green:#65b51c;--green2:#8bd13e;--green-pale:#eaf5df;
      --ink:#14213b;--muted:#586579;--line:#e4e9f1;--pale:#f4f7fb;--radius:14px;
      --shadow:0 10px 30px rgba(6,38,93,.08);
    }
    *{box-sizing:border-box}
    html{scroll-behavior:smooth;scroll-padding-top:80px}
    body{margin:0;font-family:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Arial,sans-serif;color:var(--ink);line-height:1.6;background:#fff;-webkit-font-smoothing:antialiased}
    img{max-width:100%}a{color:inherit}
    .wrap{width:min(1180px,100% - 32px);margin:auto}
    .sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}

    /* Buttons */
    .btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;background:var(--green);color:#fff;padding:14px 24px;border-radius:10px;font-weight:700;font-size:15px;border:0;cursor:pointer;font-family:inherit;transition:transform .15s,box-shadow .15s,background .15s}
    .btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(101,181,28,.35)}
    .btn.dark{background:var(--navy)}.btn.dark:hover{box-shadow:0 8px 20px rgba(6,38,93,.3)}
    .btn.ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.35);color:#fff}
    .btn.ghost:hover{background:rgba(255,255,255,.15);box-shadow:none}
    :focus-visible{outline:3px solid var(--green2);outline-offset:3px}

    /* Header */
    .topbar{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.92);backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
    .nav{height:72px;display:flex;align-items:center;justify-content:space-between;gap:20px}
    .brand{display:inline-flex;align-items:center;text-decoration:none;border-radius:10px;overflow:hidden;line-height:0;box-shadow:0 2px 8px rgba(6,38,93,.18);transition:transform .15s}
    .brand:hover{transform:translateY(-1px)}
    .brand img{display:block;height:44px;width:auto}
    .navlinks{display:flex;align-items:center;gap:28px;font-size:14.5px;font-weight:600}
    .navlinks a:not(.btn){text-decoration:none;color:#33415c}.navlinks a:not(.btn):hover{color:var(--green)}
    .navlinks .btn{padding:11px 18px;font-size:14px}
    .menu-btn{display:none;background:none;border:1px solid var(--line);border-radius:8px;width:42px;height:42px;cursor:pointer;color:var(--navy);font-size:20px}

    /* Hero */
    .hero{position:relative;background:radial-gradient(900px 500px at 85% 10%,rgba(101,181,28,.18),transparent 60%),linear-gradient(115deg,var(--navy3),var(--navy) 65%,#0d3976);color:#fff;padding:88px 0 96px;overflow:hidden}
    .hero:after{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:44px 44px;mask-image:linear-gradient(180deg,#000,transparent 85%);pointer-events:none}
    .hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:.9fr 1.1fr;gap:48px;align-items:center}
    .pill{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);padding:6px 14px 6px 8px;border-radius:999px;font-size:13px;color:#dfe8f6}
    .pill b{background:var(--green);color:#fff;border-radius:999px;padding:2px 9px;font-size:11px;letter-spacing:.5px}
    h1{font-size:clamp(38px,5.2vw,64px);line-height:1.05;letter-spacing:-2px;margin:20px 0 20px;font-weight:800}
    h1 span{color:var(--green2)}
    .lead{font-size:19px;color:#d9e3f3;max-width:580px;margin:0 0 32px}
    .actions{display:flex;flex-wrap:wrap;gap:12px}
    .hero-points{display:flex;flex-wrap:wrap;gap:10px 22px;margin-top:32px;color:#c9d6ea;font-size:14px}
    .hero-points span:before{content:"✓";color:var(--green2);font-weight:800;margin-right:8px}

    /* Dual dashboard screenshots */
    .dash-wrap{position:relative;padding:4px 0 8px}
    .shot{margin:0;background:#fff;border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.35);box-shadow:0 30px 70px rgba(0,0,0,.45);transition:transform .35s ease}
    .shot img{display:block;width:100%;height:auto}
    .shot a{display:block;cursor:zoom-in}
    .shot-bar{display:flex;align-items:center;gap:6px;padding:8px 12px;background:#eef2f7;border-bottom:1px solid #dde4ee;font-size:11.5px;font-weight:700;color:var(--navy);white-space:nowrap}
    .shot-bar i{width:9px;height:9px;border-radius:50%;flex:none;background:#e3e8f0}
    .shot-bar i:nth-child(1){background:#ff6b6b}.shot-bar i:nth-child(2){background:#ffc145}.shot-bar i:nth-child(3){background:#53c26a}
    .shot-bar span{margin-left:8px;overflow:hidden;text-overflow:ellipsis}
    .shot-bar em{margin-left:auto;font-style:normal;font-size:10.5px;color:#2c8c49;background:#e6f5e9;padding:2px 8px;border-radius:999px}
    .shot-back{width:84%;margin-left:auto;opacity:.97}
    .shot-front{position:relative;z-index:2;width:84%;margin-top:-20%;box-shadow:0 34px 80px rgba(0,0,0,.55)}
    .dash-wrap:hover .shot-back{transform:translate(6px,-6px)}
    .dash-wrap:hover .shot-front{transform:translate(-6px,6px)}
    .float{position:absolute;background:#fff;color:var(--ink);border-radius:12px;padding:10px 14px;box-shadow:0 14px 34px rgba(0,0,0,.25);font-size:12px;display:flex;align-items:center;gap:10px;animation:bob 5s ease-in-out infinite}
    .float .dot{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;background:var(--green-pale);font-size:16px}
    .float b{display:block;color:var(--navy);font-size:13px}
    .float{z-index:3}
    .float.f2{right:-16px;bottom:-6px;animation-delay:-2.5s}
    @keyframes bob{50%{transform:translateY(-8px)}}

    /* Industry strip */
    .strip{background:#fff;border-bottom:1px solid var(--line);padding:26px 0}
    .strip-inner{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}
    .strip p{margin:0;font-size:13px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8592a8}
    .strip ul{display:flex;gap:12px 30px;flex-wrap:wrap;list-style:none;margin:0;padding:0;font-weight:700;color:var(--navy)}
    .strip li{display:flex;align-items:center;gap:8px}

    /* Sections */
    section{padding:96px 0}
    .eyebrow{display:inline-block;color:#3f8a0f;background:var(--green-pale);text-transform:uppercase;letter-spacing:1.5px;font-size:12px;font-weight:800;padding:5px 12px;border-radius:999px;margin-bottom:14px}
    .eyebrow.light{background:rgba(139,209,62,.15);color:var(--green2)}
    .section-head{text-align:center;max-width:760px;margin:0 auto 52px}
    h2{font-size:clamp(30px,3.6vw,44px);line-height:1.12;letter-spacing:-1.2px;color:var(--navy);margin:0 0 16px;font-weight:800}
    .section-head p{color:var(--muted);font-size:17.5px;margin:0}


    /* Product / features */
    .product{background:var(--pale)}
    .tabs{display:flex;justify-content:center;flex-wrap:wrap;gap:8px;margin-bottom:34px}
    .tab{border:1px solid var(--line);background:#fff;color:var(--navy);padding:10px 18px;border-radius:999px;font-weight:700;font-size:14px;cursor:pointer;font-family:inherit}
    .tab[aria-selected="true"]{background:var(--navy);color:#fff;border-color:var(--navy)}
    .features{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
    .feature{padding:26px 22px;border:1px solid var(--line);border-radius:var(--radius);background:#fff;transition:transform .2s,box-shadow .2s,border-color .2s}
    .feature:hover{transform:translateY(-4px);box-shadow:var(--shadow);border-color:#cfe5b8}
    .feature[hidden]{display:none}
    a.feature{display:block;text-decoration:none;color:inherit;cursor:pointer}
    .feature .ico{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:var(--green-pale);color:#438c12;font-size:22px;margin-bottom:16px}
    .feature h3{font-size:17px;color:var(--navy);line-height:1.3;margin:0 0 8px}
    .feature p{font-size:14.5px;color:var(--muted);margin:0}

    /* GPS demo */
    .gps-grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:48px;align-items:center}
    .gps-info p{color:var(--muted);font-size:17px}
    .gps-stats{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:24px 0}
    .gps-stat{border:1px solid var(--line);border-radius:12px;padding:14px 16px;background:#fff}
    .gps-stat small{display:block;color:#8592a8;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px}
    .gps-stat strong{color:var(--navy);font-size:16px;word-break:break-all}
    .gps-note{font-size:13px;color:#8592a8;margin-top:14px}
    .gps-map{position:relative;height:440px;border-radius:18px;overflow:hidden;border:1px solid var(--line);box-shadow:var(--shadow);background:linear-gradient(135deg,#eef3f9,#f8fbff)}
    #map{position:absolute;inset:0}
    .gps-empty{position:absolute;inset:0;display:grid;place-items:center;text-align:center;padding:24px;color:var(--muted);z-index:1}
    .gps-empty .pin{font-size:46px;animation:bob 3s ease-in-out infinite}
    .gps-status{min-height:24px;font-size:14px;font-weight:600}
    .gps-status.ok{color:#2f6b0c}.gps-status.err{color:#9b1c1c}
    .live-dot{display:inline-block;width:9px;height:9px;border-radius:50%;background:#53c26a;margin-right:6px;box-shadow:0 0 0 0 rgba(83,194,106,.6);animation:pulse 1.6s infinite}
    @keyframes pulse{70%{box-shadow:0 0 0 10px rgba(83,194,106,0)}100%{box-shadow:0 0 0 0 rgba(83,194,106,0)}}
    .consent{display:flex;gap:10px;align-items:flex-start;font-size:14px;color:var(--muted);margin:0 0 12px;cursor:pointer;line-height:1.5}
    .consent input{width:18px;height:18px;margin-top:2px;flex:none;accent-color:var(--green)}
    .consent a{color:#3f8a0f;font-weight:700}
    .linkbtn{background:none;border:0;padding:4px 0;color:#9b1c1c;font:inherit;font-size:14px;font-weight:700;cursor:pointer;text-decoration:underline}
    .feature .try{display:inline-block;margin-top:10px;font-size:13.5px;font-weight:700;color:#3f8a0f;text-decoration:none}
    @media(max-width:880px){.gps-grid{grid-template-columns:1fr}.gps-map{height:360px}}

    /* Industries */
    .ind-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
    .ind{position:relative;border-radius:var(--radius);padding:28px 24px;background:linear-gradient(160deg,var(--navy),var(--navy2));color:#fff;overflow:hidden;min-height:240px;display:flex;flex-direction:column}
    .ind:after{content:"";position:absolute;right:-40px;bottom:-40px;width:140px;height:140px;border-radius:50%;background:rgba(139,209,62,.12)}
    .ind .ico{font-size:34px;margin-bottom:14px}
    .ind h3{margin:0 0 8px;font-size:19px}
    .ind p{margin:0;font-size:14.5px;color:#cfdaec}

    /* Benefits */
    .benefits{background:var(--navy2);color:#fff;position:relative;overflow:hidden}
    .benefits h2{color:#fff}.benefits .section-head p{color:#c6d3e8}
    .benefit-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .benefit{padding:26px 20px;border-radius:var(--radius);background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1)}
    .benefit .num{font-size:13px;font-weight:800;color:var(--green2);letter-spacing:1px}
    .benefit b{display:block;font-size:18px;margin:8px 0 6px}
    .benefit p{margin:0;color:#c6d3e8;font-size:14.5px}

    /* Steps */
    .steps{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;counter-reset:s}
    .step{position:relative;padding:30px 26px;border-radius:var(--radius);border:1px solid var(--line);background:#fff}
    .step:before{counter-increment:s;content:counter(s);width:44px;height:44px;border-radius:50%;display:grid;place-items:center;background:var(--green);color:#fff;font-weight:800;font-size:18px;margin-bottom:16px}
    .step h3{margin:0 0 8px;color:var(--navy);font-size:19px}
    .step p{margin:0;color:var(--muted);font-size:15px}

    /* FAQ */
    .faq{background:var(--pale)}
    .faq-list{max-width:820px;margin:auto;display:grid;gap:12px}
    details{background:#fff;border:1px solid var(--line);border-radius:12px;padding:0 22px}
    summary{cursor:pointer;list-style:none;padding:20px 0;font-weight:700;color:var(--navy);display:flex;justify-content:space-between;gap:16px}
    summary::-webkit-details-marker{display:none}
    summary:after{content:"+";font-size:22px;line-height:1;color:var(--green);transition:transform .2s}
    details[open] summary:after{transform:rotate(45deg)}
    details p{margin:0 0 20px;color:var(--muted)}

    /* Contact */
    .contact-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:56px;align-items:start}
    .contact-info p{color:var(--muted);font-size:17px}
    .info-list{list-style:none;padding:0;margin:28px 0 0;display:grid;gap:14px}
    .info-list li{display:flex;gap:14px;align-items:center;padding:16px 18px;border:1px solid var(--line);border-radius:12px}
    .info-list .ico{width:42px;height:42px;border-radius:10px;background:var(--green-pale);display:grid;place-items:center;font-size:18px;flex:none}
    .info-list small{display:block;color:#8592a8;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px}
    .info-list a{text-decoration:none;font-weight:700;color:var(--navy)}
    form{background:#fff;border:1px solid var(--line);border-radius:18px;padding:32px;box-shadow:var(--shadow)}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .field{display:grid;gap:6px}.field.full{grid-column:1/-1}
    label{font-size:13.5px;font-weight:700;color:var(--navy)}
    input,select,textarea{font:inherit;font-size:15px;padding:12px 14px;border:1px solid #d5dce7;border-radius:10px;background:#fbfcfe;color:var(--ink);width:100%}
    input:focus,select:focus,textarea:focus{outline:none;border-color:var(--green);box-shadow:0 0 0 3px rgba(101,181,28,.18);background:#fff}
    textarea{min-height:130px;resize:vertical}
    .hp{position:absolute;left:-9999px}
    .form-foot{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-top:20px}
    .form-foot small{color:#8592a8}
    .alert{padding:14px 16px;border-radius:10px;margin-bottom:18px;font-size:14.5px}
    .alert.ok{background:#e9f7e1;color:#2f6b0c;border:1px solid #c7e8b0}
    .alert.err{background:#fdecec;color:#9b1c1c;border:1px solid #f6caca}
    .alert ul{margin:0;padding-left:18px}

    /* CTA band */
    .cta-band{padding:0 0 96px}
    .cta-box{border-radius:22px;padding:56px;background:radial-gradient(600px 300px at 90% 0,rgba(139,209,62,.25),transparent 60%),linear-gradient(120deg,var(--navy2),var(--navy));color:#fff;display:flex;justify-content:space-between;align-items:center;gap:30px;flex-wrap:wrap}
    .cta-box h2{color:#fff;margin:0 0 8px;font-size:clamp(26px,3vw,36px)}
    .cta-box p{margin:0;color:#cfdaec;font-size:17px}

    /* Footer */
    footer{background:var(--navy3);color:#b9c6dc;padding:56px 0 26px;font-size:14px}
    .foot-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:32px}
    footer .brand{margin-bottom:14px;box-shadow:0 0 0 1px rgba(255,255,255,.12)}
    footer h4{color:#fff;margin:0 0 12px;font-size:14px}
    footer ul{list-style:none;padding:0;margin:0;display:grid;gap:8px}
    footer a{text-decoration:none}footer a:hover{color:var(--green2)}
    footer .social{display:inline-flex;align-items:center;gap:8px}
    .foot-bottom{border-top:1px solid rgba(255,255,255,.1);margin-top:40px;padding-top:22px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:13px}

    /* Reveal on scroll */
    .reveal{opacity:0;transform:translateY(18px);transition:opacity .6s ease,transform .6s ease}
    .reveal.in{opacity:1;transform:none}
    @media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important;scroll-behavior:auto!important}.reveal{opacity:1;transform:none}}

    /* Responsive */
    @media(max-width:1020px){
      .features{grid-template-columns:repeat(3,1fr)}
      .benefit-grid{grid-template-columns:repeat(3,1fr)}
      .ind-grid{grid-template-columns:repeat(3,1fr)}
      .foot-grid{grid-template-columns:1fr 1fr}
    }
    @media(max-width:880px){
      .hero-grid,.contact-grid{grid-template-columns:1fr}
      .hero{padding:64px 0 80px}
      .dash-wrap{max-width:560px}
      .float.f2{right:-4px}
      .features{grid-template-columns:repeat(2,1fr)}
      .steps{grid-template-columns:1fr}
      .menu-btn{display:block}
      .navlinks{position:absolute;top:72px;left:0;right:0;background:#fff;border-bottom:1px solid var(--line);flex-direction:column;align-items:stretch;gap:0;padding:8px 16px 18px;display:none}
      .navlinks.open{display:flex}
      .navlinks a:not(.btn){padding:12px 4px;border-bottom:1px solid var(--line)}
      .navlinks .btn{margin-top:12px;justify-content:center}
      .cta-box{padding:40px 28px}
    }
    @media(max-width:560px){
      section{padding:72px 0}
      .lead{font-size:17px}
      .actions .btn{flex:1;justify-content:center}
      .features,.ind-grid,.benefit-grid,.form-grid,.foot-grid{grid-template-columns:1fr}
      .shot-back,.shot-front{width:90%}
      .shot-front{margin-top:-24%}
      .float{display:none}
      form{padding:22px}
    }
  </style>
</head>
<body>

  <header class="topbar">
    <div class="wrap nav">
      <a class="brand" href="#top" aria-label="XGTECH home"><img src="logo-1.png" alt="XGTECH" width="158" height="44"></a>
      <button class="menu-btn" aria-expanded="false" aria-controls="menu" aria-label="Open menu">☰</button>
      <nav class="navlinks" id="menu" aria-label="Main navigation">
        <a href="#xgone">XGOne</a>
        <a href="#industries">Industries</a>
        <a href="#benefits">Benefits</a>
        <a href="#faq">FAQ</a>
        <a class="btn" href="<?= $bookingUrl ?>">Book a Demo</a>
      </nav>
    </div>
  </header>

  <main id="top">
    <!-- HERO -->
    <section class="hero">
      <div class="wrap hero-grid">
        <div>
          <span class="pill"><b>XGOne</b> All-in-one business management platform</span>
          <h1>Technology that<br><span>empowers your business.</span></h1>
          <p class="lead">XGTECH builds XGOne — one connected system to manage your people, projects, HR &amp; Sales Reporting, Communication, Dual Dashboard so you spend less time chasing updates and more time growing.</p>
          <div class="actions">
            <a class="btn" href="#contact">Arrange a Free Demo →</a>
            <a class="btn ghost" href="#xgone">Explore XGOne</a>
          </div>
          <div class="hero-points">
            <span>Web &amp; mobile access</span><span>Secure cloud hosting</span><span>Customisable modules</span><span>Android &amp; iOS apps</span>
          </div>
        </div>

        <div class="dash-wrap">
          <figure class="shot shot-back">
            <div class="shot-bar"><i></i><i></i><i></i><span>XGOne · Project Overview</span></div>
            <a href="Dashboard3.jpeg" target="_blank" rel="noopener" title="View full size"><img src="Dashboard3.jpeg" alt="XGOne project overview with tasks, budget, expenses, progress and team members" width="1600" height="652"></a>
          </figure>
          <figure class="shot shot-front">
            <div class="shot-bar"><i></i><i></i><i></i><span>XGOne · Dashboard</span><em>● Live</em></div>
            <a href="Dashboard4.jpeg" target="_blank" rel="noopener" title="View full size"><img src="Dashboard4.jpeg" alt="XGOne dashboard showing employees, projects, tasks, expenses and project status" width="1600" height="727"></a>
          </figure>
          <div class="float f2"><span class="dot">📊</span><div><b>HR &amp; Sales Reporting</b>Real-time insights</div></div>
        </div>
      </div>
    </section>

    <!-- INDUSTRY STRIP -->
    <div class="strip">
      <div class="wrap strip-inner">
        <p>Built for</p>
        <ul>
          <li>🏗️ Construction</li><li>🏢 Property Management</li><li>🧰 Field Service</li><li>📡 Engineering</li><li>🏬 Facilities Management</li>
        </ul>
      </div>
    </div>

    <!-- PRODUCT -->
    <section class="product" id="xgone">
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow">Introducing XGOne</span>
          <h2>One system. Complete control.</h2>
          <p>Everything your operation needs, connected — from planning a project to getting the invoice paid.</p>
        </div>

        <div class="tabs" role="tablist" aria-label="Filter features">
          <button class="tab" role="tab" aria-selected="true" data-filter="all">All modules</button>
          <button class="tab" role="tab" aria-selected="false" data-filter="ops">Operations</button>
          <button class="tab" role="tab" aria-selected="false" data-filter="people">People</button>
          <button class="tab" role="tab" aria-selected="false" data-filter="hrsales">HR &amp; Sales Reporting</button>
          <button class="tab" role="tab" aria-selected="false" data-filter="platform">Platform</button>
        </div>

        <div class="features">
          <a class="feature feature-link" data-cat="ops" href="https://www.xgtech.co.uk/XGOne.html"><div class="ico">🏗</div><h3>Project Management</h3><p>Plan, schedule and track projects and tasks from start to finish.</p><span class="try">Learn more →</span></a>
          <a class="feature feature-link" data-cat="people hrsales" href="https://www.xgtech.co.uk/XGOne.html"><div class="ico">👥</div><h3>Workforce Management</h3><p>Track staff, attendance, check-in/check-out and working hours.</p><span class="try">Learn more →</span></a>
          <article class="feature" data-cat="people"><div class="ico">📍</div><h3>Live GPS Tracking</h3><p>See real-time location information for your team in the field.</p><a class="try" href="#gps-demo">Try it live →</a></article>
          <a class="feature feature-link" data-cat="ops" href="https://www.xgtech.co.uk/stock_erp.html"><div class="ico">📦</div><h3>Inventory &amp; Warehouse</h3><p>Manage stock, multiple warehouses and van inventory.</p><span class="try">Learn more →</span></a>
          <article class="feature" data-cat="ops hrsales"><div class="ico">📈</div><h3>HR &amp; Sales Reporting</h3><p>Track staff, sales and pipeline performance with clear, real-time reports.</p></article>
          <article class="feature" data-cat="hrsales"><div class="ico">🤝</div><h3>Customers &amp; Leads</h3><p>Manage customers, leads, follow-ups and proposals more easily.</p></article>
          <article class="feature" data-cat="ops"><div class="ico">✉️</div><h3>Marketing Automation</h3><p>Send SMS, email and WhatsApp campaigns and stay connected.</p></article>
          <article class="feature" data-cat="ops hrsales"><div class="ico">📊</div><h3>Reports &amp; Dashboards</h3><p>Real-time reports and insights for better business decisions.</p></article>
          <article class="feature" data-cat="platform"><div class="ico">📱</div><h3>Mobile &amp; Web Access</h3><p>Access your business anytime, anywhere, from a range of devices.</p></article>
          <article class="feature" data-cat="platform"><div class="ico">🌐</div><h3>Multi-language Support</h3><p>Available in English, Arabic and additional languages.</p></article>
          <article class="feature" data-cat="platform"><div class="ico">🛡️</div><h3>Secure &amp; Reliable</h3><p>Secure cloud hosting and daily backups protect your data.</p></article>
          <article class="feature" data-cat="platform"><div class="ico">🧩</div><h3>Easy &amp; Customisable</h3><p>A simple interface with custom modules to fit your business.</p></article>
        </div>
      </div>
    </section>

    <!-- GPS DEMO -->
    <section id="gps-demo">
      <div class="wrap gps-grid">
        <div class="gps-info reveal">
          <span class="eyebrow">Live GPS tracking demo</span>
          <h2>See where you are, right now</h2>
          <p>This is how XGOne shows your field team on the map. Click the button and allow location access to see your own live position.</p>
          <button class="btn" id="gps-btn" type="button">📍 Show my location</button>
          <div class="gps-stats">
            <div class="gps-stat"><small>Latitude</small><strong id="gps-lat">—</strong></div>
            <div class="gps-stat"><small>Longitude</small><strong id="gps-lng">—</strong></div>
            <div class="gps-stat"><small>Accuracy</small><strong id="gps-acc">—</strong></div>
            <div class="gps-stat"><small>Last update</small><strong id="gps-time">—</strong></div>
          </div>
          <?php if (!$static): ?>
          <label class="consent"><input type="checkbox" id="gps-consent"><span><strong>Optional:</strong> share my location with XGTECH. I agree to my location being stored for <?= LOC_RETENTION_DAYS ?> days, as explained in the <a href="<?= $privacyUrl ?>#location">Privacy Policy</a>.</span></label>
          <?php endif; ?>
          <div class="gps-status" id="gps-status" role="status"></div>
          <?php if (!$static): ?>
          <button class="linkbtn" id="gps-delete" type="button" hidden>🗑 Delete my shared location</button>
          <p class="gps-note">🔒 If you leave the box unticked, your location stays in your browser and is never sent to XGTECH.</p>
          <?php else: ?>
          <p class="gps-note">🔒 Your location stays in your browser. It is only shown to you and is never saved or sent anywhere.</p>
          <?php endif; ?>
        </div>
        <div class="gps-map reveal">
          <div class="gps-empty" id="gps-empty"><div><div class="pin">📍</div><p>Click <strong>“Show my location”</strong> to see yourself on the map.</p></div></div>
          <div id="map"></div>
        </div>
      </div>
    </section>

    <!-- INDUSTRIES -->
    <section id="industries">
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow">Industries</span>
          <h2>Designed around the way your teams work</h2>
          <p>On site, in the warehouse, in the office or out visiting customers — XGOne brings the whole operation together.</p>
        </div>
        <div class="ind-grid">
          <div class="ind reveal"><div class="ico">🏗️</div><h3>Construction</h3><p>Keep projects, crews, materials and costs on track across every site.</p></div>
          <div class="ind reveal"><div class="ico">🏢</div><h3>Property Management</h3><p>Coordinate maintenance jobs, contractors and tenants from one place.</p></div>
          <div class="ind reveal"><div class="ico">🧰</div><h3>Field Service</h3><p>Dispatch engineers, track jobs live and manage van stock on the move.</p></div>
          <div class="ind reveal"><div class="ico">📡</div><h3>Engineering</h3><p>Manage complex projects, teams and project profitability with clear reporting.</p></div>
          <div class="ind reveal"><div class="ico">🏬</div><h3>Facilities Management</h3><p>Schedule planned and reactive maintenance, manage assets and keep every site running smoothly.</p></div>
        </div>
      </div>
    </section>

    <!-- BENEFITS -->
    <section class="benefits" id="benefits">
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow light">The XGOne benefit</span>
          <h2>Less admin. More control.</h2>
          <p>Bring everyday business activities together and get a clearer view of what is happening across your organisation.</p>
        </div>
        <div class="benefit-grid">
          <div class="benefit reveal"><span class="num">01</span><b>Save Time</b><p>Reduce repetitive administration.</p></div>
          <div class="benefit reveal"><span class="num">02</span><b>Save Money</b><p>Reduce operational cost.</p></div>
          <div class="benefit reveal"><span class="num">03</span><b>Improve Productivity</b><p>Help teams work more efficiently.</p></div>
          <div class="benefit reveal"><span class="num">04</span><b>Control Costs</b><p>Reduce waste and improve oversight.</p></div>
          <div class="benefit reveal"><span class="num">05</span><b>Gain Visibility</b><p>See what is happening across your business.</p></div>
          <div class="benefit reveal"><span class="num">06</span><b>Grow Confidently</b><p>Build a stronger foundation for growth.</p></div>
        </div>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section>
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow">Getting started</span>
          <h2>Getting started is simple</h2>
          <p>No pressure, no jargon — just a conversation about how your business runs today.</p>
        </div>
        <div class="steps">
          <div class="step reveal"><h3>Tell us about your business</h3><p>Get in touch and share how your teams work and where time is being lost.</p></div>
          <div class="step reveal"><h3>See a tailored walkthrough</h3><p>We show you XGOne using the modules that matter most to your operation.</p></div>
          <div class="step reveal"><h3>Get set up your way</h3><p>Choose and customise the modules you need, then bring your team on board.</p></div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="faq" id="faq">
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow">FAQ</span>
          <h2>Common questions</h2>
        </div>
        <div class="faq-list">
          <details class="reveal"><summary>What is XGOne?</summary><p>XGOne is an all-in-one business management platform from XGTECH Ltd. It brings projects, workforce, stock, customers, marketing and finance together in one system.</p></details>
          <details class="reveal"><summary>Who is XGOne for?</summary><p>It is built for construction, property management, field service, engineering and facilities management businesses — especially teams split between the office, the warehouse and the field.</p></details>
          <details class="reveal"><summary>Can I use it on mobile?</summary><p>Yes. XGOne is available on web and mobile, so your team can access it anytime, anywhere.</p></details>
          <details class="reveal"><summary>Can it be customised for my business?</summary><p>Yes. XGOne offers custom modules so the system can fit the way your business works.</p></details>
          <details class="reveal"><summary>Is my data secure?</summary><p>XGOne uses secure cloud hosting with daily backups to help protect your business data.</p></details>
          <details class="reveal"><summary>Which languages are supported?</summary><p>XGOne is available in English, Arabic and additional languages.</p></details>
        </div>
      </div>
    </section>

    <!-- CONTACT -->
    <section id="contact">
      <div class="wrap contact-grid">
        <div class="contact-info reveal">
          <span class="eyebrow">Contact us</span>
          <h2>Let's talk about your business</h2>
          <p>Arrange a no-obligation conversation and a walkthrough of XGOne based on your business needs. Just a friendly chat.</p>
          <ul class="info-list">
            <li><span class="ico">☎</span><div><small>Call us</small><a href="tel:+441512461103">0151 246 1103</a></div></li>
            <li><span class="ico">✉</span><div><small>Email</small><a href="mailto:info@xgtech.co.uk">info@xgtech.co.uk</a></div></li>
            <li><span class="ico">↗</span><div><small>Product website</small><a href="https://www.xgone.co.uk" target="_blank" rel="noopener">www.xgone.co.uk</a></div></li>
          </ul>
        </div>

        <form method="post" action="#contact" class="reveal" novalidate id="demo-form"<?= $static ? ' data-mailto="info@xgtech.co.uk"' : '' ?>>
          <?php if ($sent): ?>
            <div class="alert ok" role="status"><strong>Thank you!</strong> Your message has been sent. Our team will be in touch shortly.</div>
          <?php elseif ($errors): ?>
            <div class="alert err" role="alert"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>

          <div class="form-grid">
            <div class="field"><label for="name">Full name *</label><input id="name" name="name" required autocomplete="name" value="<?= e($fields['name']) ?>"></div>
            <div class="field"><label for="company">Company</label><input id="company" name="company" autocomplete="organization" value="<?= e($fields['company']) ?>"></div>
            <div class="field"><label for="email">Email *</label><input id="email" name="email" type="email" required autocomplete="email" value="<?= e($fields['email']) ?>"></div>
            <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" autocomplete="tel" value="<?= e($fields['phone']) ?>"></div>
            <div class="field full"><label for="industry">Industry</label>
              <select id="industry" name="industry">
                <option value="">Select your industry</option>
                <?php foreach ($industries as $ind): ?>
                  <option <?= $fields['industry'] === $ind ? 'selected' : '' ?>><?= e($ind) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field full"><label for="message">How can we help? *</label><textarea id="message" name="message" required placeholder="Tell us a little about your business and what you'd like to improve..."><?= e($fields['message']) ?></textarea></div>
          </div>
          <div class="hp" aria-hidden="true"><label for="website">Leave this empty</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
          <div class="form-foot">
            <small>No obligation.<?= $static ? ' This opens your email app with your request filled in.' : '' ?> See our <a href="<?= $privacyUrl ?>">Privacy Policy</a>.</small>
            <button class="btn" type="submit">Request Your Free Demo →</button>
          </div>
        </form>
      </div>
    </section>

    <!-- CTA BAND -->
    <div class="cta-band">
      <div class="wrap">
        <div class="cta-box reveal">
          <div>
            <h2>Spend less time chasing updates.</h2>
            <p>Get more control of your business with XGOne.</p>
          </div>
          <a class="btn" href="<?= $bookingUrl ?>">Book a Demo →</a>
        </div>
      </div>
    </div>
  </main>

  <footer>
    <div class="wrap">
      <div class="foot-grid">
        <div>
          <a class="brand" href="#top" aria-label="XGTECH home"><img src="logo-1.png" alt="XGTECH" width="158" height="44" loading="lazy"></a>
          <p style="margin:0;max-width:320px">Technology That Empowers Your Business. Makers of XGOne, the all-in-one business management platform.</p>
        </div>
        <div><h4>Company</h4><ul><li><a href="#industries">Industries</a></li><li><a href="#contact">Contact</a></li></ul></div>
        <div><h4>XGOne</h4><ul><li><a href="#xgone">Features</a></li><li><a href="#benefits">Benefits</a></li><li><a href="#faq">FAQ</a></li><li><a href="https://www.xgone.co.uk" target="_blank" rel="noopener">xgone.co.uk</a></li></ul></div>
        <div><h4>Get in touch</h4><ul><li><a href="tel:+441512461103">0151 246 1103</a></li><li><a href="mailto:info@xgtech.co.uk">info@xgtech.co.uk</a></li><li><a class="social" href="https://www.linkedin.com/company/xgtech-uk/" target="_blank" rel="noopener" aria-label="XGTECH on LinkedIn (opens in a new tab)"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0z"/></svg>LinkedIn</a></li></ul></div>
      </div>
      <div class="foot-bottom"><span>© <?= date('Y') ?> XGTECH Ltd. All rights reserved.</span><span><a href="<?= $privacyUrl ?>">Privacy Policy</a> · XGOne by XGTECH Ltd</span></div>
    </div>
  </footer>

  <script>
    // Mobile menu
    const menuBtn = document.querySelector('.menu-btn'), menu = document.getElementById('menu');
    menuBtn.addEventListener('click', () => {
      const open = menu.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', open);
      menuBtn.textContent = open ? '✕' : '☰';
    });
    menu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
      menu.classList.remove('open'); menuBtn.setAttribute('aria-expanded', false); menuBtn.textContent = '☰';
    }));

    // Feature filter tabs
    const tabs = document.querySelectorAll('.tab'), feats = document.querySelectorAll('.feature');
    tabs.forEach(t => t.addEventListener('click', () => {
      tabs.forEach(x => x.setAttribute('aria-selected', x === t));
      const f = t.dataset.filter;
      feats.forEach(el => el.hidden = !(f === 'all' || el.dataset.cat.split(' ').includes(f)));
    }));

    // Live GPS demo (visitor's own location, shown only to them, never sent anywhere)
    (function () {
      const btn = document.getElementById('gps-btn'), statusEl = document.getElementById('gps-status');
      const $ = id => document.getElementById(id);
      const consentBox = $('gps-consent'), delBtn = $('gps-delete');
      let map, marker, circle, watchId = null, first = true, shared = false, lastPos = null;

      // ids of locations this browser shared, so the visitor can delete them later
      const KEY = 'xgtech_shared_location_ids';
      function getIds() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; } }
      function setIds(ids) { try { localStorage.setItem(KEY, JSON.stringify(ids)); } catch (e) {} if (delBtn) delBtn.hidden = ids.length === 0; }
      setIds(getIds());

      async function shareLocation(pos) {
        if (shared || !consentBox || !consentBox.checked) return;
        shared = true;   // one save per tracking session
        try {
          const res = await fetch('api-location.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'save', consent: true, lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy })
          });
          const data = await res.json();
          if (!data.ok) throw new Error(data.error);
          setIds(getIds().concat(data.id));
          setStatus('<span class="live-dot"></span>Live tracking on. ✅ Your location was shared with XGTECH.', 'ok');
        } catch (e) {
          shared = false;
          setStatus('Your location could not be shared right now. The demo still works.', 'err');
        }
      }

      if (consentBox) consentBox.addEventListener('change', () => { if (consentBox.checked && watchId !== null && lastPos) shareLocation(lastPos); });

      if (delBtn) delBtn.addEventListener('click', async () => {
        const ids = getIds(); if (!ids.length) return;
        try {
          const res = await fetch('api-location.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'delete', ids }) });
          const data = await res.json();
          if (!data.ok) throw new Error(data.error);
          setIds([]); consentBox.checked = false; shared = false;
          setStatus('Your shared location has been deleted from our records.', 'ok');
        } catch (e) { setStatus('Could not delete right now. Please email info@xgtech.co.uk.', 'err'); }
      });

      function setStatus(msg, cls) { statusEl.className = 'gps-status ' + (cls || ''); statusEl.innerHTML = msg; }

      function loadLeaflet() {
        if (window.L) return Promise.resolve();
        return new Promise((resolve, reject) => {
          const css = document.createElement('link');
          css.rel = 'stylesheet'; css.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css';
          document.head.appendChild(css);
          const s = document.createElement('script');
          s.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js';
          s.onload = resolve; s.onerror = () => reject(new Error('Map could not be loaded. Check your internet connection.'));
          document.head.appendChild(s);
        });
      }

      function update(pos) {
        const { latitude: lat, longitude: lng, accuracy } = pos.coords;
        $('gps-lat').textContent = lat.toFixed(6);
        $('gps-lng').textContent = lng.toFixed(6);
        $('gps-acc').textContent = '±' + Math.round(accuracy) + ' m';
        $('gps-time').textContent = new Date(pos.timestamp).toLocaleTimeString();
        $('gps-empty').style.display = 'none';
        if (!map) {
          map = L.map('map').setView([lat, lng], 16);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
          }).addTo(map);
        }
        if (!marker) {
          marker = L.marker([lat, lng]).addTo(map).bindPopup('<b>You are here</b>');
          circle = L.circle([lat, lng], { radius: accuracy, color: '#65b51c', fillColor: '#65b51c', fillOpacity: .15, weight: 1 }).addTo(map);
        } else {
          marker.setLatLng([lat, lng]); circle.setLatLng([lat, lng]).setRadius(accuracy);
        }
        if (first) { map.setView([lat, lng], 16); marker.openPopup(); first = false; }
        lastPos = pos;
        if (!shared) setStatus('<span class="live-dot"></span>Live tracking on, updating as you move', 'ok');
        shareLocation(pos);
      }

      function fail(err) {
        const msgs = {
          1: 'Location permission was denied. Allow location access in your browser settings and try again.',
          2: 'Your location could not be found. Check that location services are turned on.',
          3: 'Finding your location took too long. Please try again.'
        };
        setStatus(msgs[err.code] || err.message, 'err');
        stop();
      }

      function stop() {
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        watchId = null; btn.textContent = '📍 Show my location';
      }

      btn.addEventListener('click', async () => {
        if (watchId !== null) { stop(); setStatus('Live tracking stopped.'); return; }
        if (!('geolocation' in navigator)) { setStatus('Your browser does not support location.', 'err'); return; }
        if (!window.isSecureContext) { setStatus('Location only works over HTTPS.', 'err'); return; }
        setStatus('Finding your location…');
        try { await loadLeaflet(); } catch (e) { setStatus(e.message, 'err'); return; }
        first = true; shared = false;
        watchId = navigator.geolocation.watchPosition(update, fail, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        btn.textContent = '⏹ Stop tracking';
      });
    })();

    // Static (GitHub Pages) version: the demo form opens the visitor's email app instead of posting to PHP
    (function () {
      const form = document.getElementById('demo-form');
      if (!form || !form.dataset.mailto) return;
      form.addEventListener('submit', ev => {
        ev.preventDefault();
        const v = n => (form.elements[n] ? form.elements[n].value.trim() : '');
        const box = form.querySelector('.alert') || form.insertBefore(document.createElement('div'), form.firstChild);
        const problems = [];
        if (!v('name')) problems.push('Please enter your name.');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v('email'))) problems.push('Please enter a valid email address.');
        if (v('message').length < 5) problems.push('Please tell us a little about what you need.');
        if (problems.length) {
          box.className = 'alert err'; box.setAttribute('role', 'alert');
          box.innerHTML = '<ul>' + problems.map(p => '<li>' + p + '</li>').join('') + '</ul>';
          return;
        }
        const body = 'Name: ' + v('name') + '\nCompany: ' + v('company') + '\nEmail: ' + v('email') +
                     '\nPhone: ' + v('phone') + '\nIndustry: ' + v('industry') + '\n\n' + v('message');
        const subject = 'XGOne demo request: ' + v('name') + (v('company') ? ' (' + v('company') + ')' : '');
        window.location.href = 'mailto:' + form.dataset.mailto + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
        box.className = 'alert ok'; box.setAttribute('role', 'status');
        box.innerHTML = '<strong>Almost done!</strong> Your email app should open with your request. Just press Send. If nothing opened, email us at <a href="mailto:info@xgtech.co.uk">info@xgtech.co.uk</a> or call 0151 246 1103.';
      });
    })();

    // Reveal on scroll
    const io = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
      entries.forEach(en => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { threshold: .12 }) : null;
    document.querySelectorAll('.reveal').forEach(el => io ? io.observe(el) : el.classList.add('in'));
  </script>
</body>
</html>
