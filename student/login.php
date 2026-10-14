<?php
require_once __DIR__ . '/../includes/init.php';

if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'student') {
    redirect('/hostel-meal/student/dashboard.php');
}

$error = null;
if (is_post()) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $st = $pdo->prepare('SELECT id, role, username, password_hash, student_ref FROM users WHERE role=\'student\' AND username=:u LIMIT 1');
    $st->execute([':u'=>$username]);
    $user = $st->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // load student
        $stu = null;
        if (!empty($user['student_ref'])) {
            $st2 = $pdo->prepare('SELECT id, student_id, name, room_no FROM students WHERE id=:id LIMIT 1');
            $st2->execute([':id'=>(int)$user['student_ref']]);
            $stu = $st2->fetch();
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'role' => 'student',
            'username' => $user['username'],
            'student_ref' => $stu ? (int)$stu['id'] : null,
            'student_id' => $stu ? $stu['student_id'] : $user['username'],
            'name' => $stu ? $stu['name'] : '',
            'room_no' => $stu ? $stu['room_no'] : ''
        ];
        redirect('/hostel-meal/student/dashboard.php');
    } else {
        $error = 'Wrong username or password.';
    }
}

$title = 'Student Login';
include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width: 520px; margin: 0 auto;">
  <h2>Student Login</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= esc($error) ?></div>
  <?php endif; ?>

  <form method="post" class="form" autocomplete="off">
    <label>Student ID (Username)</label>
    <input type="text" name="username" required placeholder="S-1001" />

    <label>Password</label>
    <input type="password" name="password" required placeholder="12345" />

    <button class="btn" type="submit">Login</button>
    <a class="btn btn-ghost" href="/hostel-meal/index.php">Back</a>
  </form>

  <div class="muted" style="margin-top:10px; font-size:13px;">
    If you are new, ask admin to add you. Default password is <b>12345</b>.
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
