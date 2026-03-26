<?php
require_once __DIR__ . "/config.php";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$qLayanan = mysqli_query($conn, "SELECT * FROM layanan WHERE id=$id LIMIT 1");
$layanan  = mysqli_fetch_assoc($qLayanan);
if (!$layanan) { header("Location: layanan.php"); exit; }
$qGaleri = mysqli_query($conn, "SELECT * FROM galeri WHERE layanan_id=$id ORDER BY tgl_selesai DESC, id DESC");
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
                            Mulai Rp <?php echo number_format($layanan['harga'],0,',','.'); ?>
                        </div>
                        <p class="detail-desc"><?php echo nl2br(htmlspecialchars($layanan['deskripsi'])); ?></p>
                        <a href="pesan.php?layanan_id=<?php echo $layanan['id']; ?>" class="btn-hero-primary">
                            <i class="bi bi-calendar2-check"></i> Pesan Layanan Ini
                        </a>
                    </div>
                </div>
            </div>

            <!-- Galeri Layanan -->
            <div class="reveal">
                <div class="text-center mb-4">
                    <span class="section-label">Hasil Pengerjaan</span>
                    <h3 class="section-title mb-0">Galeri <?php echo htmlspecialchars($layanan['nama']); ?></h3>
                </div>
            </div>

            <?php if (mysqli_num_rows($qGaleri) == 0) { ?>
            <p class="text-center text-muted py-4">Belum ada galeri untuk layanan ini.</p>
            <?php } else { ?>
            <div class="row g-4">
                <?php while ($g = mysqli_fetch_assoc($qGaleri)) { ?>
                <div class="col-md-4 reveal">
                    <div class="card galeri-card h-100">
                        <div class="galeri-img-wrap">
                            <img src="assets/img/galeri/<?php echo htmlspecialchars($g['foto']); ?>"
                                alt="<?php echo htmlspecialchars($g['judul']); ?>">
                            <div class="galeri-overlay"><i class="bi bi-zoom-in"></i></div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($g['judul']); ?></h5>
                            <p class="card-text text-muted"><?php echo nl2br(htmlspecialchars($g['keterangan'])); ?></p>
                        </div>
                        <div class="card-footer d-flex align-items-center gap-2">
                            <i class="bi bi-calendar3 text-muted"></i>
                            <small class="text-muted">Selesai:
                                <?php echo htmlspecialchars($g['tgl_selesai']); ?></small>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </section>

    <div class="floating-wa">
        <a href="https://wa.me/6281234567890" target="_blank"><i class="bi bi-whatsapp"></i></a>
    </div>

    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <strong class="footer-brand">Berkah Jaya</strong>
                    <span class="footer-sep">·</span>
                    <span>Bengkel Las & Kanopi, Kebumen</span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small>&copy; <?php echo date('Y'); ?> Bengkel Las Berkah Jaya. All rights reserved.</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>