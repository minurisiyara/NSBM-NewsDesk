'use strict';

/* =====================================
   NSBM NEWSDESK - ADMIN UI
===================================== */

/* Modal Controller */
window.AdminUI = {
    openModal(id) {
        const modal = document.getElementById(id);

        if (!modal) {
            console.error('Modal not found:', id);
            return;
        }

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';

        const input = modal.querySelector(
            'input:not([type="hidden"]), textarea, select'
        );

        if (input) {
            setTimeout(() => input.focus(), 100);
        }
    },

    closeModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

/* =====================================
   DOM READY
===================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* IMPORTANT:
       Sidebar events are handled by admin-layout.php.
       Do not add another sidebar click listener here.
    */

    /* Close modal on backdrop click */
    document.querySelectorAll('.mod').forEach(modal => {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    });

    /* Escape key closes modal */
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.mod.open').forEach(modal => {
                modal.classList.remove('open');
            });

            document.body.style.overflow = '';
        }
    });

    /* News search & filters */
    const search = document.getElementById('adminSearchInput');
    const statusFilter = document.getElementById('adminStatusFilter');
    const categoryFilter = document.getElementById('adminCategoryFilter');

    function filterNews() {
        const keyword = search?.value.toLowerCase().trim() || '';
        const status = statusFilter?.value.toLowerCase() || '';
        const category = categoryFilter?.value.toLowerCase() || '';

        document.querySelectorAll('[data-filter-row]').forEach(row => {
            const text = (row.dataset.search || '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();
            const rowCategory = (row.dataset.category || '').toLowerCase();

            const matches =
                text.includes(keyword) &&
                (!status || status === rowStatus) &&
                (!category || category === rowCategory);

            row.style.display = matches ? '' : 'none';
        });
    }

    [search, statusFilter, categoryFilter].forEach(element => {
        if (element) {
            element.addEventListener('input', filterNews);
            element.addEventListener('change', filterNews);
        }
    });

});

/* =====================================
   NEWS MODAL
===================================== */

function getNewsModalEl() {
    return (
        document.getElementById('addNewsModal') ||
        document.getElementById('newsModal') ||
        document.querySelector('.mod.open') ||
        document.querySelector('.mod')
    );
}

function getNewsFormEl(formOrEvent) {
    if (formOrEvent && formOrEvent.tagName === 'FORM') {
        return formOrEvent;
    }
    if (formOrEvent && formOrEvent.target && formOrEvent.target.tagName === 'FORM') {
        return formOrEvent.target;
    }

    const modal = getNewsModalEl();
    return (
        (modal && modal.querySelector('form')) ||
        document.querySelector('#addNewsModal form') ||
        document.querySelector('#newsModal form') ||
        document.querySelector('form[action*="news"]') ||
        null
    );
}

function showValidationMessage(container, message) {
    const errEl =
        (container && container.querySelector('.form-error, .error-msg, .err')) ||
        document.getElementById('newsFormError') ||
        document.getElementById('me');

    if (errEl) {
        errEl.textContent = message;
        errEl.style.display = 'block';
        errEl.style.color = '#dc2626';
        errEl.style.marginTop = '8px';
        errEl.style.fontSize = '13px';
        errEl.style.fontWeight = '600';
    } else {
        alert(message);
    }
}

function clearValidationMessage(container) {
    const errEl =
        (container && container.querySelector('.form-error, .error-msg, .err')) ||
        document.getElementById('newsFormError') ||
        document.getElementById('me');

    if (errEl) {
        errEl.textContent = '';
        errEl.style.display = 'none';
    }
}

function openAddNewsModal() {
    const modal = getNewsModalEl();
    const form = getNewsFormEl();

    if (form) {
        form.reset();
        clearValidationMessage(form);
    }

    if (modal) {
        AdminUI.openModal(modal.id);
    } else {
        AdminUI.openModal('addNewsModal');
    }
}

function closeNewsModal() {
    const modal = getNewsModalEl();
    if (modal) {
        AdminUI.closeModal(modal.id);
    } else {
        AdminUI.closeModal('addNewsModal');
    }
}

function validateNewsForm(formOrEvent) {
    const form = getNewsFormEl(formOrEvent);
    const scope = form || getNewsModalEl() || document;

    const titleInput =
        scope.querySelector('[name="title"]') ||
        scope.querySelector('#newsTitle') ||
        scope.querySelector('#title') ||
        scope.querySelector('input[type="text"]');

    if (!titleInput) {
        return true;
    }

    const titleValue = String(titleInput.value || '').trim();

    if (!titleValue) {
        showValidationMessage(scope, 'Please enter the news title.');
        titleInput.focus();
        return false;
    }

    clearValidationMessage(scope);
    return true;
}

/* =====================================
   CATEGORY MODAL
===================================== */

function openAddCategoryModal() {
    AdminUI.openModal('categoryModal');
}

function closeCategoryModal() {
    AdminUI.closeModal('categoryModal');
}

function validateCategoryForm() {
    const name =
        document.getElementById('categoryName') ||
        document.querySelector('#categoryModal [name="name"]');

    if (!name || !String(name.value || '').trim()) {
        alert('Please enter a category name.');
        if (name) name.focus();
        return false;
    }

    return true;
}

/* =====================================
   DELETE CATEGORY
===================================== */

function confirmDeleteCategory(count, name) {
    if (Number(count) > 0) {
        alert(
            'Cannot delete "' + name +
            '" because articles are linked to this category.'
        );

        return false;
    }

    return confirm('Delete category "' + name + '"?');
}