<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$tot     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM proyek"));
$baru    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM proyek WHERE status='baru'"));
$selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM proyek WHERE status='selesai'"));
$pend    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(harga),0) AS total FROM proyek WHERE status='selesai'"));

$proses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM proyek WHERE status='proses'"));

$riwayat = mysqli_query($conn, "SELECT p.*, l.nama AS nama_layanan
                                FROM proyek p
                                LEFT JOIN layanan l ON p.layanan_id=l.id
                                ORDER BY p.id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin Berkah Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    body {
        background: #f1f5f9;
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 0.9rem;
    }

    /* ---- STAT CARDS ---- */
    .stat-card {
        border: none;
        border-radius: 14px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .stat-card .stat-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
        white-space: nowrap;
    }

    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
    }

    /* Mobile: stack vertikal, lebih compact */
    @media (max-width: 575.98px) {
        .stat-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            padding: 14px;
        }

        .stat-card .stat-label {
            white-space: normal;
            font-size: 0.68rem;
        }

        .stat-card .stat-value {
            font-size: 1.4rem;
        }

        .stat-dark .stat-value {
            font-size: 0.95rem;
        }
    }

    /* Warna stat */
    .stat-blue {
        background: #eff6ff;
    }

    .stat-blue .stat-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-blue .stat-label {
        color: #3b82f6;
    }

    .stat-blue .stat-value {
        color: #1d4ed8;
    }

    .stat-amber {
        background: #fffbeb;
    }

    .stat-amber .stat-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .stat-amber .stat-label {
        color: #f59e0b;
    }

    .stat-amber .stat-value {
        color: #b45309;
    }

    .stat-green {
        background: #f0fdf4;
    }

    .stat-green .stat-icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .stat-green .stat-label {
        color: #22c55e;
    }

    .stat-green .stat-value {
        color: #15803d;
    }

    .stat-dark {
        background: #f8fafc;
    }

    .stat-blue2 {
        background: #f5f3ff;
    }

    .stat-blue2 .stat-icon {
        background: #ede9fe;
        color: #7c3aed;
    }

    .stat-blue2 .stat-label {
        color: #8b5cf6;
    }

    .stat-blue2 .stat-value {
        color: #6d28d9;
    }

    .stat-dark .stat-icon {
        background: #e2e8f0;
        color: #475569;
    }

    .stat-dark .stat-label {
        color: #64748b;
    }

    .stat-dark .stat-value {
        color: #1e293b;
        font-size: 1.1rem;
    }

    /* ---- TABLE ---- */
    .admin-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: none;
        overflow: hidden;
    }

    .admin-card-header {
        padding: 16px 20px;
        font-weight: 700;
        font-size: 0.9rem;
        color: #0f172a;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table th {
        font-size: 0.78rem;
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

    /* Status badge */
    .badge-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: capitalize;
    }

    .badge-baru {
        background: #fef3c7;
        color: #b45309;
    }

    .badge-proses {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge-selesai {
        background: #dcfce7;
        color: #15803d;
    }

    .badge-batal {
        background: #fee2e2;
        color: #b91c1c;
    }

    .badge-default {
        background: #f1f5f9;
        color: #64748b;
    }

    .col-no {
        width: 40px;
        text-align: center;
    }

    /* Mobile responsive table scroll */
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Mobile padding */
    @media (max-width: 575.98px) {
        .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .admin-card-header {
            padding: 12px 14px;
            font-size: 0.85rem;
        }

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

        <!-- Greeting -->
        <div class="mb-4">
            <h5 class="fw-bold mb-1" style="color:#0f172a;">
                Dashboard
            </h5>
            <small class="text-muted">Halo, <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong> —
                <?php echo date('l, d F Y'); ?></small>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md col-sm-6">
                <div class="stat-card stat-blue">
                    <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                    <div>
                        <div class="stat-label">Total Pesanan</div>
                        <div class="stat-value"><?php echo (int)$tot['jml']; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md col-sm-6">
                <div class="stat-card stat-amber">
                    <div class="stat-icon"><i class="bi bi-clock"></i></div>
                    <div>
                        <div class="stat-label">Pesanan Baru</div>
                        <div class="stat-value"><?php echo (int)$baru['jml']; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md col-sm-6">
                <div class="stat-card stat-green">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="stat-label">Selesai</div>
                        <div class="stat-value"><?php echo (int)$selesai['jml']; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md col-sm-6">
                <div class="stat-card stat-blue2">
                    <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
                    <div>
                        <div class="stat-label">Proses</div>
                        <div class="stat-value"><?php echo (int)$proses['jml']; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md col-sm-12">
                <div class="stat-card stat-dark">
                    <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="stat-label">Pendapatan</div>
                        <div class="stat-value">Rp <?php echo number_format($pend['total'],0,',','.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat -->
        <div class="admin-card">
            <div class="admin-card-header">
                <i class="bi bi-list-ul text-primary"></i>
                Riwayat Pesanan Terbaru
            </div>
            <div class="table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Nama</th>
                            <th>HP</th>
                            <th>Layanan</th>
                            <th>Ukuran</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Tgl Pesan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($p = mysqli_fetch_assoc($riwayat)) {
                            $status = strtolower($p['status']);
                            $badge_class = match($status) {
                                'baru'    => 'badge-baru',
                                'proses'  => 'badge-proses',
                                'selesai' => 'badge-selesai',
                                'batal'   => 'badge-batal',
                                default   => 'badge-default'
                            };
                        ?>
                        <tr>
                            <td class="text-muted"><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($p['nama']); ?></strong></td>
                            <td><?php echo htmlspecialchars($p['hp']); ?></td>
                            <td><?php echo htmlspecialchars($p['nama_layanan']); ?></td>
                            <td><?php echo htmlspecialchars($p['ukuran']) ?: '-'; ?></td>
                            <td>Rp <?php echo number_format($p['harga'],0,',','.'); ?></td>
                            <td><span
                                    class="badge-status <?php echo $badge_class; ?>"><?php echo htmlspecialchars($p['status']); ?></span>
                            </td>
                            <td class="text-muted"><?php echo $p['tgl_pesan']; ?></td>
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