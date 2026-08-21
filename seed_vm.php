<?php
require_once __DIR__ . '/config_db.php';
$pdo = getDB();

$visi = "Terwujudnya kelurahan di wilayah pesisir yang Mandiri, Sejahtera, Bersih, dan Berdaya Saing Berbasis Ekonomi Biru yang Berkelanjutan.";
$misi = [
    "Mengembangkan sistem pengelolaan ekonomi warga pesisir yang terintegrasi dari hulu ke hilir melalui penguatan peran koperasi.",
    "Membangun infrastruktur pendukung desa nelayan yang tertata rapi, sehat, dan nyaman bagi kehidupan warga.",
    "Meningkatkan kapasitas sumber daya manusia masyarakat pesisir melalui pelatihan keterampilan serta pengolahan hasil laut.",
    "Meningkatkan produktivitas masyarakat pesisir khususnya nelayan melalui penggunaan sarana dan teknologi ramah lingkungan."
];

$stmt = $pdo->prepare("INSERT INTO konten (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
$stmt->execute(['visi_teks', $visi]);
$stmt->execute(['misi_teks', json_encode($misi, JSON_UNESCAPED_UNICODE)]);

echo "Visi Misi Berhasil Diupdate!";
