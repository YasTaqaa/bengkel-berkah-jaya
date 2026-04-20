<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$dari   = isset($_GET['dari'])   ? $_GET['dari']   : '';
$sampai = isset($_GET['sampai']) ? $_GET['sampai'] : '';

$where = "1=1";
if ($dari)   $where .= " AND DATE(p.tgl_pesan) >= '" . mysqli_real_escape_string($conn, $dari) . "'";
if ($sampai) $where .= " AND DATE(p.tgl_pesan) <= '" . mysqli_real_escape_string($conn, $sampai) . "'";

$list = mysqli_query($conn, "SELECT p.*, l.nama AS nama_layanan
                             FROM proyek p
                             LEFT JOIN layanan l ON p.layanan_id=l.id
                             WHERE $where
                             ORDER BY p.id DESC");

$total_rows = mysqli_num_rows($list);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan - Admin</title>
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
    }

    /* Filter bar */
    .filter-bar {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        padding: 16px 20px;
        margin-bottom: 16px;
    }

    .filter-bar .form-control {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        font-size: 0.85rem;
        height: 38px;
    }

    .filter-bar .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-filter {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0 18px;
        height: 38px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .btn-filter:hover {
        background: #1d4ed8;
    }

    .btn-reset {
        background: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 8px;
        padding: 0 14px;
        height: 38px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .btn-reset:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #16a34a;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0 16px;
        height: 38px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .btn-export:hover {
        background: #15803d;
        color: #fff;
    }

    .result-count {
        font-size: 0.8rem;
        color: #64748b;
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
        background: #ede9fe;
        color: #6d28d9;
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

    .btn-nota {
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border: none;
        padding: 4px 12px;
        border-radius: 6px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-nota:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 575.98px) {

        .table th,
        .table td {
            font-size: 0.78rem;
            padding: 8px 10px;
        }

        .filter-bar .d-flex {
            flex-wrap: wrap;
        }
    }
    </style>
</head>

<body>
    <?php include "navbar-menu.php"; ?>

    <div class="container-fluid px-3 px-md-4 py-4">

        <!-- Filter Bar -->
        <form method="GET" action="">
            <div class="filter-bar">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <label class="fw-semibold text-nowrap" style="font-size:0.83rem; color:#475569;">Filter
                        Tanggal Pesan:</label>
                    <input type="date" name="dari" class="form-control" style="max-width:160px;"
                        value="<?php echo htmlspecialchars($dari); ?>" placeholder="Dari">
                    <span class="text-muted" style="font-size:0.82rem;">s/d</span>
                    <input type="date" name="sampai" class="form-control" style="max-width:160px;"
                        value="<?php echo htmlspecialchars($sampai); ?>" placeholder="Sampai">
                    <button type="submit" class="btn-filter">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <?php if ($dari || $sampai): ?>
                    <a href="riwayat-pesanan.php" class="btn-reset">
                        <i class="bi bi-x me-1"></i> Reset
                    </a>
                    <?php endif; ?>
                    <!-- Export -->
                    <a href="export-pesanan.php?dari=<?php echo urlencode($dari); ?>&sampai=<?php echo urlencode($sampai); ?>"
                        class="btn-export ms-auto">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                </div>
                <?php if ($dari || $sampai): ?>
                <p class="result-count mt-2 mb-0">
                    Menampilkan <strong><?php echo $total_rows; ?></strong> pesanan
                    <?php if ($dari) echo ' dari <strong>' . htmlspecialchars($dari) . '</strong>'; ?>
                    <?php if ($sampai) echo ' sampai <strong>' . htmlspecialchars($sampai) . '</strong>'; ?>
                </p>
                <?php endif; ?>
            </div>
        </form>

        <!-- Tabel -->
        <div class="admin-card">
            <div class="admin-card-header">
                Riwayat Pesanan
                <span class="fw-normal text-muted ms-2" style="font-size:0.8rem;">(<?php echo $total_rows; ?>
                    data)</span>
            </div>
            <div class="table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>HP</th>
                            <th>Layanan</th>
                            <th>Ukuran</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Tgl Pesan</th>
                            <th>Tgl Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($total_rows === 0) {
                            echo '<tr><td colspan="10" class="text-center text-muted py-4">Tidak ada data pesanan.</td></tr>';
                        }
                        $no = 1;
                        while ($p = mysqli_fetch_assoc($list)) {
                            $status = strtolower($p['status']);
                            $badge = match($status) {
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
                                    class="badge-status <?php echo $badge; ?>"><?php echo htmlspecialchars($p['status']); ?></span>
                            </td>
                            <td class="text-muted"><?php echo $p['tgl_pesan']; ?></td>
                            <td class="text-muted"><?php echo $p['tgl_selesai'] ?: '-'; ?></td>
                            <td>
                                <a href="nota.php?id=<?php echo $p['id']; ?>" class="btn-nota" target="_blank">
                                    <i class="bi bi-printer me-1"></i>Nota
                                </a>
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