<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q  = mysqli_query($conn, "SELECT foto FROM karyawan WHERE id=$id");
    if ($d = mysqli_fetch_assoc($q)) {
        $file = "../assets/img/tim/".$d['foto'];
        if ($d['foto'] && file_exists($file)) unlink($file);
    }
    mysqli_query($conn, "DELETE FROM karyawan WHERE id=$id");
    header("Location: tim.php");
    exit;
}

$list = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY urutan ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tim Bengkel - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    body {
        background: #f1f5f9;
        font-family: system-ui, sans-serif;
        font-size: 0.9rem;
    }

    .admin-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .admin-card-header {
        padding: 14px 20px;
        font-weight: 700;
        font-size: 0.9rem;
        color: #0f172a;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .btn-tambah {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #fff;
        background: #16a34a;
        border: none;
        padding: 6px 14px;
        border-radius: 7px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-tambah:hover {
        background: #15803d;
        color: #fff;
    }

    .table th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        color: #334155;
        border-color: #f1f5f9;
        font-size: 0.85rem;
    }

    .table td img {
        border-radius: 6px;
        object-fit: cover;
    }

    .btn-edit {
        font-size: 0.78rem;
        font-weight: 600;
        color: #d97706;
        background: #fef3c7;
        border: none;
        padding: 4px 12px;
        border-radius: 6px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-edit:hover {
        background: #fde68a;
        color: #b45309;
    }

    .btn-hapus {
        font-size: 0.78rem;
        font-weight: 600;
        color: #dc2626;
        background: #fee2e2;
        border: none;
        padding: 4px 12px;
        border-radius: 6px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-hapus:hover {
        background: #fecaca;
        color: #b91c1c;
    }

    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .col-no {
        width: 40px;
        text-align: center;
    }

    .col-urutan {
        width: 70px;
        text-align: center;
    }

    .col-foto {
        width: 90px;
    }

    .col-aksi {
        width: 110px;
    }

    @media (max-width: 575.98px) {

        .table th,
        .table td {
            font-size: 0.78rem;
            padding: 8px 10px;
        }
    }
    </style>
</head>

<body>
    <?php include "navbar-menu.php"; ?>

    <div class="container-fluid px-3 px-md-4 py-4">

        <div class="admin-card">
            <div class="admin-card-header">
                <span>Data Tim / Karyawan</span>
                <a href="proses/tambah-tim.php" class="btn-tambah">
                    <i class="bi bi-plus-lg"></i> Tambah
                </a>
            </div>
            <div class="table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Pengalaman</th>
                            <th class="col-urutan">Urutan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($t = mysqli_fetch_assoc($list)) { ?>
                        <tr>
                            <td class="text-muted"><?php echo $no++; ?></td>
                            <td>
                                <?php if ($t['foto']) { ?>
                                <img src="../assets/img/tim/<?php echo htmlspecialchars($t['foto']); ?>" width="70"
                                    height="70">
                                <?php } else { ?>
                                <span class="text-muted">-</span>
                                <?php } ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($t['nama']); ?></strong></td>
                            <td><?php echo htmlspecialchars($t['jabatan']); ?></td>
                            <td><?php echo (int)$t['pengalaman']; ?> tahun</td>
                            <td class="col-urutan"><?php echo $t['urutan']; ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="proses/edit-tim.php?id=<?php echo $t['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="tim.php?hapus=<?php echo $t['id']; ?>" class="btn-hapus"
                                        onclick="return confirm('Hapus data ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>