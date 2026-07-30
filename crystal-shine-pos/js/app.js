const STORAGE_KEYS = {
    services: 'cs_services',
    sales: 'cs_sales',
    settings: 'cs_settings',
    receiptCounter: 'cs_receipt_counter',
};

const DEFAULT_SERVICES = [
    { id: 'body-wash', name: 'Body Wash', icon: 'bi-droplet-half', price: 500 },
    { id: 'under-wash', name: 'Under Wash', icon: 'bi-water', price: 300 },
    { id: 'engine-wash', name: 'Engine Wash', icon: 'bi-gear-wide-connected', price: 400 },
    { id: 'car-detailing', name: 'Car Detailing', icon: 'bi-spray', price: 2500 },
    { id: 'carpet-cleaning', name: 'Carpet Cleaning', icon: 'bi-wind', price: 800 },
    { id: 'car-buffing', name: 'Car Buffing', icon: 'bi-stars', price: 1500 },
];

const DEFAULT_SETTINGS = {
    businessName: 'Crystal Shine',
    tagline: 'Car Wash and Carpet Cleaning Services',
    phone: '',
    location: '',
};

const VEHICLE_MULTIPLIERS = {
    saloon: 1,
    suv: 1.3,
    pickup: 1.5,
    motorcycle: 0.6,
    carpet: 1,
};

let services = [];
let cart = {};
let paymentMethod = 'cash';
let settings = {};

function loadData() {
    services = JSON.parse(localStorage.getItem(STORAGE_KEYS.services)) || [...DEFAULT_SERVICES];
    settings = { ...DEFAULT_SETTINGS, ...JSON.parse(localStorage.getItem(STORAGE_KEYS.settings) || '{}') };
}

function saveServices() {
    localStorage.setItem(STORAGE_KEYS.services, JSON.stringify(services));
}

function saveSettings() {
    localStorage.setItem(STORAGE_KEYS.settings, JSON.stringify(settings));
}

function getSales() {
    return JSON.parse(localStorage.getItem(STORAGE_KEYS.sales)) || [];
}

function saveSales(sales) {
    localStorage.setItem(STORAGE_KEYS.sales, JSON.stringify(sales));
}

function nextReceiptNumber() {
    let counter = parseInt(localStorage.getItem(STORAGE_KEYS.receiptCounter) || '0', 10) + 1;
    localStorage.setItem(STORAGE_KEYS.receiptCounter, counter.toString());
    const date = new Date();
    const prefix = `CS${date.getFullYear()}${String(date.getMonth() + 1).padStart(2, '0')}`;
    return `${prefix}-${String(counter).padStart(4, '0')}`;
}

function formatCurrency(amount) {
    return `KES ${Math.round(amount).toLocaleString()}`;
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.hidden = false;
    setTimeout(() => { toast.hidden = true; }, 3000);
}

function updateDateTime() {
    const el = document.getElementById('currentDateTime');
    if (el) {
        el.textContent = new Date().toLocaleString('en-KE', {
            weekday: 'short', year: 'numeric', month: 'short',
            day: 'numeric', hour: '2-digit', minute: '2-digit',
        });
    }
}

function getVehicleMultiplier() {
    const type = document.getElementById('vehicleType').value;
    return VEHICLE_MULTIPLIERS[type] || 1;
}

function getServicePrice(service) {
    return Math.round(service.price * getVehicleMultiplier());
}

function renderServices() {
    const grid = document.getElementById('servicesGrid');
    grid.innerHTML = services.map(s => {
        const price = getServicePrice(s);
        const inCart = cart[s.id] > 0;
        return `
            <div class="service-card ${inCart ? 'in-cart' : ''}" data-id="${s.id}">
                <i class="bi ${s.icon} service-icon"></i>
                <div class="service-name">${s.name}</div>
                <div class="service-price">${formatCurrency(price)}</div>
            </div>
        `;
    }).join('');

    grid.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('click', () => addToCart(card.dataset.id));
    });
}

function addToCart(serviceId) {
    cart[serviceId] = (cart[serviceId] || 0) + 1;
    renderCart();
    renderServices();
}

function updateQuantity(serviceId, delta) {
    cart[serviceId] = (cart[serviceId] || 0) + delta;
    if (cart[serviceId] <= 0) delete cart[serviceId];
    renderCart();
    renderServices();
}

function clearCart() {
    cart = {};
    document.getElementById('customerName').value = '';
    document.getElementById('customerPhone').value = '';
    document.getElementById('vehiclePlate').value = '';
    document.getElementById('vehicleType').value = 'saloon';
    document.getElementById('discountAmount').value = '0';
    document.getElementById('mpesaRef').value = '';
    paymentMethod = 'cash';
    document.querySelectorAll('.payment-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.method === 'cash');
    });
    document.getElementById('mpesaRefSection').hidden = true;
    renderCart();
    renderServices();
}

function calculateTotals() {
    let subtotal = 0;
    for (const [id, qty] of Object.entries(cart)) {
        const service = services.find(s => s.id === id);
        if (service) subtotal += getServicePrice(service) * qty;
    }
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const total = Math.max(0, subtotal - discount);
    return { subtotal, discount, total };
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const keys = Object.keys(cart);

    if (keys.length === 0) {
        container.innerHTML = '<p class="cart-empty">No services selected</p>';
    } else {
        container.innerHTML = keys.map(id => {
            const service = services.find(s => s.id === id);
            const price = getServicePrice(service);
            const qty = cart[id];
            const lineTotal = price * qty;
            return `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${service.name}</div>
                        <div class="cart-item-price">${formatCurrency(price)} each</div>
                    </div>
                    <div class="cart-item-controls">
                        <button class="qty-btn" onclick="updateQuantity('${id}', -1)">−</button>
                        <span class="qty-value">${qty}</span>
                        <button class="qty-btn" onclick="updateQuantity('${id}', 1)">+</button>
                    </div>
                    <div class="cart-item-total">${formatCurrency(lineTotal)}</div>
                </div>
            `;
        }).join('');
    }

    const { subtotal, total } = calculateTotals();
    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('grandTotal').textContent = formatCurrency(total);

    const plate = document.getElementById('vehiclePlate').value.trim();
    document.getElementById('checkoutBtn').disabled = keys.length === 0 || !plate;
}

function checkout() {
    const plate = document.getElementById('vehiclePlate').value.trim();
    if (!plate) {
        showToast('Please enter the vehicle plate number', 'error');
        return;
    }

    if (Object.keys(cart).length === 0) {
        showToast('Please add at least one service', 'error');
        return;
    }

    if (paymentMethod === 'mpesa') {
        const ref = document.getElementById('mpesaRef').value.trim();
        if (!ref) {
            showToast('Please enter M-Pesa transaction code', 'error');
            return;
        }
    }

    const { subtotal, discount, total } = calculateTotals();
    const receiptNo = nextReceiptNumber();

    const items = Object.entries(cart).map(([id, qty]) => {
        const service = services.find(s => s.id === id);
        const unitPrice = getServicePrice(service);
        return {
            id, name: service.name, qty,
            unitPrice, lineTotal: unitPrice * qty,
        };
    });

    const sale = {
        receiptNo,
        date: new Date().toISOString(),
        customerName: document.getElementById('customerName').value.trim(),
        customerPhone: document.getElementById('customerPhone').value.trim(),
        vehiclePlate: plate,
        vehicleType: document.getElementById('vehicleType').value,
        items,
        subtotal,
        discount,
        total,
        paymentMethod,
        mpesaRef: paymentMethod === 'mpesa' ? document.getElementById('mpesaRef').value.trim() : '',
    };

    const sales = getSales();
    sales.unshift(sale);
    saveSales(sales);

    showReceipt(sale);
    updateSidebarStats();
    clearCart();
}

function showReceipt(sale) {
    const paymentLabels = { cash: 'Cash', mpesa: 'M-Pesa', card: 'Card' };
    const vehicleLabels = {
        saloon: 'Saloon/Sedan', suv: 'SUV/4x4',
        pickup: 'Pickup/Lorry', motorcycle: 'Motorcycle', carpet: 'Carpet Only',
    };

    document.getElementById('receiptContent').innerHTML = `
        <div class="receipt-header">
            <h3>${settings.businessName.toUpperCase()}</h3>
            <p>${settings.tagline}</p>
            ${settings.phone ? `<p>Tel: ${settings.phone}</p>` : ''}
            ${settings.location ? `<p>${settings.location}</p>` : ''}
        </div>
        <div class="receipt-meta">
            <div>Receipt: <strong>${sale.receiptNo}</strong></div>
            <div>Date: ${new Date(sale.date).toLocaleString('en-KE')}</div>
            ${sale.customerName ? `<div>Customer: ${sale.customerName}</div>` : ''}
            ${sale.customerPhone ? `<div>Phone: ${sale.customerPhone}</div>` : ''}
            <div>Plate: <strong>${sale.vehiclePlate}</strong></div>
            <div>Vehicle: ${vehicleLabels[sale.vehicleType] || sale.vehicleType}</div>
            <div>Payment: ${paymentLabels[sale.paymentMethod]}</div>
            ${sale.mpesaRef ? `<div>M-Pesa Ref: ${sale.mpesaRef}</div>` : ''}
        </div>
        <div class="receipt-items">
            ${sale.items.map(i => `
                <div class="receipt-item">
                    <span>${i.name} x${i.qty}</span>
                    <span>${formatCurrency(i.lineTotal)}</span>
                </div>
            `).join('')}
        </div>
        ${sale.discount > 0 ? `
            <div class="receipt-item"><span>Subtotal</span><span>${formatCurrency(sale.subtotal)}</span></div>
            <div class="receipt-item"><span>Discount</span><span>-${formatCurrency(sale.discount)}</span></div>
        ` : ''}
        <div class="receipt-total">
            <span>TOTAL</span>
            <span>${formatCurrency(sale.total)}</span>
        </div>
        <div class="receipt-footer">
            <p>Thank you for choosing ${settings.businessName}!</p>
            <p>Drive clean, shine bright ✨</p>
        </div>
    `;

    document.getElementById('receiptModal').hidden = false;
}

function updateSidebarStats() {
    const today = new Date().toDateString();
    const todaySales = getSales().filter(s => new Date(s.date).toDateString() === today);
    const total = todaySales.reduce((sum, s) => sum + s.total, 0);
    document.getElementById('sidebarTodayTotal').textContent = formatCurrency(total);
    document.getElementById('sidebarTodayCount').textContent = `${todaySales.length} transaction${todaySales.length !== 1 ? 's' : ''}`;
}

function renderHistory() {
    const filterDate = document.getElementById('historyDateFilter').value;
    let sales = getSales();

    if (filterDate) {
        sales = sales.filter(s => s.date.startsWith(filterDate));
    }

    const tbody = document.getElementById('historyTableBody');
    const empty = document.getElementById('historyEmpty');

    if (sales.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';
    const paymentLabels = { cash: 'Cash', mpesa: 'M-Pesa', card: 'Card' };

    tbody.innerHTML = sales.map(sale => `
        <tr>
            <td><strong>${sale.receiptNo}</strong></td>
            <td>${new Date(sale.date).toLocaleString('en-KE')}</td>
            <td>${sale.customerName || '—'}</td>
            <td>${sale.vehiclePlate}</td>
            <td>${sale.items.map(i => i.name).join(', ')}</td>
            <td>${paymentLabels[sale.paymentMethod]}</td>
            <td><strong>${formatCurrency(sale.total)}</strong></td>
            <td>
                <button class="btn-icon" onclick="reprintSale('${sale.receiptNo}')" title="Reprint">
                    <i class="bi bi-printer"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function reprintSale(receiptNo) {
    const sale = getSales().find(s => s.receiptNo === receiptNo);
    if (sale) showReceipt(sale);
}

function getFilteredSales(period) {
    const sales = getSales();
    const now = new Date();

    if (period === 'all') return sales;

    if (period === 'today') {
        const today = now.toDateString();
        return sales.filter(s => new Date(s.date).toDateString() === today);
    }

    if (period === 'week') {
        const weekAgo = new Date(now);
        weekAgo.setDate(weekAgo.getDate() - 7);
        return sales.filter(s => new Date(s.date) >= weekAgo);
    }

    if (period === 'month') {
        const monthAgo = new Date(now);
        monthAgo.setMonth(monthAgo.getMonth() - 1);
        return sales.filter(s => new Date(s.date) >= monthAgo);
    }

    return sales;
}

function renderReports() {
    const period = document.getElementById('reportPeriod').value;
    const sales = getFilteredSales(period);

    const revenue = sales.reduce((sum, s) => sum + s.total, 0);
    const count = sales.length;
    const average = count > 0 ? revenue / count : 0;

    document.getElementById('reportRevenue').textContent = formatCurrency(revenue);
    document.getElementById('reportTransactions').textContent = count;
    document.getElementById('reportAverage').textContent = formatCurrency(average);

    const serviceCounts = {};
    sales.forEach(s => {
        s.items.forEach(i => {
            serviceCounts[i.name] = (serviceCounts[i.name] || 0) + i.qty;
        });
    });

    const topService = Object.entries(serviceCounts).sort((a, b) => b[1] - a[1])[0];
    document.getElementById('reportTopService').textContent = topService ? topService[0] : '—';

    const serviceRevenue = {};
    sales.forEach(s => {
        s.items.forEach(i => {
            serviceRevenue[i.name] = (serviceRevenue[i.name] || 0) + i.lineTotal;
        });
    });

    const maxServiceRev = Math.max(...Object.values(serviceRevenue), 1);
    document.getElementById('serviceBreakdown').innerHTML = Object.entries(serviceRevenue)
        .sort((a, b) => b[1] - a[1])
        .map(([name, amount]) => `
            <div class="breakdown-item">
                <span class="breakdown-label">${name}</span>
                <div class="breakdown-bar-wrap">
                    <div class="breakdown-bar" style="width: ${(amount / maxServiceRev) * 100}%"></div>
                </div>
                <span class="breakdown-value">${formatCurrency(amount)}</span>
            </div>
        `).join('') || '<p class="cart-empty">No data yet</p>';

    const paymentTotals = { cash: 0, mpesa: 0, card: 0 };
    sales.forEach(s => { paymentTotals[s.paymentMethod] = (paymentTotals[s.paymentMethod] || 0) + s.total; });

    const maxPayment = Math.max(...Object.values(paymentTotals), 1);
    const paymentLabels = { cash: 'Cash', mpesa: 'M-Pesa', card: 'Card' };

    document.getElementById('paymentBreakdown').innerHTML = Object.entries(paymentTotals)
        .filter(([, amount]) => amount > 0)
        .sort((a, b) => b[1] - a[1])
        .map(([method, amount]) => `
            <div class="breakdown-item">
                <span class="breakdown-label">${paymentLabels[method]}</span>
                <div class="breakdown-bar-wrap">
                    <div class="breakdown-bar" style="width: ${(amount / maxPayment) * 100}%"></div>
                </div>
                <span class="breakdown-value">${formatCurrency(amount)}</span>
            </div>
        `).join('') || '<p class="cart-empty">No data yet</p>';
}

function renderSettings() {
    document.getElementById('settingBusinessName').value = settings.businessName;
    document.getElementById('settingTagline').value = settings.tagline;
    document.getElementById('settingPhone').value = settings.phone;
    document.getElementById('settingLocation').value = settings.location;

    document.getElementById('priceSettings').innerHTML = services.map(s => `
        <div class="price-row">
            <label>${s.name}</label>
            <input type="number" data-service-id="${s.id}" value="${s.price}" min="0" step="50">
        </div>
    `).join('');
}

function saveSettingsFromForm() {
    settings.businessName = document.getElementById('settingBusinessName').value.trim() || 'Crystal Shine';
    settings.tagline = document.getElementById('settingTagline').value.trim();
    settings.phone = document.getElementById('settingPhone').value.trim();
    settings.location = document.getElementById('settingLocation').value.trim();

    document.querySelectorAll('#priceSettings input').forEach(input => {
        const service = services.find(s => s.id === input.dataset.serviceId);
        if (service) service.price = parseInt(input.value, 10) || 0;
    });

    saveSettings();
    saveServices();
    renderServices();
    showToast('Settings saved successfully');
}

function exportCsv() {
    const filterDate = document.getElementById('historyDateFilter').value;
    let sales = getSales();
    if (filterDate) sales = sales.filter(s => s.date.startsWith(filterDate));

    if (sales.length === 0) {
        showToast('No sales to export', 'error');
        return;
    }

    const headers = ['Receipt', 'Date', 'Customer', 'Phone', 'Plate', 'Vehicle', 'Services', 'Payment', 'M-Pesa Ref', 'Subtotal', 'Discount', 'Total'];
    const rows = sales.map(s => [
        s.receiptNo,
        new Date(s.date).toLocaleString('en-KE'),
        s.customerName, s.customerPhone, s.vehiclePlate, s.vehicleType,
        s.items.map(i => `${i.name} x${i.qty}`).join('; '),
        s.paymentMethod, s.mpesaRef || '',
        s.subtotal, s.discount, s.total,
    ]);

    const csv = [headers, ...rows].map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `crystal-shine-sales-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    showToast('CSV exported');
}

function backupData() {
    const data = {
        services,
        settings,
        sales: getSales(),
        exportedAt: new Date().toISOString(),
    };
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `crystal-shine-backup-${new Date().toISOString().slice(0, 10)}.json`;
    a.click();
    showToast('Backup downloaded');
}

function resetData() {
    if (confirm('This will delete ALL sales history. Are you sure?')) {
        if (confirm('This action cannot be undone. Proceed?')) {
            localStorage.removeItem(STORAGE_KEYS.sales);
            localStorage.removeItem(STORAGE_KEYS.receiptCounter);
            updateSidebarStats();
            renderHistory();
            renderReports();
            showToast('All sales data has been reset', 'error');
        }
    }
}

function switchView(viewName) {
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

    document.getElementById(`view-${viewName}`).classList.add('active');
    document.querySelector(`[data-view="${viewName}"]`).classList.add('active');

    if (viewName === 'history') renderHistory();
    if (viewName === 'reports') renderReports();
    if (viewName === 'settings') renderSettings();
}

function init() {
    loadData();
    updateDateTime();
    setInterval(updateDateTime, 60000);

    renderServices();
    renderCart();
    updateSidebarStats();

    document.getElementById('historyDateFilter').value = new Date().toISOString().slice(0, 10);

    document.querySelectorAll('.nav-item').forEach(btn => {
        btn.addEventListener('click', () => switchView(btn.dataset.view));
    });

    document.getElementById('vehiclePlate').addEventListener('input', renderCart);
    document.getElementById('vehicleType').addEventListener('change', () => {
        renderServices();
        renderCart();
    });
    document.getElementById('discountAmount').addEventListener('input', renderCart);

    document.getElementById('clearCart').addEventListener('click', clearCart);
    document.getElementById('checkoutBtn').addEventListener('click', checkout);

    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            paymentMethod = btn.dataset.method;
            document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('mpesaRefSection').hidden = paymentMethod !== 'mpesa';
        });
    });

    document.getElementById('closeReceipt').addEventListener('click', () => {
        document.getElementById('receiptModal').hidden = true;
    });

    document.getElementById('newSaleBtn').addEventListener('click', () => {
        document.getElementById('receiptModal').hidden = true;
        switchView('pos');
    });

    document.getElementById('printReceipt').addEventListener('click', () => window.print());

    document.getElementById('historyDateFilter').addEventListener('change', renderHistory);
    document.getElementById('exportCsv').addEventListener('click', exportCsv);
    document.getElementById('reportPeriod').addEventListener('change', renderReports);
    document.getElementById('saveSettings').addEventListener('click', saveSettingsFromForm);
    document.getElementById('backupData').addEventListener('click', backupData);
    document.getElementById('resetData').addEventListener('click', resetData);
}

document.addEventListener('DOMContentLoaded', init);
