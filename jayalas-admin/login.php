<?php
require_once __DIR__ . "/../config.php";

if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    header("Location: index.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = input_filter($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin_user WHERE username='$username' LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_login'] = true;
            $_SESSION['admin_user']  = $row['username'];
            $_SESSION['admin_id']    = $row['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password salah.";
        }
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Admin - Bengkel Las Berkah Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        min-height: 100vh;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        font-family: system-ui, -apple-system, sans-serif;
    }

    .login-wrap {
        width: 100%;
        max-width: 400px;
    }

    .login-brand {
        text-align: center;
        margin-bottom: 28px;
    }

    .login-brand-icon {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.4rem;
        margin-bottom: 12px;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.4);
    }

    .login-brand h5 {
        color: #fff;
        font-weight: 700;
        font-size: 1rem;
        margin: 0;
    }

    .login-brand small {
        color: rgba(255, 255, 255, 0.45);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .login-card {
        background: #fff;
        border-radius: 18px;
        padding: 36px 32px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
    }

    .login-card h4 {
        font-weight: 800;
        font-size: 1.3rem;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .login-card .subtitle {
        color: #94a3b8;
        font-size: 0.85rem;
        margin-bottom: 24px;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.83rem;
        color: #475569;
        margin-bottom: 6px;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
    }

    .input-icon input {
        padding-left: 36px;
        border-radius: 9px;
        border: 1.5px solid #e2e8f0;
        font-size: 0.9rem;
        height: 44px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-icon input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .btn-login {
        width: 100%;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 9px;
        padding: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s;
        margin-top: 8px;
    }

    .btn-login:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

    .login-footer {
        text-align: center;
        margin-top: 20px;
        color: rgba(255, 255, 255, 0.35);
        font-size: 0.78rem;
    }

    @media (max-width: 480px) {
        .login-card {
            padding: 28px 20px;
        }
    }
    </style>
</head>

<body>
    <div class="login-wrap">

        <!-- Brand -->
        <div class="login-brand">
            <div class="login-brand-icon">
                <i class="bi bi-tools"></i>
            </div>
            <h5>Berkah Jaya</h5>
            <small>Panel Admin</small>
        </div>

        <!-- Card Login -->
        <div class="login-card">
            <h4>Selamat Datang</h4>
            <p class="subtitle">Masuk ke panel admin untuk mengelola data bengkel.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3"
                style="font-size:0.85rem; border-radius:8px;">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username"
                            required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password"
                            required>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
            </form>
        </div>

        <p class="login-footer">&copy; <?php echo date('Y'); ?> Bengkel Las Berkah Jaya</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>