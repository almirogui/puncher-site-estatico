<?php
// ─── EMAIL CONFIGURATION ─────────────────────────────────────────
define('ADMIN_EMAIL', 'puncher@puncher.com');
define('FROM_EMAIL',  'noreply@puncher.com');
define('FROM_NAME',   'Puncher.com Payment System');

// ─── PROCESS FORM ────────────────────────────────────────────────
$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $client     = trim($_POST['client']     ?? '');
    $email      = trim($_POST['email']      ?? '');
    $brand      = trim($_POST['brand']      ?? '');
    $cardnumber = preg_replace('/\D/', '', $_POST['cardnumber'] ?? '');
    $cardholder = trim($_POST['cardholder'] ?? '');
    $expiry     = trim($_POST['expiry']     ?? '');
    $cvv        = preg_replace('/\D/', '', $_POST['cvv'] ?? '');
    $now        = date('d/m/Y H:i:s');

    // Basic validation
    $allowed_brands = ['Visa', 'Mastercard', 'Amex'];
    if (!$client || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($brand, $allowed_brands) || strlen($cardnumber) < 13 ||
        !$cardholder || !$expiry || !$cvv) {
        $error = 'Please fill in all fields correctly.';
    } else {
        // Mask card number for confirmation email to customer
        $masked = str_repeat('*', strlen($cardnumber) - 4) . substr($cardnumber, -4);

        // Format card number with spaces for admin email
        $formatted_card = implode(' ', str_split($cardnumber, 4));

        // ── EMAIL 1: Admin (Portuguese) HTML ─────────────────────
        $admin_subject = "[Puncher.com] Atualizacao de Meio de Pagamento - {$client}";

        $admin_body = "
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: linear-gradient(135deg, #1a1a2e 0%, #c9a84c 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
    .content { background: #f7fafc; padding: 30px; border: 1px solid #e2e8f0; }
    .field { margin-bottom: 15px; }
    .label { font-weight: bold; color: #1a1a2e; margin-bottom: 4px; }
    .value { padding: 10px 14px; background: white; border-radius: 5px; border: 1px solid #e2e8f0; font-family: monospace; font-size: 15px; }
    .highlight { background: #fff8e1; padding: 15px; border-radius: 8px; border-left: 4px solid #c9a84c; margin-bottom: 20px; }
    .footer { text-align: center; padding: 15px; color: #718096; font-size: 12px; }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'><h2>💳 Atualizacao de Meio de Pagamento</h2></div>
    <div class='content'>
      <div class='highlight'>
        <strong>Data/Hora:</strong> {$now}<br>
        <strong>Cliente:</strong> " . htmlspecialchars($client) . "<br>
        <strong>Email:</strong> " . htmlspecialchars($email) . "
      </div>
      <div class='field'>
        <div class='label'>Bandeira</div>
        <div class='value'>{$brand}</div>
      </div>
      <div class='field'>
        <div class='label'>Numero do Cartao</div>
        <div class='value'>{$formatted_card}</div>
      </div>
      <div class='field'>
        <div class='label'>Nome no Cartao</div>
        <div class='value'>" . htmlspecialchars($cardholder) . "</div>
      </div>
      <div class='field'>
        <div class='label'>Validade</div>
        <div class='value'>{$expiry}</div>
      </div>
      <div class='field'>
        <div class='label'>Codigo de Seguranca (CVV/CID)</div>
        <div class='value'>{$cvv}</div>
      </div>
    </div>
    <div class='footer'>Enviado automaticamente por puncher.com/php/payupdate.php</div>
  </div>
</body>
</html>";

        $admin_headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: Puncher.com <' . FROM_EMAIL . '>',
            'Reply-To: ' . htmlspecialchars($email),
            'X-Mailer: PHP/' . phpversion()
        ];

        $sent_admin = mail(ADMIN_EMAIL, $admin_subject, $admin_body, implode("\r\n", $admin_headers));

        // ── EMAIL 2: Customer (English) HTML ──────────────────────
        $client_subject = "Payment Method Updated - Puncher.com";

        $client_body = "
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: linear-gradient(135deg, #1a1a2e 0%, #c9a84c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
    .content { background: #f7fafc; padding: 30px; border: 1px solid #e2e8f0; }
    .highlight { background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #38a169; margin: 20px 0; }
    .footer { text-align: center; padding: 20px; background: #1a1a2e; border-radius: 0 0 10px 10px; }
    .footer p { margin: 5px 0; color: #a0aec0; font-size: 12px; }
    .footer a { color: #c9a84c; }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'><h2>✅ Payment Method Updated</h2></div>
    <div class='content'>
      <p>Dear <strong>" . htmlspecialchars($client) . "</strong>,</p>
      <div class='highlight'>
        Your payment method on file with <strong>Puncher.com</strong> has been successfully updated.<br><br>
        <strong>Card on file:</strong> {$brand} ending in " . substr($cardnumber, -4) . "<br>
        <strong>Updated on:</strong> " . date('F j, Y') . "
      </div>
      <p>If you did not authorize this change, please contact us immediately at
        <a href='mailto:puncher@puncher.com' style='color:#c9a84c;'>puncher@puncher.com</a>.
      </p>
      <p>Thank you for your continued business.</p>
      <p>Best regards,<br><strong>The Puncher.com Team</strong></p>
    </div>
    <div class='footer'>
      <p><strong>Puncher.com</strong> — Professional Embroidery Digitizing Since 1993</p>
      <p><a href='https://puncher.com'>www.puncher.com</a></p>
    </div>
  </div>
</body>
</html>";

        $client_headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: Puncher.com <' . FROM_EMAIL . '>',
            'Reply-To: puncher@puncher.com',
            'X-Mailer: PHP/' . phpversion()
        ];

        $sent_client = mail($email, $client_subject, $client_body, implode("\r\n", $client_headers));

        if ($sent_admin) {
            $success = true;
        } else {
            $error = 'There was a problem sending the information. Please try again or contact us directly.';
        }
    }
}

// ─── CLIENT NAME FROM URL ─────────────────────────────────────────
$url_client  = trim($_GET['client'] ?? '');
$client_locked = ($url_client !== '');
$client_value  = $client_locked ? htmlspecialchars($url_client) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Update — Puncher.com</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">

<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:        #0a0a0c;
    --surface:   #111116;
    --border:    #2a2a35;
    --gold:      #c9a84c;
    --gold-soft: #e8cb80;
    --text:      #e8e6e0;
    --muted:     #7a7870;
    --error:     #e05a5a;
    --success:   #5ab87a;
    --card-dark: #141421;
    --visa:      #1a1f71;
    --mc:        #252525;
    --amex:      #1d4b8f;
    --radius:    12px;
  }

  html, body {
    min-height: 100vh;
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Mono', monospace;
  }

  body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background-image:
      radial-gradient(ellipse 60% 50% at 50% -10%, rgba(201,168,76,0.08) 0%, transparent 70%),
      radial-gradient(ellipse 40% 30% at 80% 80%, rgba(201,168,76,0.04) 0%, transparent 60%);
  }

  /* ── HEADER ── */
  .header {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeDown 0.7s ease both;
  }
  .header-logo {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(28px, 5vw, 42px);
    font-weight: 300;
    letter-spacing: 0.15em;
    color: var(--gold);
  }
  .header-logo span { font-weight: 600; }
  .header-sub {
    font-size: 11px;
    letter-spacing: 0.3em;
    color: var(--muted);
    margin-top: 6px;
    text-transform: uppercase;
  }

  /* ── CARD ── */
  .wrapper {
    width: 100%;
    max-width: 480px;
    animation: fadeUp 0.7s ease 0.1s both;
  }

  /* ── CREDIT CARD PREVIEW ── */
  .card-preview {
    width: 100%;
    aspect-ratio: 1.586;
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #1c1c2e 0%, #16213e 50%, #0f3460 100%);
    box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(201,168,76,0.15);
    transition: background 0.5s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .card-preview::before {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(255,255,255,0.03);
    top: -120px; right: -80px;
  }
  .card-preview::after {
    content: '';
    position: absolute;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.02);
    bottom: -80px; left: -50px;
  }
  .card-preview.brand-visa   { background: linear-gradient(135deg, #0d1b69 0%, #1a2f9a 100%); }
  .card-preview.brand-mastercard { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); }
  .card-preview.brand-amex   { background: linear-gradient(135deg, #0e2b5c 0%, #1a4a8f 100%); }

  .card-chip {
    width: 42px; height: 32px;
    border-radius: 5px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-soft) 100%);
    display: flex; align-items: center; justify-content: center;
    position: relative;
    z-index: 1;
  }
  .card-chip::after {
    content: '';
    width: 26px; height: 18px;
    border: 1.5px solid rgba(0,0,0,0.25);
    border-radius: 3px;
    position: absolute;
  }

  .card-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .card-brand-logo {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 600;
    letter-spacing: 0.1em;
    color: rgba(255,255,255,0.9);
    text-align: right;
    min-width: 80px;
  }

  .card-number-display {
    font-family: 'DM Mono', monospace;
    font-size: clamp(16px, 4vw, 22px);
    letter-spacing: 0.2em;
    color: rgba(255,255,255,0.9);
    text-align: center;
    position: relative; z-index: 1;
    text-shadow: 0 1px 3px rgba(0,0,0,0.4);
    padding: 8px 0;
  }

  .card-bottom {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    position: relative; z-index: 1;
  }
  .card-label { font-size: 9px; letter-spacing: 0.2em; color: rgba(255,255,255,0.4); margin-bottom: 4px; text-transform: uppercase; }
  .card-value { font-size: 14px; letter-spacing: 0.08em; color: rgba(255,255,255,0.9); }
  .card-field { display: flex; flex-direction: column; }
  .card-field.right { text-align: right; }

  /* ── FORM ── */
  .form-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 36px 32px;
  }

  .form-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 11px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(201,168,76,0.15);
  }

  .field { margin-bottom: 20px; }
  .field label {
    display: block;
    font-size: 10px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 8px;
  }
  .field input, .field select {
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 13px 16px;
    font-family: 'DM Mono', monospace;
    font-size: 14px;
    color: var(--text);
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    -webkit-appearance: none;
  }
  .field input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
  }
  .field input[readonly] {
    color: var(--muted);
    cursor: not-allowed;
  }

  /* Brand selector */
  .brand-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 24px;
  }
  .brand-btn {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 14px 10px;
    background: rgba(255,255,255,0.02);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
  }
  .brand-btn:hover { border-color: var(--gold); background: rgba(201,168,76,0.05); }
  .brand-btn.active {
    border-color: var(--gold);
    background: rgba(201,168,76,0.1);
    box-shadow: 0 0 0 1px rgba(201,168,76,0.3);
  }
  .brand-btn.active::after {
    content: '✓';
    position: absolute;
    top: 6px; right: 8px;
    font-size: 10px;
    color: var(--gold);
  }
  .brand-icon { font-size: 22px; line-height: 1; }
  .brand-name { font-size: 10px; letter-spacing: 0.1em; color: var(--muted); }
  .brand-btn.active .brand-name { color: var(--gold-soft); }

  /* Two columns */
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

  /* Submit */
  .btn-submit {
    width: 100%;
    padding: 16px;
    margin-top: 8px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-soft) 100%);
    border: none;
    border-radius: 10px;
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: #0a0a0c;
    font-weight: 400;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
  }
  .btn-submit:hover { opacity: 0.9; }
  .btn-submit:active { transform: scale(0.99); }

  /* Messages */
  .msg {
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 24px;
    font-size: 13px;
    line-height: 1.6;
    text-align: center;
  }
  .msg-error { background: rgba(224,90,90,0.1); border: 1px solid rgba(224,90,90,0.3); color: var(--error); }
  .msg-success { background: rgba(90,184,122,0.1); border: 1px solid rgba(90,184,122,0.3); color: var(--success); }

  /* Success screen */
  .success-screen {
    text-align: center;
    padding: 20px 0;
  }
  .success-icon {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: rgba(90,184,122,0.15);
    border: 1.5px solid var(--success);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin: 0 auto 24px;
  }
  .success-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px;
    font-weight: 300;
    color: var(--text);
    margin-bottom: 12px;
  }
  .success-text { font-size: 13px; color: var(--muted); line-height: 1.7; }
  .success-text strong { color: var(--gold); }

  /* Footer */
  .footer {
    margin-top: 32px;
    text-align: center;
    font-size: 11px;
    color: var(--muted);
    letter-spacing: 0.1em;
    animation: fadeUp 0.7s ease 0.3s both;
  }
  .footer a { color: var(--gold); text-decoration: none; }

  /* Secure badge */
  .secure-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 16px;
    font-size: 10px;
    letter-spacing: 0.2em;
    color: var(--muted);
    text-transform: uppercase;
  }
  .secure-badge::before {
    content: '🔒';
    font-size: 12px;
  }

  /* Animations */
  @keyframes fadeDown {
    from { opacity: 0; transform: translateY(-20px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  @media (max-width: 500px) {
    .form-box { padding: 24px 20px; }
    .card-preview { padding: 20px 22px; }
    .card-number-display { font-size: 15px; letter-spacing: 0.15em; }
  }
</style>
</head>
<body>

<header class="header">
  <div class="header-logo"><span>PUNCHER</span>.COM</div>
  <div class="header-sub">Professional Embroidery Digitizing Since 1993</div>
</header>

<div class="wrapper">

  <?php if ($success): ?>
  <!-- ── SUCCESS SCREEN ── -->
  <div class="form-box">
    <div class="success-screen">
      <div class="success-icon">✓</div>
      <div class="success-title">Payment Updated</div>
      <div class="success-text">
        Thank you, <strong><?= htmlspecialchars($_POST['client']) ?></strong>.<br><br>
        Your payment information has been successfully updated.<br>
        A confirmation email has been sent to <strong><?= htmlspecialchars($_POST['email']) ?></strong>.
      </div>
    </div>
  </div>

  <?php else: ?>

  <!-- ── CARD PREVIEW ── -->
  <div class="card-preview" id="cardPreview">
    <div class="card-top">
      <div class="card-chip"></div>
      <div class="card-brand-logo" id="previewBrand">— — —</div>
    </div>
    <div class="card-number-display" id="previewNumber">•••• &nbsp;•••• &nbsp;•••• &nbsp;••••</div>
    <div class="card-bottom">
      <div class="card-field">
        <div class="card-label">Card Holder</div>
        <div class="card-value" id="previewHolder">FULL NAME</div>
      </div>
      <div class="card-field right">
        <div class="card-label">Expires</div>
        <div class="card-value" id="previewExpiry">MM/YY</div>
      </div>
    </div>
  </div>

  <!-- ── FORM ── -->
  <div class="form-box">

    <?php if ($error): ?>
    <div class="msg msg-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="" id="payForm" novalidate>

      <!-- Client Info -->
      <div class="form-section-title">Client Information</div>

      <div class="field">
        <label>Company / Name</label>
        <input type="text"
               name="client"
               id="clientField"
               value="<?= $client_value ?>"
               <?= $client_locked ? 'readonly' : '' ?>
               placeholder="Your name or company">
      </div>

      <div class="field">
        <label>Email Address</label>
        <input type="email" name="email" id="email" placeholder="your@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <!-- Card Info -->
      <div class="form-section-title" style="margin-top:28px;">Card Details</div>

      <!-- Brand selector -->
      <div style="margin-bottom:8px; font-size:10px; letter-spacing:0.25em; text-transform:uppercase; color:var(--muted);">Card Brand</div>
      <div class="brand-selector">
        <button type="button" class="brand-btn" data-brand="Visa" onclick="selectBrand(this)">
          <span class="brand-icon">💳</span>
          <span class="brand-name">VISA</span>
        </button>
        <button type="button" class="brand-btn" data-brand="Mastercard" onclick="selectBrand(this)">
          <span class="brand-icon">🔴</span>
          <span class="brand-name">MASTERCARD</span>
        </button>
        <button type="button" class="brand-btn" data-brand="Amex" onclick="selectBrand(this)">
          <span class="brand-icon">🔷</span>
          <span class="brand-name">AMEX</span>
        </button>
      </div>
      <input type="hidden" name="brand" id="brandInput" value="">

      <div class="field">
        <label>Card Number</label>
        <input type="text" name="cardnumber" id="cardnumber"
               maxlength="19" placeholder="•••• •••• •••• ••••"
               autocomplete="cc-number" inputmode="numeric"
               value="<?= htmlspecialchars($_POST['cardnumber'] ?? '') ?>">
      </div>

      <div class="field">
        <label>Name on Card</label>
        <input type="text" name="cardholder" id="cardholder"
               placeholder="As printed on card"
               autocomplete="cc-name" style="text-transform:uppercase;"
               value="<?= htmlspecialchars($_POST['cardholder'] ?? '') ?>">
      </div>

      <div class="field-row">
        <div class="field" style="margin-bottom:0;">
          <label>Expiry Date</label>
          <input type="text" name="expiry" id="expiry"
                 maxlength="7" placeholder="MM/YYYY"
                 autocomplete="cc-exp" inputmode="numeric"
                 value="<?= htmlspecialchars($_POST['expiry'] ?? '') ?>">
        </div>
        <div class="field" style="margin-bottom:0;">
          <label>Security Code <span id="cvvLabel" style="color:var(--gold);">(CVV)</span></label>
          <input type="text" name="cvv" id="cvv"
                 maxlength="4" placeholder="•••"
                 autocomplete="cc-csc" inputmode="numeric"
                 value="<?= htmlspecialchars($_POST['cvv'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="btn-submit" id="submitBtn">Update Payment Method</button>

      <div class="secure-badge">Secure & Encrypted Connection</div>

    </form>
  </div>
  <?php endif; ?>

</div>

<div class="footer">
  &copy; <?= date('Y') ?> <a href="https://puncher.com">Puncher.com</a> — Embroidery Digitizing Services
</div>

<script>
  let selectedBrand = '';

  // ── Brand selection ──────────────────────────────────────────────
  function selectBrand(btn) {
    document.querySelectorAll('.brand-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedBrand = btn.dataset.brand;
    document.getElementById('brandInput').value = selectedBrand;

    const preview = document.getElementById('cardPreview');
    preview.className = 'card-preview';
    if (selectedBrand === 'Visa')       preview.classList.add('brand-visa');
    if (selectedBrand === 'Mastercard') preview.classList.add('brand-mastercard');
    if (selectedBrand === 'Amex')       preview.classList.add('brand-amex');

    const brandNames = { Visa: 'VISA', Mastercard: 'MASTERCARD', Amex: 'AMEX' };
    document.getElementById('previewBrand').textContent = brandNames[selectedBrand] || '— — —';

    // CVV length
    const cvvInput = document.getElementById('cvv');
    const cvvLabel = document.getElementById('cvvLabel');
    if (selectedBrand === 'Amex') {
      cvvInput.maxLength = 4;
      cvvInput.placeholder = '••••';
      cvvLabel.textContent = '(CID · 4 digits)';
    } else {
      cvvInput.maxLength = 3;
      cvvInput.placeholder = '•••';
      cvvLabel.textContent = '(CVV)';
    }

    // Card number format: Amex = 4-6-5, others = 4-4-4-4
    formatCardNumber();
  }

  // ── Card number formatting ────────────────────────────────────────
  document.getElementById('cardnumber').addEventListener('input', function() {
    formatCardNumber(this);
    updatePreviewNumber();
  });

  function formatCardNumber(input) {
    input = input || document.getElementById('cardnumber');
    let val = input.value.replace(/\D/g, '');
    let formatted = '';

    if (selectedBrand === 'Amex') {
      // 4-6-5
      input.maxLength = 17;
      if (val.length <= 4)       formatted = val;
      else if (val.length <= 10) formatted = val.slice(0,4) + ' ' + val.slice(4);
      else                       formatted = val.slice(0,4) + ' ' + val.slice(4,10) + ' ' + val.slice(10,15);
    } else {
      // 4-4-4-4
      input.maxLength = 19;
      formatted = val.match(/.{1,4}/g)?.join(' ') || val;
    }
    input.value = formatted;
    updatePreviewNumber();
  }

  function updatePreviewNumber() {
    const raw = document.getElementById('cardnumber').value.replace(/\s/g, '');
    const preview = document.getElementById('previewNumber');
    if (!raw) {
      preview.innerHTML = '•••• &nbsp;•••• &nbsp;•••• &nbsp;••••';
      return;
    }
    // Show last 4, mask rest
    let display = '';
    if (selectedBrand === 'Amex') {
      const padded = raw.padEnd(15, '•');
      display = padded.slice(0,4) + ' &nbsp;' + padded.slice(4,10) + ' &nbsp;' + padded.slice(10,15);
    } else {
      const padded = raw.padEnd(16, '•');
      display = padded.match(/.{1,4}/g).join(' &nbsp;');
    }
    preview.innerHTML = display;
  }

  // ── Cardholder ───────────────────────────────────────────────────
  document.getElementById('cardholder').addEventListener('input', function() {
    const val = this.value.toUpperCase() || 'FULL NAME';
    document.getElementById('previewHolder').textContent = val;
    this.value = val;
  });

  // ── Expiry ───────────────────────────────────────────────────────
  document.getElementById('expiry').addEventListener('input', function() {
    let val = this.value.replace(/\D/g, '');
    if (val.length > 2) val = val.slice(0,2) + '/' + val.slice(2,6);
    this.value = val;
    document.getElementById('previewExpiry').textContent = val || 'MM/YY';
  });

  // CVV — numbers only
  document.getElementById('cvv').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
  });

  // ── Restore brand if form resubmitted with error ─────────────────
  <?php if ($error && !empty($_POST['brand'])): ?>
  (function(){
    const btn = document.querySelector('[data-brand="<?= htmlspecialchars($_POST['brand']) ?>"]');
    if (btn) selectBrand(btn);
    formatCardNumber();
  })();
  <?php endif; ?>
</script>

</body>
</html>
