<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Teacher') {
    header('Location: /login.php');
    exit;
}

$errors = [];
$success = false;
$categories = ['Programming','Networking','Cybersecurity','Database','Web Development','AI','Computer Maintenance'];
$resource_types = ['Video','PDF','Resource'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $category = sanitize($_POST['category'] ?? 'Programming');
    $description = sanitize($_POST['description'] ?? '');
    $resourceType = sanitize($_POST['resource_type'] ?? 'Resource');

    if (!$title) {
        $errors[] = 'Course title is required.';
    }
    if (!$description) {
        $errors[] = 'Course description is required.';
    }

    $thumbnailPath = null;
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
        }
        if (empty($errors)) {
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile);
            $thumbnailPath = '/uploads/course-thumbnails/' . basename($targetFile);
        }
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO courses (title, category, description, teacher_id, thumbnail, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $category, $description, $current['id'], $thumbnailPath, 'Draft']);

            if (!empty($_FILES['resource_file']['name'])) {
                $uploadDir = __DIR__ . '/../uploads/course-materials/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = basename($_FILES['resource_file']['name']);
                $targetFile = $uploadDir . time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
                $mimeType = mime_content_type($_FILES['resource_file']['tmp_name']);
                $allowed = ['video/mp4', 'video/webm', 'application/pdf'];
                if (!in_array($mimeType, $allowed)) {
                    throw new Exception('Resource file must be MP4, WebM, or PDF.');
                }

                if (!move_uploaded_file($_FILES['resource_file']['tmp_name'], $targetFile)) {
                    throw new Exception('Unable to upload resource file.');
                }

                $storageType = $resourceType;
                if ($mimeType === 'application/pdf') {
                    $storageType = 'PDF';
                } elseif (strpos($mimeType, 'video/') === 0) {
                    $storageType = 'Video';
                }
                $stmt = $pdo->prepare('INSERT INTO uploads (course_id, teacher_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$courseId, $current['id'], $fileName, '/uploads/course-materials/' . basename($targetFile), $storageType]);
            }

            $pdo->commit();
            $success = true;
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Course - Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="card glass-card shadow-lg">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3>Upload a New Course</h3>
                        <p class="text-muted mb-0">Add course details, a thumbnail, and a resource file for students.</p>
                    </div>
                    <a href="/teacher/index.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success">Course uploaded successfully and is now pending admin approval.</div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Course Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>" <?= (($_POST['category'] ?? '') === $category) ? 'selected' : '' ?>><?= $category ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Thumbnail Image</label>
                        <input type="file" name="thumbnail" accept="image/*" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Resource Type</label>
                        <select name="resource_type" class="form-select">
                            <?php foreach ($resource_types as $type): ?>
                                <option value="<?= $type ?>" <?= (($_POST['resource_type'] ?? '') === $type) ? 'selected' : '' ?>><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Upload Resource File</label>
                        <input type="file" name="resource_file" accept="video/mp4,video/webm,application/pdf" class="form-control">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Publish Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
