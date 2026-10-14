<?php
require_once __DIR__ . '/../includes/init.php';
require_login('admin');

$success = flash_get('success');
$error = flash_get('error');

$year = isset($_GET['year']) && ctype_digit((string)$_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) && ctype_digit((string)$_GET['month']) ? (int)$_GET['month'] : (int)date('m');
if ($month < 1 || $month > 12) $month = (int)date('m');

if (is_post()) {
    $year = (int)($_POST['year'] ?? $year);
    $month = (int)($_POST['month'] ?? $month);
    $rate = (string)($_POST['rate'] ?? '');
    if ($month < 1 || $month > 12) {
        flash_set('error', 'Invalid month.');
        redirect('/hostel-meal/admin/set_meal_rate.php');
    }
    if (!is_numeric($rate) || (float)$rate <= 0) {
        flash_set('error', 'Rate must be a positive number.');
        redirect('/hostel-meal/admin/set_meal_rate.php?year='.$year.'&month='.$month);
    }

    $st = $pdo->prepare('INSERT INTO meal_rates (month, year, rate_per_meal_unit) VALUES (:m,:y,:r)
                         ON DUPLICATE KEY UPDATE rate_per_meal_unit=VALUES(rate_per_meal_unit)');
    $st->execute([':m'=>$month, ':y'=>$year, ':r'=>(float)$rate]);

    flash_set('success', 'Meal rate saved.');
    redirect('/hostel-meal/admin/set_meal_rate.php?year='.$year.'&month='.$month);
}

$current = get_meal_rate($pdo, $year, $month);

$title = 'Set Meal Rate';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

<div class="card" style="max-width: 700px;">
  <h2>Set Meal Rate (per 1 meal unit)</h2>
  <div class="muted">Example: if you set <b>60</b>, then Breakfast (0.5) costs 30 and Lunch costs 60.</div>

  <form method="post" class="form" style="margin-top:12px; grid-template-columns: 1fr 1fr 1fr; align-items:end;">
    <div>
      <label>Year</label>
      <input type="number" name="year" value="<?= esc((string)$year) ?>" min="2000" max="2100" required />
    </div>
    <div>
      <label>Month</label>
      <select name="month">
        <?php for ($m=1; $m<=12; $m++): ?>
          <option value="<?= $m ?>" <?= $m===$month ? 'selected' : '' ?>><?= sprintf('%02d',$m) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div>
      <label>Rate (BDT)</label>
      <input type="number" step="0.01" min="0" name="rate" value="<?= esc($current > 0 ? (string)$current : '') ?>" placeholder="e.g., 60" required />
    </div>
    <button class="btn" type="submit">Save</button>
  </form>

  <div class="card" style="margin-top:14px;">
    <div class="muted">Current saved rate for <?= esc(sprintf('%04d-%02d', $year, $month)) ?></div>
    <div style="font-size:28px; font-weight:900; margin-top:6px;">
      <?= esc(number_format($current, 2)) ?> BDT
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
