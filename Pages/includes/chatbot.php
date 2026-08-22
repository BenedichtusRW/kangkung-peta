<!-- Chatbot Widget -->
<div class="chatbot-widget" id="chatbotWidget">

    <!-- Floating Toggler Button -->
    <button class="chatbot-toggler" onclick="toggleChatbot()" aria-label="Buka Chatbot">
        <i class="fa-solid fa-robot"></i>
    </button>

    <!-- Chatbot Container -->
    <div class="chatbot-container">
        
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-avatar">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div class="chatbot-title">
                <h4>Asisten Kangkung</h4>
                <p><span class="online-dot"></span> Online • Jawaban otomatis</p>
            </div>
            <button class="chatbot-close" onclick="toggleChatbot()" aria-label="Tutup Chatbot">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Chat Body -->
        <div class="chatbot-body" id="chatBody">
            <div class="chat-msg bot">
                <div class="msg-content">
                    Tabik Pun! Selamat datang di Pusat Informasi Kelurahan Kangkung. Saya adalah Asisten Cerdas Kangkung yang siap membantu Bapak/Ibu. Ingin tahu soal surat pengantar, data penduduk, atau nama aparatur kami? 👇
                </div>
            </div>
        </div>

        <!-- Quick Reply Chips -->
        <div class="chat-chips">
            <button class="chip" onclick="sendQuickReply('Jam layanan kelurahan?')">Jam layanan?</button>
            <button class="chip" onclick="sendQuickReply('Siapa nama Pak Lurah saat ini?')">Siapa Pak Lurah?</button>
            <button class="chip" onclick="sendQuickReply('Berapa total penduduk kangkung?')">Jumlah penduduk?</button>
            <button class="chip" onclick="sendQuickReply('Ada berita terbaru apa minggu ini?')">Berita terbaru?</button>
        </div>

        <!-- Footer / Input -->
        <form class="chatbot-footer" id="chatForm" onsubmit="handleChatSubmit(event)">
            <input type="text" id="chatInput" placeholder="Tulis pertanyaan Anda..." autocomplete="off" required>
            <button type="submit" aria-label="Kirim Pesan">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>

    </div>
</div>

<script>
    window.CHATBOT_API_URL = "<?= $assetPrefix ?>../api/chatbot.php";
</script>
<script src="<?= $assetPrefix ?>js/chatbot.js?v=<?= time() ?>"></script>
