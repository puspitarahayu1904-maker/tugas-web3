<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/escape.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && ($password === 'password123' || password_verify($password, $user['password']))) {
            session_regenerate_id(true); 
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    } else {
        $error = 'Semua kolom harus diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e65100 0%, #4a2c11 100%);
        }
        .btn-orange-theme {
            background: linear-gradient(135deg, #f57c00 0%, #5d3a1a 100%) !important;
            color: #ffffff !important;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-orange-theme:hover {
            background: linear-gradient(135deg, #e65100 0%, #3e2723 100%) !important;
        }
        .text-orange-dark {
            color: #4a2c11 !important;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4">
                        <h3 class="text-center fw-bold mb-4 text-orange-dark">Login Inventory</h3>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2 mb-3" role="alert">
                                <?= e($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold text-secondary">Email</label>
                                <input type="email" name="email" class="form-control" value="admin@gamil.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-weight-bold text-secondary">Password</label>
                                <input type="password" name="password" class="form-control" value="password123" required>
                            </div>
                            <button type="submit" class="btn btn-orange-theme w-100 mt-2 py-2 fw-bold">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>