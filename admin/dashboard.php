<?php
require_once __DIR__ . '/../includes/init.php';
require_login('admin');

$today = date('Y-m-d');

$students = $pdo->query('SELECT id, student_id, name, room_no FROM students ORDER BY room_no, student_id')->fetchAll();
$totalStudents = count($students);

// default: all ON
$statusMap = [];
foreach ($students as $s) {
    $statusMap[(int)$s['id']] = ['breakfast'=>1,'lunch'=>1,'dinner'=>1];
}

$st = $pdo->prepare("SELECT student_ref, off_breakfast, off_lunch, off_dinner
                    FROM meal_requests
                    WHERE status='approved' AND :t BETWEEN from_date AND to_date");
$st->execute([':t'=>$today]);
foreach ($st->fetchAll() as $r) {
    $sid = (int)$r['student_ref'];
    if (!isset($statusMap[$sid])) continue;
    if ((int)$r['off_breakfast'] === 1) $statusMap[$sid]['breakfast']=0;
    if ((int)$r['off_lunch'] === 1)     $statusMap[$sid]['lunch']=0;
    if ((int)$r['off_dinner'] === 1)    $statusMap[$sid]['dinner']=0;
}

$bOn=$lOn=$dOn=0;
$totalUnits=0.0;
$anyOff=0;
foreach ($statusMap as $stt) {
    $bOn += (int)$stt['breakfast'];
    $lOn += (int)$stt['lunch'];
    $dOn += (int)$stt['dinner'];
    $units = meal_units_from_status($stt);
    $totalUnits += $units;
    if ($units < 2.5) $anyOff++;
}

$todayBazar = get_today_bazar_total($pdo, $today);

$title = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="grid grid-3">
  <div class="card">
    <div class="muted">Total Students</div>
    <div style="font-size:32px; font-weight:900; margin-top:6px;"><?= esc((string)$totalStudents) ?></div>
  </div>
  <div class="card">
    <div class="muted">Total Meal Units Today (<?= esc($today) ?>)</div>
    <div style="font-size:32px; font-weight:900; margin-top:6px;"><?= esc(number_format($totalUnits, 1)) ?></div>
  </div>
  <div class="card">
    <div class="muted">Today Bazar</div>
    <div style="font-size:32px; font-weight:900; margin-top:6px;"><?= esc(number_format($todayBazar, 2)) ?> BDT</div>
  </div>
</div>

<div class="grid grid-3" style="margin-top:14px;">
  <div class="card">
    <div class="muted">Breakfast ON</div>
    <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc((string)$bOn) ?></div>
  </div>
  <div class="card">
    <div class="muted">Lunch ON</div>
    <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc((string)$lOn) ?></div>
  </div>
  <div class="card">
    <div class="muted">Dinner ON</div>
    <div style="font-size:26px; font-weight:900; margin-top:6px;"><?= esc((string)$dOn) ?></div>
  </div>
</div>

<div class="card" style="margin-top:14px;">
  <h3>Quick Overview</h3>
  <div class="muted">Students with any meal OFF today: <b><?= esc((string)$anyOff) ?></b></div>
  <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn" href="/hostel-meal/admin/meal_by_date.php">View Meal Status By Date</a>
    <a class="btn btn-ghost" href="/hostel-meal/admin/monthly_report.php">Generate Monthly Report</a>
    <a class="btn btn-ghost" href="/hostel-meal/admin/manage_students.php">Manage Students</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
