<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

function linkify(string $text): string {
    $textEscaped = htmlspecialchars($text);
    return preg_replace_callback('/https?:\/\/\S+/i', function ($m) {
        $url = htmlspecialchars($m[0], ENT_QUOTES);
        return '<a href="javascript:void(0)" class="link-preview-gambar" data-img="' . $url . '">Lihat Gambar</a>';
    }, $textEscaped);
}

function ambilReferensiProyek(mysqli $conn, int $proyek_id): array
{
    $referensi = [];

    $stmt = $conn->prepare(
        'SELECT foto
         FROM proyek_referensi
         WHERE proyek_id = ?
         ORDER BY id ASC'
    );

    if (!$stmt) {
        return $referensi;
    }

    $stmt->bind_param('i', $proyek_id);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $referensi[] = $row['foto'];
    }

    $stmt->close();

    return $referensi;
}
$cari = trim($_GET['cari'] ?? '');

$sql = "SELECT p.*, l.nama AS nama_layanan,
               g.judul AS nama_model,
               g.harga AS harga_galeri
        FROM proyek p
        LEFT JOIN layanan l ON p.layanan_id = l.id
        LEFT JOIN galeri g ON p.galeri_id = g.id";

if ($cari !== '') {
    $sql .= " WHERE p.nama LIKE ? OR p.hp LIKE ?";
}

$sql .= " ORDER BY p.status='baru' DESC, p.id DESC";

$stmtList = $conn->prepare($sql);

if ($cari !== '') {
    $keyword = '%' . $cari . '%';
    $stmtList->bind_param('ss', $keyword, $keyword);
}

$stmtList->execute();
$list = $stmtList->get_result();
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
        padding: 6px 14px;
        border-radius: 7px;
        text-decoration: none;
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
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        color: #334155;
        border-color: #f1f5f9;
        font-size: 0.85rem;
    }

    .table .form-control-sm {
        border: 1.5px solid #e2e8f0;
        border-radius: 7px;
        font-size: 0.84rem;
        height: 36px;
    }

    .ukuran-input,
    .harga-input {
        box-sizing: border-box;
        width: 135px !important;
        height: 42px !important;
        min-height: 42px !important;
        margin: 0 !important;
        padding: 8px 12px !important;
        vertical-align: top;
    }

    .ukuran-input,
    .harga-input {
        display: block;
    }

    .harga-hint {
        display: block;
        height: 18px;
        margin-top: 6px;
        line-height: 18px;
        font-size: 0.68rem;
        color: #64748b;
        white-space: nowrap;
    }

    .total-hint {
        height: 20px;
        margin-top: 2px;
        line-height: 20px;
        color: #047857;
        font-size: 0.73rem;
        font-weight: 700;
    }

    .ukuran-input:focus,
    .harga-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .harga-hint {
        display: block;
        min-height: 17px;
        margin-top: 5px;
        color: #64748b;
        font-size: 0.68rem;
        line-height: 1.35;
        white-space: nowrap;
    }

    .total-hint {
        min-height: 18px;
        margin-top: 3px;
        color: #047857;
        font-size: 0.73rem;
        font-weight: 700;
        white-space: nowrap;
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

    .btn-simpan,
    .btn-aksi {
        display: block;
        width: 80px;
        padding: 5px 0;
        border: none;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
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
        display: block;
        margin-top: 3px;
        padding: 0;
        border: none;
        background: none;
        color: #2563eb;
        font-size: 0.7rem;
        cursor: pointer;
    }

    .col-no {
        width: 40px;
        text-align: center;
    }

    .link-preview-gambar {
        cursor: pointer;
    }

    @media (max-width: 575.98px) {

        .table th,
        .table td {
            font-size: 0.78rem;
            padding: 8px;
        }

        .ukuran-input,
        .harga-input {
            width: 125px !important;
        }

        .harga-hint,
        .total-hint {
            white-space: normal;
        }
    }

    .referensi-upload-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .referensi-thumb {
        width: 58px;
        height: 48px;
        padding: 0;
        border: 1px solid #dbe3ef;
        border-radius: 6px;
        overflow: hidden;
        background: #f8fafc;
        cursor: pointer;
    }

    .referensi-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .referensi-thumb:hover {
        border-color: #2563eb;
    }

    .filter-pesanan {
        padding: 12px 20px;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
    }

    .filter-pesanan-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-pesanan-input {
        display: flex;
        align-items: center;
        gap: 8px;
        width: min(360px, 100%);
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 0 11px;
        background: #fff;
    }

    .filter-pesanan-input:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .filter-pesanan-input i {
        color: #94a3b8;
    }

    .filter-pesanan-input input {
        width: 100%;
        height: 36px;
        border: 0;
        outline: 0;
        font-size: 0.84rem;
        color: #334155;
        background: transparent;
    }

    .btn-cari-pesanan,
    .btn-reset-cari {
        height: 36px;
        padding: 0 14px;
        border-radius: 7px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-cari-pesanan {
        border: 0;
        color: #fff;
        background: #2563eb;
    }

    .btn-cari-pesanan:hover {
        background: #1d4ed8;
    }

    .btn-reset-cari {
        display: inline-flex;
        align-items: center;
        color: #475569;
        background: #f1f5f9;
    }

    .btn-reset-cari:hover {
        color: #1e293b;
        background: #e2e8f0;
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

            <form method="get" action="proyek.php" class="filter-pesanan">
                <div class="filter-pesanan-wrap">
                    <div class="filter-pesanan-input">
                        <i class="bi bi-search"></i>

                        <input type="search" name="cari"
                            value="<?php echo htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Cari nama pelanggan atau nomor HP..." autocomplete="off">
                    </div>

                    <button type="submit" class="btn-cari-pesanan">
                        Cari
                    </button>

                    <?php if ($cari !== ''): ?>
                    <a href="proyek.php" class="btn-reset-cari">
                        Reset
                    </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Nama</th>
                            <th>HP</th>
                            <th>Layanan</th>
                            <th>Model</th>
                            <th>Lokasi</th>
                            <th>Catatan</th>
                            <th>Ukuran</th>
                            <th>Harga / m²</th>
                            <th>Status</th>
                            <th>Tgl Pesan</th>
                            <th>Tgl Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($p = mysqli_fetch_assoc($list)) {
                            $referensi = ambilReferensiProyek($conn, $p['id']);
                            $status = strtolower($p['status']);
                            $badge = match ($status) {
                                'baru' => 'badge-baru',
                                'proses' => 'badge-proses',
                                'selesai' => 'badge-selesai',
                                'batal' => 'badge-batal',
                                default => 'badge-default'
                            };

                            // Harga proyek yang sudah tersimpan dipertahankan.
                            // Jika masih 0, isi otomatis hanya dari model galeri yang dipilih.
                            $hargaTampil = '';
                            if ((int)$p['harga'] > 0) {
                                $hargaTampil = (int)$p['harga'];
                            } elseif (!empty($p['galeri_id']) && $p['harga_galeri'] !== null && (int)$p['harga_galeri'] > 0) {
                                $hargaTampil = (int)$p['harga_galeri'];
                            }
                        ?>
                        <tr>
                            <form method="post" action="proses/update-proyek.php">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">

                                <td class="text-muted"><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($p['nama']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['hp']); ?></td>
                                <td><?php echo htmlspecialchars($p['nama_layanan'] ?? '-'); ?></td>
                                <td class="text-muted">
                                    <?php echo $p['nama_model'] ? htmlspecialchars($p['nama_model']) : '-'; ?>
                                </td>
                                <td class="cell-expand">
                                    <div class="cell-text" id="lok-<?php echo $p['id']; ?>">
                                        <?php echo htmlspecialchars($p['lokasi']); ?>
                                    </div>
                                    <?php if (strlen($p['lokasi']) > 40) { ?>
                                    <button type="button" class="btn-expand"
                                        onclick="toggleExpand('lok-<?php echo $p['id']; ?>', this)">Lihat semua</button>
                                    <?php } ?>
                                </td>
                                <td class="cell-expand">
                                    <div class="cell-text" id="cat-<?php echo $p['id']; ?>">
                                        <?php echo linkify($p['catatan']); ?>
                                    </div>
                                    <?php if (strlen($p['catatan']) > 40) { ?>
                                    <button type="button" class="btn-expand"
                                        onclick="toggleExpand('cat-<?php echo $p['id']; ?>', this)">Lihat semua</button>
                                    <?php } ?>
                                    <?php if (!empty($referensi)): ?>
                                    <div class="referensi-upload-grid">
                                        <?php foreach ($referensi as $foto): ?>
                                        <?php $srcFoto = '../' . ltrim($foto, '/'); ?>

                                        <button type="button" class="referensi-thumb"
                                            data-img="<?php echo htmlspecialchars($srcFoto, ENT_QUOTES, 'UTF-8'); ?>"
                                            title="Lihat gambar referensi">
                                            <img src="<?php echo htmlspecialchars($srcFoto, ENT_QUOTES, 'UTF-8'); ?>"
                                                alt="Gambar referensi pelanggan">
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="text" name="ukuran" class="form-control form-control-sm ukuran-input"
                                        data-harga-input="harga-<?php echo $p['id']; ?>"
                                        value="<?php echo htmlspecialchars($p['ukuran']); ?>" placeholder="3 x 1.5">
                                    <small class="harga-hint">contoh: 3 x 1.5 m</small>
                                    <small class="harga-hint">contoh: 3x1.5</small>
                                </td>
                                <td>
                                    <input type="number" name="harga" id="harga-<?php echo $p['id']; ?>"
                                        class="form-control form-control-sm harga-input"
                                        value="<?php echo htmlspecialchars($hargaTampil); ?>" min="0"
                                        placeholder="Isi harga">
                                    <small class="harga-hint">otomatis jika model punya harga</small>
                                    <small class="harga-hint total-hint" id="total-<?php echo $p['id']; ?>"></small>
                                </td>
                                <td>
                                    <span class="badge-status <?php echo $badge; ?>">
                                        <?php echo htmlspecialchars($p['status']); ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($p['tgl_pesan']); ?></td>
                                <td class="text-muted">
                                    <?php
    $tglSelesai = $p['tgl_selesai'] ?? null;

    echo (!empty($tglSelesai) && $tglSelesai !== '0000-00-00 00:00:00')
        ? htmlspecialchars($tglSelesai)
        : '-';
    ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1" style="width:80px">
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

    <div class="modal fade" id="previewGambarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background:transparent;border:none;box-shadow:none">
                <div class="modal-body p-0 text-center position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3"
                        data-bs-dismiss="modal" aria-label="Close"
                        style="background-color:rgba(0,0,0,.6);border-radius:50%;opacity:1;padding:10px;width:34px;height:34px"></button>

                    <img id="previewGambarImg" src="" alt="Preview gambar"
                        style="width:100%;max-height:80vh;object-fit:contain;border-radius:12px;background:#000">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function toggleExpand(id, btn) {
        const el = document.getElementById(id);
        const expanded = el.classList.toggle('expanded');

        btn.textContent = expanded ?
            'Sembunyikan' :
            'Lihat semua';
    }

    function parseUkuran(value) {
        const match = String(value)
            .toLowerCase()
            .replace(',', '.')
            .match(/^\s*(\d+(?:\.\d+)?)\s*[x×*]\s*(\d+(?:\.\d+)?)/);

        return match ?
            parseFloat(match[1]) * parseFloat(match[2]) :
            null;
    }

    function hitungTotal(inputUkuran) {
        const luas = parseUkuran(inputUkuran.value);

        const hargaInput = document.getElementById(
            inputUkuran.dataset.hargaInput
        );

        const totalHint = document.getElementById(
            'total-' +
            inputUkuran.dataset.hargaInput.replace('harga-', '')
        );

        const harga = hargaInput ?
            parseFloat(hargaInput.value) || 0 :
            0;

        if (luas && harga > 0) {
            totalHint.textContent =
                luas.toLocaleString('id-ID') +
                ' m² = Rp ' +
                (luas * harga).toLocaleString('id-ID');
        } else {
            totalHint.textContent = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Hitung total harga dari ukuran × harga per m²
        document.querySelectorAll('.ukuran-input').forEach(function(input) {
            input.addEventListener('input', function() {
                hitungTotal(input);
            });

            hitungTotal(input);
        });

        document.querySelectorAll('.harga-input').forEach(function(input) {
            input.addEventListener('input', function() {
                const ukuran = document.querySelector(
                    '[data-harga-input="' + input.id + '"]'
                );

                if (ukuran) {
                    hitungTotal(ukuran);
                }
            });
        });

        // Modal dipakai untuk link gambar pada catatan
        // sekaligus thumbnail foto upload pelanggan.
        const modalElement = document.getElementById('previewGambarModal');
        const modalImage = document.getElementById('previewGambarImg');

        if (!modalElement || !modalImage || !window.bootstrap) {
            return;
        }

        const modal = new bootstrap.Modal(modalElement);

        document.body.addEventListener('click', function(event) {
            const trigger = event.target.closest(
                '.link-preview-gambar, .referensi-thumb'
            );

            if (!trigger) {
                return;
            }

            event.preventDefault();

            modalImage.src = trigger.getAttribute('data-img') || '';
            modal.show();
        });

        modalElement.addEventListener('hidden.bs.modal', function() {
            modalImage.removeAttribute('src');
        });
    });
    </script>
</body>

</html>