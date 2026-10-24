<?php
$defaults = [
    'local' => ['model'=>'','base_url'=>'','temperature'=>.2,'max_tokens'=>700,'timeout'=>20,'is_enabled'=>1,'is_default'=>0,'system_prompt'=>''],
    'gemini' => ['model'=>'gemini-3.1-flash-lite','base_url'=>'https://generativelanguage.googleapis.com/v1beta','temperature'=>.2,'max_tokens'=>700,'timeout'=>20,'is_enabled'=>0,'is_default'=>0,'system_prompt'=>''],
    'openai' => ['model'=>'gpt-4o-mini','base_url'=>'https://api.openai.com/v1','temperature'=>.2,'max_tokens'=>700,'timeout'=>20,'is_enabled'=>0,'is_default'=>0,'system_prompt'=>''],
    'groq' => ['model'=>'','base_url'=>'https://api.groq.com/openai/v1','temperature'=>.2,'max_tokens'=>700,'timeout'=>20,'is_enabled'=>0,'is_default'=>0,'system_prompt'=>''],
    'openai_compatible' => ['model'=>'','base_url'=>'','temperature'=>.2,'max_tokens'=>700,'timeout'=>20,'is_enabled'=>0,'is_default'=>0,'system_prompt'=>''],
];
$providerData=[];
foreach($providers as $key=>$label){
    $s=array_merge($defaults[$key]??$defaults['openai_compatible'],$settings[$key]??[]);
    if($key==='gemini'){$s['model']=$s['model']?:'gemini-3.1-flash-lite';$s['base_url']=$s['base_url']?:'https://generativelanguage.googleapis.com/v1beta';}
    if($key==='openai')$s['base_url']=$s['base_url']?:'https://api.openai.com/v1';
    if($key==='groq')$s['base_url']=$s['base_url']?:'https://api.groq.com/openai/v1';
    $providerData[$key]=[
        'model'=>(string)($s['model']??''),'base_url'=>(string)($s['base_url']??''),
        'temperature'=>(string)($s['temperature']??.2),'max_tokens'=>(string)($s['max_tokens']??700),
        'timeout'=>(string)($s['timeout']??20),'system_prompt'=>(string)($s['system_prompt']??''),
        'is_enabled'=>(int)($s['is_enabled']??0),'is_default'=>(int)($s['is_default']??0),
        'configured'=>!empty($s['api_key_encrypted']) || $key==='local'
    ];
}
$activeProvider=$active['provider']??'local';
if(!isset($providerData[$activeProvider]))$activeProvider=array_key_first($providers);
$stats=$stats??['total'=>0,'success'=>0,'failed'=>0,'fallback'=>0,'avg_ms'=>0,'today'=>0,'by_provider'=>[],'recent'=>[]];
$successRate=$stats['total']>0?round(($stats['success']/$stats['total'])*100,1):0;
$labels=[
    'local'=>'المحلي','gemini'=>'Google Gemini','openai'=>'OpenAI','groq'=>'Groq','openai_compatible'=>'OpenAI Compatible'
];
$operationLabels=['chat'=>'محادثة','analyze'=>'تحليل','connection_test'=>'اختبار اتصال'];
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h3 class="mb-1"><i class="fa-solid fa-brain"></i> لوحة إدارة الذكاء الاصطناعي</h3>
    <div class="text-muted small">إدارة النماذج، اختبار الاتصال، متابعة السجل وإحصائيات الاستخدام من لوحة واحدة.</div>
  </div>
  <span class="badge bg-primary fs-6 px-3 py-2">لوحة موحدة</span>
</div>

<div class="alert alert-warning small">
  <strong>أمان:</strong> مفاتيح API مشفرة على الخادم ولا تُعرض في الصفحة. اختبار النموذج المحدد يستخدم القيم الموجودة حاليًا في الحقول، أما «اختبار جميع النماذج» فيستخدم الإعدادات المحفوظة.<br>
  <strong>Fallback مجاني دائم:</strong> Rule-Based AI يعمل دائمًا كبديل تلقائي. إذا انتهت حصة Gemini أو أعاد المزود الخارجي خطأ مثل HTTP 429، يستمر النظام تلقائيًا بالمحرك المحلي المجاني دون إيقاف الميزة.
</div>

<!-- Usage statistics -->
<div class="row g-3 mb-4">
  <?php $cards=[
    ['إجمالي العمليات',$stats['total'],'fa-chart-line'],
    ['عمليات اليوم',$stats['today'],'fa-calendar-day'],
    ['نسبة النجاح',$successRate.'%','fa-circle-check'],
    ['Fallback إلى المحلي',$stats['fallback'],'fa-shield-halved'],
    ['متوسط الاستجابة',$stats['avg_ms'].' ms','fa-stopwatch'],
  ]; foreach($cards as $card): ?>
    <div class="col-6 col-md-4 col-lg">
      <div class="card stat-card p-3 h-100">
        <div class="text-muted small"><i class="fa-solid <?= e($card[2]) ?> me-1"></i><?= e($card[0]) ?></div>
        <h4 class="mt-2 mb-0"><?= e((string)$card[1]) ?></h4>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <!-- Model management -->
    <div class="card p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h5 class="mb-1">إدارة النماذج</h5><div class="small text-muted">اختر مزودًا واحدًا واضبطه من نفس اللوحة.</div></div>
        <span id="activeBadge" class="badge bg-success <?= !empty($providerData[$activeProvider]['is_default']) ? '' : 'd-none' ?>">النموذج النشط</span>
      </div>

      <form method="POST" action="<?= url('/admin/ai/save') ?>" id="aiProviderForm">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-md-7">
            <label class="form-label">مزود الذكاء الاصطناعي</label>
            <select name="provider" id="aiProvider" class="form-select" required>
              <?php foreach($providers as $key=>$label): ?>
                <option value="<?= e($key) ?>" <?= $key===$activeProvider?'selected':'' ?>><?= e($label) ?><?= !empty($providerData[$key]['is_default'])?' — نشط':'' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-5 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="aiEnabled" <?= !empty($providerData[$activeProvider]['is_enabled'])?'checked':'' ?>>
              <label class="form-check-label" for="aiEnabled">تفعيل كنموذج أساسي</label>
            </div>
          </div>
        </div>

        <div id="apiKeyWrap" class="mb-3 mt-3">
          <label class="form-label">API Key</label>
          <input type="password" name="api_key" id="aiApiKey" class="form-control" placeholder="اتركه فارغًا للاحتفاظ بالمفتاح الحالي" autocomplete="new-password">
          <div class="form-text">لا يظهر المفتاح المحفوظ ولا يتم إرساله ضمن بيانات العرض.</div>
        </div>

        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Model ID</label><input name="model" id="aiModel" class="form-control" placeholder="مثال: gemini-3.1-flash-lite"></div>
          <div class="col-md-6"><label class="form-label">Base URL</label><input name="base_url" id="aiBaseUrl" class="form-control" placeholder="https://generativelanguage.googleapis.com/v1beta"></div>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-md-4"><label class="form-label">Temperature</label><input type="number" step="0.1" min="0" max="2" name="temperature" id="aiTemperature" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Max Tokens</label><input type="number" min="100" max="8000" name="max_tokens" id="aiMaxTokens" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Timeout</label><input type="number" min="5" max="60" name="timeout" id="aiTimeout" class="form-control"></div>
        </div>
        <div class="mt-3"><label class="form-label">System Prompt</label><textarea name="system_prompt" id="aiSystemPrompt" class="form-control" rows="4" placeholder="تعليمات عامة للمساعد..."></textarea></div>
        <div class="d-flex flex-wrap gap-2 mt-4">
          <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> حفظ إعدادات النموذج</button>
          <button type="button" class="btn btn-outline-success" id="testAi"><i class="fa-solid fa-plug"></i> اختبار النموذج المحدد</button>
        </div>
        <div id="testResult" class="small mt-2" aria-live="polite"></div>
      </form>
    </div>
  </div>

  <div class="col-lg-5">
    <!-- Model testing -->
    <div class="card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h5 class="mb-1"><i class="fa-solid fa-vial"></i> اختبار النماذج</h5><div class="small text-muted">اختبر جميع الإعدادات المحفوظة دفعة واحدة.</div></div>
        <button type="button" class="btn btn-sm btn-outline-success" id="testAllAi">اختبار الجميع</button>
      </div>
      <div id="allTestResult" class="small mb-2" aria-live="polite"></div>
      <div class="list-group list-group-flush">
        <?php foreach($providers as $key=>$label): $s=$providerData[$key]; ?>
          <div class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
            <div>
              <div class="fw-semibold"><?= e($label) ?> <?php if(!empty($s['is_default'])): ?><span class="badge bg-success">نشط</span><?php endif; ?></div>
              <div class="small text-muted">
                <?= $key==='local'?'لا يحتاج API Key':(!empty($s['configured'])?'مهيأ':'غير مهيأ') ?>
                <?php if(!empty($s['model'])): ?> · <?= e($s['model']) ?><?php endif; ?>
              </div>
            </div>
            <span class="badge rounded-pill bg-light text-dark border" data-test-status="<?= e($key) ?>">—</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Provider statistics -->
    <div class="card p-4">
      <h5 class="mb-3"><i class="fa-solid fa-chart-pie"></i> الاستخدام حسب النموذج</h5>
      <?php if(empty($stats['by_provider'])): ?>
        <div class="text-muted small">لا توجد عمليات مسجلة بعد.</div>
      <?php else: ?>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
          <thead><tr><th>النموذج</th><th>العمليات</th><th>نجاح</th><th>Fallback</th><th>متوسط</th></tr></thead>
          <tbody>
          <?php foreach($stats['by_provider'] as $row): ?>
            <tr><td><?= e($labels[$row['provider']]??$row['provider']) ?></td><td><?= (int)$row['total'] ?></td><td><?= (int)$row['success'] ?></td><td><?= (int)$row['fallback'] ?></td><td><?= (int)$row['avg_ms'] ?> ms</td></tr>
          <?php endforeach; ?>
          </tbody>
        </table></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Recent AI log -->
<div class="card p-4 mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div><h5 class="mb-1"><i class="fa-solid fa-clock-rotate-left"></i> سجل الذكاء الاصطناعي</h5><div class="small text-muted">آخر 15 عملية، بما فيها اختبارات الاتصال.</div></div>
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()"><i class="fa-solid fa-rotate"></i> تحديث</button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>الوقت</th><th>المستخدم</th><th>المزود</th><th>العملية</th><th>النتيجة</th><th>Fallback</th><th>الزمن</th><th>الخطأ</th></tr></thead>
      <tbody>
      <?php if(empty($stats['recent'])): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">لا توجد عمليات مسجلة بعد.</td></tr>
      <?php else: foreach($stats['recent'] as $l): ?>
        <tr>
          <td class="small"><?= e($l['created_at']) ?></td>
          <td><?= e($l['name']??'النظام') ?></td>
          <td><?= e($labels[$l['provider']]??$l['provider']) ?></td>
          <td><?= e($operationLabels[$l['operation']]??$l['operation']) ?></td>
          <td><?= !empty($l['success'])?'<span class="text-success">✓ ناجح</span>':'<span class="text-danger">✕ فشل</span>' ?></td>
          <td><?= !empty($l['fallback_used'])?'نعم':'لا' ?></td>
          <td><?= (int)$l['duration_ms'] ?> ms</td>
          <td class="small text-danger"><?= e($l['error_message']??'') ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const AI_PROVIDERS = <?= json_encode($providerData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
const form=document.getElementById('aiProviderForm'), provider=document.getElementById('aiProvider'), apiKeyWrap=document.getElementById('apiKeyWrap'), apiKey=document.getElementById('aiApiKey'), enabled=document.getElementById('aiEnabled'), model=document.getElementById('aiModel'), baseUrl=document.getElementById('aiBaseUrl'), temperature=document.getElementById('aiTemperature'), maxTokens=document.getElementById('aiMaxTokens'), timeout=document.getElementById('aiTimeout'), systemPrompt=document.getElementById('aiSystemPrompt'), activeBadge=document.getElementById('activeBadge'), result=document.getElementById('testResult');
function loadProvider(key){const s=AI_PROVIDERS[key]||{};model.value=s.model||'';baseUrl.value=s.base_url||'';temperature.value=s.temperature||'0.2';maxTokens.value=s.max_tokens||'700';timeout.value=s.timeout||'20';systemPrompt.value=s.system_prompt||'';enabled.checked=Number(s.is_enabled||0)===1;apiKey.value='';const local=key==='local';apiKeyWrap.classList.toggle('d-none',local);model.closest('.col-md-6').classList.toggle('d-none',local);baseUrl.closest('.col-md-6').classList.toggle('d-none',local);activeBadge.classList.toggle('d-none',Number(s.is_default||0)!==1);result.textContent='';result.className='small mt-2';}
provider.addEventListener('change',()=>loadProvider(provider.value));loadProvider(provider.value);

document.getElementById('testAi').addEventListener('click',async()=>{result.textContent='جارٍ الاختبار...';result.className='small mt-2 text-muted';const body=new URLSearchParams(new FormData(form));try{const r=await fetch('<?= url('/admin/ai/test') ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},body});const j=await r.json();result.textContent=(j.ok?'✓ ':'✕ ')+(j.message||'');result.className='small mt-2 '+(j.ok?'text-success':'text-danger');}catch(e){result.textContent='تعذر إكمال الاختبار.';result.className='small mt-2 text-danger';}});

document.getElementById('testAllAi').addEventListener('click',async()=>{const box=document.getElementById('allTestResult'),btn=document.getElementById('testAllAi');btn.disabled=true;box.textContent='جارٍ اختبار جميع النماذج المحفوظة...';box.className='small mb-2 text-muted';document.querySelectorAll('[data-test-status]').forEach(x=>x.textContent='…');try{const fd=new FormData(form);fd.set('provider',provider.value);const r=await fetch('<?= url('/admin/ai/test-all') ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:fd});const j=await r.json();if(!j.ok)throw new Error(j.message||'فشل الاختبار');let ok=0;for(const item of (j.results||[])){const el=document.querySelector('[data-test-status="'+item.provider+'"]');if(el){el.textContent=item.status==='not_configured'?'غير مهيأ':(item.ok?'✓ ناجح':'✕ فشل');el.className='badge rounded-pill '+(item.status==='not_configured'?'bg-light text-dark border':(item.ok?'bg-success':'bg-danger'));}if(item.ok)ok++;}box.textContent='تم اختبار '+(j.results||[]).length+' نماذج — نجح '+ok+' منها.';box.className='small mb-2 text-success';setTimeout(()=>location.reload(),1200);}catch(e){box.textContent=e.message||'تعذر إكمال الاختبار الجماعي.';box.className='small mb-2 text-danger';}finally{btn.disabled=false;}});
</script>
