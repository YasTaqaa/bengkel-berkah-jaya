<?php
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$nama       = input_filter($conn, $_POST['nama']);
$jabatan    = input_filter($conn, $_POST['jabatan']);
$pengalaman = (int)$_POST['pengalaman'];
$urutan     = (int)$_POST['urutan'];

$fotoName = "";
if (!empty($_FILES['foto']['name'])) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $fotoName = "tim_".time().".".$ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], "../../assets/img/tim/".$fotoName);
}

$sql = "INSERT INTO karyawan (nama,jabatan,pengalaman,foto,urutan)
        VALUES ('$nama','$jabatan','$pengalaman','$fotoName','$urutan')";
mysqli_query($conn, $sql);

header("Location: ../tim.php");
exit;