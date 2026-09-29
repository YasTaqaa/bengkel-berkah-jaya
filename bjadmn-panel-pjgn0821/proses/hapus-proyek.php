<?php
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['admin_login']) ||
    $_SESSION['admin_login'] !== true
) {
    header('Location: ../login.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: ../proyek.php');
    exit;
}

$fotoReferensi = [];

$stmtFoto = $conn->prepare(
    'SELECT foto FROM proyek_referensi WHERE proyek_id = ?'
);

if ($stmtFoto) {
    $stmtFoto->bind_param('i', $id);
    $stmtFoto->execute();

    $hasilFoto = $stmtFoto->get_result();

    while ($row = $hasilFoto->fetch_assoc()) {
        $fotoReferensi[] = $row['foto'];
    }

    $stmtFoto->close();
}

foreach ($fotoReferensi as $foto) {
    $pathFoto = dirname(__DIR__, 2) . '/' . ltrim($foto, '/');

    if (is_file($pathFoto)) {
        unlink($pathFoto);
    }
}

$stmtHapusReferensi = $conn->prepare(
    'DELETE FROM proyek_referensi WHERE proyek_id = ?'
);

if ($stmtHapusReferensi) {
    $stmtHapusReferensi->bind_param('i', $id);
    $stmtHapusReferensi->execute();
    $stmtHapusReferensi->close();
}

$stmtHapusProyek = $conn->prepare(
    'DELETE FROM proyek WHERE id = ?'
);

if ($stmtHapusProyek) {
    $stmtHapusProyek->bind_param('i', $id);
    $stmtHapusProyek->execute();
    $stmtHapusProyek->close();
}

header('Location: ../proyek.php');
exit;