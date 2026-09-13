<?php
require_once '../includes/config.php';
require_once 'admin_helpers.php';
requireAdmin();

$db = getDB();

if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM testimonials WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: /admin/testimonials.php?deleted=1'); exit;
}
if (isset($_GET['toggle'])) {
    $db->prepare("UPDATE testimonials SET is_active=1-is_active WHERE id=?")->execute([(int)$_GET['toggle']]);
    header('Location: /admin/testimonials.php'); exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = sanitize($_POST['customer_name'] ?? '');
    $review = sanitize($_POST['review'] ?? '');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    if (strlen($name) < 2)   $errors[] = 'Name required.';
    if (strlen($review) < 5) $errors[] = 'Review text required.';
    if (empty($errors)) {
        $db->prepare("INSERT INTO testimonials (customer_name,review,rating) VALUES (?,?,?)")->execute([$name,$review,$rating]);
        header('Location: /admin/testimonials.php?saved=1'); exit;
    }
}

$testimonials = $db->query("SELECT * FROM testimonials ORDER BY id DESC")->fetchAll();

adminHead('Testimonials');
adminNav('testimonials');
echo '<div class="admin-main"><main>';
?>
<div class="admin-topbar"><h1>Reviews &amp; Testimonials</h1></div>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success" style="margin-bottom:1rem">&#10003; Review added.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-error" style="margin-bottom:1rem">Review deleted.</div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:1.25rem;align-items:start">
  <div class="detail-card">
    <h3>Add New Review</h3>
    <?php if (!empty($errors)): ?><div class="alert alert-error" style="margin-bottom:.9rem"><?php foreach($errors as $e): ?><p>&#9888; <?= $e ?></p><?php endforeach; ?></div><?php endif; ?>
    <form method="POST">
      <div class="admin-form-group"><label>Customer Name *</label><input type="text" name="customer_name" placeholder="e.g. Rahim Ahmed" required></div>
      <div class="admin-form-group"><label>Review *</label><textarea name="review" rows="3" placeholder="Customer's review..." required></textarea></div>
      <div class="admin-form-group">
        <label>Rating</label>
        <select name="rating">
          <option value="5">&#11088;&#11088;&#11088;&#11088;&#11088; (5 stars)</option>
          <option value="4">&#11088;&#11088;&#11088;&#11088; (4 stars)</option>
          <option value="3">&#11088;&#11088;&#11088; (3 stars)</option>
        </select>
      </div>
      <button type="submit" class="btn-primary btn-full">Add Review</button>
    </form>
  </div>

  <div class="admin-table-wrap">
    <div class="admin-table-header"><h2>All Reviews (<?= count($testimonials) ?>)</h2></div>
    <table class="atbl">
      <thead><tr><th>Customer</th><th>Review</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($testimonials as $t): ?>
      <tr>
        <td style="font-weight:500;white-space:nowrap"><?= htmlspecialchars($t['customer_name']) ?></td>
        <td style="color:var(--text-secondary);font-size:.83rem;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($t['review']) ?></td>
        <td style="color:#ffb800;font-size:.85rem"><?= str_repeat('★',(int)$t['rating']) ?></td>
        <td>
          <a href="?toggle=<?= $t['id'] ?>" class="badge <?= $t['is_active']?'b-active':'b-hidden' ?>" style="cursor:pointer;text-decoration:none">
            <?= $t['is_active']?'Active':'Hidden' ?>
          </a>
        </td>
        <td>
          <a href="?delete=<?= $t['id'] ?>" class="abtn abtn-delete" onclick="return confirm('Delete this review?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($testimonials)): ?><tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted)">No reviews yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php echo '</main></div>'; adminFoot(); ?>
