
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
  
 @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800&family=Inter:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── PAGE ── */
.login-page {
  min-height: 100vh;
  display: flex;
  font-family: 'Inter', sans-serif;
  background: #0d1117;
}

/* ── LEFT PANEL ── */
.login-left {
  width: 46%;
  min-height: 100vh;
  background: #0d1117;
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
  background: radial-gradient(circle, rgba(99,102,241,0.13) 0%, transparent 70%);
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
  background: radial-gradient(circle, rgba(56,189,248,0.09) 0%, transparent 70%);
  bottom: -80px;
  right: -60px;
  pointer-events: none;
}

/* Glowing vertical divider */
.login-left + .login-right {
  border-left: 1px solid transparent;
}
.login-left {
  border-right: 1px solid;
  border-image: linear-gradient(
    to bottom,
    transparent,
    rgba(99,102,241,0.25) 30%,
    rgba(56,189,248,0.2) 70%,
    transparent
  ) 1;
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
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: #13182a;
  border: 1px solid rgba(99,102,241,0.35);
  box-shadow: 0 0 18px rgba(99,102,241,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-brand-logo img {
  width: 22px;
  height: 22px;
  object-fit: contain;
  filter: brightness(0) invert(1);
}

.login-brand-name {
  font-family: 'Nunito', sans-serif;
  font-size: 23px;
  font-weight: 800;
  color: #e2e8f0;
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
  font-family: 'Nunito', sans-serif;
  font-size: 22px;
  font-weight: 800;
  color: #e2e8f0;
  margin-bottom: 10px;
  line-height: 1.35;
}

.login-left-sub {
  font-size: 14px;
  color: #475569;
  line-height: 1.65;
  max-width: 250px;
}

/* Feature pills */
.stat-pills {
  display: flex;
  gap: 8px;
  margin-top: 28px;
  flex-wrap: wrap;
  justify-content: center;
}

.pill {
  background: #13182a;
  border: 1px solid rgba(99,102,241,0.2);
  border-radius: 20px;
  padding: 5px 14px;
  font-size: 12px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
}

.pill-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #6366f1;
  box-shadow: 0 0 6px rgba(99,102,241,0.6);
  flex-shrink: 0;
}

/* ── RIGHT PANEL ── */
.login-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 32px;
  background: #0d1117;
}

.login-card {
  background: #111827;
  border: 1px solid rgba(99,102,241,0.18);
  border-radius: 16px;
  padding: 44px 40px;
  width: 100%;
  max-width: 400px;
  box-shadow:
    0 0 40px rgba(99,102,241,0.08),
    0 20px 40px rgba(0,0,0,0.4);
}

/* Secure login badge */
.login-card-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #1a1f35;
  border: 1px solid rgba(99,102,241,0.25);
  border-radius: 20px;
  padding: 4px 12px;
  margin-bottom: 18px;
}
.login-card-badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #6366f1;
  box-shadow: 0 0 6px rgba(99,102,241,0.7);
}
.login-card-badge span {
  font-size: 11px;
  color: #818cf8;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.login-card-header {
  margin-bottom: 32px;
}
.login-card-header h1 {
  font-family: 'Nunito', sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: #e2e8f0;
  margin-bottom: 6px;
}
.login-card-header p {
  font-size: 13.5px;
  color: #475569;
  line-height: 1.55;
}

/* ── FORM ── */
.login-form .form-group {
  margin-bottom: 18px;
}

.login-form label {
  display: block;
  font-size: 11.5px;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 7px;
  text-transform: uppercase;
  letter-spacing: 0.7px;
}

.login-form input[type="email"],
.login-form input[type="password"],
.login-form select {
  width: 100%;
  height: 44px;
  padding: 0 14px;
  background: #0d1117;
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 9px;
  font-size: 14px;
  font-family: 'Inter', sans-serif;
  color: #e2e8f0;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  appearance: none;
  -webkit-appearance: none;
}

.login-form input:focus,
.login-form select:focus {
  border-color: rgba(99,102,241,0.6);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}

.login-form input::placeholder { color: #1e293b; }

.select-wrapper { position: relative; }
.select-wrapper::after {
  content: '';
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 0; height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 6px solid #475569;
  pointer-events: none;
}
.select-wrapper select { cursor: pointer; padding-right: 36px; }

.label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 7px;
}
.label-row label { margin-bottom: 0; }

.forgot-link {
  font-size: 12.5px;
  color: #818cf8;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.15s;
}
.forgot-link:hover { color: #a5b4fc; }

.btn-primary {
  width: 100%;
  height: 46px;
  background: #4f46e5;
  color: #fff;
  border: none;
  border-radius: 9px;
  font-size: 14.5px;
  font-weight: 600;
  font-family: 'Inter', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 26px;
  transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
  box-shadow:
    0 0 20px rgba(99,102,241,0.3),
    0 4px 12px rgba(0,0,0,0.3);
  letter-spacing: 0.2px;
}
.btn-primary:hover {
  background: #4338ca;
  box-shadow:
    0 0 28px rgba(99,102,241,0.45),
    0 4px 16px rgba(0,0,0,0.4);
  transform: translateY(-1px);
}
.btn-primary:active { transform: translateY(0); }

/* Register area */
.login-register-divider {
  height: 1px;
  background: rgba(255,255,255,0.06);
  margin: 24px 0 20px;
}

.login-register-text {
  text-align: center;
  font-size: 13.5px;
  color: #334155;
}
.login-register-text a {
  color: #818cf8;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.15s;
}
.login-register-text a:hover { color: #a5b4fc; }

/* Error messages */
.error-messages {
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 8px;
  padding: 12px 14px;
  margin-bottom: 20px;
  color: #fca5a5;
  font-weight: 600;
  font-size: 13.5px;
}

/* ── RESPONSIVE ── */
@media (max-width: 860px) {
  .login-left { display: none; }
  .login-right { padding: 24px 16px; }
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
          <img src="/codexentric/employee-system/assets/logo.png" alt="CodeXentric Logo">
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