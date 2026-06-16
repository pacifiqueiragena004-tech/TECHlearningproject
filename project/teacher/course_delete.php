<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Teacher') {
    header('Location: /login.php');
    exit;
}

$courseId = intval($_GET['id'] ?? 0);
if ($courseId <= 0) {
    header('Location: /teacher/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ? AND teacher_id = ? LIMIT 1');
$stmt->execute([$courseId, $current['id']]);
$course = $stmt->fetch();
if (!$course) {
    header('Location: /teacher/index.php');
    exit;
}

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete = $pdo->prepare('DELETE FROM courses WHERE id = ? AND teacher_id = ?');
    $delete->execute([$courseId, $current['id']]);
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="card glass-card shadow-lg">
            <div class="card-body p-5">
                <?php if ($success): ?>
                    <div class="alert alert-success">Course deleted successfully. <a href="/teacher/index.php">Return to dashboard</a>.</div>
                <?php else: ?>
                    <div class="mb-4">
                        <h3>Delete Course</h3>
                        <p class="text-muted">Are you sure you want to permanently delete the course "<?= htmlspecialchars($course['title']) ?>"?</p>
                    </div>
                    <form method="post">
                        <button type="submit" class="btn btn-danger me-3">Delete Forever</button>
                        <a href="/teacher/index.php" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
