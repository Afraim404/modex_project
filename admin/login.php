<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';

// Already logged in
if (isAdminLoggedIn()) { header('Location: /admin/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username=?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION[ADMIN_SESSION]       = true;
        $_SESSION['admin_username']    = $admin['username'];
        header('Location: /admin/index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
adminHead('Admin Login');
?>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;background:var(--black)">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2.25rem;width:100%;max-width:360px">
    <div style="font-family:'Space Grotesk',sans-serif;font-size:1.7rem;font-weight:700;text-align:center;margin-bottom:.35rem">MOD<span style="color:var(--accent)">EX</span></div>
    <div style="text-align:center;color:var(--text-muted);font-size:.83rem;margin-bottom:1.75rem">Admin Panel</div>
    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <form method="POST">
      <div class="admin-form-group"><label>Username</label><input type="text" name="username" placeholder="admin" required autofocus autocomplete="username"></div>
      <div class="admin-form-group"><label>Password</label><input type="password" name="password" placeholder="••••••••" required autocomplete="current-password"></div>
      <button type="submit" class="btn-primary btn-full" style="margin-top:.35rem">Sign In</button>
    </form>
    <p style="text-align:center;margin-top:1.25rem;font-size:.78rem"><a href="/" style="color:var(--text-muted)">&#8592; Back to website</a></p>
  </div>
</div>
<?php adminFoot(); ?>
