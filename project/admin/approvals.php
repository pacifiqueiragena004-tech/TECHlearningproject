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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_id'], $_POST['action'])) {
    $teacherId = intval($_POST['teacher_id']);
    $action = $_POST['action'] === 'approve' ? 'Active' : 'Blocked';
    $stmt = $pdo->prepare('SELECT id, full_name, email, role, status FROM users WHERE id = ? AND role = ? AND status = ? LIMIT 1');
    $stmt->execute([$teacherId, 'Teacher', 'Pending']);
    $teacher = $stmt->fetch();

    if ($teacher) {
        $update = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $update->execute([$action, $teacherId]);
        $messages[] = sprintf('Teacher %s has been %s.', htmlspecialchars($teacher['full_name']), $action === 'Active' ? 'approved' : 'blocked');
    } else {
        $messages[] = 'No pending teacher found for that request.';
    }
}

$pendingTeachers = $pdo->query("SELECT id, full_name, username, email, country, interested_course, created_at FROM users WHERE role = 'Teacher' AND status = 'Pending' ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Approvals - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="card glass-card shadow-lg">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3>Pending Teacher Approvals</h3>
                        <p class="text-muted mb-0">Review teacher accounts and approve them so they can upload courses.</p>
                    </div>
                    <a href="/admin/index.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
                <?php if (!empty($messages)): ?>
                    <div class="alert alert-info"><ul class="mb-0"><?php foreach ($messages as $message): ?><li><?= $message ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Interest</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingTeachers)): ?>
                                <tr><td colspan="6" class="text-muted">No teacher applications are pending at this time.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($pendingTeachers as $teacher): ?>
                                <tr>
                                    <td><?= htmlspecialchars($teacher['full_name']) ?></td>
                                    <td><?= htmlspecialchars($teacher['username']) ?></td>
                                    <td><?= htmlspecialchars($teacher['email']) ?></td>
                                    <td><?= htmlspecialchars($teacher['interested_course']) ?></td>
                                    <td><?= date('M d, Y', strtotime($teacher['created_at'])) ?></td>
                                    <td>
                                        <form method="post" class="d-flex gap-2">
                                            <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
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
