<?php
require_once __DIR__ . '/../includes/init.php';
require_login('admin');

$date = (string)($_GET['date'] ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$students = $pdo->query('SELECT id, student_id, name, room_no FROM students ORDER BY room_no, student_id')->fetchAll();
$statusMap = [];
foreach ($students as $s) {
    $statusMap[(int)$s['id']] = ['breakfast'=>1,'lunch'=>1,'dinner'=>1];
}

$st = $pdo->prepare("SELECT student_ref, off_breakfast, off_lunch, off_dinner
                    FROM meal_requests
                    WHERE status='approved' AND :d BETWEEN from_date AND to_date");
$st->execute([':d'=>$date]);
foreach ($st->fetchAll() as $r) {
    $sid = (int)$r['student_ref'];
    if (!isset($statusMap[$sid])) continue;
    if ((int)$r['off_breakfast'] === 1) $statusMap[$sid]['breakfast']=0;
    if ((int)$r['off_lunch'] === 1)     $statusMap[$sid]['lunch']=0;
    if ((int)$r['off_dinner'] === 1)    $statusMap[$sid]['dinner']=0;
}

$totB=$totL=$totD=0;
$totalUnits=0.0;
foreach ($statusMap as $stt) {
    $totB += (int)$stt['breakfast'];
    $totL += (int)$stt['lunch'];
    $totD += (int)$stt['dinner'];
    $totalUnits += meal_units_from_status($stt);
}

$title = 'Meal Status By Date';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
  <h2>Meal Status (<?= esc($date) ?>)</h2>
  <form method="get" class="form" style="grid-template-columns: 1fr auto; align-items:end;">
    <div>
      <label>Select Date</label>
      <input type="date" name="date" value="<?= esc($date) ?>" required />
    </div>
    <button class="btn" type="submit">View</button>
  </form>

  <div class="grid grid-3" style="margin-top:14px;">
    <div class="card" style="margin:0;">
      <div class="muted">Breakfast ON</div>
      <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc((string)$totB) ?></div>
    </div>
    <div class="card" style="margin:0;">
      <div class="muted">Lunch ON</div>
      <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc((string)$totL) ?></div>
    </div>
    <div class="card" style="margin:0;">
      <div class="muted">Dinner ON</div>
      <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc((string)$totD) ?></div>
    </div>
  </div>

  <div class="card" style="margin-top:14px;">
    <div class="muted">Total meal units this date</div>
    <div style="font-size:28px; font-weight:900; margin-top:6px;"><?= esc(number_format($totalUnits, 1)) ?></div>
  </div>

  <div style="overflow:auto; margin-top:14px;">
    <table class="table">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>Room</th>
          <th>Breakfast</th>
          <th>Lunch</th>
          <th>Dinner</th>
          <th>Units</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s):
          $sid = (int)$s['id'];
          $stt = $statusMap[$sid] ?? ['breakfast'=>1,'lunch'=>1,'dinner'=>1];
          $units = meal_units_from_status($stt);
        ?>
          <tr>
            <td><?= esc($s['student_id']) ?></td>
            <td><?= esc($s['name']) ?></td>
            <td><?= esc((string)($s['room_no'] ?? '')) ?></td>
            <td><span class="badge <?= $stt['breakfast'] ? 'badge-on' : 'badge-off' ?>"><?= $stt['breakfast'] ? 'ON' : 'OFF' ?></span></td>
            <td><span class="badge <?= $stt['lunch'] ? 'badge-on' : 'badge-off' ?>"><?= $stt['lunch'] ? 'ON' : 'OFF' ?></span></td>
            <td><span class="badge <?= $stt['dinner'] ? 'badge-on' : 'badge-off' ?>"><?= $stt['dinner'] ? 'ON' : 'OFF' ?></span></td>
            <td><b><?= esc(number_format($units, 1)) ?></b></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($students)): ?>
          <tr><td colspan="7" class="muted">No students found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
