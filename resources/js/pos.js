const numberFormat = new Intl.NumberFormat('id-ID', {
    maximumFractionDigits: 0,
});

const formatNumber = (value) => numberFormat.format(Math.max(0, Math.round(Number(value) || 0)));

const formatRupiah = (value) => `Rp ${formatNumber(value)}`;

const escapeHtml = (value) =>
    String(value).replace(/[&<>"']/g, (character) => {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };

        return map[character];
    });

const parseDigits = (value) => Number(String(value).replace(/\D/g, '')) || 0;

const initPos = () => {
    const root = document.querySelector('[data-pos]');
    const dataElement = document.getElementById('pos-data');

    if (!root || !dataElement) {
        return;
    }

    let data;

    try {
        data = JSON.parse(dataElement.textContent);
    } catch {
        return;
    }

    const products = new Map(data.products.map((product) => [Number(product.id), product]));
    const cart = new Map();
    const form = root.querySelector('[data-pos-form]');
    const lines = root.querySelector('[data-cart-lines]');
    const empty = root.querySelector('[data-cart-empty]');
    const itemsInput = form?.querySelector('[data-items-input]');
    const amountValue = form?.querySelector('[data-cash-value]');
    const cashInput = form?.querySelector('[data-cash-input]');
    const changeValue = root.querySelector('[data-change-value]');
    const changeRow = root.querySelector('[data-change-row]');
    const subtotalValue = root.querySelector('[data-subtotal-value]');
    const cartCount = root.querySelectorAll('[data-cart-count]');
    const fabValue = root.querySelector('[data-fab-value]');
    const payButton = form?.querySelector('[data-pay-button]');
    const payLabel = payButton?.querySelector('[data-pay-label]');
    const payMeta = payButton?.querySelector('[data-pay-meta]');
    const payTotal = payButton?.querySelector('[data-pay-total]');
    const cashPanel = root.querySelector('[data-cash-panel]');
    const qrisPanel = root.querySelector('[data-qris-panel]');
    const errorBox = root.querySelector('[data-pos-error]');
    const idempotencyInput = form?.querySelector('[data-idempotency]');
    const confirmDialog = document.getElementById('checkout-confirm');
    const confirmItems = confirmDialog?.querySelector('[data-confirm-items]');
    const confirmTotal = confirmDialog?.querySelector('[data-confirm-total]');
    const confirmCashRow = confirmDialog?.querySelector('[data-confirm-cash-row]');
    const confirmCash = confirmDialog?.querySelector('[data-confirm-cash]');
    const confirmMethod = confirmDialog?.querySelector('[data-confirm-method]');
    const confirmButton = confirmDialog?.querySelector('[data-confirm-pay]');
    const cartPanel = root.querySelector('[data-cart-panel]');
    const cartOverlay = root.querySelector('[data-cart-overlay]');
    const fab = root.querySelector('[data-cart-fab]');

    if (!form || !lines || !itemsInput || !amountValue || !cashInput) {
        return;
    }

    let lastTotal = 0;
    let submitting = false;

    const getCheckedValue = (name, fallback) =>
        form.querySelector(`input[name="${name}"]:checked`)?.value || fallback;

    const isCash = () => getCheckedValue('payment_method', 'cash') === 'cash';

    const getQuantity = (id) => cart.get(id) || 0;

    const getTotal = () =>
        Array.from(cart.entries()).reduce((total, [id, quantity]) => {
            const product = products.get(id);
            return total + (product ? Number(product.price) * quantity : 0);
        }, 0);

    const getItemCount = () => Array.from(cart.values()).reduce((total, quantity) => total + quantity, 0);

    const showError = (message) => {
        if (!errorBox) {
            return;
        }

        errorBox.textContent = message;
        errorBox.hidden = false;
    };

    const clearError = () => {
        if (errorBox) {
            errorBox.hidden = true;
            errorBox.textContent = '';
        }
    };

    const syncHiddenFields = () => {
        const items = Array.from(cart.entries()).map(([id, quantity]) => ({
            product_id: id,
            quantity,
        }));

        itemsInput.value = JSON.stringify(items);
        amountValue.value = isCash() ? String(parseDigits(cashInput.value)) : '0';

        if (idempotencyInput && !idempotencyInput.value) {
            idempotencyInput.value =
                typeof crypto !== 'undefined' && crypto.randomUUID
                    ? crypto.randomUUID()
                    : `pos-${Date.now()}-${Math.random().toString(16).slice(2)}`;
        }
    };

    const updateCashDisplay = () => {
        const total = getTotal();
        const paid = parseDigits(cashInput.value);
        const change = paid - total;
        const short = paid < total;

        cashInput.value = paid ? formatNumber(paid) : '';

        if (changeValue) {
            changeValue.textContent = short ? `- ${formatRupiah(Math.abs(change))}` : formatRupiah(change);
        }

        changeRow?.classList.toggle('is-short', short);
    };

    const updatePayButton = () => {
        if (!payButton) {
            return;
        }

        const total = getTotal();
        const paid = parseDigits(cashInput.value);
        const emptyCart = cart.size === 0;
        const insufficient = isCash() && paid < total;

        payButton.disabled = emptyCart || insufficient || submitting;

        if (payLabel) {
            payLabel.textContent = isCash() ? 'Bayar tunai' : 'Buat transaksi QRIS';
        }

        if (payMeta) {
            payMeta.textContent = isCash() ? 'Stok dipotong setelah pembayaran' : 'Stok dipotong setelah konfirmasi';
        }

        if (payTotal) {
            payTotal.textContent = formatRupiah(total);
        }
    };

    const updateSummary = () => {
        const total = getTotal();
        const count = getItemCount();

        if (subtotalValue) {
            subtotalValue.textContent = formatRupiah(total);
        }

        cartCount.forEach((element) => {
            element.textContent = count;
        });

        if (fabValue) {
            fabValue.textContent = formatRupiah(total);
        }

        if (isCash() && (parseDigits(cashInput.value) === 0 || parseDigits(cashInput.value) === lastTotal) && total > 0) {
            cashInput.value = formatNumber(total);
        }

        lastTotal = total;
        updateCashDisplay();
        updatePayButton();
    };

    const lineMarkup = (product, quantity) => `
        <div class="cart-line" data-line="${product.id}">
            <div>
                <div class="cart-line__name">${escapeHtml(product.name)}</div>
                <div class="cart-line__unit">${formatRupiah(product.price)} / ${escapeHtml(product.unit)}</div>
            </div>
            <button type="button" class="cart-line__remove" data-cart-action="remove" data-id="${product.id}" aria-label="Hapus ${escapeHtml(product.name)}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            <div class="cart-line__bottom">
                <div class="qty-stepper">
                    <button type="button" data-cart-action="minus" data-id="${product.id}" aria-label="Kurangi ${escapeHtml(product.name)}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="qty-stepper__value">${quantity}</span>
                    <button type="button" data-cart-action="plus" data-id="${product.id}" aria-label="Tambah ${escapeHtml(product.name)}" ${product.track_stock && quantity >= product.stock ? 'disabled' : ''}>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
                <span class="cart-line__price">${formatRupiah(Number(product.price) * quantity)}</span>
            </div>
        </div>
    `;

    const renderCart = () => {
        const entries = Array.from(cart.entries());

        if (entries.length === 0) {
            lines.innerHTML = '';
            if (empty) {
                empty.hidden = false;
            }
        } else {
            if (empty) {
                empty.hidden = true;
            }
            lines.innerHTML = entries
                .map(([id, quantity]) => {
                    const product = products.get(id);
                    return product ? lineMarkup(product, quantity) : '';
                })
                .join('');
        }

        updateSummary();
        syncHiddenFields();
    };

    const addProduct = (id) => {
        const product = products.get(Number(id));

        if (!product) {
            return;
        }

        const quantity = getQuantity(product.id);

        if (product.track_stock && quantity >= product.stock) {
            showError(`Stok ${product.name} hanya ${product.stock} ${product.unit}.`);
            return;
        }

        if (quantity >= 999) {
            showError('Jumlah per item maksimal 999.');
            return;
        }

        clearError();
        cart.set(product.id, quantity + 1);
        renderCart();
    };

    const changeQuantity = (id, delta) => {
        const product = products.get(Number(id));

        if (!product) {
            return;
        }

        const next = getQuantity(product.id) + delta;

        if (next <= 0) {
            cart.delete(product.id);
        } else if (product.track_stock && next > product.stock) {
            showError(`Stok ${product.name} hanya ${product.stock} ${product.unit}.`);
            return;
        } else {
            cart.set(product.id, next);
        }

        clearError();
        renderCart();
    };

    root.querySelectorAll('[data-product-card]').forEach((card) => {
        card.addEventListener('click', () => addProduct(card.dataset.productId));
    });

    lines.addEventListener('click', (event) => {
        const button = event.target.closest('[data-cart-action]');

        if (!button) {
            return;
        }

        const id = Number(button.dataset.id);

        if (button.dataset.cartAction === 'remove') {
            cart.delete(id);
            renderCart();
        } else if (button.dataset.cartAction === 'minus') {
            changeQuantity(id, -1);
        } else if (button.dataset.cartAction === 'plus') {
            changeQuantity(id, 1);
        }
    });

    cashInput.addEventListener('input', () => {
        const digits = parseDigits(cashInput.value);
        cashInput.value = digits ? formatNumber(digits) : '';
        clearError();
        updateCashDisplay();
        updatePayButton();
        syncHiddenFields();
    });

    root.querySelectorAll('[data-cash-quick]').forEach((button) => {
        button.addEventListener('click', () => {
            const total = getTotal();
            const quick = button.dataset.cashQuick;

            if (quick === 'exact') {
                cashInput.value = formatNumber(total);
            } else {
                const step = Number(quick);
                cashInput.value = formatNumber(Math.ceil(total / step) * step);
            }

            clearError();
            updateCashDisplay();
            updatePayButton();
            syncHiddenFields();
        });
    });

    form.querySelectorAll('input[name="payment_method"]').forEach((input) => {
        input.addEventListener('change', () => {
            const cash = isCash();
            if (cashPanel) {
                cashPanel.hidden = !cash;
            }
            if (qrisPanel) {
                qrisPanel.hidden = cash;
            }
            clearError();
            updateSummary();
            syncHiddenFields();
        });
    });

    form.querySelectorAll('input[name="order_type"]').forEach((input) => {
        input.addEventListener('change', clearError);
    });

    const searchInput = root.querySelector('[data-product-search]');
    const categoryChips = root.querySelectorAll('[data-category-chip]');
    const productCards = Array.from(root.querySelectorAll('[data-product-card]'));
    const productEmpty = root.querySelector('[data-product-empty]');
    let activeCategory = 'all';

    const filterProducts = () => {
        const search = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;

        productCards.forEach((card) => {
            const matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
            const matchesSearch = !search || (card.dataset.search || '').includes(search);
            const show = matchesCategory && matchesSearch;

            card.hidden = !show;
            if (show) {
                visible += 1;
            }
        });

        if (productEmpty) {
            productEmpty.hidden = visible !== 0;
        }
    };

    searchInput?.addEventListener('input', filterProducts);

    categoryChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            activeCategory = chip.dataset.categoryChip;
            categoryChips.forEach((other) => other.classList.toggle('is-active', other === chip));
            filterProducts();
        });
    });

    const mobileCartQuery = window.matchMedia('(max-width: 1023px)');

    const openCart = () => {
        if (!mobileCartQuery.matches) {
            return;
        }

        cartPanel?.classList.add('is-open');
        cartOverlay?.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const closeCart = () => {
        cartPanel?.classList.remove('is-open');
        cartOverlay?.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    mobileCartQuery.addEventListener('change', (event) => {
        if (!event.matches) {
            closeCart();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || document.querySelector('dialog[open]')) {
            return;
        }

        if (cartPanel?.classList.contains('is-open')) {
            closeCart();
        }
    });

    fab?.addEventListener('click', openCart);
    root.querySelector('[data-cart-close]')?.addEventListener('click', closeCart);
    cartOverlay?.addEventListener('click', closeCart);

    root.querySelector('[data-cart-clear]')?.addEventListener('click', () => {
        const dialog = document.getElementById('cart-clear-confirm');
        if (cart.size === 0) {
            return;
        }
        dialog?.showModal();
    });

    document.querySelector('[data-cart-clear-confirm]')?.addEventListener('click', () => {
        cart.clear();
        clearError();
        renderCart();
        document.getElementById('cart-clear-confirm')?.close();
    });

    const fillConfirmDialog = () => {
        const total = getTotal();
        const count = getItemCount();
        const cash = isCash();
        const paid = parseDigits(cashInput.value);

        if (confirmItems) {
            confirmItems.innerHTML = Array.from(cart.entries())
                .map(([id, quantity]) => {
                    const product = products.get(id);

                    return product
                        ? `<div style="display: flex; justify-content: space-between; gap: 12px;"><span>${quantity} × ${escapeHtml(product.name)}</span><span>${formatRupiah(Number(product.price) * quantity)}</span></div>`
                        : '';
                })
                .join('');
        }
        if (confirmTotal) {
            confirmTotal.textContent = formatRupiah(total);
        }
        if (confirmMethod) {
            confirmMethod.textContent = cash ? 'Tunai' : 'QRIS';
        }
        if (confirmCashRow) {
            confirmCashRow.hidden = !cash;
        }
        if (confirmCash) {
            confirmCash.textContent = cash
                ? `${formatRupiah(paid)} · kembali ${formatRupiah(Math.max(0, paid - total))}`
                : '';
        }
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearError();

        if (cart.size === 0) {
            showError('Keranjang masih kosong.');
            return;
        }

        if (isCash() && parseDigits(cashInput.value) < getTotal()) {
            showError('Uang diterima kurang dari total pembayaran.');
            cashInput.focus();
            return;
        }

        syncHiddenFields();
        fillConfirmDialog();

        if (confirmDialog?.showModal) {
            confirmDialog.showModal();
        }
    });

    confirmButton?.addEventListener('click', () => {
        if (submitting) {
            return;
        }

        submitting = true;
        updatePayButton();
        syncHiddenFields();
        form.submit();
    });

    if (idempotencyInput && !idempotencyInput.value) {
        idempotencyInput.value =
            typeof crypto !== 'undefined' && crypto.randomUUID
                ? crypto.randomUUID()
                : `pos-${Date.now()}-${Math.random().toString(16).slice(2)}`;
    }

    if (amountValue.value && parseDigits(amountValue.value) > 0) {
        cashInput.value = formatNumber(parseDigits(amountValue.value));
    }

    (data.oldItems || []).forEach((item) => {
        const product = products.get(Number(item.product_id ?? item.id));
        const quantity = Number(item.quantity);

        if (!product || !Number.isFinite(quantity) || quantity < 1) {
            return;
        }

        const max = product.track_stock ? Math.max(0, Number(product.stock)) : 999;
        const safeQuantity = Math.min(Math.floor(quantity), max, 999);

        if (safeQuantity > 0) {
            cart.set(product.id, safeQuantity);
        }
    });

    if (!isCash()) {
        if (cashPanel) cashPanel.hidden = true;
        if (qrisPanel) qrisPanel.hidden = false;
    }

    renderCart();

    if (data.oldItems && data.oldItems.length) {
        openCart();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPos, { once: true });
} else {
    initPos();
}
