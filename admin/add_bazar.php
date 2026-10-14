<?php
require_once __DIR__ . '/../includes/init.php';
require_login('admin');

$success = flash_get('success');
$error = flash_get('error');

if (!empty($_GET['delete']) && ctype_digit((string)$_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM bazar_expense WHERE id=:id')->execute([':id'=>$id]);
    flash_set('success', 'Expense deleted.');
    redirect('/hostel-meal/admin/add_bazar.php');
}

if (is_post()) {
    $expense_date = (string)($_POST['expense_date'] ?? '');
    $amount = (string)($_POST['amount'] ?? '');
    $note = trim((string)($_POST['note'] ?? ''));

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expense_date)) {
        flash_set('error', 'Invalid date.');
        redirect('/hostel-meal/admin/add_bazar.php');
    }
    if (!is_numeric($amount) || (float)$amount <= 0) {
        flash_set('error', 'Amount must be a positive number.');
        redirect('/hostel-meal/admin/add_bazar.php');
    }

    $st = $pdo->prepare('INSERT INTO bazar_expense (expense_date, amount, note) VALUES (:d,:a,:n)');
    $st->execute([':d'=>$expense_date, ':a'=>(float)$amount, ':n'=>$note ?: null]);
    flash_set('success', 'Bazar expense added.');
    redirect('/hostel-meal/admin/add_bazar.php');
}

$today = date('Y-m-d');
$recent = $pdo->query('SELECT * FROM bazar_expense ORDER BY expense_date DESC, id DESC LIMIT 12')->fetchAll();

$title = 'Add Bazar Expense';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

<div class="grid grid-2">
  <div class="card">
    <h2>Add Daily Bazar</h2>
    <form method="post" class="form" autocomplete="off">
      <label>Date</label>
      <input type="date" name="expense_date" required value="<?= esc($today) ?>" />

      <label>Amount (BDT)</label>
      <input type="number" step="0.01" min="0" name="amount" required placeholder="e.g., 500" />

      <label>Note (optional)</label>
      <input type="text" name="note" placeholder="e.g., Rice, Fish" />

      <button class="btn" type="submit">Save Expense</button>
      <a class="btn btn-ghost" href="/hostel-meal/admin/bazar_list.php">View Monthly List</a>
    </form>
  </div>

  <div class="card">
    <h3>Recent Entries</h3>
    <div style="overflow:auto;">
      <table class="table">
        <thead>
          <tr><th>Date</th><th>Amount</th><th>Note</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $r): ?>
            <tr>
              <td><?= esc($r['expense_date']) ?></td>
              <td><?= esc(number_format((float)$r['amount'],2)) ?></td>
              <td><?= esc((string)($r['note'] ?? '')) ?></td>
              <td><a class="btn btn-danger" href="/hostel-meal/admin/add_bazar.php?delete=<?= esc((string)$r['id']) ?>" onclick="return confirm('Delete this entry?')">Delete</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($recent)): ?>
            <tr><td colspan="4" class="muted">No bazar entries yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
