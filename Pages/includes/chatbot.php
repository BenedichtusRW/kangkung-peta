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

<!-- Styling Widget Chatbot -->
<style>
/* Base Variables & Container */
.chatbot-widget {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  font-family: system-ui, -apple-system, sans-serif;
}

/* Floating Toggler Button */
.chatbot-toggler {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #064e3b;
  color: #ffffff;
  border: none;
  cursor: pointer;
  box-shadow: 0 10px 25px rgba(6, 78, 59, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-left: auto;
}

/* Chatbox Main Container */
.chatbot-container {
  display: none; /* Dikontrol via JS toggleChatbot() */
  width: 360px;
  max-width: calc(100vw - 32px);
  height: 520px;
  max-height: calc(100vh - 100px);
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border: 1px solid #e2e8f0;
  flex-direction: column;
  overflow: hidden;
  margin-bottom: 12px;
}

.chatbot-widget.active .chatbot-container {
  display: flex;
}

/* Header Style */
.chatbot-header {
  background: #064e3b;
  color: #ffffff;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.chatbot-avatar {
  width: 40px;
  height: 40px;
  background: #ecfdf5;
  color: #064e3b;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.chatbot-title {
  flex-grow: 1;
}

.chatbot-title h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
}

.chatbot-title p {
  margin: 2px 0 0 0;
  font-size: 0.75rem;
  color: #a7f3d0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.online-dot {
  width: 8px;
  height: 8px;
  background-color: #34d399;
  border-radius: 50%;
  display: inline-block;
}

.chatbot-close {
  background: transparent;
  border: none;
  color: #ffffff;
  font-size: 1.25rem;
  cursor: pointer;
  opacity: 0.8;
}
.chatbot-close:hover { opacity: 1; }

/* Chat Body Style */
.chatbot-body {
  flex-grow: 1;
  padding: 16px;
  overflow-y: auto;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.chat-msg {
  display: flex;
  max-width: 85%;
}

.chat-msg.bot {
  align-self: flex-start;
}

.chat-msg.user {
  align-self: flex-end;
}

.msg-content {
  padding: 12px 16px;
  border-radius: 16px;
  font-size: 0.9rem;
  line-height: 1.5;
}

.chat-msg.bot .msg-content {
  background: #ffffff;
  color: #1e293b;
  border: 1px solid #e2e8f0;
  border-top-left-radius: 4px;
}

.chat-msg.user .msg-content {
  background: #064e3b;
  color: #ffffff;
  border-top-right-radius: 4px;
}

/* Quick Reply Chips */
.chat-chips {
  padding: 8px 16px;
  background: #f8fafc;
  display: flex;
  gap: 8px;
  overflow-x: auto;
  border-top: 1px solid #f1f5f9;
  white-space: nowrap;
}

.chat-chips::-webkit-scrollbar { display: none; }

.chip {
  background: #ffffff;
  color: #059669;
  border: 1px solid #a7f3d0;
  padding: 6px 12px;
  border-radius: 50px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
}

.chip:hover {
  background: #ecfdf5;
}

/* Footer / Input Style */
.chatbot-footer {
  padding: 12px 16px;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.chatbot-footer input {
  flex-grow: 1;
  border: 1px solid #cbd5e1;
  padding: 10px 14px;
  border-radius: 24px;
  font-size: 0.88rem;
  outline: none;
}

.chatbot-footer input:focus {
  border-color: #059669;
}

.chatbot-footer button {
  width: 40px;
  height: 40px;
  background: #064e3b;
  color: #ffffff;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.chatbot-footer button:hover {
  background: #047857;
}
</style>

<script src="<?= $assetPrefix ?>js/chatbot.js?v=<?= time() ?>"></script>