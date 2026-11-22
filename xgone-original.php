<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="XGOne helps construction, property management, field service and engineering businesses manage people, projects, stock, customers and finances in one place.">
  <title>XGOne | One System. Complete Control.</title>
  <style>
    :root{--navy:#06265d;--navy2:#03183e;--green:#65b51c;--ink:#14213b;--muted:#586579;--pale:#f4f7fb}
    *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font-family:Arial,Helvetica,sans-serif;color:var(--ink);line-height:1.6;background:#fff}
    a{color:inherit}.wrap{width:min(1120px,92%);margin:auto}
    .topbar{background:#fff;border-bottom:1px solid #e8edf4}.nav{height:76px;display:flex;align-items:center;justify-content:space-between;gap:20px}
    .brand{font-weight:900;font-size:25px;letter-spacing:-1px;color:var(--navy)}.brand span{color:var(--green)}
    .navlinks{display:flex;align-items:center;gap:25px;font-size:14px}.navlinks a{text-decoration:none;color:#33415c}
    .btn{display:inline-block;text-decoration:none;background:var(--green);color:#fff;padding:13px 22px;border-radius:8px;font-weight:700;border:0;cursor:pointer}
    .btn.dark{background:var(--navy)}.btn.outline{background:transparent;border:1px solid rgba(255,255,255,.65);color:#fff}
    .hero{background:linear-gradient(115deg,var(--navy2),var(--navy) 67%,#0d3976);color:#fff;padding:76px 0 68px;overflow:hidden}
    .hero-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:46px;align-items:center}
    .eyebrow{color:#a9e56a;text-transform:uppercase;letter-spacing:2px;font-size:12px;font-weight:800}
    h1{font-size:clamp(42px,5.3vw,66px);line-height:1.04;letter-spacing:-2px;margin:14px 0 18px}
    h1 span{color:#8bd13e}.lead{font-size:20px;color:#e6edf8;max-width:620px;margin:0 0 27px}
    .hero-points{display:flex;flex-wrap:wrap;gap:12px 20px;margin:25px 0 30px;color:#eaf1fb;font-size:14px}
    .hero-points span:before{content:"✓";color:#9ce14b;font-weight:900;margin-right:8px}
    .dashboard{background:#f9fbff;border:7px solid #18263d;border-radius:18px;padding:14px;box-shadow:0 22px 55px #0006;color:var(--ink);transform:rotate(1deg)}
    .dash-head{display:flex;justify-content:space-between;align-items:center;padding:8px 5px 15px;font-weight:800;color:var(--navy)}
    .dash-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.metric,.panel{background:white;border:1px solid #e5eaf1;border-radius:9px;padding:13px}
    .metric small,.panel small{color:#718096;font-size:11px}.metric strong{display:block;font-size:24px;color:var(--navy)}
    .panel{grid-column:span 2;min-height:112px}.bars{height:58px;display:flex;align-items:end;gap:10px;margin-top:8px}
    .bars i{display:block;background:linear-gradient(var(--green),#2c8c49);width:11%;border-radius:4px 4px 0 0}
    .trust{padding:25px 0;background:#f4f7fb;text-align:center;color:#43516a;font-size:14px}
    .trust strong{color:var(--navy)}
    section{padding:72px 0}.section-head{text-align:center;max-width:760px;margin:0 auto 38px}
    h2{font-size:clamp(30px,3.5vw,42px);line-height:1.15;letter-spacing:-1px;color:var(--navy);margin:0 0 14px}
    .section-head p{color:var(--muted);font-size:17px;margin:0}
    .features{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
    .feature{padding:22px 18px;border:1px solid #e5eaf1;border-radius:12px;background:#fff}
    .feature .ico{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:#eaf5df;color:#438c12;font-size:21px;margin-bottom:13px}
    .feature h3{font-size:17px;color:var(--navy);line-height:1.25;margin:0 0 8px}.feature p{font-size:14px;color:var(--muted);margin:0}
    .audience{background:var(--pale)}.audience-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
    .audience-card{background:#fff;border-radius:10px;padding:20px;border:1px solid #e7ecf3;font-weight:700;color:var(--navy)}
    .audience-card span{display:block;font-size:24px;margin-bottom:9px}
    .benefits{background:var(--navy2);color:#fff}.benefits h2{color:#fff;text-align:center}.benefit-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:18px;margin-top:32px}
    .benefit{text-align:center;padding:10px}.benefit b{display:block;color:#a6e84e;font-size:19px;margin-bottom:7px}.benefit p{margin:0;color:#e2eaf6;font-size:14px}
    .cta{background:linear-gradient(120deg,#f4f8ef,#f4f7fb);text-align:center}.cta p{color:var(--muted);font-size:18px;max-width:700px;margin:0 auto 25px}
    .contact{padding:35px 0;background:#fff}.contact-inner{display:flex;justify-content:space-between;align-items:center;gap:25px;flex-wrap:wrap}
    .contact h2{font-size:27px;margin:0 0 6px}.contact p{margin:0;color:var(--muted);font-size:14px}.contact-details{display:flex;gap:20px;flex-wrap:wrap;font-size:14px}
    footer{background:#031536;color:#dce5f3;padding:20px 0;font-size:13px;text-align:center}
    @media(max-width:850px){.hero-grid{grid-template-columns:1fr}.dashboard{max-width:570px}.features{grid-template-columns:repeat(2,1fr)}.audience-grid{grid-template-columns:repeat(2,1fr)}.benefit-grid{grid-template-columns:repeat(2,1fr)}.navlinks a:not(.btn){display:none}}
    @media(max-width:520px){.nav{height:66px}.navlinks{gap:0}.navlinks .btn{padding:10px 13px;font-size:12px}.hero{padding:55px 0}.lead{font-size:17px}.features{grid-template-columns:1fr}.audience-grid{grid-template-columns:1fr 1fr}.benefit-grid{grid-template-columns:1fr}.contact-inner{display:block}.contact-details{margin-top:18px;display:block}.contact-details div{margin:7px 0}}
  </style>
</head>
<body>
  <header class="topbar">
    <div class="wrap nav">
      <a class="brand" href="#" aria-label="XGOne home">XG<span>One</span></a>
      <nav class="navlinks" aria-label="Main navigation">
        <a href="#features">Features</a><a href="#benefits">Benefits</a><a href="#contact">Contact</a>
        <a class="btn" href="mailto:info@xgtech.co.uk?subject=Book%20an%20XGOne%20demo">Book a Demo</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="wrap hero-grid">
        <div>
          <div class="eyebrow">Business management made simpler</div>
          <h1>One System.<br><span>Complete Control.</span></h1>
          <p class="lead">Run your business smarter with XGOne — an all-in-one platform to manage your people, projects, stock, customers and finances in one place.</p>
          <div class="hero-points">
            <span>Better visibility</span><span>Higher productivity</span><span>Stronger control</span>
          </div>
          <a class="btn" href="#contact">Arrange a Friendly Demo</a>
          <a class="btn outline" href="#features" style="margin-left:8px">Explore Features</a>
        </div>
        <div class="dashboard" aria-label="Illustrative XGOne dashboard preview">
          <div class="dash-head"><span>XGOne Dashboard</span><span>● Live overview</span></div>
          <div class="dash-grid">
            <div class="metric"><small>Projects</small><strong>24</strong><small>Project overview</small></div>
            <div class="metric"><small>Tasks</small><strong>128</strong><small>Team activity</small></div>
            <div class="metric"><small>Staff</small><strong>56</strong><small>Workforce</small></div>
            <div class="metric"><small>Invoices</small><strong>£82,540</strong><small>Finance overview</small></div>
            <div class="panel"><strong>Business performance</strong><div class="bars"><i style="height:28%"></i><i style="height:45%"></i><i style="height:37%"></i><i style="height:65%"></i><i style="height:83%"></i><i style="height:72%"></i><i style="height:95%"></i></div></div>
            <div class="panel"><strong>One place for your operation</strong><small>Projects · People · Stock · Customers · Finance</small></div>
          </div>
        </div>
      </div>
    </section>

    <div class="trust"><div class="wrap"><strong>Built for construction, property management, field service and engineering businesses.</strong> Access your operation from web and mobile.</div></div>

    <section id="features">
      <div class="wrap">
        <div class="section-head">
          <h2>Everything your operation needs, connected</h2>
          <p>Bring everyday business activities together, reduce disconnected admin and get a clearer view of what is happening across your organisation.</p>
        </div>
        <div class="features">
          <article class="feature"><div class="ico">🏗</div><h3>Project Management</h3><p>Plan, schedule and track projects and tasks from start to finish.</p></article>
          <article class="feature"><div class="ico">👥</div><h3>Workforce Management</h3><p>Track staff, attendance, check-in/check-out and working hours.</p></article>
          <article class="feature"><div class="ico">📍</div><h3>Live GPS Tracking</h3><p>See real-time location information for your team in the field.</p></article>
          <article class="feature"><div class="ico">📦</div><h3>Inventory & Warehouse</h3><p>Manage stock, multiple warehouses and van inventory.</p></article>
          <article class="feature"><div class="ico">£</div><h3>Finance & Accounting</h3><p>Manage invoicing, expenses, payments and project profitability in one place.</p></article>
          <article class="feature"><div class="ico">🤝</div><h3>Customer & Lead Management</h3><p>Manage customers, leads, follow-ups and proposals more easily.</p></article>
          <article class="feature"><div class="ico">✉</div><h3>Marketing Automation</h3><p>Send SMS, email and WhatsApp campaigns and stay connected.</p></article>
          <article class="feature"><div class="ico">▥</div><h3>Reports & Dashboards</h3><p>Use real-time reports and insights to make better business decisions.</p></article>
          <article class="feature"><div class="ico">▣</div><h3>Mobile & Web Access</h3><p>Access your business anytime, anywhere, from a range of devices.</p></article>
          <article class="feature"><div class="ico">◎</div><h3>Multi-language Support</h3><p>Available in English, Arabic and additional languages.</p></article>
          <article class="feature"><div class="ico">◇</div><h3>Secure & Reliable</h3><p>Secure cloud hosting and daily backups help protect your business data.</p></article>
          <article class="feature"><div class="ico">✚</div><h3>Easy to Use & Customisable</h3><p>A simple interface with custom modules to fit your business needs.</p></article>
        </div>
      </div>
    </section>

    <section class="audience">
      <div class="wrap">
        <div class="section-head"><h2>Designed around the way your teams work</h2><p>Whether your staff are on site, in the warehouse, in the office or out visiting customers, XGOne helps bring the operation together.</p></div>
        <div class="audience-grid">
          <div class="audience-card"><span>🏗️</span>Construction businesses</div>
          <div class="audience-card"><span>🏢</span>Property management</div>
          <div class="audience-card"><span>🧰</span>Field service businesses</div>
          <div class="audience-card"><span>📡</span>Engineering companies</div>
        </div>
      </div>
    </section>

    <section class="benefits" id="benefits">
      <div class="wrap">
        <h2>The XGOne Benefit</h2>
        <div class="benefit-grid">
          <div class="benefit"><b>Save Time</b><p>Reduce repetitive administration.</p></div>
          <div class="benefit"><b>Improve Productivity</b><p>Help teams work more efficiently.</p></div>
          <div class="benefit"><b>Control Costs</b><p>Reduce waste and improve oversight.</p></div>
          <div class="benefit"><b>Gain Visibility</b><p>See what is happening across your business.</p></div>
          <div class="benefit"><b>Grow Confidently</b><p>Build a stronger foundation for growth.</p></div>
        </div>
      </div>
    </section>

    <section class="cta" id="contact">
      <div class="wrap">
        <div class="eyebrow">Take a closer look</div>
        <h2>Spend less time chasing updates.<br>Get more control of your business.</h2>
        <p>See how XGOne could support your day-to-day operation. Get in touch to arrange a no-obligation conversation and a walkthrough based on your business needs.</p>
        <a class="btn dark" href="mailto:info@xgtech.co.uk?subject=I%27d%20like%20an%20XGOne%20demo">Request Your Free Demo</a>
        <p style="font-size:13px;margin-top:14px">No obligation. Just a friendly conversation.</p>
      </div>
    </section>
  </main>

  <section class="contact">
    <div class="wrap contact-inner">
      <div><h2>Let's talk about your business</h2><p>XGOne by XGTECH Ltd · Technology That Empowers Your Business</p></div>
      <div class="contact-details">
        <div>☎ <a href="tel:+441512461103">0151 246 1103</a></div>
        <div>✉ <a href="mailto:info@xgtech.co.uk">info@xgtech.co.uk</a></div>
        <div>↗ <a href="https://www.xgone.co.uk" target="_blank" rel="noopener">www.xgone.co.uk</a></div>
      </div>
    </div>
  </section>
  <footer><div class="wrap">© 2026 XGTECH Ltd. All rights reserved.</div></footer>
</body>
</html>