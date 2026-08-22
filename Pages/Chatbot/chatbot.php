<?php
require_once __DIR__ . '/../../config.php';

$assetPrefix = '../../assets/';
$navPrefix = '../';
$activeNav = 'chatbot';
$pageTitle = 'Chatbot AI';

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
    $bgStyle = 'background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url(\'../../' . $header_chatbot . '\') center/cover; color: #ffffff;';
}
?>
<section class="page-hero" style="<?= $bgStyle ?>">
  <div class="container">
    <div class="breadcrumb"><a href="<?= $navPrefix ?>index.php">Beranda</a> / Chatbot AI</div>
    <h1>Chatbot Informasi Kelurahan</h1>
    <p>Tanya seputar layanan, jam operasional, kontak, atau data kelurahan — dijawab otomatis.</p>
  </div>
</section>

<section class="page-section">
  <div class="container">
    <div class="chatbot-wrap">
      <div class="chatbot-header">
        <div class="avatar">🤖</div>
        <div>
          <strong>Asisten Kangkung</strong>
          <span>Online • Jawaban otomatis</span>
        </div>
      </div>

      <div class="chat-window" id="chatWindow">
        <div class="chat-bubble bot">
        Tabik Pun! Selamat datang di Pusat Informasi Kelurahan Kangkung. Saya adalah Asisten Cerdas Kangkung yang siap membantu Bapak/Ibu. Ingin tahu soal surat pengantar, data penduduk, atau nama aparatur kami? 👇
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
        <input type="text" id="chatInput" placeholder="Tulis pertanyaan lu di sini..." autocomplete="off">
        <button type="submit">➤</button>
      </form>
    </div>
  </div>
</section>

<script>
  const chatWindow = document.getElementById('chatWindow');
  const chatForm = document.getElementById('chatForm');
  const chatInput = document.getElementById('chatInput');

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
                addBubble('Maaf, saya sedang mengalami gangguan. Coba lagi.', 'bot');
            }
        }, 1500);
        
    } catch (e) {
        setTimeout(() => {
            indicator.remove();
            addBubble('Maaf, koneksi terputus.', 'bot');
        }, 1500);
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

<style>
.typing-indicator-full {
    display: inline-flex;
    gap: 4px;
    align-items: center;
    padding: 12px 20px !important;
}
.typing-indicator-full .dot {
    width: 6px;
    height: 6px;
    background: var(--teal-700);
    border-radius: 50%;
    animation: typingBounce 1.4s infinite;
    opacity: 0.6;
}
.typing-indicator-full .dot:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator-full .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
.chat-bubble.bot a {
    color: var(--teal-900);
    font-weight: 700;
    text-decoration: none;
    display: inline-block;
    margin-top: 4px;
}
.chat-bubble.bot a:hover {
    text-decoration: underline;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
