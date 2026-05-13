let timeout;
let pendingConfirmedAction = null;

function filterChocolates() {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        const searchInput = document.getElementById('searchInput');
        const noResultMessage = document.getElementById('noResultMessage');

        if (!searchInput) {
            return;
        }

        const searchTerm = searchInput.value.toLowerCase();
        const visibleRegularPanel = document.querySelector('.regular-tab-panel:not(.is-hidden)');
        const cardScope = visibleRegularPanel || document;
        const cards = cardScope.getElementsByClassName('choco-card');
        let hasResult = false;

        for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const chocolateName = (card.getAttribute('data-name') || '').toLowerCase();

            if (chocolateName.includes(searchTerm)) {
                card.style.display = '';
                hasResult = true;
            } else {
                card.style.display = 'none';
            }
        }

        if (noResultMessage) {
            noResultMessage.style.display = hasResult ? 'none' : 'block';
        }
    }, 300);
}

const itemsDisplay = document.getElementById('items');
const homeDisplay = document.getElementById('home');
const userDisplay = document.getElementById('users');

function setDisplay(element, displayValue) {
    if (element) {
        element.style.display = displayValue;
    }
}

function showItems() {
    setDisplay(itemsDisplay, 'block');
    setDisplay(homeDisplay, 'none');
    setDisplay(userDisplay, 'none');
}

function showHome() {
    setDisplay(itemsDisplay, 'none');
    setDisplay(homeDisplay, 'block');
    setDisplay(userDisplay, 'none');
}

function showUsers() {
    setDisplay(itemsDisplay, 'none');
    setDisplay(homeDisplay, 'none');
    setDisplay(userDisplay, 'block');
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);

    if (modal) {
        modal.classList.add('show');
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);

    if (modal) {
        modal.classList.remove('show');
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    }

    if (modalId === 'confirmActionModal') {
        pendingConfirmedAction = null;
    }
}

function closeAllModals() {
    document.querySelectorAll('.modal-overlay.show').forEach((modal) => {
        modal.classList.remove('show');
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    });

    pendingConfirmedAction = null;
}

function openAddProductModal() {
    openModal('addProductModal');
}

function closeAddProductModal() {
    closeModal('addProductModal');
}

function openAddAdminModal() {
    openModal('addAdminModal');
}

function closeAddAdminModal() {
    closeModal('addAdminModal');
}

function openResetPasswordModal(adminId, adminName) {
    const adminIdInput = document.getElementById('resetPasswordAdminId');
    const adminNameLabel = document.getElementById('resetPasswordAdminName');
    const passwordInput = document.getElementById('newAdminPassword');

    if (adminIdInput) {
        adminIdInput.value = adminId || '';
    }

    if (adminNameLabel) {
        adminNameLabel.textContent = adminName ? `Admin: ${adminName}` : '';
    }

    if (passwordInput) {
        passwordInput.value = '';
    }

    openModal('resetPasswordModal');
}

function closeResetPasswordModal() {
    closeModal('resetPasswordModal');
}


function openConfirmActionModalFromElement(element, action) {
    if (!element) {
        return;
    }

    pendingConfirmedAction = action;

    const title = element.dataset.confirmTitle || 'Confirm Action';
    const message = element.dataset.confirmMessage || 'Are you sure you want to continue?';
    const subtext = element.dataset.confirmSubtext || 'This action will continue after you confirm.';
    const confirmLabel = element.dataset.confirmConfirmLabel || 'Confirm';
    const isDanger = element.dataset.confirmDanger === 'true';

    const modal = document.getElementById('confirmActionModal');
    const titleElement = document.getElementById('confirmActionTitle');
    const messageElement = document.getElementById('confirmActionMessage');
    const subtextElement = document.getElementById('confirmActionSubtext');
    const confirmButton = document.getElementById('confirmActionButton');
    const kicker = document.getElementById('confirmActionKicker');

    if (titleElement) {
        titleElement.textContent = title;
    }

    if (messageElement) {
        messageElement.textContent = message;
    }

    if (subtextElement) {
        subtextElement.textContent = subtext;
    }

    if (confirmButton) {
        confirmButton.textContent = confirmLabel;
        confirmButton.className = isDanger ? 'btn btn-delete' : 'btn btn-submit';
    }

    if (kicker) {
        kicker.textContent = isDanger ? 'Please confirm' : 'Confirmation';
        kicker.classList.toggle('danger-kicker', isDanger);
    }

    if (modal) {
        const dialog = modal.querySelector('.confirmation-modal');
        if (dialog) {
            dialog.classList.toggle('is-danger', isDanger);
        }
    }

    openModal('confirmActionModal');
}

function closeConfirmActionModal() {
    closeModal('confirmActionModal');
}

function confirmPendingAction() {
    const action = pendingConfirmedAction;
    pendingConfirmedAction = null;

    closeModal('confirmActionModal');

    if (!action) {
        return;
    }

    if (action.type === 'form' && action.form) {
        action.form.dataset.confirmed = 'true';

        if (typeof action.form.requestSubmit === 'function') {
            action.form.requestSubmit();
        } else {
            action.form.submit();
        }

        return;
    }

    if (action.type === 'link' && action.href) {
        window.location.href = action.href;
    }
}

function initializeConfirmationHandlers() {
    document.querySelectorAll('form[data-confirm-title]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            openConfirmActionModalFromElement(form, {
                type: 'form',
                form: form
            });
        });
    });

    document.querySelectorAll('a[data-confirm-title]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            openConfirmActionModalFromElement(link, {
                type: 'link',
                href: link.href
            });
        });
    });
}

function initializeFlashMessages() {
    document.querySelectorAll('.flash-message[data-auto-hide]').forEach((message) => {
        const delay = Number(message.dataset.autoHide || 3000);

        setTimeout(() => {
            message.classList.add('is-hiding');

            setTimeout(() => {
                message.remove();
            }, 350);
        }, Math.max(delay, 3000));
    });
}

document.addEventListener('click', (event) => {
    if (event.target && event.target.classList.contains('modal-overlay')) {
        closeModal(event.target.id);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});

function openUpdateProductModalFromButton(button) {
    const inventoryIdInput = document.getElementById('updateProductId');
    const chocolateSelect = document.getElementById('updateChocolateItem');
    const quantityInput = document.getElementById('updateChocolateQuantity');
    const productNameLabel = document.getElementById('updateProductName');

    if (!button) {
        return;
    }

    if (inventoryIdInput) {
        inventoryIdInput.value = button.dataset.inventoryId || '';
    }

    if (chocolateSelect) {
        chocolateSelect.value = button.dataset.chocolateId || '';
    }

    if (quantityInput) {
        quantityInput.value = button.dataset.quantity || '';
    }

    if (productNameLabel) {
        productNameLabel.textContent = button.dataset.chocolateName ? `Product: ${button.dataset.chocolateName}` : '';
    }

    openModal('updateProductModal');
}

function closeUpdateProductModal() {
    closeModal('updateProductModal');
}

function setRegularTab(activePanelId) {
    const activePanel = document.getElementById('regularActivePanel');
    const pendingPanel = document.getElementById('regularPendingPanel');
    const activeTab = document.getElementById('regularActiveTab');
    const pendingTab = document.getElementById('regularPendingTab');
    const noResultMessage = document.getElementById('noResultMessage');

    if (!activePanel || !pendingPanel || !activeTab || !pendingTab) {
        return;
    }

    const showPending = activePanelId === 'regularPendingPanel';

    activePanel.hidden = showPending;
    activePanel.classList.toggle('is-hidden', showPending);
    pendingPanel.hidden = !showPending;
    pendingPanel.classList.toggle('is-hidden', !showPending);

    activeTab.classList.toggle('active', !showPending);
    activeTab.setAttribute('aria-selected', showPending ? 'false' : 'true');

    pendingTab.classList.toggle('active', showPending);
    pendingTab.setAttribute('aria-selected', showPending ? 'true' : 'false');

    if (noResultMessage) {
        noResultMessage.style.display = 'none';
    }

    const searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.value.trim() !== '') {
        filterChocolates();
    }
}

function showRegularActive() {
    setRegularTab('regularActivePanel');
}

function showRegularPending() {
    setRegularTab('regularPendingPanel');
}

document.addEventListener('DOMContentLoaded', () => {
    const regularPendingPanel = document.getElementById('regularPendingPanel');

    if (!regularPendingPanel) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const success = params.get('success');
    const error = params.get('error');

    if (
        success === 'product_pending' ||
        success === 'product_submitted' ||
        success === 'product_updated' ||
        success === 'product_deleted' ||
        error === 'invalid_product'
    ) {
        showRegularPending();
    } else {
        showRegularActive();
    }
});


document.addEventListener('DOMContentLoaded', () => {
    initializeConfirmationHandlers();
    initializeFlashMessages();
});

function toggleLoginPassword() {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.querySelector('.password-toggle');

    if (!passwordInput) {
        return;
    }

    const shouldShow = passwordInput.type === 'password';
    passwordInput.type = shouldShow ? 'text' : 'password';

    if (toggleButton) {
        toggleButton.textContent = shouldShow ? 'Hide' : 'Show';
    }
}
