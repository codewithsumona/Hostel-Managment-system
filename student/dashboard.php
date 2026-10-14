<?php
require_once __DIR__ . '/../includes/init.php';
require_login('student');

$user = current_user();
$studentRef = (int)($user['student_ref'] ?? 0);
if ($studentRef <= 0) {
    http_response_code(500);
    echo 'Student profile not linked. Ask admin to add you properly.';
    exit;
}

$today = date('Y-m-d');
$requestsToday = fetch_requests_for_student($pdo, $studentRef, $today, $today);
$todayStatus = meal_status_on_date($requestsToday, $today);
$todayUnits = meal_units_from_status($todayStatus);

$year = (int)date('Y');
$month = (int)date('m');
[$start,$end] = month_start_end($year,$month);
$requestsMonth = fetch_requests_for_student($pdo, $studentRef, $start, $end);

$unitsSoFar = 0.0;
$cur = new DateTime($start);
$last = new DateTime($today);
while ($cur <= $last) {
    $d = $cur->format('Y-m-d');
    $stt = meal_status_on_date($requestsMonth, $d);
    $unitsSoFar += meal_units_from_status($stt);
    $cur->modify('+1 day');
}

$title = 'Student Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="grid grid-2">
  <div class="card">
    <h2>Welcome, <?= esc((string)($user['name'] ?? $user['student_id'] ?? 'Student')) ?></h2>
    <div class="muted">Student ID: <b><?= esc((string)($user['student_id'] ?? '')) ?></b> • Room: <b><?= esc((string)($user['room_no'] ?? '')) ?></b></div>
    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
      <a class="btn" href="/hostel-meal/student/meal_request.php">Create Meal OFF Request</a>
      <a class="btn btn-ghost" href="/hostel-meal/student/my_requests.php">View My Requests</a>
    </div>
  </div>

  <div class="card">
    <h3>Today's Meal Status (<?= esc($today) ?>)</h3>
    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
      <span class="badge <?= $todayStatus['breakfast'] ? 'badge-on' : 'badge-off' ?>">Breakfast: <?= $todayStatus['breakfast'] ? 'ON' : 'OFF' ?></span>
      <span class="badge <?= $todayStatus['lunch'] ? 'badge-on' : 'badge-off' ?>">Lunch: <?= $todayStatus['lunch'] ? 'ON' : 'OFF' ?></span>
      <span class="badge <?= $todayStatus['dinner'] ? 'badge-on' : 'badge-off' ?>">Dinner: <?= $todayStatus['dinner'] ? 'ON' : 'OFF' ?></span>
    </div>
    <div class="muted" style="margin-top:10px;">Today's total meal units:</div>
    <div style="font-size:30px; font-weight:900; margin-top:4px;"><?= esc(number_format($todayUnits, 1)) ?></div>
  </div>
</div>

<div class="card" style="margin-top:14px;">
  <h3>This Month (so far)</h3>
  <div class="muted">Meal units from <?= esc($start) ?> to <?= esc($today) ?></div>
  <div style="font-size:30px; font-weight:900; margin-top:6px;"><?= esc(number_format($unitsSoFar, 1)) ?></div>
  <div class="muted" style="margin-top:6px; font-size:13px;">
    Final monthly bill depends on the meal rate set by admin.
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
