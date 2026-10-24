<h3 class="mb-4"><?= __('providers') ?></h3>
<div class="table-responsive">
<table class="table table-hover bg-white rounded shadow-sm">
<thead><tr><th><?= __('name') ?></th><th><?= __('profession') ?></th><th><?= __('rating') ?></th><th><?= __('completed') ?></th><th><?= __('verified') ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($providers as $p): ?>
<tr>
<td><?= e($p['name']) ?></td><td><?= e($p['profession'] ?? '-') ?></td>
<td><?= (float)$p['avg_rating'] ?> ★ (<?= $p['rating_count'] ?>)</td><td><?= $p['completed_jobs'] ?></td>
<td><span class="badge bg-<?= $p['is_verified'] ? 'success' : 'secondary' ?>"><?= $p['is_verified'] ? __('verified') : __('not_verified') ?></span></td>
<td>
<?php if ($p['is_verified']): ?>
<form method="POST" action="<?= url('/admin/providers/' . $p['id'] . '/unverify') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary"><?= __('unverify') ?></button></form>
<?php else: ?>
<form method="POST" action="<?= url('/admin/providers/' . $p['id'] . '/verify') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-success"><?= __('verify') ?></button></form>
<?php endif; ?>
<a href="<?= url('/providers/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary"><?= __('view') ?></a>
<a href="<?= url('/admin/providers/' . $p['id'] . '/evaluation') ?>" class="btn btn-sm btn-outline-warning"><?= __('platform_evaluation') ?></a>
<a href="<?= url('/admin/ai/provider/' . $p['id']) ?>" class="btn btn-sm btn-outline-info"><?= __('ai_analysis') ?></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
