<h3 class="mb-4"><?= __('profile') ?></h3>
<div class="card p-4 col-md-6">
  <form method="POST" action="<?= url('/customer/profile') ?>">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label"><?= __('name') ?></label><input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
    <div class="mb-3"><label class="form-label"><?= __('email') ?></label><input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled></div>
    <div class="mb-3"><label class="form-label"><?= __('phone') ?></label><input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>"></div>
    <button class="btn btn-primary"><?= __('submit') ?></button>
  </form>
</div>
