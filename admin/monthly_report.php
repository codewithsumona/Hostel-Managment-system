<?php
require_once __DIR__ . '/../includes/init.php';
require_login('admin');

$year = isset($_GET['year']) && ctype_digit((string)$_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) && ctype_digit((string)$_GET['month']) ? (int)$_GET['month'] : (int)date('m');
if ($month < 1 || $month > 12) $month = (int)date('m');

[$start,$end] = month_start_end($year,$month);
$rate = get_meal_rate($pdo,$year,$month);

// bazar list
$bst = $pdo->prepare('SELECT * FROM bazar_expense WHERE expense_date BETWEEN :s AND :e ORDER BY expense_date ASC, id ASC');
$bst->execute([':s'=>$start, ':e'=>$end]);
$bazarRows = $bst->fetchAll();
$totalBazar = 0.0;
foreach ($bazarRows as $r) $totalBazar += (float)$r['amount'];

$students = $pdo->query('SELECT id, student_id, name, room_no FROM students ORDER BY room_no, student_id')->fetchAll();

// compute monthly meal units per student
$results = [];
$totalUnits = 0.0;
$totalBill = 0.0;

foreach ($students as $s) {
    $sid = (int)$s['id'];
    $requests = fetch_requests_for_student($pdo, $sid, $start, $end);

    $units = 0.0;
    $cur = new DateTime($start);
    $last = new DateTime($end);
    while ($cur <= $last) {
        $d = $cur->format('Y-m-d');
        $stt = meal_status_on_date($requests, $d);
        $units += meal_units_from_status($stt);
        $cur->modify('+1 day');
    }

    $bill = ($rate > 0) ? $units * $rate : 0.0;
    $totalUnits += $units;
    $totalBill += $bill;

    $results[] = [
        'student_id' => $s['student_id'],
        'name' => $s['name'],
        'room_no' => $s['room_no'],
        'units' => $units,
        'bill' => $bill,
    ];
}

$title = 'Monthly Report';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
  <h2>Monthly Report (<?= esc(sprintf('%04d-%02d', $year, $month)) ?>)</h2>

  <form method="get" class="form" style="grid-template-columns: 1fr 1fr auto; align-items:end;">
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
    <button class="btn" type="submit">Generate</button>
  </form>

  <?php if ($rate <= 0): ?>
    <div class="alert alert-danger" style="margin-top:12px;">
      Meal rate is not set for this month. Please set it from <b>Meal Rate</b> menu.
    </div>
  <?php endif; ?>

  <div class="grid grid-3" style="margin-top:14px;">
    <div class="card" style="margin:0;">
      <div class="muted">Meal Rate (per unit)</div>
      <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc(number_format($rate,2)) ?> BDT</div>
    </div>
    <div class="card" style="margin:0;">
      <div class="muted">Total Hostel Meal Units</div>
      <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc(number_format($totalUnits,1)) ?></div>
    </div>
    <div class="card" style="margin:0;">
      <div class="muted">Total Bazar (Month)</div>
      <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc(number_format($totalBazar,2)) ?> BDT</div>
    </div>
  </div>

  <div class="card" style="margin-top:14px;">
    <div class="muted">Total Calculated Meal Bill (sum of all students)</div>
    <div style="font-size:28px; font-weight:900; margin-top:6px;"><?= esc(number_format($totalBill,2)) ?> BDT</div>
    <?php if ($rate > 0): ?>
      <div class="muted" style="margin-top:6px;">
        Difference (Bazar - Bill): <b><?= esc(number_format($totalBazar - $totalBill,2)) ?> BDT</b>
      </div>
    <?php endif; ?>
  </div>

  <h3 style="margin-top:18px;">Per Student Meal & Bill</h3>
  <div style="overflow:auto;">
    <table class="table">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>Room</th>
          <th>Meal Units</th>
          <th>Bill (BDT)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results as $r): ?>
          <tr>
            <td><?= esc($r['student_id']) ?></td>
            <td><?= esc($r['name']) ?></td>
            <td><?= esc((string)($r['room_no'] ?? '')) ?></td>
            <td><b><?= esc(number_format((float)$r['units'],1)) ?></b></td>
            <td><b><?= esc(number_format((float)$r['bill'],2)) ?></b></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($results)): ?>
          <tr><td colspan="5" class="muted">No students found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <h3 style="margin-top:18px;">Daily Bazar (Expense Details)</h3>
  <div style="overflow:auto;">
    <table class="table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Amount (BDT)</th>
          <th>Note</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bazarRows as $b): ?>
          <tr>
            <td><?= esc($b['expense_date']) ?></td>
            <td><b><?= esc(number_format((float)$b['amount'],2)) ?></b></td>
            <td><?= esc((string)($b['note'] ?? '')) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($bazarRows)): ?>
          <tr><td colspan="3" class="muted">No bazar entries for this month.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
