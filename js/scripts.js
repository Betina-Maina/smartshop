/* ============================================================
   SmartShop – Main JavaScript
   Handles: dark mode, AJAX cart, toasts, qty controls, CSRF
   ============================================================ */

/* ---------- Dark Mode ---------- */
(function () {
  // Apply saved preference before DOM paint to avoid flash
  if (localStorage.getItem('ss-dark') === '1') {
    document.documentElement.classList.add('dark-pending');
  }
})();

document.addEventListener('DOMContentLoaded', function () {
  // Apply dark class to body (not html) for CSS vars
  if (localStorage.getItem('ss-dark') === '1') {
    document.body.classList.add('dark');
  }

  const btn = document.getElementById('darkToggle');
  if (btn) {
    updateDarkBtn(btn);
    btn.addEventListener('click', function () {
      document.body.classList.toggle('dark');
      const isDark = document.body.classList.contains('dark');
      localStorage.setItem('ss-dark', isDark ? '1' : '0');
      updateDarkBtn(btn);
    });
  }

  // Inject CSRF token into all AJAX requests via meta tag
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  window.csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

  // Initialize quantity controls
  initQtyControls();

  // Initialize admin sidebar toggle
  initSidebar();

  // Auto-dismiss alerts after 5s
  document.querySelectorAll('.alert-auto-dismiss').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 5000);
  });
});

function updateDarkBtn(btn) {
  const isDark = document.body.classList.contains('dark');
  btn.innerHTML = isDark ? '☀️' : '🌙';
  btn.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
}

/* ---------- Toast Notifications ---------- */
function showToast(message, type) {
  type = type || 'info';
  var container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  var icons = { success: '✓', danger: '✕', info: 'ℹ', warning: '⚠' };
  var toast = document.createElement('div');
  toast.className = 'toast-item toast-' + type;
  toast.innerHTML =
    '<span>' + (icons[type] || 'ℹ') + '</span>' +
    '<span>' + escapeHtml(message) + '</span>' +
    '<button class="toast-close" aria-label="Close">×</button>';

  container.appendChild(toast);

  toast.querySelector('.toast-close').addEventListener('click', function () {
    dismissToast(toast);
  });

  setTimeout(function () { dismissToast(toast); }, 4500);
}

function dismissToast(toast) {
  toast.style.animation = 'fadeOut .3s ease forwards';
  setTimeout(function () { toast.remove(); }, 300);
}

function escapeHtml(str) {
  var d = document.createElement('div');
  d.appendChild(document.createTextNode(str));
  return d.innerHTML;
}

/* ---------- AJAX Add to Cart ---------- */
function addToCart(productId, qty, btn) {
  qty = qty || 1;

  // Show loading state on button
  if (btn) {
    btn.disabled = true;
    btn.dataset.originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Adding…';
  }

  fetch('/smartshop/ajax/add_to_cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      product_id: productId,
      quantity: qty,
      csrf_token: window.csrfToken || ''
    })
  })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (data.success) {
        showToast('Added to cart!', 'success');
        updateCartBadge(data.cart_count);
      } else if (data.redirect) {
        window.location.href = data.redirect;
      } else {
        showToast(data.message || 'Could not add to cart.', 'danger');
      }
    })
    .catch(function () {
      showToast('Network error. Please try again.', 'danger');
    })
    .finally(function () {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.originalText || 'Add to Cart';
      }
    });
}

/* Update cart badge count in navbar */
function updateCartBadge(count) {
  var badge = document.getElementById('cartBadge');
  if (!badge) return;
  if (count > 0) {
    badge.textContent = count;
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }
}

/* ---------- Quantity Controls ---------- */
function initQtyControls() {
  document.querySelectorAll('.qty-control').forEach(function (ctrl) {
    var input = ctrl.querySelector('input[type="number"]');
    var dec   = ctrl.querySelector('[data-action="dec"]');
    var inc   = ctrl.querySelector('[data-action="inc"]');
    if (!input) return;

    if (dec) dec.addEventListener('click', function () {
      var v = parseInt(input.value, 10) || 1;
      if (v > 1) { input.value = v - 1; input.dispatchEvent(new Event('change')); }
    });

    if (inc) inc.addEventListener('click', function () {
      var v = parseInt(input.value, 10) || 1;
      var max = parseInt(input.max, 10) || 999;
      if (v < max) { input.value = v + 1; input.dispatchEvent(new Event('change')); }
    });
  });
}

/* ---------- Admin Sidebar Toggle ---------- */
function initSidebar() {
  var toggle = document.getElementById('sidebarToggle');
  var sidebar = document.querySelector('.admin-sidebar');
  if (!toggle || !sidebar) return;

  toggle.addEventListener('click', function () {
    sidebar.classList.toggle('open');
  });

  // Close sidebar when clicking outside on mobile
  document.addEventListener('click', function (e) {
    if (sidebar.classList.contains('open') &&
        !sidebar.contains(e.target) &&
        e.target !== toggle) {
      sidebar.classList.remove('open');
    }
  });
}

/* ---------- Confirm Delete ---------- */
function confirmDelete(msg) {
  return confirm(msg || 'Are you sure you want to delete this item? This cannot be undone.');
}

/* ---------- Image Preview on File Input ---------- */
function previewImage(input, previewId) {
  var preview = document.getElementById(previewId);
  if (!preview || !input.files || !input.files[0]) return;
  var reader = new FileReader();
  reader.onload = function (e) {
    preview.src = e.target.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(input.files[0]);
}

/* ---------- Search Form: clear empty params ---------- */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[data-clean-params]').forEach(function (form) {
    form.addEventListener('submit', function () {
      Array.from(form.elements).forEach(function (el) {
        if (el.name && !el.value) el.disabled = true;
      });
    });
  });
});
