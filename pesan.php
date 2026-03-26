<?php
require_once __DIR__ . "/config.php";
$qLayanan = mysqli_query($conn, "SELECT * FROM layanan ORDER BY urutan ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_db'])) {
    $nama       = input_filter($conn, $_POST['nama']);
    $hp         = input_filter($conn, $_POST['hp']);
    $layanan_id = (int)$_POST['layanan_id'];
    $lokasi     = input_filter($conn, $_POST['lokasi']);
    $catatan    = input_filter($conn, $_POST['catatan']);

    $sql = "INSERT INTO proyek (nama,hp,layanan_id,lokasi,ukuran,harga,catatan)
            VALUES ('$nama','$hp','$layanan_id','$lokasi','',0,'$catatan')";
    mysqli_query($conn, $sql);

    header("Location: terima-kasih.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form Pesan - Bengkel Las Berkah Jaya</title>
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
                <span class="section-label-light">Gratis Survey Area Kebumen</span>
                <h1 class="page-header-title">Form Pemesanan & Survey</h1>
                <p class="page-header-desc">Isi form di bawah, kami akan menghubungi Anda untuk konfirmasi jadwal
                    survey.</p>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7 reveal">
                    <div class="form-card">
                        <form method="post" action="">

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person me-2 text-primary"></i>Nama Lengkap
                                </label>
                                <input type="text" name="nama" class="form-control form-control-lg"
                                    placeholder="Masukkan nama Anda" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-whatsapp me-2 text-primary"></i>No. HP / WhatsApp
                                </label>
                                <input type="text" name="hp" class="form-control form-control-lg"
                                    placeholder="Contoh: 08123456789" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-grid me-2 text-primary"></i>Layanan yang Diinginkan
                                </label>
                                <select name="layanan_id" class="form-select form-select-lg" required>
                                    <option value="">-- Pilih Layanan --</option>
                                    <?php
                                    mysqli_data_seek($qLayanan, 0);
                                    while ($lay = mysqli_fetch_assoc($qLayanan)) {
                                        $sel = (isset($_GET['layanan_id']) && $_GET['layanan_id'] == $lay['id']) ? 'selected' : '';
                                        echo '<option value="'.$lay['id'].'" '.$sel.'>'.htmlspecialchars($lay['nama']).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt me-2 text-primary"></i>Lokasi / Alamat Lengkap
                                </label>
                                <textarea name="lokasi" class="form-control" rows="3"
                                    placeholder="Contoh: Jl. Mawar No. 10, Kebumen (dekat Masjid Al-Ikhlas)"
                                    required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-chat-text me-2 text-primary"></i>Catatan Tambahan
                                    <span class="text-muted fw-normal">(opsional)</span>
                                </label>
                                <textarea name="catatan" class="form-control" rows="3"
                                    placeholder="Contoh: ukuran kira-kira 3x4 meter, warna hitam doff"></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="simpan_db" class="btn-submit">
                                    <i class="bi bi-send"></i> Kirim
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- Info Samping -->
                <div class="col-md-4 offset-md-1 reveal">
                    <div class="pesan-info">
                        <h5 class="pesan-info-title">Kenapa pesan di sini?</h5>
                        <ul class="pesan-info-list">
                            <li><i class="bi bi-check-circle-fill"></i> Gratis survey area Kebumen</li>
                            <li><i class="bi bi-check-circle-fill"></i> Estimasi harga transparan</li>
                            <li><i class="bi bi-check-circle-fill"></i> Pengerjaan tepat waktu</li>
                            <li><i class="bi bi-check-circle-fill"></i> Bergaransi</li>
                            <li><i class="bi bi-check-circle-fill"></i> Tim berpengalaman 10+ tahun</li>
                        </ul>
                        <div class="pesan-info-contact">
                            <p class="mb-2"><i class="bi bi-whatsapp me-2"></i>+62 812‑3456‑7890</p>
                            <p class="mb-2"><i class="bi bi-clock me-2"></i>Senin–Sabtu, 08.00–17.00</p>
                            <p class="mb-0"><i class="bi bi-geo-alt me-2"></i>Pejagoan, Kebumen</p>
                        </div>
                    </div>
                </div>
            </div>
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