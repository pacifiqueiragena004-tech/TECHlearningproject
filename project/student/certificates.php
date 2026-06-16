<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Student') {
    header('Location: /login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT c.*, co.title AS course_title FROM certificates c JOIN courses co ON c.course_id = co.id WHERE c.user_id = ? ORDER BY c.issued_at DESC');
$stmt->execute([$current['id']]);
$certificates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3>Your Certificates</h3>
                <p class="text-muted mb-0">Download certificates for completed courses.</p>
            </div>
            <a href="/student/index.php" class="btn btn-outline-primary">Back to Dashboard</a>
        </div>
        <div class="row g-4">
            <?php if (empty($certificates)): ?>
                <div class="col-12">
                    <div class="alert alert-warning">No certificates yet. Complete a course quiz to earn one.</div>
                </div>
            <?php endif; ?>
            <?php foreach ($certificates as $certificate): ?>
                <div class="col-md-6">
                    <div class="card glass-card p-4">
                        <h5><?= htmlspecialchars($certificate['course_title']) ?></h5>
                        <p class="text-muted mb-2">Issued on <?= date('M d, Y', strtotime($certificate['issued_at'])) ?></p>
                        <a href="<?= htmlspecialchars($certificate['certificate_file']) ?>" class="btn btn-primary" target="_blank">Download Certificate</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
