<?php
$role=Auth::role(); $current=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH) ?: '/';
$links=[];
if($role==='customer') $links=[['fa-gauge-high',__('dashboard'),'/customer/dashboard'],['fa-list-check',__('my_requests'),'/customer/requests'],['fa-file-invoice-dollar','المالية والفواتير','/customer/finance'],['fa-plus-circle',__('new_request'),'/customer/requests/create'],['fa-comment-dots',__('smart_assistant'),'/customer/ai/assistant'],['fa-user-gear',__('search'),'/providers'],['fa-triangle-exclamation',__('complaints'),'/customer/complaints'],['fa-user',__('profile'),'/customer/profile']];
elseif($role==='provider') $links=[['fa-gauge-high',__('dashboard'),'/provider/dashboard'],['fa-inbox',__('incoming_requests'),'/provider/requests'],['fa-wallet','المالية والمدفوعات','/provider/finance'],['fa-star',__('reviews'),'/provider/reviews'],['fa-id-card',__('professional_profile'),'/provider/profile']];
elseif($role==='employee') $links=[['fa-gauge-high','لوحة الموظف','/employee/dashboard'],['fa-clipboard-list','متابعة الطلبات','/employee/requests'],['fa-user-tie','مقدمو الخدمة','/employee/providers'],['fa-comments','محادثات الطلبات','/employee/conversations'],['fa-star','التقييمات','/employee/reviews'],['fa-triangle-exclamation','الشكاوى','/employee/complaints'],['fa-file-invoice-dollar','المتابعة المالية','/employee/finance'],['fa-bell','الإشعارات','/employee/notifications']];
elseif($role==='admin') $links=[['fa-gauge-high',__('dashboard'),'/admin/dashboard'],['fa-users',__('users'),'/admin/users'],['fa-user-shield','موظفو المنصة','/admin/employees'],['fa-user-tie',__('providers'),'/admin/providers'],['fa-star-half-stroke',__('review_management'),'/admin/reviews'],['fa-layer-group',__('categories_services'),'/admin/categories'],['fa-map-location-dot',__('locations'),'/admin/locations'],['fa-clipboard-list',__('requests'),'/admin/requests'],['fa-triangle-exclamation',__('complaints'),'/admin/complaints'],['fa-file-invoice-dollar',__('finance'),'/admin/finance'],['fa-chart-line',__('reports_analytics'),'/admin/reports'],['fa-bell',__('notifications'),'/admin/notifications'],['fa-brain',__('ai'),'/admin/ai'],['fa-gear',__('settings'),'/admin/settings']];
?>
<aside class="dash-sidebar">
 <div class="sidebar-brand"><a href="<?= url('/') ?>" class="sidebar-brand-link"><img class="sidebar-logo" src="<?= asset('images/logo-mihan-original.png') ?>" alt="<?= e(__('app_name')) ?>"><span class="sidebar-brand-text"><?= e(setting('site_name', __('app_name'))) ?><small><?= __('professional_services_platform') ?></small></span></a></div>
 <ul class="sidebar-nav"><li class="sidebar-section"><?= __('main_menu') ?></li>
 <?php foreach($links as $link): $active=str_starts_with($current,rtrim(BASE_PATH.$link[2],'/')); ?><li><a class="<?= $active?'active':'' ?>" href="<?= url($link[2]) ?>"><i class="fa-solid <?= $link[0] ?>"></i><span><?= e($link[1]) ?></span></a></li><?php endforeach; ?>
 </ul>
</aside>
<div class="mobile-overlay"></div>
