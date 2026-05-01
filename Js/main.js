(function() {
  'use strict';
  
  if (window._schecterInitialized) return;
  window._schecterInitialized = true;

  function formatPrice(price) {
    return parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }
  
  function getCart() {
    try {
      return JSON.parse(localStorage.getItem('userCart')) || [];
    } catch (e) {
      return [];
    }
  }
  
  function saveCart(cart) {
    localStorage.setItem('userCart', JSON.stringify(cart));
  }

  function showMessage(element, text, type) {
    if (!element) return;
    element.textContent = text;
    element.className = 'auth-message ' + type;
    element.style.display = 'block';
    element.style.color = type === 'success' ? '#28a745' : '#dc3545';
    setTimeout(() => { element.style.display = 'none'; }, 5000);
  }

  window.isLoggedIn = function() {
    return !!(localStorage.getItem('schecterCurrentUser') || 
              sessionStorage.getItem('schecterCurrentUser'));
  };
  
  window.getCurrentUser = function() {
    const stored = localStorage.getItem('schecterCurrentUser') || 
                   sessionStorage.getItem('schecterCurrentUser');
    return stored ? JSON.parse(stored) : null;
  };
  
  window.logout = function() {
    localStorage.removeItem('schecterCurrentUser');
    sessionStorage.removeItem('schecterCurrentUser');
    if (typeof window.updateAuthNav === 'function') window.updateAuthNav();
    if (typeof window.updateDynamicHeader === 'function') window.updateDynamicHeader();
    if (typeof window.updateCartBadge === 'function') window.updateCartBadge();
    if (window.location.href.includes('account.html')) {
      window.location.href = 'index.html';
    }
  };

  window.updateDynamicHeader = function() {
    const isLoggedIn = window.isLoggedIn();
    const authLinks = document.querySelectorAll('header a[href*="login"], header a[href*="signup"], #authLink, .header-auth-link');
    
    authLinks.forEach(link => {
      const href = link.getAttribute('href') || '';
      if (href.includes('login.php') || href.includes('signup.php') || link.id === 'authLink' || link.classList.contains('header-auth-link')) {
        if (isLoggedIn) {
          link.textContent = 'My Account';
          const basePath = window.location.pathname.includes('/html/') || window.location.pathname.includes('/php/') ? '../html/' : 'html/';
          link.href = basePath + 'account.html';
        } else {
          link.textContent = 'Sign In';
          const basePath = window.location.pathname.includes('/html/') || window.location.pathname.includes('/php/') ? '../php/' : 'php/';
          link.href = basePath + 'login.php';
        }
      }
    });
  };

  window.updateAuthNav = function() {
    const user = window.getCurrentUser();
    
    document.querySelectorAll('#myOffcanvasNav a').forEach(function(link) {
      var href = link.getAttribute('href') || '';
      var isLogin = href.indexOf('login.php') !== -1;
      var isSignup = href.indexOf('signup.php') !== -1;
      
      if (isLogin || isSignup) {
        if (user) {
          link.textContent = 'My Account';
          var _p = window.location.pathname;
          link.href = _p.includes('/html/') ? 'account.html'
                    : _p.includes('/php/')  ? '../html/account.html'
                    :                         'html/account.html';
        } else {
          link.textContent = isSignup ? 'Sign Up' : 'Sign In';
          link.href = isSignup 
            ? (window.location.pathname.includes('php/') ? 'signup.php' : 'php/signup.php')
            : (window.location.pathname.includes('php/') ? 'login.php' : 'php/login.php');
        }
        link.onclick = function(e) {
          if (link.href === window.location.href) {
            e.preventDefault();
            if (typeof closeNav === 'function') closeNav();
          }
        };
      }
    });
    
    var authLink = document.getElementById('authLink');
    if (authLink) {
      if (user) {
        authLink.textContent = 'My Account';
        authLink.href = window.location.pathname.includes('php/') ? '../html/account.html' : 'html/account.html';
      } else {
        authLink.textContent = 'Sign In';
        authLink.href = window.location.pathname.includes('html/') ? '../php/login.php' : 'php/login.php';
      }
    }
    
    document.querySelectorAll('[data-action="logout"], #signOutBtn, .logout-btn').forEach(function(btn) {
      btn.onclick = function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to sign out?')) {
          window.logout();
        }
      };
    });
  };

  window.updateCartBadge = function() {
    const cart = getCart();
    const total = cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 1), 0);
    document.querySelectorAll('.cart-count, #cart-count, [data-cart-badge]').forEach(badge => {
      badge.textContent = total;
    });
  };

  window.addToCart = function(id, name, price, image, quantity = 1) {
    if (!id || !name) return false;
    const numericPrice = parseFloat(price);
    if (isNaN(numericPrice)) return false;
    
    let cart = getCart();
    const idx = cart.findIndex(item => String(item.id) === String(id));
    
    if (idx > -1) {
      cart[idx].quantity = (parseInt(cart[idx].quantity) || 1) + quantity;
    } else {
      cart.push({ 
        id: String(id), 
        name: String(name), 
        price: numericPrice, 
        image: image || '', 
        quantity: parseInt(quantity) || 1 
      });
    }
    
    saveCart(cart);
    window.updateCartBadge();
    return true;
  };

  window.removeItem = function(index) {
    let cart = getCart();
    if (cart[index]) {
      cart.splice(index, 1);
      saveCart(cart);
      if (typeof window.renderCartDisplay === 'function') window.renderCartDisplay();
      window.updateCartBadge();
    }
  };
  
  window.changeQuantity = function(index, delta) {
    let cart = getCart();
    if (!cart[index]) return;
    cart[index].quantity = Math.max(1, (parseInt(cart[index].quantity) || 1) + delta);
    saveCart(cart);
    if (typeof window.renderCartDisplay === 'function') window.renderCartDisplay();
    window.updateCartBadge();
  };

window.renderCartDisplay = function() {
  const container = document.getElementById('cartContainer');
  const summary = document.getElementById('cartSummary');
  const totalEl = document.getElementById('cartTotal');
  if (!container) return;

  if (window.location.pathname.includes('cart.php')) {
    fetch('cart.php', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'fetch' })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success && data.items) {
        localStorage.setItem('userCart', JSON.stringify(data.items));
        renderCartFromItems(data.items);
        return;
      }
      renderCartFromItems(getCart());
    })
    .catch(err => {
      console.log('Cart fetch failed:', err);
      renderCartFromItems(getCart());
    });
    return;
  }

  renderCartFromItems(getCart());

  function renderCartFromItems(cart) {
    if (cart.length === 0) {
      container.innerHTML = '<div class="empty-cart"><p>Your cart is empty.</p><p><a href="products.html">Continue Shopping</a></p></div>';
      if (summary) summary.style.display = 'none';
      if (totalEl) totalEl.textContent = '0.00';
      window.updateCartBadge();
      return;
    }

    let html = '<div class="cart-items">';
    let total = 0;

cart.forEach(function(item, index) {
  const price = parseFloat(item.price) || 0;
  const qty = parseInt(item.quantity) || 1;
  const itemTotal = price * qty;
  total += itemTotal;

  const rawImg = item.image || '';
  let fixedImg = rawImg;
  
  if (rawImg && !rawImg.startsWith('http') && !rawImg.startsWith('//')) {
    let cleanPath = rawImg.replace(/^\.\.\/+|^\/+/, '');
    
    fixedImg = '../' + cleanPath;
  } else if (!rawImg) {
    fixedImg = '../images/placeholder.jpg';
  }

  html += '<div class="cart-item" data-index="' + index + '">' +
    '<img src="' + fixedImg + '" alt="' + item.name + '" class="cart-item-image" onerror="this.src=\'../images/placeholder.jpg\'">' +
    '<div class="cart-item-info">' +
      '<h3><a href="' + item.id + '.html">' + item.name + '</a></h3>' +
      '<div class="cart-item-price">Price: $' + formatPrice(price) + '</div>' +
      '<div class="cart-item-quantity">Quantity: ' + qty + '</div>' +
      '<div class="cart-item-price">Subtotal: $' + formatPrice(itemTotal) + '</div>' +
      '<button class="remove-btn" data-index="' + index + '" data-id="' + item.id + '">Remove</button>' +
    '</div>' +
  '</div>';
});
    html += '</div>';
    container.innerHTML = html;

    if (totalEl) totalEl.textContent = formatPrice(total);
    if (summary) summary.style.display = 'block';

    document.querySelectorAll('.remove-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const index = parseInt(this.getAttribute('data-index'));
        const productId = this.getAttribute('data-id');

        if (window.location.pathname.includes('cart.php') && productId) {
          fetch('cart.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', product_id: productId })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              window.renderCartDisplay(); 
            }
          });
        } else {
          window.removeItem(index); 
        }
      });
    });

    window.updateCartBadge();
  }
};
  function initImageGallery() {
    document.querySelectorAll('.image-gallery').forEach(gallery => {
      const mainImg = gallery.querySelector('.main-image img');
      const thumbs = gallery.querySelectorAll('.thumbnails img');
      if (!mainImg || !thumbs.length) return;
      
      if (thumbs[0]) thumbs[0].classList.add('active');
      
      thumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
          const newSrc = this.dataset.full || this.src;
          mainImg.style.opacity = '0';
          setTimeout(() => {
            mainImg.src = newSrc;
            mainImg.onload = () => { mainImg.style.opacity = '1'; };
          }, 300);
          thumbs.forEach(t => t.classList.remove('active'));
          this.classList.add('active');
        });
      });
    });
  }

  function attachListeners() {
    document.querySelectorAll('.add-to-cart').forEach(btn => {
      if (btn._attached) return;
      btn._attached = true;
      
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const success = window.addToCart(
          this.dataset.id,
          this.dataset.name,
          this.dataset.price,
          this.dataset.image,
          parseInt(this.dataset.quantity) || 1
        );
        if (success) {
          const orig = this.textContent;
          this.textContent = '✓ Added!';
          this.disabled = true;
          setTimeout(() => { this.textContent = orig; this.disabled = false; }, 1500);
        }
      });
    });
    
    window.updateAuthNav();
    window.updateDynamicHeader();
  }

  function init() {
    attachListeners();
    initImageGallery();
    window.updateCartBadge();
    if (document.getElementById('cartContainer')) window.renderCartDisplay();
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
  window._reinit = function() { 
    attachListeners(); 
    if (document.getElementById('cartContainer')) window.renderCartDisplay(); 
  };

  (function() {
    const form = document.getElementById('loginForm');
    const msg = document.getElementById('loginMessage');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const remember = document.getElementById('remember').checked;
      
      if (!email || !password) {
        showMessage(msg, 'Please fill in all fields', 'error');
        return;
      }
      
      const users = JSON.parse(localStorage.getItem('schecterUsers')) || [];
      const user = users.find(u => u.email === email && u.password === password);
      
      if (user) {
        const sessionData = { 
          email: user.email, 
          name: (user.firstName || '') + ' ' + (user.lastName || ''),
          id: user.id
        };
        if (remember) {
          localStorage.setItem('schecterCurrentUser', JSON.stringify(sessionData));
        } else {
          sessionStorage.setItem('schecterCurrentUser', JSON.stringify(sessionData));
        }
        
        showMessage(msg, 'Login successful! Redirecting...', 'success');
        setTimeout(() => { 
          if (typeof window.updateAuthNav === 'function') window.updateAuthNav();
          if (typeof window.updateDynamicHeader === 'function') window.updateDynamicHeader();
          if (typeof window.updateCartBadge === 'function') window.updateCartBadge();
          window.location.href = 'index.html'; 
        }, 1000);
      } else {
        showMessage(msg, 'Invalid email or password', 'error');
      }
    });
  })();

(function initProductCart() {
  const addToCartBtns = document.querySelectorAll('.add-to-cart');
  if (!addToCartBtns.length) return;

  function updateBadge(n) {
    document.querySelectorAll('.cart-count, #cart-count, [data-cart-badge]').forEach(function(el) {
      el.textContent = n;
    });
  }

  function getLocalCart() {
    try {
      return JSON.parse(localStorage.getItem('userCart') || '[]');
    } catch (e) {
      return [];
    }
  }

  function saveLocalCart(cart) {
    localStorage.setItem('userCart', JSON.stringify(cart));
  }

  function localAdd(btn) {
    var cart = getLocalCart();
    var pid = btn.dataset.productId || btn.dataset.id;
    var name = btn.dataset.name;
    var price = parseFloat(btn.dataset.price) || 0;
    var image = btn.dataset.image || '';
    
    var found = cart.find(function(i) { return String(i.id) === String(pid); });
    
    if (found) {
      found.quantity = (found.quantity || 1) + 1;
    } else {
      cart.push({ 
        id: String(pid),   
        name: name, 
        price: price, 
        image: image, 
        quantity: 1 
      });
    }
    
    saveLocalCart(cart);
    var total = cart.reduce(function(s, i) { return s + (i.quantity || 1); }, 0);
    updateBadge(total);
    
    var originalText = btn.textContent;
    btn.textContent = '✓ Added!';
    btn.disabled = true;
    setTimeout(function() { 
      btn.textContent = originalText; 
      btn.disabled = false; 
    }, 1500);
    
    return true;
  }

  addToCartBtns.forEach(function(btn) {
    if (btn._productCartAttached) return;
    btn._productCartAttached = true;
    
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
      var productId = parseInt(btn.dataset.productId) || parseInt(btn.dataset.id);
      if (!productId) {
        localAdd(btn);
        return;
      }
      
      btn.classList.add('loading');
      btn.disabled = true;
      
      fetch(window.location.href, {
        method: 'POST',
        credentials: 'include',  
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          product_id: productId,
          quantity: 1
        })
      })
      .then(function(response) {
        if (!response.ok) throw new Error('Network error');
        return response.json();
      })
      .then(function(data) {
        if (data && data.success) {
          if (data.cart_count !== undefined) {
            updateBadge(data.cart_count);
          }
          var cart = getLocalCart();
          var existing = cart.find(function(i) { return String(i.id) === String(productId); });
          if (existing) {
            existing.quantity = (existing.quantity || 1) + 1;
          } else {
            cart.push({
              id: String(productId), 
              name: btn.dataset.name,
              price: parseFloat(btn.dataset.price) || 0,
              image: btn.dataset.image || '',
              quantity: 1
            });
          }
          saveLocalCart(cart);
          
          var originalText = btn.textContent;
          btn.textContent = '✓ Added!';
          setTimeout(function() { 
            btn.textContent = originalText; 
            btn.disabled = false; 
            btn.classList.remove('loading');
          }, 1500);
          
        } else if (data && data.message === 'not_logged_in') {
          localAdd(btn);
          btn.classList.remove('loading');
        } else {
          localAdd(btn);
          btn.classList.remove('loading');
        }
      })
      .catch(function(err) {
        console.log('PHP cart fallback:', err);
        localAdd(btn);
        btn.classList.remove('loading');
      });
    });
  });
  
  var localCart = getLocalCart();
  var localTotal = localCart.reduce(function(s, i) { return s + (i.quantity || 1); }, 0);
  if (localTotal > 0) {
    updateBadge(localTotal);
  }
})();

  (function() {
    const form = document.getElementById('signupForm');
    const msg = document.getElementById('signupMessage');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const firstName = document.getElementById('firstName').value.trim();
      const lastName = document.getElementById('lastName').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      const terms = document.getElementById('terms').checked;
      
      if (!firstName || !lastName || !email || !password || !confirmPassword) {
        showMessage(msg, 'Please fill in all fields', 'error');
        return;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showMessage(msg, 'Please enter a valid email', 'error');
        return;
      }
      if (password.length < 6) {
        showMessage(msg, 'Password must be at least 6 characters', 'error');
        return;
      }
      if (password !== confirmPassword) {
        showMessage(msg, 'Passwords do not match', 'error');
        return;
      }
      if (!terms) {
        showMessage(msg, 'Please agree to the Terms of Service', 'error');
        return;
      }
      
      const users = JSON.parse(localStorage.getItem('schecterUsers')) || [];
      if (users.find(u => u.email === email)) {
        showMessage(msg, 'An account with this email already exists', 'error');
        return;
      }
      
      const newUser = {
        id: Date.now().toString(),
        firstName, lastName, email, password,
        createdAt: new Date().toISOString(),
        cart: []
      };
      users.push(newUser);
      localStorage.setItem('schecterUsers', JSON.stringify(users));
      
      localStorage.setItem('schecterCurrentUser', JSON.stringify({
        email: newUser.email,
        name: firstName + ' ' + lastName,
        id: newUser.id
      }));
      
      showMessage(msg, 'Account created! Redirecting...', 'success');
      setTimeout(() => { 
        if (typeof window.updateAuthNav === 'function') window.updateAuthNav();
        if (typeof window.updateDynamicHeader === 'function') window.updateDynamicHeader();
        window.location.href = 'index.html'; 
      }, 1000);
    });
  })();

})();

function showCheckoutMsg(text, type) {
  const el = document.getElementById('checkoutMessage');
  if (!el) return;
  el.textContent    = text;
  el.style.display  = 'block';
  el.style.background = type === 'success' ? '#1a4731' : '#4a1a1a';
  el.style.color      = type === 'success' ? '#4ade80' : '#f87171';
  el.style.border     = '1px solid ' + (type === 'success' ? '#4ade80' : '#f87171');
}

function placeOrder() {
  const requiredFields = [
    document.getElementById('co-name'),
    document.getElementById('co-address'),
    document.getElementById('co-city'),
    document.getElementById('co-state'),
    document.getElementById('co-postal'),
    document.getElementById('co-phone'),
    document.getElementById('co-email')
  ];

  for (const field of requiredFields) {
    if (!field || !field.value.trim()) {
      showCheckoutMsg('Please fill in all shipping fields.', 'error');
      if (field) field.focus();
      return;
    }
  }

  const paymentEl = document.querySelector('input[name="payment"]:checked');
  if (!paymentEl) {
    showCheckoutMsg('Please select a payment method.', 'error');
    return;
  }

  const btn = document.getElementById('placeOrderBtn');
  btn.disabled    = true;
  btn.textContent = 'Placing order...';

  fetch('checkout.php', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'place_order' })
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      localStorage.removeItem('userCart');
      if (typeof window.updateCartBadge === 'function') window.updateCartBadge();

      showCheckoutMsg(
        '✓ Order #' + data.order_id + ' placed successfully! Total: $' + parseFloat(data.total).toFixed(2),
        'success'
      );

      var form = document.getElementById('checkoutForm');
      if (form) {
        form.style.opacity       = '0.4';
        form.style.pointerEvents = 'none';
      }
      btn.style.display = 'none';

      setTimeout(function() {
        window.location.href = '../html/account.html';
      }, 2500);
    } else {
      showCheckoutMsg(
        data.message === 'not_logged_in'
          ? 'You must be signed in to place an order.'
          : (data.message || 'Order failed. Please try again.'),
        'error'
      );
      btn.disabled    = false;
      btn.textContent = 'Place Order';
    }
  })
  .catch(function() {
    showCheckoutMsg('Network error. Please try again.', 'error');
    btn.disabled    = false;
    btn.textContent = 'Place Order';
  });
}

function initAccountPage() {
  if (!window.location.href.includes('account.html')) return;

  const user = window.getCurrentUser();

  if (!user) {
    window.location.href = window.location.pathname.includes('php/') ? '../php/login.php' : 'php/login.php';
    return;
  }

  const name = (user.name || 'User').trim();
  document.querySelectorAll('#accountEmail, #profileEmail').forEach(el => {
    if (el) el.textContent = user.email || '-';
  });
  document.querySelectorAll('#profileName').forEach(el => {
    if (el) el.textContent = name;
  });

  const initialsEl = document.getElementById('accountInitials');
  if (initialsEl) {
    const parts = name.split(' ');
    initialsEl.textContent = ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || 'U';
  }

  const signOutBtn = document.getElementById('signOutBtn');
  if (signOutBtn) {
    signOutBtn.onclick = function(e) {
      e.preventDefault();
      if (confirm('Are you sure you want to sign out?')) {
        window.logout();
        window.location.href = 'index.html';
      }
    };
  }

  if (typeof window.updateCartBadge === 'function') window.updateCartBadge();

  const apiBase = '../php/checkout.php';

  const cartPreview  = document.getElementById('cartPreview');
  const cartCountEl  = document.getElementById('cartItems');

  fetch(apiBase + '?action=get_cart', { credentials: 'include' })
    .then(res => res.json())
    .then(data => {
      if (!data.success || !data.items) {
        if (cartPreview) cartPreview.innerHTML = '<p class="empty-cart-preview">Your cart is empty. <a href="../html/products.html">Browse products</a></p>';
        if (cartCountEl) cartCountEl.textContent = '0';
        return;
      }

      const items = data.items;
      const totalQty = items.reduce((s, i) => s + (i.quantity || 1), 0);

      localStorage.setItem('userCart', JSON.stringify(items));
      if (typeof window.updateCartBadge === 'function') window.updateCartBadge();

      if (cartCountEl) cartCountEl.textContent = totalQty;

      if (cartPreview) {
        if (items.length === 0) {
          cartPreview.innerHTML = '<p class="empty-cart-preview">Your cart is empty. <a href="../html/products.html">Browse products</a></p>';
        } else {
          let html = '';
          items.slice(0, 3).forEach(function(item) {
            const rawImg = item.image || '';
            const img = rawImg && !rawImg.startsWith('http') ? '../' + rawImg.replace(/^\.\.\/+|^\/+/, '') : (rawImg || '../images/placeholder.jpg');
            html += '<div class="cart-preview-item">' +
              '<img src="' + img + '" alt="' + item.name + '" class="preview-img" onerror="this.src=\'../images/placeholder.jpg\'">' +
              '<div class="preview-info">' +
                '<p class="preview-name">' + item.name + '</p>' +
                '<p class="preview-qty">Qty: ' + (item.quantity || 1) + '</p>' +
              '</div>' +
              '<span class="preview-price">$' + (parseFloat(item.price) || 0).toFixed(2) + '</span>' +
            '</div>';
          });
          if (items.length > 3) {
            html += '<p class="preview-more">+ ' + (items.length - 3) + ' more item(s)</p>';
          }
          cartPreview.innerHTML = html;
        }
      }
    })
    .catch(function() {
      if (cartPreview) cartPreview.innerHTML = '<p class="empty-cart-preview">Could not load cart.</p>';
    });

  const ordersList   = document.getElementById('ordersList');
  const totalOrderEl = document.getElementById('totalOrders');
  const memberSinceEls = document.querySelectorAll('#memberSince, #profileMemberSince');

  fetch(apiBase + '?action=get_orders', { credentials: 'include' })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        if (ordersList) ordersList.innerHTML = '<p class="no-orders">No orders yet. <a href="../html/products.html">Start shopping!</a></p>';
        if (totalOrderEl) totalOrderEl.textContent = '0';
        return;
      }

      const orders = data.orders || [];

      if (totalOrderEl) totalOrderEl.textContent = orders.length;

      if (orders.length > 0) {
        const earliest = orders[orders.length - 1].created_at;
        const since = new Date(earliest).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        memberSinceEls.forEach(el => { if (el) el.textContent = since; });
      } else {
        memberSinceEls.forEach(el => { if (el) el.textContent = '2026'; });
      }

      if (!ordersList) return;

      if (orders.length === 0) {
        ordersList.innerHTML = '<p class="no-orders">No orders yet. <a href="../html/products.html">Start shopping!</a></p>';
        return;
      }

      let html = '';
      orders.slice(0, 5).forEach(function(order) {
        const date = new Date(order.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
        const statusClass = 'status-' + (order.status || 'pending');

        let itemsHtml = '';
        if (order.items && order.items.length > 0) {
          itemsHtml = '<div class="order-item-details" style="font-size:0.8rem;color:#aaa;margin-top:4px;">';
          order.items.slice(0, 2).forEach(function(it) {
            itemsHtml += '<span>' + it.name + ' x' + it.quantity + '</span> ';
          });
          if (order.items.length > 2) {
            itemsHtml += '<span>+' + (order.items.length - 2) + ' more</span>';
          }
          itemsHtml += '</div>';
        }

        html += '<div class="order-item">' +
          '<span class="order-id">#' + order.id + '</span>' +
          '<span class="order-date">' + date + '</span>' +
          '<span class="order-total">$' + parseFloat(order.total_amount).toFixed(2) + '</span>' +
          '<span class="order-status ' + statusClass + '">' + order.status + '</span>' +
          itemsHtml +
        '</div>';
      });

      if (orders.length > 5) {
        html += '<p style="color:#aaa;font-size:0.85rem;margin-top:8px;">Showing 5 of ' + orders.length + ' orders.</p>';
      }

      ordersList.innerHTML = html;
    })
    .catch(function() {
      if (ordersList) ordersList.innerHTML = '<p class="no-orders">Could not load orders.</p>';
    });
}

document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.updateDynamicHeader === 'function') window.updateDynamicHeader();
  initAccountPage();
});

function openNav() {
  document.getElementById("myOffcanvasNav").style.width = "220px";
}

function closeNav() {
  document.getElementById("myOffcanvasNav").style.width = "0px";
}

function openModal() {
  const modal = document.getElementById("promoModal");
  if (modal) {
    modal.style.display = "block";
    document.body.style.overflow = "hidden";
  }
}

function closeModal() {
  const modal = document.getElementById("promoModal");
  if (modal) {
    modal.style.display = "none";
    document.body.style.overflow = "";
  }
}

window.onclick = function(event) {
  const modal = document.getElementById("promoModal");
  if (modal && event.target === modal) {
    closeModal();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  const hasSeenModal = sessionStorage.getItem('schecterModalSeen');
  const modal = document.getElementById('promoModal');
  if (!hasSeenModal && modal) {
    setTimeout(openModal, 1000);
    sessionStorage.setItem('schecterModalSeen', 'true');
  }
});

let slideIndex = 0;

function showSlide(index) {
  const slides = document.querySelectorAll('.carousel-slide');
  const dots = document.querySelectorAll('.dot');
  if (!slides.length) return;

  if (index >= slides.length) slideIndex = 0;
  else if (index < 0) slideIndex = slides.length - 1;
  else slideIndex = index;
  
  slides.forEach((slide, i) => {
    slide.classList.toggle('active', i === slideIndex);
  });
  dots.forEach((dot, i) => {
    dot.classList.toggle('active', i === slideIndex);
  });
}

function moveSlide(direction) {
  showSlide(slideIndex + direction);
  resetAutoSlide();
}

function currentSlide(index) {
  showSlide(index - 1);
  resetAutoSlide();
}

let autoSlideInterval;

function startAutoSlide() {
  const slides = document.querySelectorAll('.carousel-slide');
  if (!slides.length) return;
  autoSlideInterval = setInterval(() => moveSlide(1), 5000);
}

function resetAutoSlide() {
  clearInterval(autoSlideInterval);
  startAutoSlide();
}

document.addEventListener('DOMContentLoaded', function() {
  const slides = document.querySelectorAll('.carousel-slide');
  if (slides.length > 0) {
    showSlide(0);
    startAutoSlide();
    const carousel = document.querySelector('.carousel-container');
    if (carousel) {
      carousel.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
      carousel.addEventListener('mouseleave', startAutoSlide);
    }
  }
});

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.featured, .reviews, .bio, .featured-videos')
  .forEach(el => observer.observe(el));

window.animateCarouselSlide = function(direction) {
  const slides = document.querySelectorAll('.carousel-slide');
  const activeSlide = document.querySelector('.carousel-slide.active');
  if (!activeSlide) return;

  activeSlide.classList.add('fade-out');
  setTimeout(() => {
    const currentIndex = Array.from(slides).indexOf(activeSlide);
    let nextIndex = direction === 'next'
      ? (currentIndex + 1) % slides.length
      : (currentIndex - 1 + slides.length) % slides.length;
    
    const nextSlide = slides[nextIndex];
    activeSlide.classList.remove('active', 'fade-out');
    nextSlide.classList.add('active');
    document.querySelectorAll('.dot').forEach((dot, i) => {
      dot.classList.toggle('active', i === nextIndex);
    });
  }, 300);
};

document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    loadOrders();
});

async function loadCart() {
    const container = document.getElementById('cart-container');
    
    try {
        const response = await fetch('checkout.php?action=get_cart', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        
        if (data.success && data.items?.length > 0) {
            let html = `<table class="cart-table"><thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>`;
            let total = 0;
            
            data.items.forEach(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                html += `
                    <tr>
                        <td>
                            <img src="${item.image}" alt="${item.name}" width="50" style="vertical-align:middle; margin-right:8px;">
                            ${escapeHtml(item.name)}
                        </td>
                        <td>$${parseFloat(item.price).toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>$${subtotal.toFixed(2)}</td>
                    </tr>`;
            });
            
            html += `</tbody></table>
                     <p><strong>Cart Total: $${total.toFixed(2)}</strong></p>
                     <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>`;
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>Your cart is empty. <a href="index.php">Continue shopping</a></p>';
        }
    } catch (err) {
        container.innerHTML = '<p class="error">Failed to load cart.</p>';
        console.error('Cart error:', err);
    }
}

async function loadOrders() {
    const container = document.getElementById('orders-container');
    
    try {
        const response = await fetch('checkout.php?action=get_orders', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        
        if (data.success && data.orders?.length > 0) {
            let html = `<table class="orders-table"><thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th>Details</th></tr></thead><tbody>`;
            
            data.orders.forEach(order => {
                const date = new Date(order.created_at).toLocaleDateString();
                html += `
                    <tr>
                        <td>#${order.id}</td>
                        <td>${date}</td>
                        <td>$${parseFloat(order.total_amount).toFixed(2)}</td>
                        <td><span class="status status-${order.status}">${order.status}</span></td>
                        <td><a href="#">View</a></td>
                    </tr>`;
            });
            
            html += `</tbody></table>`;
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>You haven\'t placed any orders yet.</p>';
        }
    } catch (err) {
        container.innerHTML = '<p class="error">Failed to load orders.</p>';
        console.error('Orders error:', err);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}