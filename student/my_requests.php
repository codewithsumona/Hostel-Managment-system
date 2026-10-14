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

if (!empty($_GET['delete']) && ctype_digit((string)$_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM meal_requests WHERE id=:id AND student_ref=:sr')->execute([':id'=>$id, ':sr'=>$studentRef]);
    flash_set('success', 'Request deleted.');
    redirect('/hostel-meal/student/my_requests.php');
}

$st = $pdo->prepare('SELECT * FROM meal_requests WHERE student_ref=:sr ORDER BY created_at DESC, id DESC');
$st->execute([':sr'=>$studentRef]);
$rows = $st->fetchAll();

$title = 'My Requests';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

<div class="card">
  <h2>My Meal OFF Requests</h2>
  <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn" href="/hostel-meal/student/meal_request.php">New Request</a>
    <a class="btn btn-ghost" href="/hostel-meal/student/dashboard.php">Dashboard</a>
  </div>

  <div style="overflow:auto; margin-top:14px;">
    <table class="table">
      <thead>
        <tr>
          <th>From</th>
          <th>To</th>
          <th>Breakfast</th>
          <th>Lunch</th>
          <th>Dinner</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= esc($r['from_date']) ?></td>
            <td><?= esc($r['to_date']) ?></td>
            <td><span class="badge <?= ((int)$r['off_breakfast']===1) ? 'badge-off' : 'badge-on' ?>"><?= ((int)$r['off_breakfast']===1) ? 'OFF' : 'ON' ?></span></td>
            <td><span class="badge <?= ((int)$r['off_lunch']===1) ? 'badge-off' : 'badge-on' ?>"><?= ((int)$r['off_lunch']===1) ? 'OFF' : 'ON' ?></span></td>
            <td><span class="badge <?= ((int)$r['off_dinner']===1) ? 'badge-off' : 'badge-on' ?>"><?= ((int)$r['off_dinner']===1) ? 'OFF' : 'ON' ?></span></td>
            <td><span class="badge badge-on"><?= esc($r['status']) ?></span></td>
            <td>
              <a class="btn btn-danger" href="/hostel-meal/student/my_requests.php?delete=<?= esc((string)$r['id']) ?>" onclick="return confirm('Delete this request?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="7" class="muted">No requests yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
