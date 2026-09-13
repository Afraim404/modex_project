<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db     = getDB();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $aStmt = $db->prepare("SELECT * FROM admin_users WHERE username=?");
    $aStmt->execute([$_SESSION['admin_username']]);
    $admin = $aStmt->fetch();

    if (!password_verify($current, $admin['password'])) {
        $errors[] = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        $db->prepare("UPDATE admin_users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
        $success = true;
    }
}

adminHead('Settings');
adminNav('settings');
echo '<div class="admin-main"><main>';
?>
<div class="admin-topbar"><h1>Settings</h1></div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start">
  <div class="detail-card">
    <h3>Change Password</h3>
    <?php if (!empty($errors)): ?><div class="alert alert-error" style="margin-bottom:.9rem"><?php foreach($errors as $e): ?><p>&#9888; <?= $e ?></p><?php endforeach; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" style="margin-bottom:.9rem">&#10003; Password changed successfully!</div><?php endif; ?>
    <form method="POST">
      <div class="admin-form-group"><label>Current Password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
      <div class="admin-form-group"><label>New Password</label><input type="password" name="new_password" placeholder="Min 6 characters" required autocomplete="new-password"></div>
      <div class="admin-form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required autocomplete="new-password"></div>
      <button type="submit" class="btn-primary btn-full">Update Password</button>
    </form>
  </div>

  <div class="detail-card">
    <h3>Site Info</h3>
    <div class="detail-row"><span>Inside Dhaka Delivery</span><span style="color:var(--accent);font-weight:700">&#2547;<?= DELIVERY_INSIDE_DHAKA ?></span></div>
    <div class="detail-row"><span>Outside Dhaka Delivery</span><span style="color:var(--accent);font-weight:700">&#2547;<?= DELIVERY_OUTSIDE_DHAKA ?></span></div>
    <p style="font-size:.75rem;color:var(--text-muted);margin-top:.9rem">Change delivery charges in <code style="background:var(--surface3);padding:1px 5px;border-radius:3px">includes/config.php</code></p>

    <div style="margin-top:1.25rem;padding-top:1.1rem;border-top:1px solid var(--border)">
      <h3 style="margin-bottom:.65rem">Session</h3>
      <p style="font-size:.875rem">&#128100; Logged in as <strong><?= htmlspecialchars($_SESSION['admin_username']??'admin') ?></strong></p>
      <a href="/admin/logout.php" class="abtn abtn-cancel" style="display:inline-block;margin-top:.65rem">Logout</a>
    </div>
  </div>
</div>
<?php echo '</main></div>'; adminFoot(); ?>
