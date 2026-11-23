/**
 * NSBM NewsDesk - Admin Panel Interactive Scripts
 */

// Apply collapsed state immediately when script loads (before DOMContentLoaded)
(function () {
    const isCollapsed = localStorage.getItem('nsbm-admin-sidebar-collapsed') === 'true';
    if (isCollapsed) {
        document.documentElement.classList.add('sidebar-collapsed', 'no-sidebar-transition');
        if (document.body) {
            document.body.classList.add('sidebar-collapsed');
        }
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');

    // Sync state on body as well
    if (localStorage.getItem('nsbm-admin-sidebar-collapsed') === 'true') {
        document.documentElement.classList.add('sidebar-collapsed');
        document.body.classList.add('sidebar-collapsed');
    }

    // Re-enable smooth transitions AFTER initial page paint completes
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.documentElement.classList.remove('no-sidebar-transition');
        });
    });

    // Toggle collapse ONLY when the sidebar hamburger button is clicked
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (window.innerWidth <= 768 && adminSidebar) {
                adminSidebar.classList.remove('mobile-open');
            } else {
                const isNowCollapsed = !document.documentElement.classList.contains('sidebar-collapsed');
                document.documentElement.classList.toggle('sidebar-collapsed', isNowCollapsed);
                document.body.classList.toggle('sidebar-collapsed', isNowCollapsed);
                localStorage.setItem('nsbm-admin-sidebar-collapsed', String(isNowCollapsed));
            }
        });
    }

    // Mobile Top-Bar Hamburger Toggle
    if (mobileMenuBtn && adminSidebar) {
        mobileMenuBtn.addEventListener('click', () => {
            adminSidebar.classList.toggle('mobile-open');
        });
    }

    // Close modals when clicking outside the modal box
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                backdrop.classList.remove('open');
            }
        });
    });
});

/* =========================================================
   NEWS MANAGEMENT MODAL & VALIDATION
   ========================================================= */
function openAddNewsModal() {
    const modal = document.getElementById('newsModal');
    if (!modal) return;

    document.getElementById('newsModalTitle').textContent = 'Add News Article';
    document.getElementById('newsId').value = '0';
    document.getElementById('newsTitle').value = '';
    document.getElementById('newsCategory').value = '';
    document.getElementById('newsStatus').value = 'draft';
    document.getElementById('newsSummary').value = '';
    document.getElementById('newsImage').value = '';
    document.getElementById('newsContent').value = '';
    document.getElementById('newsFeatured').checked = false;
    document.getElementById('newsBreaking').checked = false;

    modal.classList.add('open');
}

function openEditNewsModal(article) {
    const modal = document.getElementById('newsModal');
    if (!modal) return;

    document.getElementById('newsModalTitle').textContent = 'Edit News Article';
    document.getElementById('newsId').value = article.id;
    document.getElementById('newsTitle').value = article.title;
    document.getElementById('newsCategory').value = article.category_id;
    document.getElementById('newsStatus').value = article.status;
    document.getElementById('newsSummary').value = article.summary;
    document.getElementById('newsImage').value = article.image_path;
    document.getElementById('newsContent').value = article.content;
    document.getElementById('newsFeatured').checked = Boolean(article.is_featured);
    document.getElementById('newsBreaking').checked = Boolean(article.is_breaking);

    modal.classList.add('open');
}

function closeNewsModal() {
    const modal = document.getElementById('newsModal');
    if (modal) {
        modal.classList.remove('open');
    }
}

function validateNewsForm() {
    const title = document.getElementById('newsTitle').value.trim();
    const category = document.getElementById('newsCategory').value;
    const content = document.getElementById('newsContent').value.trim();

    if (!title || !category || !content) {
        alert('Please fill in all required fields (Title, Category, and Content).');
        return false;
    }
    return true;
}

/* =========================================================
   CATEGORY MANAGEMENT POPUP MODAL & VALIDATION
   ========================================================= */
function openAddCategoryModal() {
    const modal = document.getElementById('categoryModal');
    if (!modal) return;

    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    document.getElementById('categoryId').value = '0';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('saveCategoryBtn').textContent = 'Save Category';

    modal.classList.add('open');
}

function openEditCategoryModal(category) {
    const modal = document.getElementById('categoryModal');
    if (!modal) return;

    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryDescription').value = category.description;
    document.getElementById('saveCategoryBtn').textContent = 'Update Category';

    modal.classList.add('open');
}

function closeCategoryModal() {
    const modal = document.getElementById('categoryModal');
    if (modal) {
        modal.classList.remove('open');
    }
}

function validateCategoryForm() {
    const name = document.getElementById('categoryName').value.trim();
    if (!name) {
        alert('Please enter a category name.');
        return false;
    }
    return true;
}

function confirmDeleteCategory(articleCount, categoryName) {
    if (articleCount > 0) {
        alert(
            `Cannot delete "${categoryName}" because it currently has ${articleCount} linked article(s).\n\nPlease reassign or delete those articles first.`
        );
        return false;
    }
    return confirm(`Are you sure you want to delete the "${categoryName}" category?`);
}

//users

/* =========================================================
   USER MANAGEMENT POPUP MODAL & VALIDATION
   ========================================================= */
function openAddUserModal() {
    const modal = document.getElementById('userModal');
    if (!modal) return;

    document.getElementById('userName').value = '';
    document.getElementById('userEmail').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userRole').value = 'student';

    modal.classList.add('open');
}

function closeUserModal() {
    const modal = document.getElementById('userModal');
    if (modal) {
        modal.classList.remove('open');
    }
}

function validateUserForm() {
    const name = document.getElementById('userName').value.trim();
    const email = document.getElementById('userEmail').value.trim();
    const password = document.getElementById('userPassword').value;

    if (!name || !email || password.length < 6) {
        alert('Please enter a valid name, email, and a password with at least 6 characters.');
        return false;
    }
    return true;
}