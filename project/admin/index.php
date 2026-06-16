<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Admin') {
    header('Location: /login.php');
    exit;
}

$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Student'")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Teacher'")->fetchColumn();
$pendingTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Teacher' AND status = 'Pending'")->fetchColumn();
$totalCourses = $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="row g-0">
        <aside class="col-lg-3 sidebar d-flex flex-column justify-content-between">
            <div>
                <a href="/admin/index.php" class="d-flex align-items-center mb-4 text-decoration-none">
                    <div class="me-3 bg-primary rounded-circle" style="width: 48px; height: 48px;"></div>
                    <div>
                        <h5 class="mb-0">Admin Center</h5>
                        <small class="text-muted">Platform oversight</small>
                    </div>
                </a>
                <nav class="nav flex-column gap-2">
                    <a class="nav-link active" href="/admin/index.php">Dashboard</a>
                    <a class="nav-link" href="/admin/users.php">Manage Users</a>
                    <a class="nav-link" href="/admin/courses.php">Manage Courses</a>
                    <a class="nav-link" href="/admin/approvals.php">Approve Teachers</a>
                    <a class="nav-link text-danger" href="/logout.php">Logout</a>
                </nav>
            </div>
        </aside>
        <main class="col-lg-9 dashboard-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h2>Admin Dashboard</h2>
                    <p class="text-muted">Manage students, teachers, courses, and platform activity.</p>
                </div>
                <a href="/logout.php" class="btn btn-danger">Logout</a>
            </div>

            <div class="dashboard-grid mb-4">
                <div class="dashboard-card">
                    <h5>Students</h5>
                    <p class="display-6 mb-0"><?= $totalStudents ?></p>
                </div>
                <div class="dashboard-card">
                    <h5>Teachers</h5>
                    <p class="display-6 mb-0"><?= $totalTeachers ?></p>
                </div>
                <div class="dashboard-card">
                    <h5>Pending Approvals</h5>
                    <p class="display-6 mb-0"><?= $pendingTeachers ?></p>
                </div>
                <div class="dashboard-card">
                    <h5>Courses</h5>
                    <p class="display-6 mb-0"><?= $totalCourses ?></p>
                </div>
            </div>

            <section id="users" class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Manage Users</h4>
                    <a href="#" class="text-decoration-none">View details</a>
                </div>
                <div class="dashboard-card p-4">
                    <p class="text-muted">Approve or delete student and teacher accounts, and monitor platform safety.</p>
                </div>
            </section>

            <section id="courses" class="mb-5">
                <h4>Manage Courses</h4>
                <div class="row g-4 mt-3">
                    <div class="col-md-4">
                        <div class="dashboard-card p-4">
                            <h5>Course Review</h5>
                            <p class="text-muted">Approve course content and manage curriculum quality.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card p-4">
                            <h5>Statistics</h5>
                            <p class="text-muted">Review platform performance metrics and enrollment trends.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card p-4">
                            <h5>User Controls</h5>
                            <p class="text-muted">Delete users or block accounts when needed.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="approvals">
                <h4>Approve Teachers</h4>
                <div class="dashboard-card p-4 mt-3">
                    <p class="text-muted">Use this section to review and approve teacher applications.</p>
                    <button class="btn btn-primary">Approve Teachers</button>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
