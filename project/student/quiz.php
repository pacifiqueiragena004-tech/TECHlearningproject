<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_login();
$current = user();
if ($current['role'] !== 'Student') {
    header('Location: /login.php');
    exit;
}

$quizId = intval($_GET['quiz_id'] ?? 0);
if ($quizId <= 0) {
    header('Location: /student/index.php');
    exit;
}

$quizStmt = $pdo->prepare('SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ? LIMIT 1');
$quizStmt->execute([$quizId]);
$quiz = $quizStmt->fetch();
if (!$quiz) {
    header('Location: /student/index.php');
    exit;
}

$enrollStmt = $pdo->prepare('SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1');
$enrollStmt->execute([$current['id'], $quiz['course_id']]);
$enrollment = $enrollStmt->fetch();
if (!$enrollment) {
    header('Location: /student/course.php?course_id=' . $quiz['course_id']);
    exit;
}

$questions = json_decode($quiz['questions'], true) ?: [];
$errors = [];
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correct = 0;
    $answers = [];
    foreach ($questions as $index => $question) {
        $answer = sanitize($_POST['answer_' . $index] ?? '');
        $isCorrect = $answer === $question['answer'];
        if ($isCorrect) {
            $correct++;
        }
        $answers[] = [
            'question' => $question['question'],
            'selected' => $answer,
            'correct_answer' => $question['answer'],
            'is_correct' => $isCorrect,
        ];
    }
    $score = $questions ? round(($correct / count($questions)) * 100) : 0;
    $passed = $score >= 70;

    $insertAttempt = $pdo->prepare('INSERT INTO quiz_attempts (quiz_id, user_id, score, passed, answers) VALUES (?, ?, ?, ?, ?)');
    $insertAttempt->execute([$quizId, $current['id'], $score, $passed ? 1 : 0, json_encode($answers)]);

    if ($passed) {
        $newProgress = 100;
        $completed = 1;
        $update = $pdo->prepare('UPDATE enrollments SET progress = ?, completed = ? WHERE id = ?');
        $update->execute([$newProgress, $completed, $enrollment['id']]);

        $certStmt = $pdo->prepare('SELECT id FROM certificates WHERE user_id = ? AND course_id = ? LIMIT 1');
        $certStmt->execute([$current['id'], $quiz['course_id']]);
        $certificate = $certStmt->fetch();
        if (!$certificate) {
            $certDir = __DIR__ . '/../uploads/certificates/';
            if (!is_dir($certDir)) {
                mkdir($certDir, 0755, true);
            }
            $certFileName = 'certificate_' . $current['id'] . '_' . $quiz['course_id'] . '_' . time() . '.txt';
            $certPath = $certDir . $certFileName;
            $certificateText = "Certificate of Completion\n\n" .
                "This certifies that " . $current['full_name'] . " has successfully completed the \"" . $quiz['course_title'] . "\" course on " . date('F j, Y') . ".\n";
            file_put_contents($certPath, $certificateText);
            $insert = $pdo->prepare('INSERT INTO certificates (user_id, course_id, certificate_file) VALUES (?, ?, ?)');
            $insert->execute([$current['id'], $quiz['course_id'], '/uploads/certificates/' . $certFileName]);
        }
    }
    $result = [
        'score' => $score,
        'passed' => $passed,
        'correct' => $correct,
        'total' => count($questions)
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="dashboard-shell">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                <p class="text-muted mb-0">Course: <?= htmlspecialchars($quiz['course_title']) ?></p>
            </div>
            <div>
                <a href="/student/course.php?course_id=<?= $quiz['course_id'] ?>" class="btn btn-outline-primary">Back to Course</a>
            </div>
        </div>
        <?php if ($result): ?>
            <div class="alert <?= $result['passed'] ? 'alert-success' : 'alert-danger' ?>">
                <h5 class="mb-2"><?= $result['passed'] ? 'Quiz Passed' : 'Quiz Failed' ?></h5>
                <p class="mb-1">Score: <?= $result['score'] ?>%</p>
                <p class="mb-0">Correct answers: <?= $result['correct'] ?>/<?= $result['total'] ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$result): ?>
            <form method="post" class="row g-4">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="col-12">
                        <div class="card glass-card p-4">
                            <h5 class="mb-3"><?= htmlspecialchars($question['question']) ?></h5>
                            <?php foreach ($question['options'] as $option): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="answer_<?= $index ?>" id="answer_<?= $index ?>_<?= htmlspecialchars(md5($option)) ?>" value="<?= htmlspecialchars($option) ?>" required>
                                    <label class="form-check-label" for="answer_<?= $index ?>_<?= htmlspecialchars(md5($option)) ?>"><?= htmlspecialchars($option) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Submit Quiz</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
