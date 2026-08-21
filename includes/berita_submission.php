<?php
/**
 * Penyimpanan dan validasi pengajuan berita warga.
 * Berita publik hanya dibuat setelah admin menyetujuinya.
 */
define('BERITA_FILE', __DIR__ . '/../data/berita.json');
define('PENGAJUAN_BERITA_FILE', __DIR__ . '/../data/pengajuan_berita.json');
define('PENGAJUAN_UPLOAD_DIR', __DIR__ . '/../assets/img/pengajuan');
define('PENGAJUAN_UPLOAD_URL', 'assets/img/pengajuan');

function json_load_array(string $file): array
{
    if (!is_file($file)) return [];
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function json_save_array(string $file, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return $json !== false && file_put_contents($file, $json, LOCK_EX) !== false;
}

function pengajuan_berita_load(): array { return json_load_array(PENGAJUAN_BERITA_FILE); }
function pengajuan_berita_save(array $items): bool { return json_save_array(PENGAJUAN_BERITA_FILE, $items); }
function berita_load(): array { return json_load_array(BERITA_FILE); }
function berita_save(array $items): bool { return json_save_array(BERITA_FILE, $items); }

function berita_slug(string $title, array $published): string
{
    $base = strtolower(trim($title));
    $base = preg_replace('/[^a-z0-9]+/i', '-', $base);
    $base = trim((string) $base, '-');
    $base = $base !== '' ? $base : 'berita-warga';
    $used = array_column($published, 'slug');
    $slug = $base;
    $number = 2;
    while (in_array($slug, $used, true)) $slug = $base . '-' . $number++;
    return $slug;
}

function berita_upload_foto(string $field, ?string &$error = null): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Foto kegiatan wajib diunggah.';
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload foto gagal. Silakan coba lagi.';
        return null;
    }
    if ((int) $file['size'] > 5 * 1024 * 1024) {
        $error = 'Ukuran foto maksimal 5 MB.';
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || @getimagesize($file['tmp_name']) === false) {
        $error = 'Foto harus berformat JPEG, PNG, atau WebP yang valid.';
        return null;
    }
    if (!is_dir(PENGAJUAN_UPLOAD_DIR) && !mkdir(PENGAJUAN_UPLOAD_DIR, 0755, true)) {
        $error = 'Folder upload belum dapat dibuat.';
        return null;
    }
    $filename = 'berita-' . date('Ymd-His') . '-' . bin2hex(random_bytes(5)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], PENGAJUAN_UPLOAD_DIR . '/' . $filename)) {
        $error = 'Foto tidak dapat disimpan.';
        return null;
    }
    return PENGAJUAN_UPLOAD_URL . '/' . $filename;
}

function berita_next_id(array $items): int
{
    $ids = array_map(fn($item) => (int) ($item['id'] ?? 0), $items);
    return ($ids ? max($ids) : 0) + 1;
}
