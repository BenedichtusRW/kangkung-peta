<?php
/**
 * admin/includes/functions.php
 * Fungsi bantu untuk CRUD data titik lokasi (data/pois.json) dan upload gambar.
 * Kalau nanti pindah ke database, cukup ganti isi fungsi-fungsi ini saja —
 * halaman admin lain tidak perlu diubah.
 */

define('POIS_FILE', __DIR__ . '/../../data/pois.json');
define('UPLOAD_DIR', __DIR__ . '/../../assets/img/uploads');
define('UPLOAD_URL_PREFIX', 'assets/img/uploads');



/**
 * Tangani upload file gambar dari form.
 * Return path relatif (untuk disimpan di JSON) atau null kalau tidak ada file/upload gagal.
 */
function handle_image_upload(string $fieldName): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = 'poi-' . time() . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    $destination = UPLOAD_DIR . '/' . $filename;

    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destination)) {
        return null;
    }

    return UPLOAD_URL_PREFIX . '/' . $filename;
}

/**
 * Validasi & rapikan input form jadi array siap simpan.
 */
function build_poi_from_post(array $post, ?string $gambar, int $id): array
{
    return [
        'id'        => $id,
        'nama'      => trim($post['nama'] ?? ''),
        'kategori'  => trim($post['kategori'] ?? ''),
        'deskripsi' => trim($post['deskripsi'] ?? ''),
        'alamat'    => trim($post['alamat'] ?? ''),
        'kontak'    => trim($post['kontak'] ?? ''),
        'jam_buka'  => trim($post['jam_buka'] ?? ''),
        'lat'       => (float) ($post['lat'] ?? 0),
        'lng'       => (float) ($post['lng'] ?? 0),
        'gambar'    => $gambar ?? ($post['gambar_lama'] ?? ''),
    ];
}

function validate_poi(array $poi): array
{
    $errors = [];
    if ($poi['nama'] === '') $errors[] = 'Nama tempat wajib diisi.';
    if ($poi['kategori'] === '') $errors[] = 'Kategori wajib dipilih.';
    if ($poi['alamat'] === '') $errors[] = 'Alamat wajib diisi.';
    if ($poi['lat'] === 0.0 || $poi['lng'] === 0.0) $errors[] = 'Koordinat (lat/lng) wajib diisi dan tidak boleh 0.';
    return $errors;
}
