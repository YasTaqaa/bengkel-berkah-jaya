<?php
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_menu = input_filter($conn, $_POST['nama_menu']);
    $link_file = input_filter($conn, $_POST['link_file']);
    $urutan    = (int)$_POST['urutan'];
    $aktif     = isset($_POST['aktif']) ? 1 : 0;

    $sql = "UPDATE menu_navbar SET
            nama_menu='$nama_menu',
            link_file='$link_file',
            urutan='$urutan',
            aktif='$aktif'
            WHERE id=$id";
    mysqli_query($conn, $sql);

    header("Location: ../navbar-menu.php");
    exit;
}