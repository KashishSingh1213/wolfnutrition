// assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // --------------------------------------------------------
    // 1. ROTATING ANNOUNCEMENT BAR
    // --------------------------------------------------------
    const announcements = document.querySelectorAll('.announcement-item');
    if (announcements.length > 1) {
        let currentIndex = 0;
        setInterval(() => {
            announcements[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % announcements.length;
            announcements[currentIndex].classList.add('active');
        }, 4000);
    }

    // Hero Slider Autoplay
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length > 1) {
        let slideIndex = 0;
        setInterval(() => {
            slides[slideIndex].classList.remove('active');
            slideIndex = (slideIndex + 1) % slides.length;
            slides[slideIndex].classList.add('active');
        }, 5000);
    }

    // --------------------------------------------------------
    // 2. STICKY HEADER SCROLL EFFECT
    // --------------------------------------------------------
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // --------------------------------------------------------
    // 3. CART DRAWER TRIGGERS
    // --------------------------------------------------------
    const cartTriggers = document.querySelectorAll('.cart-drawer-trigger');
    const cartDrawer = document.querySelector('.cart-drawer');
    const cartBackdrop = document.querySelector('.cart-drawer-backdrop');
    const cartClose = document.querySelector('.cart-drawer-close');

    function openCart() {
        if (cartDrawer && cartBackdrop) {
            cartDrawer.classList.add('active');
            cartBackdrop.classList.add('active');
            document.body.style.overflow = 'hidden'; // Disable page scrolling
            fetchCartDrawer(); // refresh items
        }
    }

    function closeCart() {
        if (cartDrawer && cartBackdrop) {
            cartDrawer.classList.remove('active');
            cartBackdrop.classList.remove('active');
            document.body.style.overflow = 'auto'; // Re-enable page scrolling
        }
    }

    cartTriggers.forEach(trigger => trigger.addEventListener('click', (e) => {
        e.preventDefault();
        openCart();
    }));

    if (cartClose) cartClose.addEventListener('click', closeCart);
    if (cartBackdrop) cartBackdrop.addEventListener('click', closeCart);

    // --------------------------------------------------------
    // 4. SEARCH OVERLAY TRIGGERS
    // --------------------------------------------------------
    const searchTrigger = document.querySelector('.search-trigger');
    const searchOverlay = document.querySelector('.search-overlay');
    const searchCloseBtn = document.querySelector('.search-close');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.querySelector('.search-live-results');

    if (searchTrigger && searchOverlay) {
        searchTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            searchOverlay.classList.add('active');
            if (searchInput) searchInput.focus();
        });

        if (searchCloseBtn) {
            searchCloseBtn.addEventListener('click', () => {
                searchOverlay.classList.remove('active');
                if (searchInput) searchInput.value = '';
                if (searchResults) searchResults.innerHTML = '';
            });
        }
    }

    // Live Search suggestions AJAX
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            fetch(`search_api.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length === 0) {
                        searchResults.innerHTML = '<div style="padding:15px; color:#888;">No products found matching your search.</div>';
                        return;
                    }
                    data.forEach(item => {
                        const div = document.createElement('a');
                        div.href = `product.php?slug=${item.slug}`;
                        div.className = 'search-result-item';
                        div.innerHTML = `
                            <img src="${item.image}" alt="${item.name}">
                            <div class="search-result-info">
                                <h5>${item.name}</h5>
                                <p>₹${item.price} - ${item.category}</p>
                            </div>
                        `;
                        searchResults.appendChild(div);
                    });
                })
                .catch(err => console.error('Error fetching search results:', err));
        });
    }

    // --------------------------------------------------------
    // 5. PRODUCT TABS TOGGLE (Index tabs & Product detail tabs)
    // --------------------------------------------------------
    
    // Homepage category filter tabs
    const homeTabButtons = document.querySelectorAll('.tab-btn');
    const homeTabPanes = document.querySelectorAll('.tab-pane');
    homeTabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            homeTabButtons.forEach(b => b.classList.remove('active'));
            homeTabPanes.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const targetPane = document.getElementById(targetId);
            if (targetPane) targetPane.classList.add('active');
        });
    });

    // Product detail tabs
    const detailTabButtons = document.querySelectorAll('.product-tab-btn');
    const detailTabPanes = document.querySelectorAll('.product-tab-pane');
    detailTabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            detailTabButtons.forEach(b => b.classList.remove('active'));
            detailTabPanes.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            const targetPane = document.getElementById(targetId);
            if (targetPane) targetPane.classList.add('active');
        });
    });

    // --------------------------------------------------------
    // 6. VARIANT SELECTION (Live Price Update)
    // --------------------------------------------------------
    const variantRadios = document.querySelectorAll('.variant-option-radio');
    const mainPriceSale = document.getElementById('main-price-sale');
    const mainPriceMrp = document.getElementById('main-price-mrp');
    const mainSaveAmount = document.getElementById('main-save-amount');

    variantRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const salePrice = parseFloat(this.dataset.salePrice);
            const mrp = parseFloat(this.dataset.mrp);
            const saving = mrp - salePrice;

            if (mainPriceSale) mainPriceSale.textContent = `₹${salePrice.toLocaleString('en-IN')}`;
            if (mainPriceMrp) mainPriceMrp.textContent = `MRP ₹${mrp.toLocaleString('en-IN')}`;
            if (mainSaveAmount && saving > 0) {
                mainSaveAmount.textContent = `You save ₹${saving.toLocaleString('en-IN')}`;
            }
        });
    });

    // --------------------------------------------------------
    // 7. QUANTITY PICKER (Product Page & Cart)
    // --------------------------------------------------------
    const detailQtyMinus = document.querySelector('.detail-qty-minus');
    const detailQtyPlus = document.querySelector('.detail-qty-plus');
    const detailQtyInput = document.querySelector('.detail-qty-input');

    if (detailQtyInput) {
        if (detailQtyMinus) {
            detailQtyMinus.addEventListener('click', () => {
                let currentVal = parseInt(detailQtyInput.value) || 1;
                if (currentVal > 1) {
                    detailQtyInput.value = currentVal - 1;
                }
            });
        }
        if (detailQtyPlus) {
            detailQtyPlus.addEventListener('click', () => {
                let currentVal = parseInt(detailQtyInput.value) || 1;
                detailQtyInput.value = currentVal + 1;
            });
        }
    }

    // --------------------------------------------------------
    // 8. AJAX CART OPERATIONS & DRAWER RENDERING
    // --------------------------------------------------------
    
    // Add item form submit
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add');
            
            fetch('cart_api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateCartCountBadge(data.cart_count);
                    openCart(); // Show slide drawer
                } else {
                    alert(data.message || 'Error adding item to cart.');
                }
            })
            .catch(err => console.error('Error adding to cart:', err));
        });
    }

    // Quick Add Button Click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quick-add-btn')) {
            e.preventDefault();
            const productId = e.target.dataset.productId;
            const variantId = e.target.dataset.variantId;
            const csrfToken = e.target.dataset.csrf || '';
            
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('variant_id', variantId);
            formData.append('quantity', 1);
            formData.append('csrf_token', csrfToken);

            fetch('cart_api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateCartCountBadge(data.cart_count);
                    openCart();
                } else {
                    alert(data.message || 'Error adding item.');
                }
            })
            .catch(err => console.error('Error quick add:', err));
        }
    });

    // Add Bundle Combo Pack
    const addBundleBtn = document.getElementById('add-bundle-btn');
    if (addBundleBtn) {
        addBundleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const bundleId = this.dataset.bundleId;
            const formData = new FormData();
            formData.append('action', 'add_bundle');
            formData.append('bundle_id', bundleId);
            formData.append('quantity', 1);

            fetch('cart_api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateCartCountBadge(data.cart_count);
                    openCart();
                } else {
                    alert(data.message || 'Combo is currently out of stock.');
                }
            })
            .catch(err => console.error('Error adding bundle:', err));
        });
    }

    // Refresh Cart count badge
    function updateCartCountBadge(count) {
        const badges = document.querySelectorAll('.cart-badge');
        badges.forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    // Fetch and populate Cart Drawer html
    function fetchCartDrawer() {
        const drawerItemsContainer = document.querySelector('.cart-drawer-items');
        const drawerTotal = document.getElementById('cart-drawer-total-val');
        
        if (!drawerItemsContainer) return;
        
        fetch('cart_api.php?action=get')
            .then(res => res.json())
            .then(data => {
                drawerItemsContainer.innerHTML = '';
                if (Object.keys(data.items).length === 0) {
                    drawerItemsContainer.innerHTML = '<div style="padding:40px 0; text-align:center; color:#888;">Your cart is empty.</div>';
                    if (drawerTotal) drawerTotal.textContent = '₹0';
                    updateCartCountBadge(0);
                    return;
                }

                // Render cart items
                for (const key in data.items) {
                    const item = data.items[key];
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'cart-drawer-item';
                    itemDiv.innerHTML = `
                        <img src="${item.image}" alt="${item.name}">
                        <div class="cart-drawer-item-details">
                            <div class="cart-drawer-item-title">${item.name}</div>
                            <div class="cart-drawer-item-size">${item.size}</div>
                            <div class="cart-drawer-item-price">₹${item.price.toLocaleString('en-IN')}</div>
                            <div class="cart-drawer-quantity-controls">
                                <button class="qty-btn drawer-qty-minus" data-key="${key}">-</button>
                                <span class="qty-input">${item.qty}</span>
                                <button class="qty-btn drawer-qty-plus" data-key="${key}">+</button>
                            </div>
                            <button class="cart-drawer-item-remove" data-key="${key}">Remove</button>
                        </div>
                    `;
                    drawerItemsContainer.appendChild(itemDiv);
                }

                if (drawerTotal) drawerTotal.textContent = `₹${data.totals.subtotal.toLocaleString('en-IN')}`;
                updateCartCountBadge(data.cart_count);
            })
            .catch(err => console.error('Error fetching cart details:', err));
    }

    // Drawer Cart Quantity Adjustments & Removals via delegation
    const drawerItems = document.querySelector('.cart-drawer-items');
    if (drawerItems) {
        drawerItems.addEventListener('click', function(e) {
            const target = e.target;
            const key = target.dataset.key;
            if (!key) return;

            if (target.classList.contains('drawer-qty-minus') || target.classList.contains('drawer-qty-plus')) {
                const currentQtySpan = target.parentNode.querySelector('.qty-input');
                let currentVal = parseInt(currentQtySpan.textContent) || 1;
                let newVal = target.classList.contains('drawer-qty-plus') ? currentVal + 1 : currentVal - 1;

                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('key', key);
                formData.append('qty', newVal);

                fetch('cart_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchCartDrawer();
                        // If we are on the main cart page, reload the page to refresh calculations
                        if (window.location.pathname.includes('cart.php')) {
                            window.location.reload();
                        }
                    }
                })
                .catch(err => console.error('Error updating quantity:', err));
            }

            if (target.classList.contains('cart-drawer-item-remove')) {
                const formData = new FormData();
                formData.append('action', 'remove');
                formData.append('key', key);

                fetch('cart_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetchCartDrawer();
                        if (window.location.pathname.includes('cart.php')) {
                            window.location.reload();
                        }
                    }
                })
                .catch(err => console.error('Error removing item:', err));
            }
        });
    }

    // --------------------------------------------------------
    // 9. PINCODE ESTIMATOR
    // --------------------------------------------------------
    const pincodeCheckBtn = document.getElementById('pincode-check-btn');
    const pincodeInput = document.getElementById('pincode-input');
    const pincodeResult = document.getElementById('pincode-result');

    if (pincodeCheckBtn && pincodeInput && pincodeResult) {
        pincodeCheckBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const pincode = pincodeInput.value.trim();
            if (pincode.length !== 6 || isNaN(pincode)) {
                pincodeResult.style.color = 'var(--danger-color)';
                pincodeResult.textContent = 'Please enter a valid 6-digit Pincode.';
                return;
            }

            fetch(`pincode_api.php?pincode=${pincode}`)
                .then(res => res.json())
                .then(data => {
                    if (data.valid) {
                        pincodeResult.style.color = 'var(--success-color)';
                        pincodeResult.textContent = `✅ Estimated Delivery: ${data.estimate}`;
                    } else {
                        pincodeResult.style.color = 'var(--danger-color)';
                        pincodeResult.textContent = `❌ ${data.message}`;
                    }
                })
                .catch(err => {
                    pincodeResult.style.color = 'var(--danger-color)';
                    pincodeResult.textContent = 'Error checking pincode availability.';
                });
        });
    }

    // --------------------------------------------------------
    // 10. REVIEWS STARS SELECTION
    // --------------------------------------------------------
    const starSelectors = document.querySelectorAll('.rating-select-stars i');
    const ratingInput = document.getElementById('review-rating-input');

    if (starSelectors.length > 0 && ratingInput) {
        starSelectors.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.value);
                ratingInput.value = rating;
                
                // Highlight active stars
                starSelectors.forEach(s => {
                    if (parseInt(s.dataset.value) <= rating) {
                        s.classList.remove('far');
                        s.classList.add('fas', 'active');
                    } else {
                        s.classList.remove('fas', 'active');
                        s.classList.add('far');
                    }
                });
            });
        });
    }
});
