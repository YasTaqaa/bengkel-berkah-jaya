<?php
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Ubah status via GET
if (isset($_GET['aksi'], $_GET['id'])) {
    $id  = (int)$_GET['id'];
    $aks = $_GET['aksi'];

    if ($aks === 'baru') {
        mysqli_query($conn,
            "UPDATE proyek SET status='baru', tgl_selesai=NULL WHERE id=$id");
    } elseif ($aks === 'proses') {
        mysqli_query($conn,
            "UPDATE proyek SET status='proses', tgl_selesai=NULL WHERE id=$id");
    } elseif ($aks === 'selesai') {
        mysqli_query($conn,
            "UPDATE proyek SET status='selesai', tgl_selesai=NOW() WHERE id=$id");
    }

    header("Location: ../proyek.php");
    exit;
}

// Simpan ukuran & harga via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)$_POST['id'];
    $ukuran = input_filter($conn, $_POST['ukuran']);
    $harga  = (float)$_POST['harga'];

    mysqli_query($conn,
        "UPDATE proyek SET ukuran='$ukuran', harga=$harga WHERE id=$id");

    header("Location: ../proyek.php");
    exit;
}