<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';
if (empty($_SESSION['admin_berita_csrf'])) $_SESSION['admin_berita_csrf'] = bin2hex(random_bytes(32));

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0); $action = $_POST['action'] ?? '';
    if (!hash_equals($_SESSION['admin_berita_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Permintaan tidak valid.'];
    } else {
        $stmt = $pdo->prepare("SELECT * FROM berita WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pengajuan tidak ditemukan atau sudah diproses.'];
        } elseif ($action === 'setujui') {
            $baseSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($item['judul']));
            $slug = trim($baseSlug, '-') ?: 'berita';
            $num = 2;
            $slugTest = $slug;
            while(true) {
                $check = $pdo->prepare("SELECT id FROM berita WHERE slug = ? AND id != ?");
                $check->execute([$slugTest, $id]);
                if (!$check->fetch()) break;
                $slugTest = $slug . '-' . $num++;
            }
            $stmt = $pdo->prepare("UPDATE berita SET status = 'published', slug = ? WHERE id = ?");
            if ($stmt->execute([$slugTest, $id])) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Berita disetujui dan sudah tampil di halaman publik.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menerbitkan berita.'];
            }
        } elseif ($action === 'tolak') {
            $stmt = $pdo->prepare("UPDATE berita SET status = 'rejected' WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pengajuan berita ditolak.'];
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Status pengajuan gagal diperbarui.'];
            }
        }
    }
    header('Location: pengajuan-berita.php'); exit;
}
$stmt = $pdo->query("SELECT * FROM berita WHERE status != 'published' ORDER BY created_at DESC, id DESC");
$pengajuan = $stmt->fetchAll();
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengajuan Berita | <?= NAMA_KELURAHAN ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="../assets/css/admin.css?v=<?= time() ?>">
</head>
<body><div class="admin-shell"><?php include __DIR__ . '/includes/sidebar.php'; ?><main class="admin-main">
<div class="admin-topbar"><div><h1>Pengajuan Berita Warga</h1><p>Tinjau berita dan foto sebelum diterbitkan ke halaman publik.</p></div><a href="../Pages/Berita/ajukan.php" class="btn btn-outline" target="_blank">Lihat Form Warga</a></div>
<?php if ($flash): ?><div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>
<div class="table-card submission-admin-list">
<?php if (!$pengajuan): ?><div class="empty-row">Belum ada pengajuan berita dari warga.</div>
<?php else: foreach ($pengajuan as $item): ?><article class="submission-admin-item">
<img class="thumb" src="../<?= htmlspecialchars($item['gambar'] ?? 'assets/img/placeholder-default.jpg') ?>" alt="" onerror="this.src='../assets/img/placeholder-default.jpg'">
<div class="submission-admin-content"><div class="submission-admin-meta"><span class="badge"><?= htmlspecialchars(ucfirst($item['status'] ?? 'pending')) ?></span> <?= htmlspecialchars(date('d M Y H:i', strtotime($item['created_at'] ?? 'now'))) ?></div>
<h2><?= htmlspecialchars($item['judul'] ?? '') ?></h2><p><?= nl2br(htmlspecialchars($item['ringkasan'] ?? '')) ?></p>
<details><summary>Baca isi berita &amp; kontak pengaju</summary><p><?= nl2br(htmlspecialchars($item['konten'] ?? '')) ?></p><p><strong>Pengaju:</strong> <?= htmlspecialchars($item['penulis'] ?? '-') ?></p></details>
<?php if (($item['status'] ?? '') === 'pending'): ?><form method="post" class="row-actions"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_berita_csrf']) ?>"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button name="action" value="setujui" class="btn btn-primary" type="submit">Setujui &amp; Terbitkan</button><button name="action" value="tolak" class="btn btn-danger" type="submit" onclick="return confirm('Tolak pengajuan berita ini?')">Tolak</button></form><?php endif; ?>
</div></article><?php endforeach; endif; ?></div></main></div></body></html>

