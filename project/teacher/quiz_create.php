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
$courses = $pdo->prepare('SELECT id, title FROM courses WHERE teacher_id = ? ORDER BY title');
$courses->execute([$current['id']]);
$courses = $courses->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $questions = [];

    if (!$title) {
        $errors[] = 'Quiz title is required.';
    }
    if ($course_id <= 0) {
        $errors[] = 'Select a course for this quiz.';
    }

    for ($i = 1; $i <= 3; $i++) {
        $questionText = sanitize($_POST["question_text_$i"] ?? '');
        $options = [];
        for ($j = 1; $j <= 4; $j++) {
            $options[] = sanitize($_POST["question_{$i}_option_$j"] ?? '');
        }
        $answer = sanitize($_POST["question_{$i}_answer"] ?? '');
        if ($questionText && !in_array($answer, $options, true)) {
            $errors[] = "Question $i answer must match one of the options.";
        }
        if ($questionText && $answer) {
            $questions[] = [
                'question' => $questionText,
                'options' => $options,
                'answer' => $answer,
            ];
        }
    }

    if (empty($questions)) {
        $errors[] = 'At least one quiz question is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO quizzes (course_id, teacher_id, title, questions) VALUES (?, ?, ?, ?)');
        $stmt->execute([$course_id, $current['id'], $title, json_encode($questions)]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="card glass-card shadow-lg">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3>Create a Quiz</h3>
                        <p class="text-muted mb-0">Build a quiz for one of your published courses.</p>
                    </div>
                    <a href="/teacher/index.php" class="btn btn-outline-primary">Back to Dashboard</a>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success">Quiz created successfully.</div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <?php if (empty($courses)): ?>
                    <div class="alert alert-warning">You need to upload a course before creating a quiz. <a href="/teacher/course_upload.php">Upload a course</a>.</div>
                <?php else: ?>
                    <form method="post" class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Quiz Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Select a course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" <?= (($_POST['course_id'] ?? '') == $course['id']) ? 'selected' : '' ?>><?= htmlspecialchars($course['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="col-12">
                            <div class="card bg-secondary bg-opacity-10 border-0">
                                <div class="card-body">
                                    <h5 class="card-title">Question <?= $i ?></h5>
                                    <div class="mb-3">
                                        <label class="form-label">Question text</label>
                                        <input type="text" name="question_text_<?= $i ?>" value="<?= htmlspecialchars($_POST["question_text_$i"] ?? '', ENT_QUOTES) ?>" class="form-control">
                                    </div>
                                    <div class="row g-3">
                                        <?php for ($j = 1; $j <= 4; $j++): ?>
                                            <div class="col-md-6">
                                                <label class="form-label">Option <?= $j ?></label>
                                                <input type="text" name="question_<?= $i ?>_option_<?= $j ?>" value="<?= htmlspecialchars($_POST["question_{$i}_option_$j"] ?? '', ENT_QUOTES) ?>" class="form-control">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label">Correct option</label>
                                        <input type="text" name="question_<?= $i ?>_answer" value="<?= htmlspecialchars($_POST["question_{$i}_answer"] ?? '', ENT_QUOTES) ?>" class="form-control" placeholder="Enter exact correct answer text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Create Quiz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
