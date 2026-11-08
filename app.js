'use strict';
document.addEventListener('DOMContentLoaded', () => {
  const toggle=document.querySelector('.menu-toggle');
  const nav=document.querySelector('#primary-nav');
  if(toggle && nav){
    toggle.addEventListener('click',()=>{
      const open=toggle.getAttribute('aria-expanded')==='true';
      toggle.setAttribute('aria-expanded',String(!open));nav.classList.toggle('is-open',!open);
    });
    nav.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{nav.classList.remove('is-open');toggle.setAttribute('aria-expanded','false');}));
  }
  document.querySelectorAll('.flash-close').forEach(button=>button.addEventListener('click',()=>button.closest('.flash')?.remove()));
  document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{
    if(!window.confirm(form.getAttribute('data-confirm')||'Confirm this action?'))event.preventDefault();
  }));
  document.querySelectorAll('[data-auto-submit]').forEach(el=>el.addEventListener('change',()=>el.form?.submit()));
});
