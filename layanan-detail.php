<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/config.php";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$qLayanan = mysqli_query($conn, "SELECT * FROM layanan WHERE id=$id LIMIT 1");
$layanan = mysqli_fetch_assoc($qLayanan);
if (!$layanan) {
    header("Location: layanan.php");
    exit;
}

$qGaleri = mysqli_query($conn, "SELECT * FROM galeri WHERE layanan_id=$id ORDER BY tgl_selesai DESC, id DESC");

// Ambil data estimasi harga dari database (diisi lewat form tambah/edit layanan)
// Dibungkus try supaya kalau tabel/kolom belum sesuai, halaman TIDAK ikut mati blank
$qEstimasi = @mysqli_query($conn, "SELECT * FROM layanan_estimasi WHERE layanan_id=$id ORDER BY urutan ASC, id ASC");
$estimasiError = ($qEstimasi === false) ? mysqli_error($conn) : null;

// Deskripsi ditulis di admin dengan format baris seperti ini:
// baris pertama = ringkasan, baris berikutnya diawali "-" = poin spesifikasi
$deskripsiLines = preg_split('/\r\n|\r|\n/', trim($layanan['deskripsi']));
$ringkasan = '';
$spesifikasi = [];
foreach ($deskripsiLines as $line) {
    $line = trim($line);
    if ($line === '') continue;
    if (strpos($line, '-') === 0) {
        $spesifikasi[] = trim(substr($line, 1));
    } elseif ($ringkasan === '') {
        $ringkasan = $line;
    } else {
        $ringkasan .= ' ' . $line;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($layanan['nama']); ?> - Bengkel Las Berkah Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="no-hero">
    <?php include "navbar.php"; ?>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <div class="page-header-inner">
                <span class="section-label-light">Detail Layanan</span>
                <h1 class="page-header-title"><?php echo htmlspecialchars($layanan['nama']); ?></h1>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <a href="layanan.php" class="btn-back mb-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Layanan
            </a>

            <?php if ($estimasiError) { ?>
            <div class="alert alert-danger" style="font-size:0.85rem">
                <strong>Debug info (hapus blok ini setelah masalah selesai):</strong><br>
                Query estimasi gagal: <?php echo htmlspecialchars($estimasiError); ?>
            </div>
            <?php } ?>

            <!-- Detail Layanan -->
            <div class="row g-4 align-items-start mb-5 reveal">
                <div class="col-md-5">
                    <img src="assets/img/layanan/<?php echo htmlspecialchars($layanan['foto']); ?>"
                        class="img-fluid rounded-3 shadow" alt="<?php echo htmlspecialchars($layanan['nama']); ?>">
                </div>
                <div class="col-md-7">
                    <div class="detail-card">
                        <h2 class="detail-title"><?php echo htmlspecialchars($layanan['nama']); ?></h2>
                        <div class="detail-harga">
                            <i class="bi bi-tag-fill me-2"></i>
                            Mulai Rp <?php echo number_format($layanan['harga'], 0, ',', '.'); ?>
                            <span class="detail-harga-satuan">/m<sup>2</sup></span>
                        </div>

                        <?php if ($ringkasan) { ?>
                        <p class="detail-desc"><?php echo htmlspecialchars($ringkasan); ?></p>
                        <?php } ?>

                        <?php if (!empty($spesifikasi)) { ?>
                        <ul class="detail-spek-list">
                            <?php foreach ($spesifikasi as $poin) { ?>
                            <li><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($poin); ?></li>
                            <?php } ?>
                        </ul>
                        <?php } ?>

                        <div class="detail-note">
                            <i class="bi bi-info-circle me-2"></i>
                            Harga final tergantung model, ukuran, dan tingkat kesulitan pemasangan. Silakan pesan
                            untuk survey dan penawaran harga pasti secara gratis.
                        </div>

                        <a href="pesan.php?layanan_id=<?php echo $layanan['id']; ?>" class="btn-hero-primary mt-3">
                            <i class="bi bi-calendar2-check"></i> Pesan Layanan Ini
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estimasi Harga (diisi lewat form Tambah/Edit Layanan) -->
            <div class="reveal mb-5">
                <div class="text-center mb-4">
                    <span class="section-label">Kisaran Biaya</span>
                    <h3 class="section-title mb-0">Estimasi Harga <?php echo htmlspecialchars($layanan['nama']); ?>
                    </h3>
                </div>
                <div class="estimasi-card">
                    <div class="table-responsive">
                        <table class="table estimasi-table mb-0">
                            <thead>
                                <tr>
                                    <th>Ukuran / Model</th>
                                    <th>Estimasi Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($qEstimasi && mysqli_num_rows($qEstimasi) > 0) { ?>
                                <?php while ($e = mysqli_fetch_assoc($qEstimasi)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($e['ukuran_model']); ?></td>
                                    <td>Rp <?php echo number_format($e['estimasi_harga'], 0, ',', '.'); ?>
                                        <sup>/m2</sup>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php } else { ?>
                                <tr>
                                    <td>Model standar, ukuran kecil</td>
                                    <td>Mulai Rp <?php echo number_format($layanan['harga'], 0, ',', '.'); ?>
                                        <sup>/m2</sup>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="estimasi-footnote">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Estimasi di atas bersifat umum. Harga pasti akan disampaikan setelah tim kami melakukan
                        survey lokasi secara gratis.
                    </p>
                </div>
            </div>

            <!-- Galeri Layanan -->
            <div class="reveal">
                <div class="text-center mb-4">
                    <span class="section-label">Hasil Pengerjaan</span>
                    <h3 class="section-title mb-0">Galeri <?php echo htmlspecialchars($layanan['nama']); ?></h3>
                </div>

                <?php if (mysqli_num_rows($qGaleri) == 0) { ?>
                <p class="text-center text-muted py-4">Belum ada galeri untuk layanan ini.</p>
                <?php } else { ?>
                <div class="row g-4">
                    <?php while ($g = mysqli_fetch_assoc($qGaleri)) { ?>
                    <div class="col-md-4 reveal">
                        <div class="card galeri-card h-100">
                            <div class="galeri-img-wrap" data-bs-toggle="modal" data-bs-target="#galeriModal"
                                data-img="assets/img/galeri/<?php echo htmlspecialchars($g['foto']); ?>"
                                data-title="<?php echo htmlspecialchars($g['judul']); ?>"
                                data-desc="<?php echo htmlspecialchars($g['keterangan']); ?>">
                                <img src="assets/img/galeri/<?php echo htmlspecialchars($g['foto']); ?>"
                                    alt="<?php echo htmlspecialchars($g['judul']); ?>"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                <div class="galeri-no-image" style="display:none">Gambar tidak tersedia</div>
                                <div class="galeri-overlay"><i class="bi bi-zoom-in"></i></div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($g['judul']); ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo nl2br(htmlspecialchars($g['keterangan'])); ?></p>
                            </div>
                            <div class="card-footer d-flex align-items-center gap-2">
                                <i class="bi bi-calendar3 text-muted"></i>
                                <small class="text-muted">Selesai
                                    <?php echo htmlspecialchars($g['tgl_selesai']); ?></small>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <strong class="footer-brand">Berkah Jaya</strong>
                    <span class="footer-sep"></span>
                    <span>Bengkel Las, Pejagoan, Kebumen</span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small>&copy; <?php echo date("Y"); ?> Bengkel Las Berkah Jaya.</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal Preview Galeri (dipakai bersama dengan halaman Galeri) -->
    <div class="modal fade modal-galeri" id="galeriModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-body p-0 text-center position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                    <img id="modalGaleriImg" src="" alt="" class="modal-preview-img">
                    <div class="modal-caption px-3 pb-2">
                        <h5 id="modalGaleriTitle"></h5>
                        <p id="modalGaleriDesc"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>