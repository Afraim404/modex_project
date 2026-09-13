// MODEX v3

// Navbar toggle
const navToggle = document.getElementById('navToggle');
const navLinks  = document.getElementById('navLinks');
if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    const open = navLinks.classList.toggle('open');
    navToggle.classList.toggle('open', open);
    navToggle.setAttribute('aria-expanded', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
  navLinks.querySelectorAll('a').forEach(l => l.addEventListener('click', () => {
    navLinks.classList.remove('open');
    navToggle.classList.remove('open');
    navToggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }));
  document.addEventListener('click', e => {
    if (navLinks.classList.contains('open') && !navLinks.contains(e.target) && !navToggle.contains(e.target)) {
      navLinks.classList.remove('open');
      navToggle.classList.remove('open');
      document.body.style.overflow = '';
    }
  });
}

// Active nav
const path = window.location.pathname;
document.querySelectorAll('.nav-links a').forEach(l => {
  const h = l.getAttribute('href');
  if (h === path || (path === '/' && h === '/') || (path !== '/' && h !== '/' && h.replace('.php','') !== '' && path.startsWith(h.replace('.php','')))) {
    l.style.color = 'var(--accent)';
  }
});

// Navbar darken on scroll; hide-on-scroll-down on mobile
let lastY = 0;
window.addEventListener('scroll', () => {
  const nb = document.querySelector('.navbar');
  if (!nb) return;
  const y = window.scrollY;
  nb.style.background = y > 40 ? 'rgba(10,10,10,.97)' : 'rgba(10,10,10,.88)';
  if (window.innerWidth <= 768) {
    nb.style.transform = (y > lastY && y > 80) ? 'translateY(-100%)' : 'translateY(0)';
    nb.style.transition = 'transform .3s ease, background .3s ease';
  }
  lastY = y;
}, { passive: true });

// Scroll reveal
window.addEventListener('DOMContentLoaded', () => {
  const els = document.querySelectorAll('.product-card,.process-step,.testimonial-card,.stat-item,.form-card,.detail-card,.tracking-step,.cart-item');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('revealed'); io.unobserve(e.target); } });
    }, { threshold: 0.05, rootMargin: '0px 0px -10px 0px' });
    els.forEach(el => { el.classList.add('will-reveal'); io.observe(el); });
  } else {
    els.forEach(el => el.classList.add('revealed'));
  }
});

// Lightbox
function openLightbox(src) {
  const lb = document.getElementById('lightbox');
  const img = document.getElementById('lightbox-img');
  if (!lb || !img) return;
  img.src = src;
  lb.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  const lb = document.getElementById('lightbox');
  if (lb) lb.classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// Product image thumbnail switch
function switchImg(el, src) {
  const main = document.getElementById('mainImg');
  if (main) main.src = src;
  document.querySelectorAll('.thumb-img').forEach(t => t.style.borderColor = 'var(--border)');
  el.style.borderColor = 'var(--accent)';
}

// Cart quantity controls (product detail page)
function detailChangeQty(delta) {
  const el = document.getElementById('detail-qty');
  if (!el) return;
  let v = parseInt(el.value) + delta;
  if (v < 1) v = 1;
  if (v > 99) v = 99;
  el.value = v;
}

// Cart quantity on cart page
function cartChangeQty(productId, delta) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/cart_action.php';
  const fields = { action: 'update', product_id: productId, delta: delta, redirect: '/cart.php' };
  Object.entries(fields).forEach(([k, v]) => {
    const i = document.createElement('input');
    i.type = 'hidden'; i.name = k; i.value = v;
    form.appendChild(i);
  });
  document.body.appendChild(form);
  form.submit();
}

// Update cart badge dynamically after add-to-cart
function updateCartBadge(count) {
  const badge = document.getElementById('cartBadge');
  const countEl = document.getElementById('cartCount');
  if (badge) badge.textContent = count > 0 ? count : '';
  if (countEl) countEl.textContent = count;
  const btn = document.getElementById('cartCount');
  if (btn) btn.classList.add('cart-pulse');
  setTimeout(() => { if (btn) btn.classList.remove('cart-pulse'); }, 400);
}

// Order form delivery zone auto-detect
// District list and delivery prices come from PHP via window.INSIDE_DHAKA_DISTRICTS / window.DELIVERY_INSIDE / window.DELIVERY_OUTSIDE
function updateDeliveryZone(district) {
  const districts = window.INSIDE_DHAKA_DISTRICTS || ['Dhaka','Gazipur','Narayanganj','Manikganj','Munshiganj','Narsingdi','Rajbari'];
  const inside = districts.includes(district);
  const zi = document.getElementById('zone_inside');
  const zo = document.getElementById('zone_outside');
  if (zi) zi.checked = inside;
  if (zo) zo.checked = !inside;
  recalcOrderTotal();
}

function recalcOrderTotal() {
  const radio = document.querySelector('input[name="is_inside_dhaka"]:checked');
  if (!radio) return;
  const insideCharge  = window.DELIVERY_INSIDE  || 80;
  const outsideCharge = window.DELIVERY_OUTSIDE || 120;
  const charge = radio.value === '1' ? insideCharge : outsideCharge;
  const subtotalEl = document.getElementById('order-subtotal');
  const subtotal = subtotalEl ? parseFloat(subtotalEl.dataset.subtotal || 0) : 0;
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  set('delivery-charge-display', '৳' + charge.toLocaleString());
  set('order-total', '৳' + (subtotal + charge).toLocaleString());
}

// Order form validation
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[name="is_inside_dhaka"]').forEach(r => r.addEventListener('change', recalcOrderTotal));
  recalcOrderTotal();

  const form = document.getElementById('orderForm');
  if (form) {
    form.addEventListener('submit', e => {
      const name     = document.getElementById('customer_name');
      const phone    = document.getElementById('customer_phone');
      const address  = document.getElementById('delivery_address');
      const district = document.getElementById('district');
      const errors   = [];
      if (!name    || name.value.trim().length < 2)    errors.push('Please enter your full name.');
      if (!phone   || !/^(\+?880|0)1[3-9]\d{8}$/.test(phone.value.trim())) errors.push('Enter a valid Bangladeshi phone number (e.g. 01XXXXXXXXX).');
      if (!address || address.value.trim().length < 10) errors.push('Please enter your full delivery address.');
      if (!district || !district.value)                 errors.push('Please select your district.');
      // Use the client-errors box (unique ID, not the server-errors box)
      const errBox = document.getElementById('client-errors');
      if (errors.length > 0) {
        e.preventDefault();
        if (errBox) {
          errBox.innerHTML = errors.map(m => `<p>&#9888; ${m}</p>`).join('');
          errBox.style.display = 'block';
          errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }
      if (errBox) errBox.style.display = 'none';
      const btn = document.getElementById('submitBtn');
      if (btn) { btn.disabled = true; btn.textContent = 'Placing order…'; }
    });
  }
});
