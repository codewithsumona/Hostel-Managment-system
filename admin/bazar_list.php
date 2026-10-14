<?php
require_once __DIR__ . '/../includes/init.php';
require_login('admin');

$success = flash_get('success');
$error = flash_get('error');

$year = isset($_GET['year']) && ctype_digit((string)$_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) && ctype_digit((string)$_GET['month']) ? (int)$_GET['month'] : (int)date('m');
if ($month < 1 || $month > 12) $month = (int)date('m');

if (!empty($_GET['delete']) && ctype_digit((string)$_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM bazar_expense WHERE id=:id')->execute([':id'=>$id]);
    flash_set('success', 'Expense deleted.');
    redirect('/hostel-meal/admin/bazar_list.php?year=' . $year . '&month=' . $month);
}

[$start,$end] = month_start_end($year,$month);

$st = $pdo->prepare('SELECT * FROM bazar_expense WHERE expense_date BETWEEN :s AND :e ORDER BY expense_date ASC, id ASC');
$st->execute([':s'=>$start, ':e'=>$end]);
$rows = $st->fetchAll();
$total = 0.0;
foreach ($rows as $r) $total += (float)$r['amount'];

$title = 'Bazar List';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

<div class="card">
  <h2>Bazar List (<?= esc(sprintf('%04d-%02d', $year, $month)) ?>)</h2>

  <form method="get" class="form" style="grid-template-columns: 1fr 1fr auto; align-items:end;">
    <div>
      <label>Year</label>
      <input type="number" name="year" value="<?= esc((string)$year) ?>" min="2000" max="2100" required />
    </div>
    <div>
      <label>Month</label>
      <select name="month">
        <?php for ($m=1; $m<=12; $m++): ?>
          <option value="<?= $m ?>" <?= $m===$month ? 'selected' : '' ?>><?= sprintf('%02d', $m) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <button class="btn" type="submit">Load</button>
  </form>

  <div class="card" style="margin-top:14px;">
    <div class="muted">Total bazar this month</div>
    <div style="font-size:30px; font-weight:900; margin-top:6px;"><?= esc(number_format($total,2)) ?> BDT</div>
  </div>

  <div style="overflow:auto; margin-top:14px;">
    <table class="table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Amount (BDT)</th>
          <th>Note</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= esc($r['expense_date']) ?></td>
            <td><b><?= esc(number_format((float)$r['amount'],2)) ?></b></td>
            <td><?= esc((string)($r['note'] ?? '')) ?></td>
            <td>
              <a class="btn btn-danger" href="/hostel-meal/admin/bazar_list.php?year=<?= esc((string)$year) ?>&month=<?= esc((string)$month) ?>&delete=<?= esc((string)$r['id']) ?>" onclick="return confirm('Delete this entry?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="4" class="muted">No entries for this month.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
