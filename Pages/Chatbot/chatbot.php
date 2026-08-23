<?php
require_once __DIR__ . '/../../config.php';

$assetPrefix = '../../assets/';
$navPrefix   = '../';
$activeNav   = 'chatbot';
$pageTitle   = 'Chatbot AI';

$forceSolidHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<?php
require_once __DIR__ . '/../../config_db.php';
$pdo = getDB();
$stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = 'header_chatbot'");
$stmt->execute();
$header_chatbot = $stmt->fetchColumn();
$bgStyle = '';
if (!empty($header_chatbot)) {
    $header_chatbot = htmlspecialchars((string)$header_chatbot, ENT_QUOTES, 'UTF-8');
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(\'../../' . $header_chatbot . '\') center/cover; color: #ffffff;';
}
?>

<!-- Hero Section -->
<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= $navPrefix ?>index.php">Beranda</a> <span>/</span> <strong style="color: #ffffff;">Chatbot AI</strong>
    </div>
    <h1>Chatbot Informasi Kelurahan</h1>
    <p>Tanya seputar layanan, jam operasional, kontak, atau data kelurahan — dijawab otomatis.</p>
  </div>
</section>
  </div>
</section>

<!-- Chatbot Interface Section -->
<section class="page-section" style="padding: 40px 0;">
  <div class="container">
    <div class="chatbot-wrap">
      <div class="chatbot-header">
        <div class="avatar">🤖</div>
        <div>
          <strong>Asisten <?= htmlspecialchars(defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kangkung') ?></strong>
          <span>Online • Jawaban otomatis</span>
        </div>
      </div>

      <div class="chat-window" id="chatWindow">
        <div class="chat-bubble bot">
          <?php 
            $welcome_msg = $settings['chatbot_welcome_message'] ?? '';
            if (empty($welcome_msg)) {
                $welcome_msg = "Tabik Pun! Selamat datang di Pusat Informasi Kelurahan " . (defined('NAMA_KELURAHAN') ? NAMA_KELURAHAN : 'Kangkung') . ". Saya adalah Asisten Cerdas yang siap membantu Bapak/Ibu. Ingin tahu soal surat pengantar, data penduduk, atau nama aparatur kami? 👇";
            }
            echo nl2br(htmlspecialchars($welcome_msg));
          ?>
        </div>
      </div>

      <div class="chat-input-area">
        <div class="chat-suggestions">
          <button class="chat-chip" data-q="Jam layanan kelurahan?">Jam layanan?</button>
          <button class="chat-chip" data-q="Siapa nama Pak Lurah saat ini?">Siapa Pak Lurah?</button>
          <button class="chat-chip" data-q="Berapa total penduduk kangkung?">Jumlah penduduk?</button>
          <button class="chat-chip" data-q="Ada berita terbaru apa minggu ini?">Berita terbaru?</button>
        </div>

        <form class="chat-input-row" id="chatForm">
          <input type="text" id="chatInput" placeholder="Tulis pertanyaan Anda di sini..." autocomplete="off">
          <button type="submit" aria-label="Kirim Pesan">➤</button>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
  const chatWindow = document.getElementById('chatWindow');
  const chatForm   = document.getElementById('chatForm');
  const chatInput  = document.getElementById('chatInput');

  function addBubble(text, who) {
    const div = document.createElement('div');
    div.className = 'chat-bubble ' + who;
    div.innerHTML = text;
    chatWindow.appendChild(div);
    chatWindow.scrollTop = chatWindow.scrollHeight;
  }
  
  function addTypingIndicator() {
    const div = document.createElement('div');
    div.className = 'chat-bubble bot typing-indicator-full';
    div.innerHTML = '<span class="dot"></span><span class="dot"></span><span class="dot"></span>';
    chatWindow.appendChild(div);
    chatWindow.scrollTop = chatWindow.scrollHeight;
    return div;
  }
  
  async function respond(text) {
    addBubble(text, 'user');
    const indicator = addTypingIndicator();
    
    try {
      const formData = new FormData();
      formData.append('message', text);

      const response = await fetch('../../api/chatbot.php', {
        method: 'POST',
        body: formData
      });
      
      const data = await response.json();
      
      setTimeout(() => {
        indicator.remove();
        if (data.status === 'success') {
          addBubble(data.reply, 'bot');
        } else {
          addBubble('Maaf, saya sedang mengalami gangguan. Coba lagi beberapa saat lagi.', 'bot');
        }
      }, 1000);
        
    } catch (e) {
      setTimeout(() => {
        indicator.remove();
        addBubble('Maaf, koneksi terputus. Silakan periksa jaringan Anda.', 'bot');
      }, 1000);
    }
  }

  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const val = chatInput.value.trim();
    if (!val) return;
    respond(val);
    chatInput.value = '';
  });

  document.querySelectorAll('.chat-chip').forEach((chip) => {
    chip.addEventListener('click', () => respond(chip.dataset.q));
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>