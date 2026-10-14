<?php
require_once __DIR__ . '/includes/init.php';

// If already logged in, redirect to role dashboard
if (!empty($_SESSION['user'])) {
    if (($_SESSION['user']['role'] ?? '') === 'admin') redirect('/hostel-meal/admin/dashboard.php');
    if (($_SESSION['user']['role'] ?? '') === 'student') redirect('/hostel-meal/student/dashboard.php');
}

$title = 'Welcome';
include __DIR__ . '/includes/header.php';
?>

<div class="grid grid-2">
  <div class="card">
    <h2>Admin Panel</h2>
    <p class="muted">Manage students, meal status, bazar expenses, meal rate, and monthly reports.</p>
    <a class="btn" href="/hostel-meal/admin/login.php">Login as Admin</a>
    <div class="muted" style="margin-top:10px; font-size: 13px;">
      Demo: <b>admin</b> / <b>admin123</b>
    </div>
  </div>

  <div class="card">
    <h2>Student Panel</h2>
    <p class="muted">Request meal OFF by date range (breakfast/lunch/dinner) and view your requests.</p>
    <a class="btn" href="/hostel-meal/student/login.php">Login as Student</a>
    <div class="muted" style="margin-top:10px; font-size: 13px;">
      Demo: <b>S-1001</b> / <b>12345</b>
    </div>
  </div>
</div>

<div class="card" style="margin-top: 14px;">
  <h3>How meal counting works</h3>
  <ul class="muted" style="margin-top: 6px;">
    <li>Breakfast = 0.5 meal unit</li>
    <li>Lunch = 1 meal unit</li>
    <li>Dinner = 1 meal unit</li>
    <li>If a student sets a meal OFF for a date range, that meal is not counted on those dates.</li>
  </ul>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
