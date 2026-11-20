// 1. Стили (SASS -> CSS через Webpack)
import './scss/styles.scss';

// 2. Bootstrap JS (нужен для Modal, Popover, Toast, Collapse)
import * as bootstrap from 'bootstrap';

// 3. Инициализация Popover
document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
  new bootstrap.Popover(el, { trigger: 'hover focus' });
});

// 4. Toast при клике на «Загрузить»
const loadBtn = document.getElementById('loadBtn');
const loadToastEl = document.getElementById('loadToast');
if (loadBtn && loadToastEl) {
  const toast = new bootstrap.Toast(loadToastEl, { delay: 3500 });
  loadBtn.addEventListener('click', () => toast.show());
}

// 5. РАСШИРЕННЫЙ УРОВЕНЬ: переключение модалок стрелками ← →
const modalIds = ['modal1', 'modal2', 'modal3', 'modal4', 'modal5'];
let currentIndex = -1;

modalIds.forEach((id, idx) => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener('shown.bs.modal', () => { currentIndex = idx; });
  el.addEventListener('hidden.bs.modal', () => { currentIndex = -1; });
});

document.addEventListener('keydown', (e) => {
  if (currentIndex === -1) return;
  if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;

  e.preventDefault();

  const step = e.key === 'ArrowRight' ? 1 : -1;
  const nextIdx = (currentIndex + step + modalIds.length) % modalIds.length;

  const currentEl = document.getElementById(modalIds[currentIndex]);
  const nextEl    = document.getElementById(modalIds[nextIdx]);

  const currentInstance = bootstrap.Modal.getInstance(currentEl);
  const nextInstance    = bootstrap.Modal.getOrCreateInstance(nextEl);

  currentEl.addEventListener('hidden.bs.modal', () => {
    nextInstance.show();
  }, { once: true });

  currentInstance.hide();
});
