<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Student') {
    header('Location: /login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT qa.score, qa.passed, qa.taken_at, q.title AS quiz_title, c.title AS course_title FROM quiz_attempts qa JOIN quizzes q ON qa.quiz_id = q.id JOIN courses c ON q.course_id = c.id WHERE qa.user_id = ? ORDER BY qa.taken_at DESC');
$stmt->execute([$current['id']]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3>Quiz History</h3>
                <p class="text-muted mb-0">Review your past quiz attempts and scores.</p>
            </div>
            <a href="/student/index.php" class="btn btn-outline-primary">Back to Dashboard</a>
        </div>
        <div class="table-responsive">
            <table class="table table-borderless align-middle">
                <thead>
                    <tr>
                        <th>Quiz</th>
                        <th>Course</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr><td colspan="5" class="text-muted">No quiz attempts found yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($history as $attempt): ?>
                        <tr>
                            <td><?= htmlspecialchars($attempt['quiz_title']) ?></td>
                            <td><?= htmlspecialchars($attempt['course_title']) ?></td>
                            <td><?= htmlspecialchars($attempt['score']) ?>%</td>
                            <td><?= $attempt['passed'] ? 'Passed' : 'Failed' ?></td>
                            <td><?= date('M d, Y', strtotime($attempt['taken_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
