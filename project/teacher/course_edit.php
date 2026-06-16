<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Teacher') {
    header('Location: /login.php');
    exit;
}

$courseId = intval($_GET['id'] ?? 0);
if ($courseId <= 0) {
    header('Location: /teacher/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ? AND teacher_id = ? LIMIT 1');
$stmt->execute([$courseId, $current['id']]);
$course = $stmt->fetch();
if (!$course) {
    header('Location: /teacher/index.php');
    exit;
}

$errors = [];
$success = false;
$categories = ['Programming','Networking','Cybersecurity','Database','Web Development','AI','Computer Maintenance'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $category = sanitize($_POST['category'] ?? 'Programming');
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'Published');

    if (!$title) {
        $errors[] = 'Course title is required.';
    }
    if (!$description) {
        $errors[] = 'Course description is required.';
    }

    $thumbnailPath = $course['thumbnail'];
    if (!empty($_FILES['thumbnail']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/course-thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = basename($_FILES['thumbnail']['name']);
        $targetFile = $uploadDir . time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
        $fileType = mime_content_type($_FILES['thumbnail']['tmp_name']);
        if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/webp'])) {
            $errors[] = 'Thumbnail must be JPG, PNG, or WEBP.';
        } else {
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile);
            $thumbnailPath = '/uploads/course-thumbnails/' . basename($targetFile);
        }
    }

    if (empty($errors)) {
        $update = $pdo->prepare('UPDATE courses SET title = ?, category = ?, description = ?, thumbnail = ?, status = ? WHERE id = ? AND teacher_id = ?');
        $update->execute([$title, $category, $description, $thumbnailPath, $status, $courseId, $current['id']]);
        $success = true;
        $course = array_merge($course, ['title' => $title, 'category' => $category, 'description' => $description, 'thumbnail' => $thumbnailPath, 'status' => $status]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="card glass-card shadow-lg">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3>Edit Course</h3>
                        <p class="text-muted mb-0">Update course details and publish status.</p>
                    </div>
                    <a href="/teacher/index.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success">Course details updated successfully.</div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Course Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($course['title'], ENT_QUOTES) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>" <?= ($course['category'] === $category) ? 'selected' : '' ?>><?= $category ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($course['description'], ENT_QUOTES) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Published" <?= ($course['status'] === 'Published') ? 'selected' : '' ?>>Published</option>
                            <option value="Draft" <?= ($course['status'] === 'Draft') ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail Image</label>
                        <input type="file" name="thumbnail" accept="image/*" class="form-control">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
