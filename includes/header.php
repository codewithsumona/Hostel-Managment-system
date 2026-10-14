<?php
// expects: $title (string), optional $active (string)
$user = current_user();
$role = $user['role'] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($title ?? 'Hostel Meal Management') ?></title>
  <link rel="stylesheet" href="/hostel-meal/assets/css/style.css" />
</head>
<body>
  <header class="topbar">
    <div class="container topbar-inner">
      <div class="brand">
        <div class="logo">HM</div>
        <div>
          <div class="brand-title">Hostel Meal Management</div>
          <div class="brand-sub">Breakfast 0.5 • Lunch 1 • Dinner 1</div>
        </div>
      </div>

      <div class="topbar-right">
        <?php if ($user): ?>
          <div class="chip">Logged in: <b><?= esc($user['username'] ?? '') ?></b> (<?= esc($role) ?>)</div>
          <?php if ($role === 'admin'): ?>
            <a class="btn btn-ghost" href="/hostel-meal/admin/logout.php">Logout</a>
          <?php else: ?>
            <a class="btn btn-ghost" href="/hostel-meal/student/logout.php">Logout</a>
          <?php endif; ?>
        <?php else: ?>
          <a class="btn btn-ghost" href="/hostel-meal/index.php">Home</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <?php if ($user): ?>
    <nav class="nav">
      <div class="container nav-inner">
        <?php if ($role === 'admin'): ?>
          <a class="navlink" href="/hostel-meal/admin/dashboard.php">Dashboard</a>
          <a class="navlink" href="/hostel-meal/admin/manage_students.php">Students</a>
          <a class="navlink" href="/hostel-meal/admin/meal_by_date.php">Meal By Date</a>
          <a class="navlink" href="/hostel-meal/admin/add_bazar.php">Add Bazar</a>
          <a class="navlink" href="/hostel-meal/admin/bazar_list.php">Bazar List</a>
          <a class="navlink" href="/hostel-meal/admin/set_meal_rate.php">Meal Rate</a>
          <a class="navlink" href="/hostel-meal/admin/monthly_report.php">Monthly Report</a>
        <?php else: ?>
          <a class="navlink" href="/hostel-meal/student/dashboard.php">Dashboard</a>
          <a class="navlink" href="/hostel-meal/student/meal_request.php">Meal OFF Request</a>
          <a class="navlink" href="/hostel-meal/student/my_requests.php">My Requests</a>
        <?php endif; ?>
      </div>
    </nav>
  <?php endif; ?>

  <main class="container page">
