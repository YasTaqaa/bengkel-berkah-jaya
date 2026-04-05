<?php

function upload_foto(array $file, string $dest_folder): array
{
    // Tidak ada file diupload
    if (empty($file['name'])) {
        return ['file' => '', 'error' => ''];
    }

    // Cek error upload dari PHP
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['file' => '', 'error' => 'Upload gagal, coba lagi.'];
    }

    // Validasi ukuran maksimal 3MB
    $max_size = 3 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return ['file' => '', 'error' => 'Ukuran file maksimal 3MB.'];
    }

    // Validasi MIME type dari isi file (bukan ekstensi)
    $allowed_mime = ['image/jpeg', 'image/png', 'image/webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed_mime)) {
        return ['file' => '', 'error' => 'File harus berupa gambar JPG, PNG, atau WebP.'];
    }

    // Tentukan ekstensi dari MIME type (bukan dari nama file asli)
    $ext_map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    $ext = $ext_map[$mime];

    // Generate nama file unik
    $nama_file = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest       = rtrim($dest_folder, '/') . '/' . $nama_file;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['file' => '', 'error' => 'Gagal menyimpan file.'];
    }

    return ['file' => $nama_file, 'error' => ''];
}