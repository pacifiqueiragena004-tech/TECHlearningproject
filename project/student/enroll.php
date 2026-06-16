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

$stmt = $pdo->prepare('SELECT id, title FROM courses WHERE id = ? AND status = ? LIMIT 1');
$stmt->execute([$courseId, 'Published']);
$course = $stmt->fetch();
if (!$course) {
    header('Location: /student/index.php');
    exit;
}

$enrollStmt = $pdo->prepare('SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1');
$enrollStmt->execute([$current['id'], $courseId]);
if ($enrollStmt->fetch()) {
    header('Location: /student/course.php?course_id=' . $courseId);
    exit;
}

$insert = $pdo->prepare('INSERT INTO enrollments (user_id, course_id, progress, completed) VALUES (?, ?, 0, 0)');
$insert->execute([$current['id'], $courseId]);
header('Location: /student/course.php?course_id=' . $courseId);
exit;
