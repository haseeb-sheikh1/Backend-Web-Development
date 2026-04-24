<?php 

    $title= "Register";
   
    require_once '../pages/Database.php';
    require_once '../pages/Users.php';
    $db = new Database();
    $connection = $db->getConnection();
    $user = new Users($connection);
   $user->registerUser(); 
    include_once "../includes/header.php"; 
?>



<style>
  
  @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Source+Sans+3:wght@300;400;500;600;700&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  .login-page {
    min-height: 100vh;
    display: flex;
    font-family: 'Source Sans 3', sans-serif;
    background: #F0F4FA;
  }

  
  .login-left {
    width: 45%;
    min-height: 100vh;
    background: linear-gradient(160deg, #1559B5 0%, #1E6FD9 55%, #2B87F0 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 48px;
    position: relative;
    overflow: hidden;
  }

  /* Decorative circles */
  .login-left::before {
    content: '';
    position: absolute;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    top: -120px;
    left: -120px;
  }
  .login-left::after {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
    bottom: -80px;
    right: -80px;
  }
  .login-left-inner {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  /* Logo area */
  .login-brand {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 56px;
  }
  .login-brand-logo {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
  }
  .login-brand-logo img {
    width: 32px;
    height: 32px;
    object-fit: contain;
  }
  .login-brand-name {
    font-family: 'Nunito', sans-serif;
    font-size: 26px;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.2px;
  }

  /* Illustration placeholder */
  .login-illustration {
    width: 100%;
    max-width: 340px;
    margin-bottom: 44px;
  }
  .login-illustration svg {
    width: 100%;
    height: auto;
    filter: drop-shadow(0 16px 40px rgba(0,0,0,0.18));
  }

  .login-left-headline {
    font-family: 'Nunito', sans-serif;
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 12px;
    line-height: 1.3;
  }
  .login-left-sub {
    font-size: 14.5px;
    color: rgba(255,255,255,0.75);
    line-height: 1.6;
    max-width: 280px;
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

  .login-card-header {
    margin-bottom: 36px;
  }
  .login-card-header h1 {
    font-family: 'Nunito', sans-serif;
    font-size: 26px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 6px;
  }
  .login-card-header p {
    font-size: 14px;
    color: #6B7280;
    line-height: 1.5;
  }

  /* Form */
  .login-form .form-group {
    margin-bottom: 20px;
  }
  .login-form label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 7px;
  }
  .login-form input[type="text"],
  .login-form input[type="email"],
  .login-form input[type="password"],
  .login-form select {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border: 1.5px solid #D1D5DB;
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Source Sans 3', sans-serif;
    color: #111827;
    background: #fff;
    transition: border-color 0.18s, box-shadow 0.18s;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
  }
  .login-form input:focus,
  .login-form select:focus {
    border-color: #1E6FD9;
    box-shadow: 0 0 0 3px rgba(30,111,217,0.12);
  }
  .login-form input::placeholder { color: #9CA3AF; }

  .select-wrapper {
    position: relative;
  }
  .select-wrapper::after {
    content: '';
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #6B7280;
    pointer-events: none;
  }
  .select-wrapper select {
    cursor: pointer;
    padding-right: 36px;
  }

  /* Two-column row for first/last name */
  .form-row {
    display: flex;
    gap: 14px;
  }
  .form-row .form-group {
    flex: 1;
  }

  .btn-primary {
    width: 100%;
    height: 46px;
    background: linear-gradient(135deg, #1559B5 0%, #1E6FD9 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    font-family: 'Source Sans 3', sans-serif;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 28px;
    transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
    box-shadow: 0 4px 14px rgba(21,89,181,0.30);
    letter-spacing: 0.2px;
  }
  .btn-primary:hover {
    background: linear-gradient(135deg, #1248A0 0%, #1559B5 100%);
    box-shadow: 0 6px 20px rgba(21,89,181,0.40);
    transform: translateY(-1px);
  }
  .btn-primary:active { transform: translateY(0); }

  .login-footer-link {
    text-align: center;
    margin-top: 22px;
    font-size: 13.5px;
    color: #6B7280;
  }
  .login-footer-link a {
    color: #1E6FD9;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.15s;
  }
  .login-footer-link a:hover { color: #1559B5; text-decoration: underline; }

  /* ── RESPONSIVE ── */
  @media (max-width: 860px) {
    .login-left { display: none; }
    .login-right { padding: 24px 16px; }
  }
  @media (max-width: 560px) {
    .form-row { flex-direction: column; gap: 0; }
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

          <!-- Add user icon (plus badge on last person) -->
          <circle cx="262" cy="88" r="12" fill="rgba(255,255,255,0.30)"/>
          <line x1="262" y1="82" x2="262" y2="94" stroke="rgba(255,255,255,0.90)" stroke-width="2.5" stroke-linecap="round"/>
          <line x1="256" y1="88" x2="268" y2="88" stroke="rgba(255,255,255,0.90)" stroke-width="2.5" stroke-linecap="round"/>

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

      <h2 class="login-left-headline">Join the Platform Today</h2>
      <p class="login-left-sub">Create your account and start managing your workforce with ease and efficiency.</p>

    </div>
  </aside>

  <!-- ── RIGHT FORM PANEL ── -->
  <div class="login-right">
    <div class="login-card">
      <div class="login-card-header">
        <h1>Create Account</h1>
        <p>Fill in the details below to register a new user.</p>
      </div>

      <form action="register.php" method="POST" class="login-form">


        <!-- First Name & Last Name side by side -->
<div class="form-row">
  <div class="form-group">
    <label for="first_name">First Name</label>
    <input type="text" id="first_name" name="first_name" 
           placeholder="Haseeb"
           value="<?php echo isset($user->Fname) ? htmlspecialchars($user->Fname) : ''; ?>">
    <?php if (isset($user->errors['first_name'])): ?>
      <span style="color:red; font-size:12px;"><?php echo $user->errors['first_name']; ?></span>
    <?php endif; ?>
  </div>

  <div class="form-group">
    <label for="last_name">Last Name</label>
    <input type="text" id="last_name" name="last_name" 
           placeholder="Sheikh"
           value="<?php echo isset($user->Lname) ? htmlspecialchars($user->Lname) : ''; ?>">
    <?php if (isset($user->errors['last_name'])): ?>
      <span style="color:red; font-size:12px;"><?php echo $user->errors['last_name']; ?></span>
    <?php endif; ?>
  </div>
</div>

<!-- Email -->
<div class="form-group">
  <label for="email">Email Address</label>
  <input type="email" id="email" name="email" 
         placeholder="name@gmail.com"
         value="<?php echo isset($user->email) ? htmlspecialchars($user->email) : ''; ?>">
  <?php if (isset($user->errors['email'])): ?>
    <span style="color:red; font-size:12px;"><?php echo $user->errors['email']; ?></span>
  <?php endif; ?>
</div>

<!-- Password -->
<div class="form-group">
  <label for="password">Password</label>
  <input type="password" id="password" name="password" placeholder="••••••••">
  <?php if (isset($user->errors['password'])): ?>
    <span style="color:red; font-size:12px;"><?php echo $user->errors['password']; ?></span>
  <?php endif; ?>
</div>

<!-- Confirm Password -->
<div class="form-group">
  <label for="confirm_password">Confirm Password</label>
  <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••">
  <?php if (isset($user->errors['confirm_password'])): ?>
    <span style="color:red; font-size:12px;"><?php echo $user->errors['confirm_password']; ?></span>
  <?php endif; ?>
</div>

<!-- General Error (e.g. DB error, email exists) -->
<?php if (isset($user->errors['general'])): ?>
  <div style="color:red; font-size:13px; margin-bottom:10px;">
    <?php echo $user->errors['general']; ?>
  </div>
<?php endif; ?>

        <button type="submit" class="btn-primary" name="register">
          <span>Create Account</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <line x1="19" y1="8" x2="19" y2="14"></line>
            <line x1="22" y1="11" x2="16" y2="11"></line>
          </svg>
        </button>

      </form>

      <p class="login-footer-link">Already have an account? <a href="login.php">Sign in</a></p>

    </div>
  </div>

</div>

<?php include_once "../includes/footer.php" ; ?>