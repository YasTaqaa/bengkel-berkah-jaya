<?php
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$nama_menu = input_filter($conn, $_POST['nama_menu']);
$link_file = input_filter($conn, $_POST['link_file']);
$urutan    = (int)$_POST['urutan'];
$aktif     = isset($_POST['aktif']) ? 1 : 0;

$sql = "INSERT INTO menu_navbar (nama_menu,link_file,urutan,aktif)
        VALUES ('$nama_menu','$link_file','$urutan','$aktif')";
mysqli_query($conn, $sql);

header("Location: ../navbar-menu.php");
exit;