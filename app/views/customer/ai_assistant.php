<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="fw-bold mb-1"><i class="fa-solid fa-comment-dots text-primary"></i> مساعد مِهَن الذكي</h3>
    <p class="section-subtitle mb-0">اكتب مشكلتك أو سؤالك. يستخدم المساعد المزود الذي فعّله مدير النظام، مع fallback محلي عند الحاجة.</p>
  </div>
  <?php renderBackButton('/customer/dashboard', 'العودة للوحة التحكم'); ?>
</div>

<div class="surface-card p-3 p-md-4">
  <div id="chatLog" class="assistant-chat-log mb-3">
    <div class="chat-bubble bot">
      مرحبًا 👋 أنا مساعد مِهَن. صف مشكلتك وسأحاول اقتراح الخدمة المناسبة، مثال: "المكيف يعمل لكن لا يبرد".
    </div>
  </div>
  <form id="chatForm" class="d-flex gap-2">
    <?= csrf_field() ?>
    <input type="text" id="chatInput" class="form-control" placeholder="اكتب مشكلتك هنا..." autocomplete="off" required>
    <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>

<style>
.assistant-chat-log{max-height:420px;overflow-y:auto;display:flex;flex-direction:column;gap:.6rem;padding:.25rem}
.chat-bubble{max-width:80%;padding:.7rem 1rem;border-radius:14px;font-size:.92rem;line-height:1.6}
.chat-bubble.user{align-self:flex-end;background:var(--mihan-primary);color:#fff;border-bottom-left-radius:4px}
.chat-bubble.bot{align-self:flex-start;background:#f7faf9;border:1px solid var(--mihan-border);border-bottom-right-radius:4px}
.chat-bubble .badge{font-size:.7rem}
</style>

<script>
const chatLog = document.getElementById('chatLog');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const csrfToken = document.querySelector('#chatForm input[name="_csrf"]').value;

function addBubble(html, who) {
  const div = document.createElement('div');
  div.className = 'chat-bubble ' + who;
  div.innerHTML = html;
  chatLog.appendChild(div);
  chatLog.scrollTop = chatLog.scrollHeight;
}

chatForm.addEventListener('submit', function (e) {
  e.preventDefault();
  const text = chatInput.value.trim();
  if (!text) return;
  addBubble(text.replace(/</g, '&lt;'), 'user');
  chatInput.value = '';
  addBubble('<span class="text-muted"><i class="fa-solid fa-ellipsis fa-fade"></i> يحلل المساعد مشكلتك...</span>', 'bot');
  const thinkingBubble = chatLog.lastElementChild;

  fetch('<?= url('/customer/ai/chat') ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'message=' + encodeURIComponent(text) + '&_csrf=' + encodeURIComponent(csrfToken)
  }).then(r => r.json()).then(data => {
    thinkingBubble.remove();
    if (!data.ok) { addBubble(data.message || 'حدث خطأ أثناء التحليل.', 'bot'); return; }
    let engineLabel = data.provider || 'local';
    if (engineLabel === 'gemini') engineLabel = 'Google Gemini';
    if (engineLabel === 'local') engineLabel = 'Rule-Based AI';
    const modelLabel = data.model ? ' · ' + data.model : '';
    const attempted = data.fallback && data.attempted_provider
      ? '<br>النموذج المطلوب: ' + String(data.attempted_provider).replace(/</g,'&lt;') + (data.attempted_model ? ' · ' + String(data.attempted_model).replace(/</g,'&lt;') : '')
      : '';
    const reason = data.fallback_reason ? '<br>' + String(data.fallback_reason).replace(/</g,'&lt;') : '';
    addBubble((data.text || '').replace(/</g, '&lt;').replace(/\n/g, '<br>') + '<div class="small text-muted mt-2">المحرك الفعلي: ' + engineLabel + modelLabel + (data.fallback ? ' — تم التحويل إلى البديل المجاني' : '') + attempted + reason + '</div>', 'bot');
  }).catch(() => {
    thinkingBubble.remove();
    addBubble('حدث خطأ أثناء التحليل، حاول مرة أخرى.', 'bot');
  });
});
</script>
