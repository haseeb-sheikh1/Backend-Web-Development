<?php 
    $is_login_page = true;
    $extra_css = ""; 
    $title= "Login";
   
    require_once '../pages/Database.php';
    require_once '../pages/Users.php';
    $db = new Database();
    $connection = $db->getConnection();
    $user = new Users($connection);
    $user->logIn(); 
    include_once "../includes/header.php"; 
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Sora:wght@400;600;700;800&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --cx-teal:    #1a5c5a;
  --cx-teal-d:  #0f3b39;
  --cx-orange:  #f5821f;
  --cx-orange-d:#d4691a;
  --cx-bg:      #f5f5f5;
  --cx-white:   #ffffff;
  --cx-text:    #1e2b2b;
  --cx-muted:   #5a6e6d;
  --cx-border:  #dde5e5;
  --cx-input-bg:#f9fbfb;
  --cx-error:   #b91c1c;
  --cx-error-bg:#fef2f2;
}

body { font-family: 'Nunito', sans-serif; }

/* ── PAGE ── */
.login-page {
  min-height: 100vh;
  display: flex;
  background: var(--cx-bg);
}

/* ── LEFT PANEL (orange curve — like OrangeHRM) ── */
.login-left {
  width: 42%;
  min-height: 100vh;
  background: var(--cx-orange);
  position: relative;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  overflow: hidden;
}

/* The big white curved cutout on the right edge */
.login-left::after {
  content: '';
  position: absolute;
  right: -80px;
  top: -5%;
  width: 220px;
  height: 110%;
  background: var(--cx-bg);
  border-radius: 50% 0 0 50% / 50% 0 0 50%;
  z-index: 2;
}

/* Decorative teal circle inside orange panel */
.login-left-circle {
  position: absolute;
  right: 60px;
  top: 50%;
  transform: translateY(-50%);
  width: 200px;
  height: 200px;
  border-radius: 50%;
  background: var(--cx-white);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3;
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}

.login-left-circle-inner {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  border: 3px solid var(--cx-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 6px;
}

.login-left-circle-inner svg {
  width: 48px;
  height: 48px;
}

.login-left-circle-inner span {
  font-family: 'Sora', sans-serif;
  font-size: 10px;
  font-weight: 700;
  color: var(--cx-teal);
  letter-spacing: 1px;
  text-transform: uppercase;
}

/* ── RIGHT PANEL ── */
.login-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 32px;
  background: var(--cx-bg);
  position: relative;
  z-index: 1;
}

/* Logo card at the top (like OrangeHRM) */
.login-logo-card {
  background: var(--cx-white);
  border-radius: 12px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  padding: 20px 40px;
  margin-bottom: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 320px;
}

/* ── CodeXentric SVG Logo ── */
.cx-logo {
  display: flex;
  align-items: center;
  gap: 0;
  font-family: 'Sora', sans-serif;
  font-weight: 800;
  font-size: 28px;
  user-select: none;
}

.cx-logo .bracket-left {
  color: var(--cx-orange);
  font-size: 32px;
  line-height: 1;
  margin-right: -2px;
}

.cx-logo .logo-main {
  color: var(--cx-teal);
  letter-spacing: -0.5px;
}

.cx-logo .logo-x {
  color: var(--cx-orange);
  font-style: italic;
}

.cx-logo .bracket-right {
  color: var(--cx-orange);
  font-size: 32px;
  line-height: 1;
  margin-left: -2px;
}

.cx-logo-sub {
  font-family: 'Nunito', sans-serif;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--cx-muted);
  margin-top: 4px;
  text-align: center;
}

.logo-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Login card */
.login-card {
  background: transparent;
  width: 100%;
  max-width: 380px;
}

.login-title {
  font-family: 'Sora', sans-serif;
  font-size: 26px;
  font-weight: 700;
  color: var(--cx-teal);
  text-align: center;
  margin-bottom: 24px;
  letter-spacing: -0.3px;
}

/* ── FORM ── */
.login-form .form-group {
  margin-bottom: 18px;
  position: relative;
}

.login-form .field-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--cx-muted);
  display: flex;
  align-items: center;
}

.login-form input[type="text"],
.login-form input[type="email"],
.login-form input[type="password"] {
  width: 100%;
  height: 52px;
  padding: 0 16px 0 44px;
  background: var(--cx-white);
  border: 1.5px solid var(--cx-border);
  border-radius: 10px;
  font-size: 15px;
  font-family: 'Nunito', sans-serif;
  font-weight: 600;
  color: var(--cx-text);
  outline: none;
  transition: all 0.2s ease;
}

.login-form input:focus {
  border-color: var(--cx-teal);
  box-shadow: 0 0 0 3px rgba(26,92,90,0.12);
}

.login-form input::placeholder { color: #aab8b8; font-weight: 400; }

/* Forgot link */
.forgot-link {
  display: block;
  text-align: center;
  margin-top: 10px;
  font-size: 13.5px;
  color: var(--cx-orange);
  font-weight: 700;
  text-decoration: none;
  transition: color 0.15s;
}
.forgot-link:hover { color: var(--cx-orange-d); text-decoration: underline; }

.btn-primary {
  width: 100%;
  height: 52px;
  background: var(--cx-orange);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-family: 'Sora', sans-serif;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 24px;
  transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
  box-shadow: 0 4px 14px rgba(245,130,31,0.35);
  letter-spacing: 0.3px;
}
.btn-primary:hover {
  background: var(--cx-orange-d);
  box-shadow: 0 6px 20px rgba(245,130,31,0.4);
  transform: translateY(-1px);
}
.btn-primary:active { transform: translateY(0); box-shadow: none; }

/* Error messages */
.error-messages {
  background: var(--cx-error-bg);
  border: 1px solid #fecaca;
  border-radius: 10px;
  padding: 14px 16px;
  margin-bottom: 20px;
  color: var(--cx-error);
  font-weight: 700;
  font-size: 14px;
}

/* Footer text */
.login-footer-text {
  margin-top: 20px;
  text-align: center;
  font-size: 12.5px;
  color: var(--cx-muted);
  line-height: 1.8;
}

.login-footer-text a {
  color: var(--cx-teal);
  font-weight: 700;
  text-decoration: none;
}
.login-footer-text a:hover { text-decoration: underline; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .login-left { display: none; }
  .login-right { padding: 32px 20px; }
  .login-logo-card { min-width: unset; width: 100%; max-width: 380px; }
}
</style>

<div class="login-page">

  <!-- ── LEFT ORANGE PANEL ── -->
  <aside class="login-left">
    <div class="login-left-circle">
      <div class="login-left-circle-inner">
        <!-- Minimal <C> Logo -->
        <div style="font-family: 'Sora', sans-serif; font-size: 64px; font-weight: 800; line-height: 1; display: flex; align-items: center; gap: 2px; margin-bottom: 4px;">
          <span style="color: var(--cx-orange);">&lt;</span>
          <span style="color: var(--cx-teal); transform: translateY(-1px);">C</span>
          <span style="color: var(--cx-orange);">&gt;</span>
        </div>
        <span style="font-size: 11px; letter-spacing: 5px; font-weight: 800; color: var(--cx-teal); margin-left: 5px;">EMS</span>
      </div>
    </div>
  </aside>

  <!-- ── RIGHT FORM PANEL ── -->
  <div class="login-right">

    <!-- Logo card -->
    <div class="login-logo-card">
      <div class="logo-wrap">
        <div class="cx-logo">
          <span class="bracket-left">&lt;</span>
          <span class="logo-main">code<span class="logo-x"><svg width="22" height="28" viewBox="0 0 22 28" style="display:inline-block; vertical-align:middle; margin: 0 -2px;"><path d="M5 6 L17 22" stroke="var(--cx-teal)" stroke-width="4" stroke-linecap="round"/><path d="M19 3 L3 25" stroke="var(--cx-orange)" stroke-width="6" stroke-linecap="round"/></svg></span>entric</span>
          <span class="bracket-right">&gt;</span>
        </div>
        <div class="cx-logo-sub">Employee Management System</div>
      </div>
    </div>

    <div class="login-card">
      <h1 class="login-title">Login</h1>

      <form action="login_process.php" method="POST" class="login-form">

        <?php
        if (isset($user->errors) && count($user->errors) > 0 && isset($user->errors['general'])) {
            echo '<div class="error-messages">';
            foreach ($user->errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        ?>

        <div class="form-group">
          <span class="field-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </span>
          <input type="email" id="email" name="email" placeholder="Email Address">
        </div>

        <div class="form-group">
          <span class="field-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
          </span>
          <input type="password" id="password" name="password" placeholder="Password">
        </div>

        <button type="submit" class="btn-primary" name="login">
          Login
        </button>

        <a href="#" class="forgot-link">Forgot your password?</a>

      </form>

      <div class="login-footer-text">
        CodeXentric HRM &bull; All rights reserved.
      </div>
    </div>

  </div>

</div>

<?php include_once "../includes/footer.php"; ?>