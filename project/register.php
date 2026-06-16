<?php
session_start();
require_once __DIR__ . '/config/db.php';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = sanitize($_POST['gender'] ?? 'Other');
    $country = sanitize($_POST['country'] ?? '');
    $role = sanitize($_POST['role'] ?? 'Student');
    $interested_course = sanitize($_POST['interested_course'] ?? 'Programming');
    $terms = isset($_POST['terms']);
    $status = $role === 'Teacher' ? 'Pending' : 'Active';

    if (!$full_name) {
        $errors[] = 'Full Name is required.';
    }
    if (!$username) {
        $errors[] = 'Username is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Password and confirm password must match.';
    }
    if (!$terms) {
        $errors[] = 'You must accept terms and conditions.';
    }

    if (!empty($_FILES['profile_picture']['name'])) {
        $uploadDir = __DIR__ . '/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = basename($_FILES['profile_picture']['name']);
        $targetFile = $uploadDir . time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
        $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
        $validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($fileType, $validTypes)) {
            $errors[] = 'Profile picture must be JPG, PNG, or WEBP.';
        }
        if (empty($errors)) {
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile);
            $profile_picture = '/uploads/profiles/' . basename($targetFile);
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $errors[] = 'A user with that email or username already exists.';
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (full_name, username, email, phone, password, gender, country, profile_picture, role, interested_course, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$full_name, $username, $email, $phone, $passwordHash, $gender, $country, $profile_picture ?? null, $role, $interested_course, $status]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - IT Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
    <div class="auth-page min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="card shadow-lg rounded-4 w-100" style="max-width: 980px;">
            <div class="row g-0">
                <div class="col-lg-6 bg-primary text-white rounded-start-4 p-5 d-flex flex-column justify-content-center">
                    <div>
                        <h2>Join the IT community</h2>
                        <p class="opacity-75">Register now to enroll in courses, track progress, and become a modern IT professional.</p>
                        <ul class="list-unstyled mt-4">
                            <li>• Student and Teacher accounts</li>
                            <li>• Upload courses, videos, PDFs</li>
                            <li>• Secure authentication with PHP and MySQL</li>
                            <li>• Responsive dashboard experience</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 p-4 p-md-5">
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="mb-0">Create an Account</h3>
                            <p class="text-muted mb-0">Complete the form to get started.</p>
                        </div>
                        <a href="/login.php" class="btn btn-outline-primary">Login</a>
                    </div>
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Registration successful! <?php if (($role ?? 'Student') === 'Teacher'): ?>Your teacher account is pending approval by admin.<?php else: ?><a href="/login.php">Login here</a>.<?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form action="" method="post" enctype="multipart/form-data" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES) ?>" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES) ?>" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES) ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" value="<?= htmlspecialchars($_POST['country'] ?? '', ENT_QUOTES) ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" name="profile_picture" accept="image/*" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="Student" <?= (($_POST['role'] ?? '') === 'Student') ? 'selected' : '' ?>>Student</option>
                                    <option value="Teacher" <?= (($_POST['role'] ?? '') === 'Teacher') ? 'selected' : '' ?>>Teacher</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Course Interested In</label>
                                <select name="interested_course" class="form-select">
                                    <?php
                                    $courses = ['Programming','Networking','Cybersecurity','Database','Web Development','AI','Computer Maintenance'];
                                    foreach ($courses as $course):
                                        $selected = (($_POST['interested_course'] ?? '') === $course) ? 'selected' : '';
                                        echo "<option value=\"$course\" $selected>$course</option>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" <?= isset($_POST['terms']) ? 'checked' : '' ?> />
                                    <label class="form-check-label" for="terms">I accept the terms and conditions.</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Register</button>
                            </div>
                            <div class="col-12 text-center">
                                <small class="text-muted">Already have an account? <a href="/login.php">Login</a></small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>
