<?php
session_start();
require_once __DIR__ . '/config/db.php';
$query = sanitize($_GET['q'] ?? '');
$category = sanitize($_GET['category'] ?? 'All');
$categoryOptions = ['All','Programming','Networking','Cybersecurity','Database','Web Development','AI','Computer Maintenance'];

$sql = 'SELECT c.*, u.full_name AS teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id WHERE c.status = ?';
$params = ['Published'];

if ($query) {
    $sql .= ' AND (c.title LIKE ? OR c.description LIKE ? OR u.full_name LIKE ? OR c.category LIKE ?)';
    $likeQuery = '%' . $query . '%';
    $params[] = $likeQuery;
    $params[] = $likeQuery;
    $params[] = $likeQuery;
    $params[] = $likeQuery;
}
if ($category && $category !== 'All') {
    $sql .= ' AND c.category = ?';
    $params[] = $category;
}
$sql .= ' ORDER BY c.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

$enrolledCourses = [];
if (is_logged_in() && user()['role'] === 'Student') {
    $enrollStmt = $pdo->prepare('SELECT course_id FROM enrollments WHERE user_id = ?');
    $enrollStmt->execute([$_SESSION['user_id']]);
    $enrolledCourses = array_column($enrollStmt->fetchAll(), 'course_id');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-5">
            <div>
                <h1>Course Catalog</h1>
                <p class="text-muted mb-0">Browse available IT and computer science courses, filter by category, and enroll with one click.</p>
            </div>
            <form class="d-flex gap-2" method="get" action="/courses.php">
                <input type="search" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES) ?>" class="form-control" placeholder="Search courses">
                <select name="category" class="form-select">
                    <?php foreach ($categoryOptions as $option): ?>
                        <option value="<?= $option ?>" <?= $category === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
        <div class="row g-4">
            <?php if (empty($courses)): ?>
                <div class="col-12">
                    <div class="alert alert-warning">No courses match your search. Try a different keyword or category.</div>
                </div>
            <?php endif; ?>
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6">
                    <div class="card glass-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5><?= htmlspecialchars($course['title']) ?></h5>
                                    <p class="text-muted mb-1"><?= htmlspecialchars($course['category']) ?> • <?= htmlspecialchars($course['teacher_name']) ?></p>
                                </div>
                                <span class="badge bg-primary align-self-start"><?= htmlspecialchars($course['status']) ?></span>
                            </div>
                            <p class="text-muted flex-grow-1"><?= htmlspecialchars(substr($course['description'], 0, 140)) ?></p>
                            <?php if (is_logged_in() && user()['role'] === 'Student'): ?>
                                <?php if (in_array($course['id'], $enrolledCourses, true)): ?>
                                    <a href="/student/course.php?course_id=<?= $course['id'] ?>" class="btn btn-outline-primary mt-3">View Course</a>
                                <?php else: ?>
                                    <a href="/student/enroll.php?course_id=<?= $course['id'] ?>" class="btn btn-primary mt-3">Enroll Now</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="/login.php" class="btn btn-primary mt-3">Login to Enroll</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
