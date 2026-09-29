<?php
require_once __DIR__ . "/../config.php";

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: login.php");
    exit;
}

$cari = trim($_GET['cari'] ?? '');

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    if ($id > 0) {
        $fotoGaleri = [];

        $stmtUtama = $conn->prepare("SELECT foto FROM galeri WHERE id = ?");
        $stmtUtama->bind_param('i', $id);
        $stmtUtama->execute();
        $resUtama = $stmtUtama->get_result();

        if ($d = $resUtama->fetch_assoc()) {
            if (!empty($d['foto'])) {
                $fotoGaleri[] = $d['foto'];
            }
        }
        $stmtUtama->close();

        $stmtFoto = $conn->prepare("SELECT foto FROM galeri_foto WHERE galeri_id = ?");
        $stmtFoto->bind_param('i', $id);
        $stmtFoto->execute();
        $resFoto = $stmtFoto->get_result();

        while ($row = $resFoto->fetch_assoc()) {
            if (!empty($row['foto'])) {
                $fotoGaleri[] = $row['foto'];
            }
        }
        $stmtFoto->close();

        foreach (array_unique($fotoGaleri) as $foto) {
            $foto = ltrim(str_replace('\\', '/', $foto), '/');
            $foto = preg_replace('#^assets/img/galeri/#i', '', $foto);
            $path = __DIR__ . '/../assets/img/galeri/' . $foto;

            if (is_file($path)) {
                unlink($path);
            }
        }

        $stmtHapusFoto = $conn->prepare("DELETE FROM galeri_foto WHERE galeri_id = ?");
        $stmtHapusFoto->bind_param('i', $id);
        $stmtHapusFoto->execute();
        $stmtHapusFoto->close();

        $stmtHapusGaleri = $conn->prepare("DELETE FROM galeri WHERE id = ?");
        $stmtHapusGaleri->bind_param('i', $id);
        $stmtHapusGaleri->execute();
        $stmtHapusGaleri->close();
    }

    header("Location: galeri.php");
    exit;
}

$sql = "SELECT g.*, l.nama AS nama_layanan
        FROM galeri g
        LEFT JOIN layanan l ON g.layanan_id = l.id";

if ($cari !== '') {
    $sql .= " WHERE g.judul LIKE ?";
}

$sql .= " ORDER BY g.tgl_selesai DESC, g.id DESC";

$stmtList = $conn->prepare($sql);

if ($cari !== '') {
    $keyword = '%' . $cari . '%';
    $stmtList->bind_param('s', $keyword);
}

$stmtList->execute();
$list = $stmtList->get_result();

$fotoTambahan = [];

$stmtSemuaFoto = $conn->prepare(
    "SELECT galeri_id, foto
     FROM galeri_foto
     ORDER BY urutan ASC, id ASC"
);

$stmtSemuaFoto->execute();
$resSemuaFoto = $stmtSemuaFoto->get_result();

while ($fotoRow = $resSemuaFoto->fetch_assoc()) {
    $fotoTambahan[(int) $fotoRow['galeri_id']][] = $fotoRow['foto'];
}

$stmtSemuaFoto->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Galeri Proyek - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    body {
        background: #f1f5f9;
        font-family: system-ui, sans-serif;
        font-size: .9rem;
    }

    .admin-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
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
        font-size: .82rem;
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
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        color: #334155;
        border-color: #f1f5f9;
        font-size: .85rem;
    }

    .table td img {
        border-radius: 6px;
        object-fit: cover;
    }

    .foto-thumbnail {
        width: 70px;
        height: 50px;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer;
    }

    .foto-thumbnail img {
        width: 70px;
        height: 50px;
        border-radius: 6px;
        object-fit: cover;
    }

    .foto-thumbnail:hover img {
        opacity: .75;
    }

    .harga-galeri {
        color: #1d4ed8;
        font-weight: 700;
        white-space: nowrap;
    }

    .btn-edit,
    .btn-hapus {
        font-size: .78rem;
        font-weight: 600;
        border: 0;
        padding: 4px 12px;
        border-radius: 6px;
        text-decoration: none;
    }

    .btn-edit {
        color: #d97706;
        background: #fef3c7;
    }

    .btn-hapus {
        color: #dc2626;
        background: #fee2e2;
    }

    .btn-edit:hover {
        background: #fde68a;
        color: #b45309;
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

    .filter-galeri {
        padding: 12px 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .filter-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-input {
        display: flex;
        align-items: center;
        gap: 8px;
        width: min(320px, 100%);
        height: 36px;
        padding: 0 10px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
    }

    .filter-input:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
    }

    .filter-input i {
        color: #94a3b8;
    }

    .filter-input input {
        width: 100%;
        height: 32px;
        border: 0;
        outline: 0;
        font-size: .84rem;
    }

    .btn-cari,
    .btn-reset {
        height: 36px;
        padding: 0 14px;
        border-radius: 7px;
        font-size: .8rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        border: 0;
    }

    .btn-cari {
        color: #fff;
        background: #2563eb;
    }

    .btn-reset {
        color: #475569;
        background: #f1f5f9;
    }

    @media (max-width:575.98px) {

        .table th,
        .table td {
            font-size: .78rem;
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
                <span>Galeri Proyek</span>
                <a href="proses/tambah-galeri.php" class="btn-tambah"><i class="bi bi-plus-lg"></i> Tambah</a>
            </div>

            <form method="get" action="galeri.php" class="filter-galeri">
                <div class="filter-wrap">
                    <div class="filter-input">
                        <i class="bi bi-search"></i>
                        <input type="search" name="cari" value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Cari judul galeri..." autocomplete="off">
                    </div>
                    <button type="submit" class="btn-cari">Cari</button>
                    <?php if ($cari !== ''): ?><a href="galeri.php" class="btn-reset">Reset</a><?php endif; ?>
                </div>
            </form>

            <div class="table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Foto</th>
                            <th>Judul</th>
                            <th>Layanan</th>
                            <th>Harga / m²</th>
                            <th>Keterangan</th>
                            <th>Tgl Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while ($g = $list->fetch_assoc()): ?>
                        <?php
$idGaleri = (int) $g['id'];
$daftarFoto = [];

/* Foto utama dari tabel galeri */
if (!empty($g['foto'])) {
    $daftarFoto[] = $g['foto'];
}

/* Foto tambahan dari tabel galeri_foto */
if (!empty($fotoTambahan[$idGaleri])) {
    $daftarFoto = array_merge($daftarFoto, $fotoTambahan[$idGaleri]);
}

/* Hilangkan data kosong dan foto yang dobel */
$daftarFoto = array_values(array_unique(array_filter($daftarFoto)));

/* Ubah nama file menjadi path URL untuk browser */
$urlFoto = array_map(function ($foto) {
    $namaFile = basename(str_replace('\\', '/', $foto));
    return '../assets/img/galeri/' . rawurlencode($namaFile);
}, $daftarFoto);

$fotoUtama = $urlFoto[0] ?? '';
$jsonFoto = json_encode($urlFoto, JSON_UNESCAPED_SLASHES);
?>

                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td>
                                <?php if ($fotoUtama !== ''): ?>
                                <button type="button" class="foto-thumbnail"
                                    data-fotos="<?= htmlspecialchars($jsonFoto, ENT_QUOTES, 'UTF-8') ?>"
                                    data-title="<?= htmlspecialchars($g['judul'], ENT_QUOTES, 'UTF-8') ?>"
                                    title="Klik untuk melihat <?= count($urlFoto) ?> foto">

                                    <img src="<?= htmlspecialchars($fotoUtama, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($g['judul'], ENT_QUOTES, 'UTF-8') ?>">
                                </button>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($g['judul']) ?></strong></td>
                            <td><?= htmlspecialchars($g['nama_layanan'] ?? '-') ?></td>
                            <td><?php if ((float)$g['harga'] > 0): ?><span class="harga-galeri">Rp
                                    <?= number_format((float)$g['harga'],0,',','.') ?> /m²</span><?php else: ?><span
                                    class="text-muted">-</span><?php endif; ?></td>
                            <td class="text-muted" style="max-width:200px;">
                                <?= htmlspecialchars(mb_strimwidth($g['keterangan'],0,60,'...')) ?></td>
                            <td><?= htmlspecialchars($g['tgl_selesai']) ?></td>
                            <td>
                                <div class="d-flex gap-1"><a href="proses/edit-galeri.php?id=<?= (int)$g['id'] ?>"
                                        class="btn-edit">Edit</a><a href="galeri.php?hapus=<?= (int)$g['id'] ?>"
                                        class="btn-hapus"
                                        onclick="return confirm('Hapus data ini beserta semua foto tambahannya?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="adminFotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h5 id="adminFotoTitle" class="modal-title text-white"></h5><button type="button"
                        class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="adminFotoPreview" src="" alt="" class="img-fluid"
                        style="max-height:65vh; object-fit:contain;">

                    <div id="adminFotoThumbs" class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('adminFotoModal');
        const preview = document.getElementById('adminFotoPreview');
        const title = document.getElementById('adminFotoTitle');
        const thumbs = document.getElementById('adminFotoThumbs');

        if (!modalEl || !preview || !title || !thumbs || !window.bootstrap) {
            return;
        }

        const modal = new bootstrap.Modal(modalEl);

        function pilihFoto(url, tombolAktif) {
            preview.src = url;

            thumbs.querySelectorAll('.modal-foto-thumb').forEach(function(button) {
                button.classList.remove('border-primary', 'border-3');
                button.classList.add('border-transparent');
            });

            if (tombolAktif) {
                tombolAktif.classList.remove('border-transparent');
                tombolAktif.classList.add('border-primary', 'border-3');
            }
        }

        document.querySelectorAll('.foto-thumbnail').forEach(function(button) {
            button.addEventListener('click', function() {
                let fotoList = [];

                try {
                    fotoList = JSON.parse(button.dataset.fotos || '[]');
                } catch (error) {
                    fotoList = [];
                }

                title.textContent = button.dataset.title || 'Preview gambar';
                thumbs.innerHTML = '';

                if (!fotoList.length) {
                    preview.removeAttribute('src');
                    modal.show();
                    return;
                }

                fotoList.forEach(function(url, index) {
                    const thumbButton = document.createElement('button');
                    const thumbImage = document.createElement('img');

                    thumbButton.type = 'button';
                    thumbButton.className =
                        'modal-foto-thumb border border-3 border-transparent rounded p-0 bg-transparent';

                    thumbImage.src = url;
                    thumbImage.alt = 'Foto ' + (index + 1);
                    thumbImage.style.width = '72px';
                    thumbImage.style.height = '54px';
                    thumbImage.style.objectFit = 'cover';
                    thumbImage.style.borderRadius = '4px';

                    thumbButton.appendChild(thumbImage);

                    thumbButton.addEventListener('click', function() {
                        pilihFoto(url, thumbButton);
                    });

                    thumbs.appendChild(thumbButton);

                    if (index === 0) {
                        pilihFoto(url, thumbButton);
                    }
                });

                modal.show();
            });
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            preview.removeAttribute('src');
            thumbs.innerHTML = '';
        });
    });
    </script>
</body>

</html>