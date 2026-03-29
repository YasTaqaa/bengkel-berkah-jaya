<?php
require_once __DIR__ . "/../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$list = mysqli_query($conn, "SELECT p.*, l.nama AS nama_layanan
                             FROM proyek p
                             LEFT JOIN layanan l ON p.layanan_id=l.id
                             ORDER BY p.status='baru' DESC, p.id DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pesanan - Admin</title>
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

    /* Input di dalam tabel */
    .table .form-control-sm {
        font-size: 0.82rem;
        border-radius: 6px;
        border: 1.5px solid #e2e8f0;
        min-width: 90px;
    }

    .table .form-control-sm:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
    }

    /* Badge status */
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

    /* Tombol aksi */
    .btn-simpan,
    .btn-aksi {
        font-size: 0.78rem;
        font-weight: 600;
        border: none;
        padding: 5px 0;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        display: block;
        width: 80px;
        text-align: center;
        transition: background 0.2s;
    }

    .btn-simpan {
        color: #fff;
        background: #2563eb;
    }

    .btn-simpan:hover {
        background: #1d4ed8;
    }

    .btn-aksi.baru {
        color: #475569;
        background: #f1f5f9;
    }

    .btn-aksi.proses {
        color: #6d28d9;
        background: #ede9fe;
    }

    .btn-aksi.selesai {
        color: #15803d;
        background: #dcfce7;
    }

    .btn-aksi.hapus {
        color: #dc2626;
        background: #fee2e2;
    }

    .btn-aksi.baru:hover {
        background: #e2e8f0;
    }

    .btn-aksi.proses:hover {
        background: #ddd6fe;
    }

    .btn-aksi.selesai:hover {
        background: #bbf7d0;
    }

    .btn-aksi.hapus:hover {
        background: #fecaca;
    }

    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .cell-expand {
        max-width: 160px;
    }

    .cell-text {
        max-height: 1.4em;
        overflow: hidden;
        font-size: 0.83rem;
        line-height: 1.4em;
        word-break: break-word;
    }

    .cell-text.expanded {
        max-height: none;
        overflow: visible;
        white-space: pre-wrap;
    }

    .btn-expand {
        font-size: 0.7rem;
        color: #2563eb;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        margin-top: 3px;
        display: block;
    }

    .btn-expand:hover {
        text-decoration: underline;
    }

    .col-no {
        width: 40px;
        text-align: center;
    }

    @media (max-width: 575.98px) {

        .table th,
        .table td {
            font-size: 0.78rem;
            padding: 8px 8px;
        }
    }
    </style>
</head>

<body>
    <?php include "navbar-menu.php"; ?>

    <div class="container-fluid px-3 px-md-4 py-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <span>Data Pesanan</span>
                <a href="proses/tambah-proyek.php" class="btn-tambah">
                    <i class="bi bi-plus-lg"></i> Tambah
                </a>
            </div>
            <div class="table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Nama</th>
                            <th>HP</th>
                            <th>Layanan</th>
                            <th>Lokasi</th>
                            <th>Catatan</th>
                            <th>Ukuran</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Tgl Pesan</th>
                            <th>Tgl Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($p = mysqli_fetch_assoc($list)) {
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
                            <form method="post" action="proses/update-proyek.php">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <td class="text-muted"><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($p['nama']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['hp']); ?></td>
                                <td><?php echo htmlspecialchars($p['nama_layanan']); ?></td>
                                <td class="cell-expand">
                                    <div class="cell-text" id="lok-<?php echo $p['id']; ?>">
                                        <?php echo htmlspecialchars($p['lokasi']) ?: '-'; ?>
                                    </div>
                                    <?php if (strlen($p['lokasi']) > 40) { ?>
                                    <button type="button" class="btn-expand"
                                        onclick="toggleExpand('lok-<?php echo $p['id']; ?>', this)">
                                        Lihat semua
                                    </button>
                                    <?php } ?>
                                </td>
                                <td class="cell-expand">
                                    <div class="cell-text" id="cat-<?php echo $p['id']; ?>">
                                        <?php echo htmlspecialchars($p['catatan']) ?: '-'; ?>
                                    </div>
                                    <?php if (strlen($p['catatan']) > 40) { ?>
                                    <button type="button" class="btn-expand"
                                        onclick="toggleExpand('cat-<?php echo $p['id']; ?>', this)">
                                        Lihat semua
                                    </button>
                                    <?php } ?>
                                </td>
                                <td>
                                    <input type="text" name="ukuran"
                                        value="<?php echo htmlspecialchars($p['ukuran']); ?>"
                                        class="form-control form-control-sm" style="width:90px;">
                                </td>
                                <td>
                                    <input type="number" name="harga" value="<?php echo (float)$p['harga']; ?>"
                                        class="form-control form-control-sm" style="width:110px;">
                                </td>
                                <td>
                                    <span class="badge-status <?php echo $badge; ?>">
                                        <?php echo htmlspecialchars($p['status']); ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo $p['tgl_pesan']; ?></td>
                                <td class="text-muted"><?php echo $p['tgl_selesai'] ?: '-'; ?></td>
                                <td>
                                    <div class="d-flex flex-column gap-1" style="width:80px;">
                                        <button type="submit" class="btn-simpan">
                                            <i class="bi bi-floppy me-1"></i>Simpan
                                        </button>
                                        <a href="proses/update-proyek.php?aksi=baru&id=<?php echo $p['id']; ?>"
                                            class="btn-aksi baru">Baru</a>
                                        <a href="proses/update-proyek.php?aksi=proses&id=<?php echo $p['id']; ?>"
                                            class="btn-aksi proses">Proses</a>
                                        <a href="proses/update-proyek.php?aksi=selesai&id=<?php echo $p['id']; ?>"
                                            class="btn-aksi selesai"
                                            onclick="return confirm('Yakin pesanan ini sudah selesai?')">Selesai</a>
                                        <a href="proses/hapus-proyek.php?id=<?php echo $p['id']; ?>"
                                            class="btn-aksi hapus"
                                            onclick="return confirm('Yakin hapus pesanan ini?')">Hapus</a>
                                    </div>
                                </td>
                            </form>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleExpand(id, btn) {
        const el = document.getElementById(id);
        const expanded = el.classList.toggle('expanded');
        btn.textContent = expanded ? 'Sembunyikan' : 'Lihat semua';
    }
    </script>
</body>

</html>