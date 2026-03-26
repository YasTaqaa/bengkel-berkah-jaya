<?php
require_once __DIR__ . "/../../config.php";
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

mysqli_query($conn, "DELETE FROM proyek WHERE id=$id");

header("Location: ../proyek.php");
exit;