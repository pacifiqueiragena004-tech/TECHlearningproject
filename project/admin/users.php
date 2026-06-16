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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $userId = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action === 'block') {
        $update = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $update->execute(['Blocked', $userId]);
        $messages[] = 'User has been blocked.';
    } elseif ($action === 'activate') {
        $update = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $update->execute(['Active', $userId]);
        $messages[] = 'User has been activated.';
    } elseif ($action === 'delete') {
        $delete = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $delete->execute([$userId]);
        $messages[] = 'User has been deleted.';
    }
}

$users = $pdo->query('SELECT id, full_name, username, email, role, status, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="card glass-card shadow-lg">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3>Manage Users</h3>
                        <p class="text-muted mb-0">Approve, block, or remove students and teachers.</p>
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
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="7" class="text-muted">No registered users yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td><?= htmlspecialchars($user['status']) ?></td>
                                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <form method="post" class="d-flex gap-2 flex-wrap">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <?php if ($user['status'] !== 'Active'): ?>
                                                <button type="submit" name="action" value="activate" class="btn btn-sm btn-success">Activate</button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="block" class="btn btn-sm btn-warning">Block</button>
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
