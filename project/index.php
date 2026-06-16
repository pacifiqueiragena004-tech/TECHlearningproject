<?php
session_start();
require_once __DIR__ . '/config/db.php';
if (is_logged_in()) {
    $current = user();
    if ($current['role'] === 'Admin') {
        header('Location: /admin/index.php');
        exit;
    }
    if ($current['role'] === 'Teacher') {
        header('Location: /teacher/index.php');
        exit;
    }
    header('Location: /student/index.php');
    exit;
}

$videoList = [
    ['id' => 'dQw4w9WgXcQ', 'title' => 'Platform Tour'],
    ['id' => '3GwjfUFyY6M', 'title' => 'Learning Workflow'],
    ['id' => 'kXYiU_JCYtU', 'title' => 'Cybersecurity Basics'],
    ['id' => '9bZkp7q19f0', 'title' => 'AI Development'],
    ['id' => 'fRh_vgS2dFE', 'title' => 'Web Development Overview'],
    ['id' => '60ItHLz5WEA', 'title' => 'Database Fundamentals'],
    ['id' => '3fumBcKC6RE', 'title' => 'Networking Essentials'],
    ['id' => 'CevxZvSJLk8', 'title' => 'Programming Tips'],
    ['id' => 'RgKAFK5djSk', 'title' => 'Cloud Computing Intro'],
    ['id' => 'IcrbM1l_BoI', 'title' => 'Computer Maintenance Guide'],
];
$videoList = array_slice($videoList, 0, 30);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>IT Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
    <div class="landing-page d-flex align-items-center justify-content-center min-vh-100 bg-primary text-light">
        <div class="glass-card p-4 p-md-5 rounded-4 shadow-lg text-center w-100" style="max-width: 900px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="display-6 fw-bold">IT & Computer Learning</h1>
                    <p class="lead mb-0">Build skills with interactive courses, videos, quizzes, and certificates.</p>
                </div>
                <button id="themeToggle" class="btn btn-outline-light btn-sm">Dark Mode</button>
            </div>
            <div class="row gap-3 gap-lg-0 mt-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm bg-secondary bg-opacity-10">
                        <div class="card-body">
                            <h3 class="card-title">Ready to learn?</h3>
                            <p class="card-text">Register as a student or teacher to join live courses, upload training material, and track progress.</p>
                            <a href="/register.php" class="btn btn-primary btn-lg me-2">Get Started</a>
                            <a href="/courses.php" class="btn btn-outline-light btn-lg me-2">Browse Courses</a>
                            <a href="/login.php" class="btn btn-outline-light btn-lg">Login</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card h-100 border-0 shadow-sm bg-dark bg-opacity-10">
                        <div class="card-body">
                            <h5>Platform features</h5>
                            <ul class="list-unstyled mb-0 feature-list">
                                <li>• Responsive dashboard experience</li>
                                <li>• Secure login with hashed passwords</li>
                                <li>• Course enrollment and progress tracking</li>
                                <li>• Teacher course uploads and student analytics</li>
                                <li>• Admin management controls</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm bg-secondary bg-opacity-10">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="mb-3">Watch Tutorial</h4>
                            <p class="text-muted mb-4">Watch a quick product tour to learn how students and teachers use the platform.</p>
                            <div class="ratio ratio-16x9 rounded overflow-hidden mb-4">
                                <iframe id="mainTutorialVideo" src="https://www.youtube.com/embed/<?= htmlspecialchars($videoList[0]['id'], ENT_QUOTES) ?>" title="Tutorial Video" allowfullscreen></iframe>
                            </div>
                            <div class="row row-cols-1 row-cols-md-3 g-3">
                                <?php foreach ($videoList as $video): ?>
                                    <div class="col">
                                        <button type="button" class="video-selection-card btn p-0 text-start w-100" data-video-id="<?= htmlspecialchars($video['id'], ENT_QUOTES) ?>">
                                            <div class="card h-100 bg-dark bg-opacity-10 border-0 shadow-sm">
                                                <img src="https://img.youtube.com/vi/<?= htmlspecialchars($video['id'], ENT_QUOTES) ?>/mqdefault.jpg" class="card-img-top" alt="<?= htmlspecialchars($video['title'], ENT_QUOTES) ?>">
                                                <div class="card-body">
                                                    <h6 class="card-title mb-0 text-white"><?= htmlspecialchars($video['title'], ENT_QUOTES) ?></h6>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.video-selection-card').forEach(function(button) {
                button.addEventListener('click', function() {
                    const videoId = this.dataset.videoId;
                    const iframe = document.getElementById('mainTutorialVideo');
                    if (videoId && iframe) {
                        iframe.src = 'https://www.youtube.com/embed/' + videoId;
                    }
                });
            });
        });
    </script>
</body>
</html>
