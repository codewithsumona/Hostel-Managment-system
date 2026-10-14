<?php
declare(strict_types=1);

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function is_post(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function flash_set(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}

function flash_get(string $key): ?string {
    if (!isset($_SESSION['flash'][$key])) return null;
    $msg = (string)$_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function require_login(string $role): void {
    if (empty($_SESSION['user'])) {
        redirect('/hostel-meal/index.php');
    }
    if (($_SESSION['user']['role'] ?? '') !== $role) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

// Meal rules
function meal_units_from_status(array $status): float {
    $units = 0.0;
    if (!empty($status['breakfast'])) $units += 0.5;
    if (!empty($status['lunch']))     $units += 1.0;
    if (!empty($status['dinner']))    $units += 1.0;
    return $units;
}

// Fetch all approved requests for a student that overlap [start,end]
function fetch_requests_for_student(PDO $pdo, int $studentRef, string $start, string $end): array {
    $sql = "SELECT from_date, to_date, off_breakfast, off_lunch, off_dinner
            FROM meal_requests
            WHERE student_ref = :sid
              AND status = 'approved'
              AND NOT (to_date < :start OR from_date > :end)
            ORDER BY from_date ASC";
    $st = $pdo->prepare($sql);
    $st->execute([':sid' => $studentRef, ':start' => $start, ':end' => $end]);
    return $st->fetchAll();
}

// Determine meal status for a student on a specific date, given overlapping requests (any-off wins)
function meal_status_on_date(array $requests, string $date): array {
    $status = ['breakfast' => 1, 'lunch' => 1, 'dinner' => 1];
    foreach ($requests as $r) {
        if ($date < $r['from_date'] || $date > $r['to_date']) continue;
        if ((int)$r['off_breakfast'] === 1) $status['breakfast'] = 0;
        if ((int)$r['off_lunch'] === 1)     $status['lunch'] = 0;
        if ((int)$r['off_dinner'] === 1)    $status['dinner'] = 0;
    }
    return $status;
}

function month_start_end(int $year, int $month): array {
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = date('Y-m-t', strtotime($start));
    return [$start, $end];
}

function get_meal_rate(PDO $pdo, int $year, int $month): float {
    $st = $pdo->prepare('SELECT rate_per_meal_unit FROM meal_rates WHERE year=:y AND month=:m LIMIT 1');
    $st->execute([':y' => $year, ':m' => $month]);
    $v = $st->fetchColumn();
    return $v !== false ? (float)$v : 0.0;
}

function get_monthly_bazar_total(PDO $pdo, string $start, string $end): float {
    $st = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM bazar_expense WHERE expense_date BETWEEN :s AND :e');
    $st->execute([':s' => $start, ':e' => $end]);
    return (float)$st->fetchColumn();
}

function get_today_bazar_total(PDO $pdo, string $date): float {
    $st = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM bazar_expense WHERE expense_date = :d');
    $st->execute([':d' => $date]);
    return (float)$st->fetchColumn();
}
