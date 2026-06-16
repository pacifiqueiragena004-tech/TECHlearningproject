<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Student') {
    header('Location: /login.php');
    exit;
}

$courseId = intval($_GET['course_id'] ?? 0);
if ($courseId <= 0) {
    header('Location: /student/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT c.*, u.full_name AS teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id = u.id WHERE c.id = ? AND c.status = ? LIMIT 1');
$stmt->execute([$courseId, 'Published']);
$course = $stmt->fetch();
if (!$course) {
    header('Location: /student/index.php');
    exit;
}

$enrollmentStmt = $pdo->prepare('SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1');
$enrollmentStmt->execute([$current['id'], $courseId]);
$enrollment = $enrollmentStmt->fetch();

$materialsStmt = $pdo->prepare('SELECT * FROM uploads WHERE course_id = ? ORDER BY uploaded_at DESC');
$materialsStmt->execute([$courseId]);
$materials = $materialsStmt->fetchAll();

$quizzesStmt = $pdo->prepare('SELECT id, title FROM quizzes WHERE course_id = ? ORDER BY created_at DESC');
$quizzesStmt->execute([$courseId]);
$quizzes = $quizzesStmt->fetchAll();

$certificateStmt = $pdo->prepare('SELECT * FROM certificates WHERE user_id = ? AND course_id = ? LIMIT 1');
$certificateStmt->execute([$current['id'], $courseId]);
$certificate = $certificateStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> - Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><?= htmlspecialchars($course['title']) ?></h2>
                <p class="text-muted mb-0"><?= htmlspecialchars($course['category']) ?> course by <?= htmlspecialchars($course['teacher_name'] ?: 'Instructor') ?></p>
            </div>
            <div>
                <a href="/student/index.php" class="btn btn-outline-primary">Back to Dashboard</a>
            </div>
        </div>

        <?php if (!$enrollment): ?>
            <div class="alert alert-warning">You are not enrolled in this course yet. <a href="/student/enroll.php?course_id=<?= $courseId ?>">Enroll now</a>.</div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card glass-card p-4 mb-4">
                    <h5>Course Description</h5>
                    <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                </div>
                <div class="card glass-card p-4 mb-4">
                    <h5>Materials</h5>
                    <?php if (empty($materials)): ?>
                        <p class="text-muted">No uploaded resources yet.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($materials as $material): ?>
                                <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($material['file_name']) ?></strong>
                                        <div class="text-muted small"><?= htmlspecialchars($material['file_type']) ?> • <?= date('M d, Y', strtotime($material['uploaded_at'])) ?></div>
                                    </div>
                                    <a href="<?= htmlspecialchars($material['file_path']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">Open</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="card glass-card p-4 mb-4">
                    <h5>Quizzes</h5>
                    <?php if (!$enrollment): ?>
                        <p class="text-muted">Enroll first to unlock quizzes.</p>
                    <?php elseif (empty($quizzes)): ?>
                        <p class="text-muted">No quizzes are available for this course yet.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($quizzes as $quiz): ?>
                                <li class="list-group-item bg-transparent border-0 d-flex justify-content-between align-items-center">
                                    <div><?= htmlspecialchars($quiz['title']) ?></div>
                                    <a href="/student/quiz.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-sm btn-primary">Take quiz</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card glass-card p-4 mb-4">
                    <h5>Progress</h5>
                    <?php if ($enrollment): ?>
                        <p class="mb-1"><strong><?= intval($enrollment['progress']) ?>%</strong> complete</p>
                        <div class="progress mb-3" style="height: 12px;"><div class="progress-bar bg-primary" style="width: <?= intval($enrollment['progress']) ?>%;"></div></div>
                        <p class="mb-0">Status: <?= $enrollment['completed'] ? 'Completed' : 'In progress' ?></p>
                    <?php else: ?>
                        <p class="text-muted">Enroll to start tracking your progress.</p>
                    <?php endif; ?>
                </div>
                <div class="card glass-card p-4">
                    <h5>Certificate</h5>
                    <?php if ($certificate): ?>
                        <p class="mb-3">Certificate issued on <?= date('M d, Y', strtotime($certificate['issued_at'])) ?>.</p>
                        <a href="<?= htmlspecialchars($certificate['certificate_file']) ?>" class="btn btn-outline-primary" target="_blank">Download certificate</a>
                    <?php else: ?>
                        <p class="text-muted">Complete the course quiz with 70% or higher to earn a certificate.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
