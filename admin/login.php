<?php
require_once __DIR__ . '/../includes/init.php';

if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
    redirect('/hostel-meal/admin/dashboard.php');
}

$error = null;
if (is_post()) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $st = $pdo->prepare('SELECT id, role, username, password_hash FROM users WHERE role=\'admin\' AND username=:u LIMIT 1');
    $st->execute([':u' => $username]);
    $user = $st->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = ['id' => (int)$user['id'], 'role' => 'admin', 'username' => $user['username']];
        redirect('/hostel-meal/admin/dashboard.php');
    } else {
        $error = 'Wrong username or password.';
    }
}

$title = 'Admin Login';
include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width: 520px; margin: 0 auto;">
  <h2>Admin Login</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= esc($error) ?></div>
  <?php endif; ?>

  <form method="post" class="form" autocomplete="off">
    <label>Username</label>
    <input type="text" name="username" required placeholder="admin" />

    <label>Password</label>
    <input type="password" name="password" required placeholder="admin123" />

    <button class="btn" type="submit">Login</button>
    <a class="btn btn-ghost" href="/hostel-meal/index.php">Back</a>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
