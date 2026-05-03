
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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── PAGE ── */
.login-page {
  min-height: 100vh;
  display: flex;
  font-family: 'Inter', sans-serif;
  background: #ffffff;
}

/* ── LEFT PANEL ── */
.login-left {
  width: 46%;
  min-height: 100vh;
  background: linear-gradient(135deg, #0f1c2e 0%, #1252cc 60%, #1a6eff 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 48px;
  position: relative;
  overflow: hidden;
}

.login-left::before {
  content: '';
  position: absolute;
  width: 480px;
  height: 480px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  top: -100px;
  left: -100px;
  pointer-events: none;
}

.login-left::after {
  content: '';
  position: absolute;
  width: 320px;
  height: 320px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
  bottom: -80px;
  right: -60px;
  pointer-events: none;
}

.login-left-inner {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

/* Brand */
.login-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 52px;
}

.login-brand-logo {
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-brand-logo img {
  height: 48px;
  width: auto;
  object-fit: contain;
}

.login-brand-name {
  font-size: 24px;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: 0.3px;
}

/* Illustration */
.login-illustration {
  width: 100%;
  max-width: 280px;
  margin-bottom: 40px;
}
.login-illustration svg {
  width: 100%;
  height: auto;
}

.login-left-headline {
  font-size: 24px;
  font-weight: 800;
  color: #ffffff;
  margin-bottom: 12px;
  line-height: 1.35;
}

.login-left-sub {
  font-size: 15px;
  color: rgba(255,255,255,0.8);
  line-height: 1.65;
  max-width: 280px;
}

/* Feature pills */
.stat-pills {
  display: flex;
  gap: 10px;
  margin-top: 32px;
  flex-wrap: wrap;
  justify-content: center;
}

.pill {
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 20px;
  padding: 6px 16px;
  font-size: 13px;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 8px;
  backdrop-filter: blur(10px);
}

.pill-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 8px rgba(16,185,129,0.8);
  flex-shrink: 0;
}

/* ── RIGHT PANEL ── */
.login-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 32px;
  background: #F0F4FA;
}

.login-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 32px rgba(21,89,181,0.10);
  padding: 48px 44px;
  width: 100%;
  max-width: 420px;
}

/* Secure login badge */
.login-card-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f0f6ff;
  border: 1px solid #dbeafe;
  border-radius: 20px;
  padding: 5px 14px;
  margin-bottom: 24px;
}
.login-card-badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #1a6eff;
  box-shadow: 0 0 6px rgba(26,110,255,0.5);
}
.login-card-badge span {
  font-size: 11.5px;
  color: #1a6eff;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.login-card-header {
  margin-bottom: 36px;
}
.login-card-header h1 {
  font-size: 28px;
  font-weight: 800;
  color: #111827;
  margin-bottom: 8px;
  letter-spacing: -0.5px;
}
.login-card-header p {
  font-size: 15px;
  color: #4b5563;
  line-height: 1.55;
}

/* ── FORM ── */
.login-form .form-group {
  margin-bottom: 20px;
}

.login-form label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}

.login-form input[type="email"],
.login-form input[type="password"],
.login-form select {
  width: 100%;
  height: 48px;
  padding: 0 16px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 15px;
  font-family: 'Inter', sans-serif;
  color: #111827;
  outline: none;
  transition: all 0.2s ease;
  appearance: none;
  -webkit-appearance: none;
}

.login-form input:focus,
.login-form select:focus {
  background: #ffffff;
  border-color: #1a6eff;
  box-shadow: 0 0 0 4px rgba(26,110,255,0.15);
}

.login-form input::placeholder { color: #9ca3af; }

.label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}
.label-row label { margin-bottom: 0; }

.forgot-link {
  font-size: 13px;
  color: #1a6eff;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.15s;
}
.forgot-link:hover { color: #1252cc; text-decoration: underline; }

.btn-primary {
  width: 100%;
  height: 50px;
  background: #1a6eff;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-top: 30px;
  transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
  box-shadow: 0 4px 14px rgba(26,110,255,0.25);
}
.btn-primary:hover {
  background: #1252cc;
  box-shadow: 0 6px 20px rgba(26,110,255,0.3);
  transform: translateY(-1px);
}
.btn-primary:active { transform: translateY(0); box-shadow: none; }

/* Register area */
.login-register-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 32px 0 24px;
}

.login-register-text {
  text-align: center;
  font-size: 14px;
  color: #6b7280;
}
.login-register-text a {
  color: #1a6eff;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.15s;
}
.login-register-text a:hover { color: #1252cc; text-decoration: underline; }

/* Error messages */
.error-messages {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  padding: 14px 16px;
  margin-bottom: 24px;
  color: #b91c1c;
  font-weight: 600;
  font-size: 14px;
}

/* ── RESPONSIVE ── */
@media (max-width: 860px) {
  .login-left { display: none; }
  .login-right { padding: 32px 24px; background: #f9fafb; }
  .login-card { background: #ffffff; padding: 40px 32px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
}
@media (max-width: 480px) {
  .login-card { padding: 32px 24px; }
}
</style>

<div class="login-page">

  <!-- ── LEFT BRANDED PANEL ── -->
  <aside class="login-left">
    <div class="login-left-inner">

      <div class="login-brand">
        <div class="login-brand-logo">
          <img src="../assets/logo.png" alt="CodeXentric Logo">
        </div>
        <span class="login-brand-name">CodeXentric</span>
      </div>

      <!-- Abstract HRM Illustration -->
      <div class="login-illustration">
        <svg viewBox="0 0 340 260" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Background card shapes -->
          <rect x="20" y="60" width="300" height="180" rx="16" fill="rgba(255,255,255,0.10)"/>
          <rect x="40" y="30" width="260" height="50" rx="10" fill="rgba(255,255,255,0.08)"/>

          <!-- People icons -->
          <!-- Person 1 -->
          <circle cx="100" cy="110" r="22" fill="rgba(255,255,255,0.20)"/>
          <circle cx="100" cy="103" r="9" fill="rgba(255,255,255,0.70)"/>
          <path d="M78 136 Q100 124 122 136" stroke="rgba(255,255,255,0.70)" stroke-width="3" fill="none" stroke-linecap="round"/>

          <!-- Person 2 -->
          <circle cx="170" cy="110" r="22" fill="rgba(255,255,255,0.20)"/>
          <circle cx="170" cy="103" r="9" fill="rgba(255,255,255,0.70)"/>
          <path d="M148 136 Q170 124 192 136" stroke="rgba(255,255,255,0.70)" stroke-width="3" fill="none" stroke-linecap="round"/>

          <!-- Person 3 -->
          <circle cx="240" cy="110" r="22" fill="rgba(255,255,255,0.20)"/>
          <circle cx="240" cy="103" r="9" fill="rgba(255,255,255,0.70)"/>
          <path d="M218 136 Q240 124 262 136" stroke="rgba(255,255,255,0.70)" stroke-width="3" fill="none" stroke-linecap="round"/>

          <!-- Stats bar -->
          <rect x="50" y="160" width="240" height="12" rx="6" fill="rgba(255,255,255,0.12)"/>
          <rect x="50" y="160" width="160" height="12" rx="6" fill="rgba(255,255,255,0.50)"/>

          <rect x="50" y="182" width="240" height="8" rx="4" fill="rgba(255,255,255,0.12)"/>
          <rect x="50" y="182" width="100" height="8" rx="4" fill="rgba(255,255,255,0.35)"/>

          <rect x="50" y="200" width="240" height="8" rx="4" fill="rgba(255,255,255,0.12)"/>
          <rect x="50" y="200" width="200" height="8" rx="4" fill="rgba(255,255,255,0.42)"/>

          <!-- Decorative dots -->
          <circle cx="290" cy="50" r="5" fill="rgba(255,255,255,0.30)"/>
          <circle cx="304" cy="50" r="3" fill="rgba(255,255,255,0.20)"/>
          <circle cx="50" cy="50" r="4" fill="rgba(255,255,255,0.25)"/>
        </svg>
      </div>

      <h2 class="login-left-headline">Streamline Your Workforce</h2>
      <p class="login-left-sub">Manage employees, payroll, and HR operations — all from one intelligent platform.</p>
      <div class="stat-pills">
  <div class="pill"><div class="pill-dot"></div>HR Analytics</div>
  <div class="pill"><div class="pill-dot" style="background:#38bdf8;box-shadow:0 0 6px rgba(56,189,248,0.6)"></div>Payroll</div>
  <div class="pill"><div class="pill-dot" style="background:#818cf8;box-shadow:0 0 6px rgba(129,140,248,0.6)"></div>Attendance</div>
</div>
    </div>
  </aside>

  <!-- ── RIGHT FORM PANEL ── -->
  <div class="login-right">
    <div class="login-card">
      <div class="login-card-badge">
  <div class="login-card-badge-dot"></div>
  <span>Secure Login</span>
</div>
      <div class="login-card-header">
        <h1>Welcome Back</h1>
        <p>Please enter your credentials to access the system.</p>
      </div>

      <form action="login_process.php" method="POST" class="login-form"

        >
        <?php
        if (count($user->errors) > 0 && isset($user->errors['general'])) {
            echo '<div class="error-messages" style="margin-bottom: 20px; color: #B91C1C; font-weight: 600;">';
            foreach ($user->errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        ?>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="name@company.com">
        </div>



        <div class="form-group">
          <div class="label-row">
            <label for="password">Password</label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>
          <input type="password" id="password" name="password" placeholder="••••••••">
        </div>

        <button type="submit" class="btn-primary" name = "login">
          <span>Login</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>

<div class="login-register-divider"></div>
<p class="login-register-text">
  Don't have an account? <a href="register.php">Register here</a>
</p>
      </form>
    </div>
  </div>

</div>

<?php include_once "../includes/footer.php" ; ?>