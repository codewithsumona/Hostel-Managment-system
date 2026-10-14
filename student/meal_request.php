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

$success = flash_get('success');
$error = flash_get('error');

if (is_post()) {
    $from = (string)($_POST['from_date'] ?? '');
    $to = (string)($_POST['to_date'] ?? '');
    $offB = isset($_POST['off_breakfast']) ? 1 : 0;
    $offL = isset($_POST['off_lunch']) ? 1 : 0;
    $offD = isset($_POST['off_dinner']) ? 1 : 0;

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        flash_set('error', 'Please choose valid dates.');
        redirect('/hostel-meal/student/meal_request.php');
    }
    if ($from > $to) {
        flash_set('error', 'From date must be earlier than or equal to To date.');
        redirect('/hostel-meal/student/meal_request.php');
    }
    if (($offB + $offL + $offD) === 0) {
        flash_set('error', 'Please select at least one meal to turn OFF.');
        redirect('/hostel-meal/student/meal_request.php');
    }

    $st = $pdo->prepare('INSERT INTO meal_requests (student_ref, from_date, to_date, off_breakfast, off_lunch, off_dinner, status)
                         VALUES (:sr,:f,:t,:b,:l,:d,\'approved\')');
    $st->execute([
        ':sr'=>$studentRef,
        ':f'=>$from,
        ':t'=>$to,
        ':b'=>$offB,
        ':l'=>$offL,
        ':d'=>$offD,
    ]);

    flash_set('success', 'Meal OFF request saved.');
    redirect('/hostel-meal/student/my_requests.php');
}

$today = date('Y-m-d');
$title = 'Meal OFF Request';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

<div class="card" style="max-width: 760px;">
  <h2>Create Meal OFF Request</h2>
  <div class="muted">Choose a date range and select which meals you want to turn OFF.</div>

  <form method="post" class="form" style="margin-top:12px;">
    <div class="grid grid-2" style="gap:10px;">
      <div>
        <label>From Date</label>
        <input type="date" name="from_date" required value="<?= esc($today) ?>" />
      </div>
      <div>
        <label>To Date</label>
        <input type="date" name="to_date" required value="<?= esc($today) ?>" />
      </div>
    </div>

    <div class="card" style="margin: 10px 0 0;">
      <div class="muted" style="margin-bottom:8px;">Select meals to turn OFF:</div>
      <label style="display:flex; gap:10px; align-items:center; margin:6px 0;">
        <input type="checkbox" name="off_breakfast" /> Breakfast (0.5)
      </label>
      <label style="display:flex; gap:10px; align-items:center; margin:6px 0;">
        <input type="checkbox" name="off_lunch" /> Lunch (1)
      </label>
      <label style="display:flex; gap:10px; align-items:center; margin:6px 0;">
        <input type="checkbox" name="off_dinner" /> Dinner (1)
      </label>
    </div>

    <button class="btn" type="submit">Submit Request</button>
    <a class="btn btn-ghost" href="/hostel-meal/student/dashboard.php">Back</a>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
