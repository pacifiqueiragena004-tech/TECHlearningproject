<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Teacher') {
    header('Location: /login.php');
    exit;
}

$totalCourses = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE teacher_id = ?');
$totalCourses->execute([$current['id']]);
$totalCourses = $totalCourses->fetchColumn();

$myCourses = $pdo->prepare('SELECT * FROM courses WHERE teacher_id = ? ORDER BY created_at DESC');
$myCourses->execute([$current['id']]);
$myCourses = $myCourses->fetchAll();

$totalStudents = $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="row g-0">
        <aside class="col-lg-3 sidebar d-flex flex-column justify-content-between">
            <div>
                <a href="/teacher/index.php" class="d-flex align-items-center mb-4 text-decoration-none">
                    <div class="me-3 bg-primary rounded-circle" style="width: 48px; height: 48px;"></div>
                    <div>
                        <h5 class="mb-0">Teacher Hub</h5>
                        <small class="text-muted">Manage your courses</small>
                    </div>
                </a>
                <nav class="nav flex-column gap-2">
                    <a class="nav-link active" href="/teacher/index.php">Dashboard</a>
                    <a class="nav-link" href="#upload">Upload Course</a>
                    <a class="nav-link" href="#materials">Manage Materials</a>
                    <a class="nav-link" href="#students">Student Performance</a>
                    <a class="nav-link text-danger" href="/logout.php">Logout</a>
                </nav>
            </div>
        </aside>
        <main class="col-lg-9 dashboard-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h2>Welcome, <?= htmlspecialchars($current['full_name']) ?></h2>
                    <p class="text-muted">Upload courses, videos, PDFs and track your learners.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/teacher/quiz_create.php" class="btn btn-outline-primary">Create quiz</a>
                    <a href="/logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>

            <div class="dashboard-grid mb-4">
                <div class="dashboard-card">
                    <h5>Courses Created</h5>
                    <p class="display-6 mb-0"><?= $totalCourses ?></p>
                </div>
                <div class="dashboard-card">
                    <h5>Enrolled Students</h5>
                    <p class="display-6 mb-0"><?= $totalStudents ?></p>
                </div>
                <div class="dashboard-card">
                    <h5>Teacher Status</h5>
                    <p class="mb-0">Approved and ready to publish.</p>
                </div>
            </div>

            <section id="upload" class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Upload Course</h4>
                    <a href="#" class="text-decoration-none">Add new</a>
                </div>
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="dashboard-card p-4">
                            <h5>Course Management</h5>
                            <p class="text-muted">Upload a new course with resources and descriptions.</p>
                            <a href="/teacher/course_upload.php" class="btn btn-primary">Upload Course</a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="dashboard-card p-4">
                            <h5>Video Library</h5>
                            <p class="text-muted">Add video lessons and PDF attachments to your course.</p>
                            <a href="/teacher/course_upload.php" class="btn btn-primary">Upload Materials</a>
                        </div>
                    </div>
                </div>
            </section>

            <section id="students" class="mb-5">
                <h4>Student Performance</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-borderless text-white align-middle">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Status</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Placeholder Student</td>
                                <td>Web Development</td>
                                <td>Active</td>
                                <td>72%</td>
                            </tr>
                            <tr>
                                <td>Placeholder Student</td>
                                <td>AI Foundations</td>
                                <td>Active</td>
                                <td>52%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="materials">
                <h4>Your Courses</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-borderless text-white align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($myCourses)): ?>
                                <tr><td colspan="4" class="text-muted">You haven't uploaded any courses yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($myCourses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['title']) ?></td>
                                    <td><?= htmlspecialchars($course['category']) ?></td>
                                    <td><?= htmlspecialchars($course['status']) ?></td>
                                    <td><?= date('M d, Y', strtotime($course['created_at'])) ?></td>
                                    <td>
                                        <a href="/teacher/course_edit.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-outline-primary me-2">Edit</a>
                                        <a href="/teacher/course_delete.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
