<?php
require_once __DIR__ . '/../includes/init.php';
require_login('admin');

$defaultPassHash = password_hash('12345', PASSWORD_BCRYPT);

$success = flash_get('success');
$error = flash_get('error');

// Delete
if (!empty($_GET['delete']) && ctype_digit((string)$_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM students WHERE id=:id')->execute([':id'=>$id]);
    flash_set('success', 'Student deleted.');
    redirect('/hostel-meal/admin/manage_students.php');
}

// Reset student password
if (!empty($_GET['reset_pass']) && ctype_digit((string)$_GET['reset_pass'])) {
    $id = (int)$_GET['reset_pass'];
    $stmt = $pdo->prepare('SELECT student_id FROM students WHERE id=:id LIMIT 1');
    $stmt->execute([':id'=>$id]);
    $stuId = $stmt->fetchColumn();
    if ($stuId) {
        $pdo->prepare("UPDATE users SET password_hash=:ph WHERE role='student' AND username=:u")->execute([
            ':ph' => $defaultPassHash,
            ':u'  => $stuId
        ]);
        flash_set('success', 'Password reset to 12345.');
    }
    redirect('/hostel-meal/admin/manage_students.php');
}

// Add / Update
if (is_post()) {
    $action = (string)($_POST['action'] ?? '');

    $student_id = trim((string)($_POST['student_id'] ?? ''));
    $name = trim((string)($_POST['name'] ?? ''));
    $room_no = trim((string)($_POST['room_no'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));

    if ($student_id === '' || $name === '') {
        flash_set('error', 'Student ID and Name are required.');
        redirect('/hostel-meal/admin/manage_students.php');
    }

    try {
        if ($action === 'add') {
            $pdo->beginTransaction();
            $st = $pdo->prepare('INSERT INTO students (student_id, name, room_no, phone) VALUES (:sid,:n,:r,:p)');
            $st->execute([':sid'=>$student_id, ':n'=>$name, ':r'=>$room_no ?: null, ':p'=>$phone ?: null]);
            $newId = (int)$pdo->lastInsertId();

            // create login user: username = student_id, password = 12345
            $st2 = $pdo->prepare("INSERT INTO users (role, username, password_hash, student_ref) VALUES ('student', :u, :ph, :sr)");
            $st2->execute([':u'=>$student_id, ':ph'=>$defaultPassHash, ':sr'=>$newId]);
            $pdo->commit();

            flash_set('success', 'Student added. Login created (password: 12345).');
            redirect('/hostel-meal/admin/manage_students.php');
        }

        if ($action === 'update' && ctype_digit((string)($_POST['id'] ?? ''))) {
            $id = (int)$_POST['id'];
            // old student_id (to update username)
            $old = $pdo->prepare('SELECT student_id FROM students WHERE id=:id LIMIT 1');
            $old->execute([':id'=>$id]);
            $oldSid = $old->fetchColumn();

            $pdo->beginTransaction();
            $st = $pdo->prepare('UPDATE students SET student_id=:sid, name=:n, room_no=:r, phone=:p WHERE id=:id');
            $st->execute([':sid'=>$student_id, ':n'=>$name, ':r'=>$room_no ?: null, ':p'=>$phone ?: null, ':id'=>$id]);

            if ($oldSid) {
                $pdo->prepare("UPDATE users SET username=:newu WHERE role='student' AND username=:oldu")->execute([
                    ':newu' => $student_id,
                    ':oldu' => $oldSid
                ]);
            }
            $pdo->commit();

            flash_set('success', 'Student updated.');
            redirect('/hostel-meal/admin/manage_students.php');
        }

        flash_set('error', 'Invalid action.');
        redirect('/hostel-meal/admin/manage_students.php');

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash_set('error', 'Error: ' . $e->getMessage());
        redirect('/hostel-meal/admin/manage_students.php');
    }
}

$editId = (!empty($_GET['edit']) && ctype_digit((string)$_GET['edit'])) ? (int)$_GET['edit'] : null;
$edit = null;
if ($editId) {
    $st = $pdo->prepare('SELECT * FROM students WHERE id=:id LIMIT 1');
    $st->execute([':id'=>$editId]);
    $edit = $st->fetch();
}

$students = $pdo->query('SELECT * FROM students ORDER BY room_no, student_id')->fetchAll();

$title = 'Manage Students';
include __DIR__ . '/../includes/header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

<div class="grid grid-2">
  <div class="card">
    <h3><?= $edit ? 'Edit Student' : 'Add Student' ?></h3>
    <form method="post" class="form" autocomplete="off">
      <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>" />
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= esc((string)$edit['id']) ?>" /><?php endif; ?>

      <label>Student ID (Login Username)</label>
      <input name="student_id" required value="<?= esc((string)($edit['student_id'] ?? '')) ?>" placeholder="S-1002" />

      <label>Name</label>
      <input name="name" required value="<?= esc((string)($edit['name'] ?? '')) ?>" placeholder="Full Name" />

      <div class="grid grid-2" style="gap:10px;">
        <div>
          <label>Room No</label>
          <input name="room_no" value="<?= esc((string)($edit['room_no'] ?? '')) ?>" placeholder="101" />
        </div>
        <div>
          <label>Phone</label>
          <input name="phone" value="<?= esc((string)($edit['phone'] ?? '')) ?>" placeholder="01XXXXXXXXX" />
        </div>
      </div>

      <button class="btn" type="submit"><?= $edit ? 'Update' : 'Add Student' ?></button>
      <?php if ($edit): ?>
        <a class="btn btn-ghost" href="/hostel-meal/admin/manage_students.php">Cancel</a>
      <?php endif; ?>

      <div class="muted" style="margin-top:10px; font-size:13px;">
        New student login password default: <b>12345</b> (can reset from list)
      </div>
    </form>
  </div>

  <div class="card">
    <h3>All Students</h3>
    <div class="muted" style="margin-bottom:10px;">Total: <b><?= esc((string)count($students)) ?></b></div>
    <div style="overflow:auto;">
      <table class="table">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Room</th>
            <th>Phone</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= esc($s['student_id']) ?></td>
              <td><?= esc($s['name']) ?></td>
              <td><?= esc((string)($s['room_no'] ?? '')) ?></td>
              <td><?= esc((string)($s['phone'] ?? '')) ?></td>
              <td>
                <a class="btn btn-ghost" href="/hostel-meal/admin/manage_students.php?edit=<?= esc((string)$s['id']) ?>">Edit</a>
                <a class="btn btn-ghost" href="/hostel-meal/admin/manage_students.php?reset_pass=<?= esc((string)$s['id']) ?>" onclick="return confirm('Reset password to 12345?')">Reset Pass</a>
                <a class="btn btn-danger" href="/hostel-meal/admin/manage_students.php?delete=<?= esc((string)$s['id']) ?>" onclick="return confirm('Delete this student?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($students)): ?>
            <tr><td colspan="5" class="muted">No students yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
