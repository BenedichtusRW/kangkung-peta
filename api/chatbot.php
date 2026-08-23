<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config_db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$message = trim($_POST['message'] ?? '');
if (empty($message)) {
    echo json_encode(['status' => 'success', 'reply' => 'Maaf, saya tidak menangkap pesan Anda. Bisa diulangi?']);
    exit;
}

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'TARUH_API_KEY_ANDA_DISINI') {
    echo json_encode(['status' => 'success', 'reply' => 'Admin belum mengatur API Key Gemini. Silakan masukkan API Key di config.php agar saya bisa menjawab pertanyaan Anda.']);
    exit;
}

$pdo = getDB();

// --- 1. KUMPULKAN KONTEKS WEBSITE DARI DATABASE ---

// A. Profil & Konfigurasi Dasar
$context = "INFORMASI DASAR KELURAHAN:\n";
$context .= "- Nama Kelurahan: " . NAMA_KELURAHAN . "\n";
$context .= "- Kecamatan: " . NAMA_KECAMATAN . " | Kota: " . NAMA_KOTA . "\n";
$context .= "- Alamat: " . ALAMAT_KANTOR . "\n";
$context .= "- Jam Layanan: " . JAM_LAYANAN . "\n";
$context .= "- Telepon: " . KONTAK_TELEPON . " | Email: " . KONTAK_EMAIL . "\n\n";

// B. Data Statistik Penduduk
$stmt = $pdo->prepare("SELECT key_value FROM statistik WHERE key_name = 'jumlah_penduduk'");
$stmt->execute();
$total_penduduk = $stmt->fetchColumn();
if ($total_penduduk) {
    $context .= "DATA STATISTIK:\n- Total Penduduk: " . number_format((int)$total_penduduk, 0, ',', '.') . " jiwa.\n\n";
}

// C. Berita Terbaru
$stmt = $pdo->query("SELECT judul, ringkasan, tanggal FROM berita WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
$berita = $stmt->fetchAll();
if (count($berita) > 0) {
    $context .= "BERITA TERBARU (Sebutkan jika ditanya):\n";
    foreach ($berita as $b) {
        $context .= "- " . $b['judul'] . " (" . $b['tanggal'] . "): " . $b['ringkasan'] . "\n";
    }
    $context .= "\n";
}

// D. Susunan Aparatur Kelurahan
$stmt = $pdo->query("SELECT nama, jabatan FROM aparatur ORDER BY (CASE WHEN jabatan LIKE '%Lurah%' THEN 1 WHEN jabatan LIKE '%Sekretaris%' OR jabatan LIKE '%Seklur%' THEN 2 WHEN jabatan LIKE '%Kasi%' THEN 3 WHEN jabatan LIKE '%Kaur%' THEN 4 ELSE 5 END) ASC, id ASC");
$aparatur = $stmt->fetchAll();
if (count($aparatur) > 0) {
    $context .= "SUSUNAN APARATUR KELURAHAN:\n";
    foreach ($aparatur as $a) {
        $context .= "- " . $a['jabatan'] . ": " . $a['nama'] . "\n";
    }
    $context .= "\n";
}

// E. Tim KKN Pembuat Website
$stmt = $pdo->query("SELECT nama, jabatan FROM tim_kkn ORDER BY (CASE WHEN jabatan LIKE '%Dosen%' OR jabatan LIKE '%DPL%' THEN 1 WHEN jabatan LIKE '%Kordes%' OR jabatan LIKE '%Ketua%' THEN 2 WHEN jabatan LIKE '%Sekretaris%' THEN 3 WHEN jabatan LIKE '%Bendahara%' THEN 4 ELSE 5 END) ASC, id ASC");
$tim_kkn = $stmt->fetchAll();
if (count($tim_kkn) > 0) {
    $context .= "TIM KKN (Pembuat Website):\n";
    foreach ($tim_kkn as $t) {
        $context .= "- " . $t['nama'] . " (" . $t['jabatan'] . ")\n";
    }
    $context .= "\n";
}

// F. Custom Q&A dari Database
$stmt = $pdo->query("SELECT kata_kunci, jawaban FROM chatbot_qa ORDER BY id ASC");
$custom_qa = $stmt->fetchAll();

// G. Fallback Message
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'chatbot_fallback'");
$stmt->execute();
$fallback_msg = $stmt->fetchColumn();
if (empty($fallback_msg)) {
    $fallback_msg = "Tabik Pun! Maaf Bapak/Ibu, saya adalah Asisten Virtual " . NAMA_KELURAHAN . ". Saya hanya bisa menjawab seputar jam layanan, jumlah penduduk, aparatur kelurahan, kontak, tim KKN, dan berita kelurahan. Untuk pertanyaan lain, silakan datang langsung ke kantor ya!";
}

// --- 2. PSEUDO-AI RULE-BASED MATCHING ---

$lower_msg = strtolower($message);
$reply = "";

// Helper untuk merapikan list
function formatList($arr, $keyLabel, $valLabel) {
    if (empty($arr)) return "Belum ada data.";
    $res = "<ul>";
    foreach ($arr as $item) {
        $res .= "<li><b>" . htmlspecialchars($item[$keyLabel]) . "</b>: " . htmlspecialchars($item[$valLabel]) . "</li>";
    }
    $res .= "</ul>";
    return $res;
}

// Cek Custom Q&A terlebih dahulu
foreach ($custom_qa as $qa) {
    $kunci_array = array_map('trim', explode(',', strtolower($qa['kata_kunci'])));
    foreach ($kunci_array as $k) {
        if (!empty($k) && strpos($lower_msg, $k) !== false) {
            $reply = $qa['jawaban'];
            break 2; // Hentikan pengecekan jika sudah ada yang cocok
        }
    }
}

// Jika tidak ada di Custom Q&A, cek aturan bawaan (Dinamis)
if (empty($reply)) {
    if (strpos($lower_msg, 'jam') !== false || strpos($lower_msg, 'buka') !== false || strpos($lower_msg, 'layanan') !== false) {
        $reply = "Tabik Pun! Jam layanan Kantor " . NAMA_KELURAHAN . " adalah <b>" . JAM_LAYANAN . "</b>. Silakan datang pada jam kerja tersebut ya Bapak/Ibu.";
    } 
    elseif (strpos($lower_msg, 'penduduk') !== false || strpos($lower_msg, 'warga') !== false) {
        $formatted_total = $total_penduduk ? number_format((int)$total_penduduk, 0, ',', '.') : 'Belum diketahui';
        $reply = "Tabik Pun! Saat ini, " . NAMA_KELURAHAN . " memiliki total penduduk sebanyak <b>" . $formatted_total . " jiwa</b>.";
    } 
    elseif (strpos($lower_msg, 'lurah') !== false || strpos($lower_msg, 'aparatur') !== false || strpos($lower_msg, 'struktur') !== false) {
        $reply = "Tabik Pun! Berikut adalah susunan aparatur " . NAMA_KELURAHAN . ":<br>";
    $reply .= formatList($aparatur, 'jabatan', 'nama');
} 
elseif (strpos($lower_msg, 'berita') !== false || strpos($lower_msg, 'kabar') !== false || strpos($lower_msg, 'terbaru') !== false) {
    if (count($berita) > 0) {
        $reply = "Tabik Pun! Berikut adalah berita terbaru di kelurahan kita:<br><ul>";
        foreach ($berita as $b) {
            $reply .= "<li><b>" . htmlspecialchars($b['judul']) . "</b> (" . $b['tanggal'] . ")<br><i>" . htmlspecialchars($b['ringkasan']) . "</i></li>";
        }
        $reply .= "</ul>";
    } else {
        $reply = "Tabik Pun! Saat ini belum ada berita terbaru yang dipublikasikan.";
    }
} 
elseif (strpos($lower_msg, 'kkn') !== false || strpos($lower_msg, 'pembuat') !== false || strpos($lower_msg, 'mahasiswa') !== false) {
    $reply = "Tabik Pun! Website ini dipersembahkan oleh Mahasiswa KKN UIN Raden Intan Lampung Kelompok 31.<br>";
    $reply .= formatList($tim_kkn, 'jabatan', 'nama');
} 
elseif (strpos($lower_msg, 'kontak') !== false || strpos($lower_msg, 'telepon') !== false || strpos($lower_msg, 'email') !== false || strpos($lower_msg, 'alamat') !== false) {
    $reply = "Tabik Pun! Kantor kami beralamat di <b>" . ALAMAT_KANTOR . "</b>.<br>Anda bisa menghubungi kami melalui telepon di <b>" . KONTAK_TELEPON . "</b> atau email ke <b>" . KONTAK_EMAIL . "</b>.";
} 
elseif (strpos($lower_msg, 'surat') !== false || strpos($lower_msg, 'pengantar') !== false || strpos($lower_msg, 'syarat') !== false) {
    $reply = "Tabik Pun! Untuk pembuatan surat pengantar atau administrasi lainnya, silakan datang langsung ke Kantor Kelurahan pada <b>" . JAM_LAYANAN . "</b> dengan membawa KTP dan KK asli serta fotokopi secukupnya.";
} 
    elseif (strpos($lower_msg, 'website') !== false || strpos($lower_msg, 'situs') !== false || strpos($lower_msg, 'web') !== false || strpos($lower_msg, 'aplikasi') !== false || strpos($lower_msg, 'peta') !== false) {
        $reply = "Tabik Pun! Website ini adalah platform Pusat Informasi dan Peta Interaktif " . NAMA_KELURAHAN . ". Di sini Bapak/Ibu bisa melihat letak fasilitas umum di peta, statistik kependudukan, berita terbaru, dan informasi aparatur kelurahan.";
    }
    else {
        $reply = $fallback_msg;
    }
}

echo json_encode(['status' => 'success', 'reply' => $reply]);
