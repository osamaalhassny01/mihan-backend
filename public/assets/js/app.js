document.addEventListener('DOMContentLoaded',()=>{
 const sidebar=document.querySelector('.dash-sidebar');
 const overlay=document.querySelector('.mobile-overlay');
 document.querySelectorAll('[data-sidebar-toggle]').forEach(b=>b.addEventListener('click',()=>{sidebar?.classList.toggle('open');overlay?.classList.toggle('show')}));
 overlay?.addEventListener('click',()=>{sidebar?.classList.remove('open');overlay?.classList.remove('show')});
 document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el=>{if(window.bootstrap)new bootstrap.Tooltip(el)});
});
