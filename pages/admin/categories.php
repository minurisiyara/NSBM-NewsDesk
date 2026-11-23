<?php
declare(strict_types=1);

require_once '../../backend/config/session.php';
require_once '../../backend/config/database.php';

// Enforce Admin Authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Generate CSRF token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$activePage = 'categories';
$loggedInUserName = $_SESSION['user_name'] ?? 'Admin User';
$firstLetter = strtoupper(substr($loggedInUserName, 0, 1));

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle POST Actions (Create, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
        header('Location: categories.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    try {
        // 1. Add or Update Category
        if ($action === 'save_category') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($name === '') {
                $_SESSION['flash_error'] = 'Category name cannot be empty.';
                header('Location: categories.php');
                exit;
            }

            // Duplicate Name Validation (Case-insensitive)
            $dupStmt = $pdo->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) AND id != :id LIMIT 1");
            $dupStmt->execute(['name' => $name, 'id' => $categoryId]);
            if ($dupStmt->fetch()) {
                $_SESSION['flash_error'] = "A category named '{$name}' already exists.";
                header('Location: categories.php');
                exit;
            }

            if ($categoryId > 0) {
                $stmt = $pdo->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
                $stmt->execute([
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                    'id' => $categoryId
                ]);
                $_SESSION['flash_success'] = 'Category updated successfully.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
                $stmt->execute([
                    'name' => $name,
                    'description' => $description !== '' ? $description : null
                ]);
                $_SESSION['flash_success'] = 'New category added successfully.';
            }
        }

        // 2. Delete Category (Respecting ON DELETE RESTRICT Foreign Key)
        if ($action === 'delete_category') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);

            // Check if any articles are assigned to this category
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = :id");
            $countStmt->execute(['id' => $categoryId]);
            $linkedArticles = (int) $countStmt->fetchColumn();

            if ($linkedArticles > 0) {
                $_SESSION['flash_error'] = "Cannot delete this category because {$linkedArticles} article(s) are still assigned to it. Reassign or delete those articles first.";
            } else {
                $pdo->prepare("DELETE FROM categories WHERE id = :id")->execute(['id' => $categoryId]);
                $_SESSION['flash_success'] = 'Category deleted successfully.';
            }
        }

    } catch (PDOException $e) {
        error_log('Category management error: ' . $e->getMessage());
        $_SESSION['flash_error'] = 'Database operation failed.';
    }

    header('Location: categories.php');
    exit;
}

// Fetch Categories with Article Counts
$stmt = $pdo->query("
    SELECT c.id, c.name, c.description, COUNT(a.id) AS article_count
    FROM categories c
    LEFT JOIN articles a ON c.id = a.category_id
    GROUP BY c.id, c.name, c.description
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | NSBM NewsDesk Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css?v=4">
</head>
<body>

    <?php include '../../components/admin-sidebar.php'; ?>

    <main class="admin-main">
        <!-- Top Header -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">☰</button>
                <h1>Categories</h1>
            </div>
            <div class="top-bar-actions">
                <div class="avatar-circle" title="<?= htmlspecialchars($loggedInUserName, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($firstLetter, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <a href="../home.php" class="btn btn-outline">View Site</a>
                <form action="../../backend/auth/logout.php" method="POST" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Flash Feedback Messages -->
            <?php if ($flashSuccess !== ''): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($flashError !== ''): ?>
                <div class="alert alert-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <!-- Action Bar with "+ Add Category" Button -->
            <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <button type="button" class="btn btn-primary" onclick="openAddCategoryModal()">+ Add Category</button>
            </div>

            <!-- Full-Width Categories Table -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Articles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php $count = (int) $cat['article_count']; ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td style="color: #6b7280; word-break: break-word;">
                                        <?= htmlspecialchars($cat['description'] ?? 'No description provided.', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><span class="count-pill"><?= $count ?></span></td>
                                    <td style="white-space: nowrap;">
                                        <!-- Edit Button (Opens Modal with Existing Details) -->
                                        <button type="button" class="btn btn-outline btn-sm"
                                            onclick='openEditCategoryModal(<?= json_encode([
                                                "id" => (int) $cat["id"],
                                                "name" => $cat["name"],
                                                "description" => $cat["description"] ?? ""
                                            ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                            Edit
                                        </button>

                                        <!-- Delete Button with Article Count Warning -->
                                        <form method="POST" style="display:inline;" onsubmit="return confirmDeleteCategory(<?= $count ?>, '<?= htmlspecialchars(addslashes($cat['name']), ENT_QUOTES, 'UTF-8') ?>');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="category_id" value="<?= (int) $cat['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No categories available. Click "+ Add Category" to create one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add / Edit Category Popup Modal -->
    <div class="modal-backdrop" id="categoryModal">
        <div class="modal-box" style="max-width: 480px;">
            <div class="modal-header">
                <h2 id="categoryModalTitle">Add Category</h2>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeCategoryModal()">✕</button>
            </div>

            <form method="POST" action="categories.php" onsubmit="return validateCategoryForm()">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="category_id" id="categoryId" value="0">

                <div class="form-group">
                    <label for="categoryName">Category Name *</label>
                    <input type="text" name="name" id="categoryName" class="form-control" placeholder="e.g., Academic, Sports, Events" required>
                </div>

                <div class="form-group">
                    <label for="categoryDescription">Description (Optional)</label>
                    <textarea name="description" id="categoryDescription" rows="4" class="form-control" placeholder="Short description of this category..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeCategoryModal()">Cancel</button>
                    <button type="submit" id="saveCategoryBtn" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/admin.js?v=2"></script>
</body>
</html>