let timeout;
let pendingActionForm = null;
let pendingActionLink = null;

function filterChocolates() {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        let searchInput = document.getElementById('searchInput');
        let noResultMessage = document.getElementById('noResultMessage');

        if (!searchInput) {
            return;
        }

        let searchTerm = searchInput.value.toLowerCase();
        let cards = document.getElementsByClassName('choco-card');
        let hasResult = false;

        for (let i = 0; i < cards.length; i++) {
            let card = cards[i];
            let chocolateName = (card.getAttribute('data-name') || '').toLowerCase();

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

function showHome() {
    let home = document.getElementById('home');
    let items = document.getElementById('items');
    let users = document.getElementById('users');
    let archives = document.getElementById('archives');

    if (home) home.style.display = 'block';
    if (items) items.style.display = 'none';
    if (users) users.style.display = 'none';
    if (archives) archives.style.display = 'none';
}

function showItems() {
    let home = document.getElementById('home');
    let items = document.getElementById('items');
    let users = document.getElementById('users');
    let archives = document.getElementById('archives');

    if (home) home.style.display = 'none';
    if (items) items.style.display = 'block';
    if (users) users.style.display = 'none';
    if (archives) archives.style.display = 'none';
}

function showUsers() {
    let home = document.getElementById('home');
    let items = document.getElementById('items');
    let users = document.getElementById('users');
    let archives = document.getElementById('archives');

    if (home) home.style.display = 'none';
    if (items) items.style.display = 'none';
    if (users) users.style.display = 'block';
    if (archives) archives.style.display = 'none';
}

function showArchives() {
    let home = document.getElementById('home');
    let items = document.getElementById('items');
    let users = document.getElementById('users');
    let archives = document.getElementById('archives');

    if (home) home.style.display = 'none';
    if (items) items.style.display = 'none';
    if (users) users.style.display = 'none';
    if (archives) archives.style.display = 'block';
}

function setActiveAdminTab(button) {
    let tabs = document.querySelectorAll('.admin-dashboard-nav .tab');

    for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }

    if (button) {
        button.classList.add('active');
    }
}

function showAdminSection(sectionId, button) {
    if (sectionId === 'home') showHome();
    if (sectionId === 'items') showItems();
    if (sectionId === 'users') showUsers();
    if (sectionId === 'archives') showArchives();

    setActiveAdminTab(button);
}

function openModal(modalId) {
    let modal = document.getElementById(modalId);

    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
    }
}

function closeModal(modalId) {
    let modal = document.getElementById(modalId);

    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    }

    if (modalId === 'confirmActionModal') {
        pendingActionForm = null;
        pendingActionLink = null;
    }
}

function closeAllModals() {
    let modals = document.getElementsByClassName('modal-overlay');

    for (let i = 0; i < modals.length; i++) {
        modals[i].style.display = 'none';
        modals[i].classList.remove('show');
        modals[i].classList.remove('active');
        modals[i].setAttribute('aria-hidden', 'true');
    }

    pendingActionForm = null;
    pendingActionLink = null;
}

function openAddAdminModal() {
    openModal('addAdminModal');
}

function openResetPasswordModal(adminId, adminName) {
    let adminIdInput = document.getElementById('resetPasswordAdminId');
    let adminNameLabel = document.getElementById('resetPasswordAdminName');
    let passwordInput = document.getElementById('newAdminPassword');
    let confirmPasswordInput = document.getElementById('confirmAdminPassword');

    if (adminIdInput) adminIdInput.value = adminId || '';
    if (adminNameLabel) adminNameLabel.textContent = adminName ? 'Admin: ' + adminName : '';
    if (passwordInput) passwordInput.value = '';
    if (confirmPasswordInput) confirmPasswordInput.value = '';

    openModal('resetPasswordModal');
}

function openAdminAddItemModal() {
    openModal('adminAddItemModal');
}

function openAdminAddUserModal() {
    let password = document.getElementById('adminUserPassword');
    let confirmPassword = document.getElementById('adminUserConfirmPassword');

    if (password) password.value = '';
    if (confirmPassword) confirmPassword.value = '';

    openModal('adminAddUserModal');
}

function openAdminUpdateItemModal(button) {
    let itemId = document.getElementById('adminUpdateItemId');
    let itemName = document.getElementById('adminUpdateItemName');
    let itemSelect = document.getElementById('adminUpdateChocolateItem');
    let itemQuantity = document.getElementById('adminUpdateItemQuantity');

    if (itemId) itemId.value = button.getAttribute('data-inventory-id') || '';
    if (itemName) itemName.textContent = button.getAttribute('data-chocolate-name') || '';
    if (itemSelect) itemSelect.value = button.getAttribute('data-chocolate-id') || '';
    if (itemQuantity) itemQuantity.value = button.getAttribute('data-quantity') || '';

    openModal('adminUpdateItemModal');
}

function openAdminViewItemModal(button) {
    let itemName = document.getElementById('adminViewItemName');
    let itemDetails = document.getElementById('adminViewItemDetails');

    let name = button.getAttribute('data-chocolate-name') || 'Chocolate item';
    let quantity = button.getAttribute('data-quantity') || '0';
    let createdBy = button.getAttribute('data-created-by') || 'Unknown user';
    let createdByEmail = button.getAttribute('data-created-by-email') || '';
    let createdByRole = button.getAttribute('data-created-by-role') || '';
    let createdAt = button.getAttribute('data-created-at') || '';

    if (itemName) itemName.textContent = name;

    if (itemDetails) {
        itemDetails.innerHTML =
            'Quantity: <strong>' + quantity + '</strong><br>' +
            'Created by: ' + createdBy + (createdByEmail ? ' · ' + createdByEmail : '') + '<br>' +
            'Role: ' + createdByRole + '<br>' +
            'Created at: ' + createdAt;
    }

    openModal('adminViewItemModal');
}

function openAdminResetUserPasswordModal(userId, userName) {
    let idInput = document.getElementById('adminResetUserPasswordId');
    let nameText = document.getElementById('adminResetUserPasswordName');
    let passwordInput = document.getElementById('adminNewUserPassword');
    let confirmPasswordInput = document.getElementById('adminConfirmUserPassword');

    if (idInput) idInput.value = userId || '';
    if (nameText) nameText.textContent = userName ? 'User: ' + userName : '';
    if (passwordInput) passwordInput.value = '';
    if (confirmPasswordInput) confirmPasswordInput.value = '';

    openModal('adminResetUserPasswordModal');
}

function openAddProductModal() {
    openModal('addProductModal');
}

function openUpdateProductModalFromButton(button) {
    let inventoryIdInput = document.getElementById('updateProductId');
    let chocolateSelect = document.getElementById('updateChocolateItem');
    let quantityInput = document.getElementById('updateChocolateQuantity');
    let productNameLabel = document.getElementById('updateProductName');

    if (inventoryIdInput) inventoryIdInput.value = button.getAttribute('data-inventory-id') || '';
    if (chocolateSelect) chocolateSelect.value = button.getAttribute('data-chocolate-id') || '';
    if (quantityInput) quantityInput.value = button.getAttribute('data-quantity') || '';
    if (productNameLabel) productNameLabel.textContent = button.getAttribute('data-chocolate-name') ? 'Product: ' + button.getAttribute('data-chocolate-name') : '';

    openModal('updateProductModal');
}

function showRegularActive() {
    let activePanel = document.getElementById('regularActivePanel');
    let pendingPanel = document.getElementById('regularPendingPanel');
    let activeTab = document.getElementById('regularActiveTab');
    let pendingTab = document.getElementById('regularPendingTab');
    let noResultMessage = document.getElementById('noResultMessage');

    if (activePanel) {
        activePanel.style.display = 'block';
        activePanel.hidden = false;
        activePanel.classList.remove('is-hidden');
    }

    if (pendingPanel) {
        pendingPanel.style.display = 'none';
        pendingPanel.hidden = true;
        pendingPanel.classList.add('is-hidden');
    }

    if (activeTab) {
        activeTab.classList.add('active');
        activeTab.setAttribute('aria-selected', 'true');
    }

    if (pendingTab) {
        pendingTab.classList.remove('active');
        pendingTab.setAttribute('aria-selected', 'false');
    }

    if (noResultMessage) noResultMessage.style.display = 'none';
}

function showRegularPending() {
    let activePanel = document.getElementById('regularActivePanel');
    let pendingPanel = document.getElementById('regularPendingPanel');
    let activeTab = document.getElementById('regularActiveTab');
    let pendingTab = document.getElementById('regularPendingTab');
    let noResultMessage = document.getElementById('noResultMessage');

    if (activePanel) {
        activePanel.style.display = 'none';
        activePanel.hidden = true;
        activePanel.classList.add('is-hidden');
    }

    if (pendingPanel) {
        pendingPanel.style.display = 'block';
        pendingPanel.hidden = false;
        pendingPanel.classList.remove('is-hidden');
    }

    if (activeTab) {
        activeTab.classList.remove('active');
        activeTab.setAttribute('aria-selected', 'false');
    }

    if (pendingTab) {
        pendingTab.classList.add('active');
        pendingTab.setAttribute('aria-selected', 'true');
    }

    if (noResultMessage) noResultMessage.style.display = 'none';
}

function validateConfirmPassword(passwordId, confirmPasswordId) {
    let password = document.getElementById(passwordId);
    let confirmPassword = document.getElementById(confirmPasswordId);

    if (!password || !confirmPassword) {
        return true;
    }

    if (confirmPassword.value !== '' && password.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity('Passwords do not match.');
        return false;
    }

    confirmPassword.setCustomValidity('');
    return true;
}

function validateFormPasswords(form) {
    let pairs = [
        ['newAdminPassword', 'confirmAdminPassword'],
        ['adminUserPassword', 'adminUserConfirmPassword'],
        ['adminNewUserPassword', 'adminConfirmUserPassword']
    ];

    for (let i = 0; i < pairs.length; i++) {
        let password = document.getElementById(pairs[i][0]);
        let confirmPassword = document.getElementById(pairs[i][1]);

        if (password && confirmPassword && form.contains(password) && form.contains(confirmPassword)) {
            let passwordsMatch = validateConfirmPassword(pairs[i][0], pairs[i][1]);

            if (!passwordsMatch) {
                confirmPassword.reportValidity();
                return false;
            }
        }
    }

    return true;
}

function openConfirmActionModal(form) {
    pendingActionForm = form;
    pendingActionLink = null;

    let title = document.getElementById('confirmActionTitle');
    let message = document.getElementById('confirmActionMessage');
    let subtext = document.getElementById('confirmActionSubtext');
    let button = document.getElementById('confirmActionButton');
    let kicker = document.getElementById('confirmActionKicker');
    let icon = document.getElementById('confirmActionIcon');

    if (title) title.textContent = form.getAttribute('data-confirm-title') || 'Confirm Action';
    if (message) message.textContent = form.getAttribute('data-confirm-message') || 'Are you sure you want to continue?';
    if (subtext) subtext.textContent = form.getAttribute('data-confirm-subtext') || 'This action will continue after you confirm.';
    if (button) button.textContent = form.getAttribute('data-confirm-confirm-label') || 'Confirm';

    if (form.getAttribute('data-confirm-danger') === 'true') {
        if (button) button.className = 'btn btn-delete';
        if (kicker) kicker.textContent = 'Please confirm';
        if (icon) icon.textContent = '!';
    } else {
        if (button) button.className = 'btn btn-submit';
        if (kicker) kicker.textContent = 'Confirmation';
        if (icon) icon.textContent = '✓';
    }

    openModal('confirmActionModal');
}

function openConfirmLinkModal(link) {
    pendingActionForm = null;
    pendingActionLink = link.href;

    let title = document.getElementById('confirmActionTitle');
    let message = document.getElementById('confirmActionMessage');
    let subtext = document.getElementById('confirmActionSubtext');
    let button = document.getElementById('confirmActionButton');
    let kicker = document.getElementById('confirmActionKicker');
    let icon = document.getElementById('confirmActionIcon');

    if (title) title.textContent = link.getAttribute('data-confirm-title') || 'Confirm Action';
    if (message) message.textContent = link.getAttribute('data-confirm-message') || 'Are you sure you want to continue?';
    if (subtext) subtext.textContent = link.getAttribute('data-confirm-subtext') || 'This action will continue after you confirm.';
    if (button) button.textContent = link.getAttribute('data-confirm-confirm-label') || 'Confirm';

    if (link.getAttribute('data-confirm-danger') === 'true') {
        if (button) button.className = 'btn btn-delete';
        if (kicker) kicker.textContent = 'Please confirm';
        if (icon) icon.textContent = '!';
    } else {
        if (button) button.className = 'btn btn-submit';
        if (kicker) kicker.textContent = 'Confirmation';
        if (icon) icon.textContent = '✓';
    }

    openModal('confirmActionModal');
}

function confirmPendingAction() {
    let formToSubmit = pendingActionForm;
    let linkToOpen = pendingActionLink;

    pendingActionForm = null;
    pendingActionLink = null;
    closeModal('confirmActionModal');

    if (formToSubmit) {
        formToSubmit.submit();
        return;
    }

    if (linkToOpen) {
        window.location.href = linkToOpen;
    }
}

function toggleLoginPassword() {
    let passwordInput = document.getElementById('password');
    let toggleButton = document.querySelector('.password-toggle');

    if (!passwordInput) {
        return;
    }

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (toggleButton) toggleButton.textContent = 'Hide';
    } else {
        passwordInput.type = 'password';
        if (toggleButton) toggleButton.textContent = 'Show';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    let searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.addEventListener('input', filterChocolates);

    let openAddAdminButton = document.getElementById('openAddAdminButton');
    if (openAddAdminButton) openAddAdminButton.addEventListener('click', openAddAdminModal);

    let resetAdminButtons = document.getElementsByClassName('reset-admin-password-button');
    for (let i = 0; i < resetAdminButtons.length; i++) {
        resetAdminButtons[i].addEventListener('click', function () {
            openResetPasswordModal(this.getAttribute('data-admin-id'), this.getAttribute('data-admin-name'));
        });
    }

    let openAdminAddItemButton = document.getElementById('openAdminAddItemButton');
    if (openAdminAddItemButton) openAdminAddItemButton.addEventListener('click', openAdminAddItemModal);

    let openAdminAddUserButtons = document.getElementsByClassName('open-admin-add-user-button');
    for (let i = 0; i < openAdminAddUserButtons.length; i++) {
        openAdminAddUserButtons[i].addEventListener('click', openAdminAddUserModal);
    }

    let adminTabs = document.querySelectorAll('[data-admin-section]');
    for (let i = 0; i < adminTabs.length; i++) {
        adminTabs[i].addEventListener('click', function () {
            showAdminSection(this.getAttribute('data-admin-section'), this);
        });
    }

    let adminViewButtons = document.getElementsByClassName('admin-view-item-button');
    for (let i = 0; i < adminViewButtons.length; i++) {
        adminViewButtons[i].addEventListener('click', function () {
            openAdminViewItemModal(this);
        });
    }

    let adminUpdateButtons = document.getElementsByClassName('admin-update-item-button');
    for (let i = 0; i < adminUpdateButtons.length; i++) {
        adminUpdateButtons[i].addEventListener('click', function () {
            openAdminUpdateItemModal(this);
        });
    }

    let adminResetUserButtons = document.getElementsByClassName('admin-reset-user-password-button');
    for (let i = 0; i < adminResetUserButtons.length; i++) {
        adminResetUserButtons[i].addEventListener('click', function () {
            openAdminResetUserPasswordModal(this.getAttribute('data-user-id'), this.getAttribute('data-user-name'));
        });
    }

    let openAddProductButton = document.getElementById('openAddProductButton');
    if (openAddProductButton) openAddProductButton.addEventListener('click', openAddProductModal);

    let updateProductButtons = document.getElementsByClassName('update-product-button');
    for (let i = 0; i < updateProductButtons.length; i++) {
        updateProductButtons[i].addEventListener('click', function () {
            openUpdateProductModalFromButton(this);
        });
    }

    let regularActiveTab = document.querySelector('[data-regular-tab="active"]');
    let regularPendingTab = document.querySelector('[data-regular-tab="pending"]');

    if (regularActiveTab) regularActiveTab.addEventListener('click', showRegularActive);
    if (regularPendingTab) regularPendingTab.addEventListener('click', showRegularPending);

    let closeButtons = document.querySelectorAll('[data-close-modal]');
    for (let i = 0; i < closeButtons.length; i++) {
        closeButtons[i].addEventListener('click', function () {
            closeModal(this.getAttribute('data-close-modal'));
        });
    }

    let confirmButton = document.getElementById('confirmActionButton');
    if (confirmButton) confirmButton.addEventListener('click', confirmPendingAction);

    let confirmForms = document.querySelectorAll('form[data-confirm-title]');
    for (let i = 0; i < confirmForms.length; i++) {
        confirmForms[i].addEventListener('submit', function (event) {
            if (!validateFormPasswords(this)) {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            openConfirmActionModal(this);
        });
    }

    let confirmLinks = document.querySelectorAll('a[data-confirm-title]');
    for (let i = 0; i < confirmLinks.length; i++) {
        confirmLinks[i].addEventListener('click', function (event) {
            event.preventDefault();
            openConfirmLinkModal(this);
        });
    }

    let passwordPairs = [
        ['newAdminPassword', 'confirmAdminPassword'],
        ['adminUserPassword', 'adminUserConfirmPassword'],
        ['adminNewUserPassword', 'adminConfirmUserPassword']
    ];

    for (let i = 0; i < passwordPairs.length; i++) {
        let password = document.getElementById(passwordPairs[i][0]);
        let confirmPassword = document.getElementById(passwordPairs[i][1]);

        if (password && confirmPassword) {
            password.addEventListener('input', function () {
                validateConfirmPassword(passwordPairs[i][0], passwordPairs[i][1]);
            });

            confirmPassword.addEventListener('input', function () {
                validateConfirmPassword(passwordPairs[i][0], passwordPairs[i][1]);
            });
        }
    }

    let messages = document.querySelectorAll('[data-auto-hide]');
    for (let i = 0; i < messages.length; i++) {
        let delay = parseInt(messages[i].getAttribute('data-auto-hide') || '3000', 10);
        let message = messages[i];

        setTimeout(function () {
            message.style.display = 'none';
        }, delay);
    }

    let params = new URLSearchParams(window.location.search);
    let success = params.get('success');
    let error = params.get('error');

    if (
        success === 'product_pending' ||
        success === 'product_submitted' ||
        success === 'product_updated' ||
        success === 'product_deleted' ||
        error === 'invalid_product'
    ) {
        showRegularPending();
    }
});

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('modal-overlay')) {
        closeModal(event.target.id);
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});
