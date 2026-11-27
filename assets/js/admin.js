
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

    /* News search */
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

function openAddNewsModal() {
    const form = document.querySelector('#addNewsModal form');

    if (form) form.reset();

    AdminUI.openModal('addNewsModal');
}

function closeNewsModal() {
    AdminUI.closeModal('addNewsModal');
}

function validateNewsForm() {
    const form = document.querySelector('#addNewsModal form');

    if (!form) return false;

    const title = form.querySelector('[name="title"]');

    if (!title || !title.value.trim()) {
        alert('Please enter the news title.');
        return false;
    }

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
    const name = document.getElementById('categoryName');

    if (!name || !name.value.trim()) {
        alert('Please enter a category name.');
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
