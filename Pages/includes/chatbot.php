<!-- Chatbot Widget -->
<div class="chatbot-widget" id="chatbotWidget">
    
    <!-- Floating Toggler Button -->
    <div class="chatbot-toggler" onclick="toggleChatbot()">
        <i class="fa-solid fa-robot"></i>
    </div>

    <!-- Chatbot Container -->
    <div class="chatbot-container">
        
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-avatar">
                <i class="fa-solid fa-robot" style="color: var(--teal-900);"></i>
            </div>
            <div class="chatbot-title">
                <h4>Asisten Kangkung</h4>
                <p>Online • Jawaban otomatis</p>
            </div>
            <button class="chatbot-close" onclick="toggleChatbot()">
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
            <div class="chip" onclick="sendQuickReply('Jam layanan kelurahan?')">Jam layanan?</div>
            <div class="chip" onclick="sendQuickReply('Siapa nama Pak Lurah saat ini?')">Siapa Pak Lurah?</div>
            <div class="chip" onclick="sendQuickReply('Berapa total penduduk kangkung?')">Jumlah penduduk?</div>
            <div class="chip" onclick="sendQuickReply('Ada berita terbaru apa minggu ini?')">Berita terbaru?</div>
        </div>

        <!-- Footer / Input -->
        <form class="chatbot-footer" id="chatForm" onsubmit="handleChatSubmit(event)">
            <input type="text" id="chatInput" placeholder="Tulis pertanyaan Anda di sini..." autocomplete="off">
            <button type="submit">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>

    </div>
</div>

<script>
    window.CHATBOT_API_URL = "<?= $assetPrefix ?>../api/chatbot.php";
</script>
<script src="<?= $assetPrefix ?>js/chatbot.js?v=<?= time() ?>"></script>
