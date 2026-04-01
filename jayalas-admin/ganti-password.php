<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$error  = '';
$sukses = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi    = $_POST['konfirmasi'];
    $admin_id      = (int)$_SESSION['admin_id'];

    // Validasi input tidak kosong
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
        $error = 'Semua field wajib diisi.';
    } elseif (strlen($password_baru) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($password_baru !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Ambil password tersimpan
        $stmt = $conn->prepare("SELECT password FROM admin_user WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if (!$admin || !password_verify($password_lama, $admin['password'])) {
            $error = 'Password lama tidak sesuai.';
        } else {
            $hash  = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("UPDATE admin_user SET password = ? WHERE id = ?");
            $stmt2->bind_param("si", $hash, $admin_id);
            $stmt2->execute();
            $sukses = 'Password berhasil diubah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Ganti Password - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    body {
        min-height: 100vh;
        background: #f1f5f9;
        font-family: system-ui, sans-serif;
        font-size: 0.9rem;
    }

    .form-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        padding: 28px;
        max-width: 440px;
    }

    .form-card h5 {
        font-weight: 700;
        font-size: 1rem;
        color: #0f172a;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.82rem;
        color: #475569;
        margin-bottom: 5px;
    }

    .input-wrap {
        position: relative;
    }

    .input-wrap input {
        padding-right: 40px;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        font-size: 0.88rem;
        height: 42px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-wrap input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .toggle-pw {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0;
        font-size: 1rem;
        line-height: 1;
    }

    .toggle-pw:hover {
        color: #475569;
    }

    .btn-simpan {
        width: 100%;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 11px;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.2s;
        margin-top: 4px;
    }

    .btn-simpan:hover {
        background: #1d4ed8;
    }

    .alert {
        border-radius: 8px;
        font-size: 0.85rem;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pw-hint {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 4px;
    }
    </style>
</head>

<body>
    <?php include "navbar-menu.php"; ?>

    <div class="container-fluid px-3 px-md-4 py-4">

        <?php if ($sukses): ?>
        <div class="alert alert-success mb-3" style="max-width:440px;">
            <i class="bi bi-check-circle-fill text-success"></i>
            <?php echo $sukses; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger mb-3" style="max-width:440px;">
            <i class="bi bi-exclamation-circle-fill text-danger"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="form-card">
            <h5><i class="bi bi-key me-2 text-primary"></i>Ganti Password</h5>

            <form method="post" action="">

                <div class="mb-3">
                    <label class="form-label">Password Lama</label>
                    <div class="input-wrap">
                        <input type="password" name="password_lama" id="pw_lama" class="form-control" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw_lama', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <div class="input-wrap">
                        <input type="password" name="password_baru" id="pw_baru" class="form-control" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw_baru', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <p class="pw-hint">Minimal 8 karakter.</p>
                </div>

                <div class="mb-4">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-wrap">
                        <input type="password" name="konfirmasi" id="pw_konfirm" class="form-control" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw_konfirm', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-simpan">
                    <i class="bi bi-floppy me-2"></i>Simpan Password
                </button>

            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePw(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
    </script>
</body>

</html>