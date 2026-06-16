<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Student') {
    header('Location: /login.php');
    exit;
}

$coursesStmt = $pdo->prepare('SELECT c.*, e.id AS enrolled FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id AND e.user_id = ? WHERE c.status = ? ORDER BY c.created_at DESC LIMIT 6');
$coursesStmt->execute([$current['id'], 'Published']);
$courses = $coursesStmt->fetchAll();
$enrollments = $pdo->prepare('SELECT c.title, e.progress, e.completed FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = ? ORDER BY e.enrolled_at DESC LIMIT 6');
$enrollments->execute([$current['id']]);
$enrollments = $enrollments->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="row g-0">
        <aside class="col-lg-3 sidebar d-flex flex-column justify-content-between">
            <div>
                <a href="/student/index.php" class="d-flex align-items-center mb-4 text-decoration-none">
                    <div class="me-3 bg-primary rounded-circle" style="width: 48px; height: 48px;"></div>
                    <div>
                        <h5 class="mb-0">Student Hub</h5>
                        <small class="text-muted">Welcome back</small>
                    </div>
                </a>
                <nav class="nav flex-column gap-2">
                    <a class="nav-link active" href="/student/index.php">Dashboard</a>
                    <a class="nav-link" href="#courses">Courses</a>
                    <a class="nav-link" href="#progress">Progress</a>
                    <a class="nav-link" href="/student/quiz_history.php">Quiz History</a>
                    <a class="nav-link" href="#certificates">Certificates</a>
                    <a class="nav-link" href="#profile">Edit Profile</a>
                    <a class="nav-link text-danger" href="/logout.php">Logout</a>
                </nav>
            </div>
        </aside>
        <main class="col-lg-9 dashboard-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h2>Hi, <?= htmlspecialchars($current['full_name']) ?></h2>
                    <p class="text-muted">Explore new IT courses and keep your learning progress up to date.</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary">Search Courses</button>
                    <a href="/logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>

            <div class="dashboard-grid mb-4">
                <div class="dashboard-card">
                    <h5>Enrollments</h5>
                    <p class="display-6 mb-0"><?= count($enrollments) ?></p>
                </div>
                <div class="dashboard-card">
                    <h5>Courses Available</h5>
                    <p class="display-6 mb-0"><?= count($courses) ?></p>
                </div>
                <div class="dashboard-card">
                    <h5>Current Status</h5>
                    <p class="mb-0">Keep learning and collect certificates.</p>
                </div>
            </div>

            <section id="courses" class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Available Courses</h4>
                    <a href="#" class="text-decoration-none">View all</a>
                </div>
                <div class="row g-4">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-6">
                            <div class="card dashboard-card h-100">
                                <div class="card-body">
                                    <span class="badge bg-primary mb-2"><?= htmlspecialchars($course['category']) ?></span>
                                    <h5><?= htmlspecialchars($course['title']) ?></h5>
                                    <p class="text-muted"><?= htmlspecialchars(substr($course['description'], 0, 100)) ?></p>
                                    <?php if ($course['enrolled']): ?>
                                        <a href="/student/course.php?course_id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-light">View Course</a>
                                    <?php else: ?>
                                        <a href="/student/enroll.php?course_id=<?= $course['id'] ?>" class="btn btn-sm btn-primary">Enroll</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="progress" class="mb-5">
                <h4>Recent Progress</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-borderless text-white align-middle">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Progress</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enrollments)): ?>
                                <tr><td colspan="3" class="text-muted">No enrollments yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($enrollments as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['title']) ?></td>
                                    <td><?= intval($item['progress']) ?>%</td>
                                    <td><?= $item['completed'] ? 'Completed' : 'In progress' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="certificates">
                <h4>Certificates</h4>
                <div class="row g-4 mt-3">
                    <div class="col-md-4">
                        <div class="dashboard-card p-4">
                            <h5>Certificates</h5>
                            <p class="text-muted">View certificates for completed courses.</p>
                            <a href="/student/certificates.php" class="btn btn-outline-primary btn-sm">View all</a>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="dashboard-card p-4">
                            <h5>Profile</h5>
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= htmlspecialchars($current['profile_picture'] ?: 'https://via.placeholder.com/72') ?>" class="profile-avatar" alt="Profile">
                                <div>
                                    <strong><?= htmlspecialchars($current['full_name']) ?></strong>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($current['email']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
