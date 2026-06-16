<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Admin') {
    header('Location: /login.php');
    exit;
}

$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['action'])) {
    $courseId = intval($_POST['course_id']);
    $action = $_POST['action'];

    if ($action === 'publish') {
        $update = $pdo->prepare('UPDATE courses SET status = ? WHERE id = ?');
        $update->execute(['Published', $courseId]);
        $messages[] = 'Course has been approved and published.';
    } elseif ($action === 'draft') {
        $update = $pdo->prepare('UPDATE courses SET status = ? WHERE id = ?');
        $update->execute(['Draft', $courseId]);
        $messages[] = 'Course has been moved back to draft.';
    } elseif ($action === 'delete') {
        $delete = $pdo->prepare('DELETE FROM courses WHERE id = ?');
        $delete->execute([$courseId]);
        $messages[] = 'Course has been deleted.';
    }
}

$courses = $pdo->query('SELECT c.*, u.full_name AS teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id = u.id ORDER BY c.created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="card glass-card shadow-lg">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3>Manage Courses</h3>
                        <p class="text-muted mb-0">Approve, unpublish, or delete courses created by teachers.</p>
                    </div>
                    <a href="/admin/index.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
                <?php if (!empty($messages)): ?>
                    <div class="alert alert-info"><ul class="mb-0"><?php foreach ($messages as $message): ?><li><?= htmlspecialchars($message) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Teacher</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($courses)): ?>
                                <tr><td colspan="6" class="text-muted">No courses available.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['title']) ?></td>
                                    <td><?= htmlspecialchars($course['teacher_name'] ?: 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($course['category']) ?></td>
                                    <td><?= htmlspecialchars($course['status']) ?></td>
                                    <td><?= date('M d, Y', strtotime($course['created_at'])) ?></td>
                                    <td>
                                        <form method="post" class="d-flex gap-2 flex-wrap">
                                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                            <?php if ($course['status'] === 'Draft'): ?>
                                                <button type="submit" name="action" value="publish" class="btn btn-sm btn-success">Approve</button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="draft" class="btn btn-sm btn-warning">Unpublish</button>
                                            <?php endif; ?>
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
