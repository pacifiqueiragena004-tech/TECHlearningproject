<?php
session_start();
require_once __DIR__ . '/config/db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!$login) {
        $errors[] = 'Email or username is required.';
    }
    if (!$password) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'Active') {
                $errors[] = 'Your account is not active. Contact admin.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                if ($remember) {
                    setcookie('remember_token', base64_encode($user['id'] . ':' . hash_hmac('sha256', $user['email'], 'secret')), time() + 30 * 24 * 60 * 60, '/');
                }
                if ($user['role'] === 'Admin') {
                    header('Location: /admin/index.php');
                    exit;
                }
                if ($user['role'] === 'Teacher') {
                    header('Location: /teacher/index.php');
                    exit;
                }
                header('Location: /student/index.php');
                exit;
            }
        } else {
            $errors[] = 'Invalid login credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - IT Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
    <div class="auth-page min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="card shadow-lg rounded-4 w-100" style="max-width: 900px;">
            <div class="row g-0">
                <div class="col-lg-6 bg-dark text-white rounded-start-4 p-5 d-flex flex-column justify-content-center">
                    <div>
                        <h2>Welcome Back</h2>
                        <p class="opacity-75">Login to continue to the modern IT learning platform and access secure student, teacher, and admin tools.</p>
                        <a href="/register.php" class="btn btn-outline-light">Create account</a>
                    </div>
                </div>
                <div class="col-lg-6 p-4 p-md-5">
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-0">Sign In</h3>
                            <p class="text-muted mb-0">Enter your credentials below.</p>
                        </div>
                        <a href="#" class="text-decoration-none">Forgot password?</a>
                    </div>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form action="" method="post" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Email or Username</label>
                            <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '', ENT_QUOTES) ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" <?= isset($_POST['remember']) ? 'checked' : '' ?> />
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-4">
                        <small class="text-muted">Don't have an account? <a href="/register.php">Register here</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>
