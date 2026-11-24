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

$activePage = 'news';
$loggedInUserName = $_SESSION['user_name'] ?? 'Admin User';
$firstLetter = strtoupper(substr($loggedInUserName, 0, 1));

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Handle POST Actions (Create, Update, Toggle Featured, Toggle Breaking, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
        header('Location: news.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    try {
        // 1. Create or Update News Article
        if ($action === 'save_news') {
            $articleId = (int) ($_POST['article_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
            $summary = trim($_POST['summary'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $imagePath = trim($_POST['image_path'] ?? '');
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            $isBreaking = isset($_POST['is_breaking']) ? 1 : 0;

            // Server-side Validation
            if ($title === '' || $categoryId <= 0 || $content === '') {
                $_SESSION['flash_error'] = 'Title, Category, and Content are required fields.';
                header('Location: news.php');
                exit;
            }

            if ($articleId > 0) {
                // Update existing article using schema's image_path & published_at columns
                $stmt = $pdo->prepare("
                    UPDATE articles 
                    SET title = :title, 
                        category_id = :category_id, 
                        status = :status, 
                        summary = :summary, 
                        content = :content, 
                        image_path = :image_path,
                        is_featured = :is_featured, 
                        is_breaking = :is_breaking,
                        published_at = IF(:status_check = 'published' AND published_at IS NULL, NOW(), published_at)
                    WHERE id = :id
                ");
                $stmt->execute([
                    'title' => $title,
                    'category_id' => $categoryId,
                    'status' => $status,
                    'summary' => $summary !== '' ? $summary : null,
                    'content' => $content,
                    'image_path' => $imagePath !== '' ? $imagePath : null,
                    'is_featured' => $isFeatured,
                    'is_breaking' => $isBreaking,
                    'status_check' => $status,
                    'id' => $articleId
                ]);
                $_SESSION['flash_success'] = 'News article updated successfully.';
            } else {
                // Insert new article
                $stmt = $pdo->prepare("
                    INSERT INTO articles (title, category_id, author_id, status, summary, content, image_path, is_featured, is_breaking, published_at)
                    VALUES (:title, :category_id, :author_id, :status, :summary, :content, :image_path, :is_featured, :is_breaking, :published_at)
                ");
                $stmt->execute([
                    'title' => $title,
                    'category_id' => $categoryId,
                    'author_id' => (int) $_SESSION['user_id'],
                    'status' => $status,
                    'summary' => $summary !== '' ? $summary : null,
                    'content' => $content,
                    'image_path' => $imagePath !== '' ? $imagePath : null,
                    'is_featured' => $isFeatured,
                    'is_breaking' => $isBreaking,
                    'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null
                ]);
                $_SESSION['flash_success'] = 'New article created successfully.';
            }
        }

        // 2. Toggle Featured Flag
        if ($action === 'toggle_featured') {
            $id = (int) ($_POST['article_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE articles SET is_featured = NOT is_featured WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash_success'] = 'Featured status updated.';
        }

        // 3. Toggle Breaking Flag
        if ($action === 'toggle_breaking') {
            $id = (int) ($_POST['article_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE articles SET is_breaking = NOT is_breaking WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash_success'] = 'Breaking news status updated.';
        }

        // 4. Delete Article
        if ($action === 'delete_news') {
            $id = (int) ($_POST['article_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $_SESSION['flash_success'] = 'News article deleted.';
        }

    } catch (PDOException $e) {
        error_log('News management error: ' . $e->getMessage());
        $_SESSION['flash_error'] = 'Database operation failed: ' . $e->getMessage();
    }

    header('Location: news.php');
    exit;
}

// Fetch Categories for Filter & Modal Dropdowns
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Build Filtered Query for Articles
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$categoryFilter = (int) ($_GET['category'] ?? 0);
$dateFilter = trim($_GET['date'] ?? '');

$sql = "
    SELECT a.*, c.name AS category_name, u.name AS author_name
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    LEFT JOIN users u ON a.author_id = u.id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (a.title LIKE :search_title OR u.name LIKE :search_author)";
    $params['search_title'] = '%' . $search . '%';
    $params['search_author'] = '%' . $search . '%';
}
if ($statusFilter !== '') {
    $sql .= " AND a.status = :status";
    $params['status'] = $statusFilter;
}
if ($categoryFilter > 0) {
    $sql .= " AND a.category_id = :category_id";
    $params['category_id'] = $categoryFilter;
}
if ($dateFilter !== '') {
    $sql .= " AND DATE(a.created_at) = :created_date";
    $params['created_date'] = $dateFilter;
}

$sql .= " ORDER BY a.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Management | NSBM NewsDesk Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css?v=4">
</head>
<body>

    <?php include '../../components/admin-sidebar.php'; ?>

    <main class="admin-main">
        <!-- Top Header -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">☰</button>
                <h1>News Management</h1>
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

            <!-- Search & Filter Bar -->
            <form method="GET" action="news.php" class="filter-bar">
                <input type="text" name="search" class="form-control" placeholder="Search title or author..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All status</option>
                    <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>

                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="0">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= $categoryFilter === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($dateFilter, ENT_QUOTES, 'UTF-8') ?>" onchange="this.form.submit()">

                <button type="button" class="btn btn-primary" onclick="openAddNewsModal()">+ Add News</button>
            </form>

            <!-- Articles Data Table -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Highlights</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($articles)): ?>
                            <?php foreach ($articles as $art): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($art['title'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= htmlspecialchars($art['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($art['author_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($art['status']) === 'published' ? 'published' : 'draft' ?>">
                                            <?= htmlspecialchars(ucfirst($art['status']), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Toggle Featured -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="toggle_featured">
                                            <input type="hidden" name="article_id" value="<?= (int) $art['id'] ?>">
                                            <button type="submit" class="btn-toggle <?= !empty($art['is_featured']) ? 'active-featured' : '' ?>">
                                                <?= !empty($art['is_featured']) ? '★ Featured' : '☆ Feature' ?>
                                            </button>
                                        </form>

                                        <!-- Toggle Breaking -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="toggle_breaking">
                                            <input type="hidden" name="article_id" value="<?= (int) $art['id'] ?>">
                                            <button type="submit" class="btn-toggle <?= !empty($art['is_breaking']) ? 'active-breaking' : '' ?>">
                                                <?= !empty($art['is_breaking']) ? '⚡ Breaking' : 'Breaking' ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td><?= date('d M Y', strtotime($art['created_at'])) ?></td>
                                    <td style="white-space: nowrap;">
                                        <!-- Edit Button (Passes JSON data safely to JS Modal) -->
                                        <button type="button" class="btn btn-outline btn-sm"
                                            onclick='openEditNewsModal(<?= json_encode([
                                                "id" => (int) $art["id"],
                                                "title" => $art["title"],
                                                "category_id" => (int) $art["category_id"],
                                                "status" => $art["status"],
                                                "summary" => $art["summary"] ?? "",
                                                "content" => $art["content"] ?? "",
                                                "image_path" => $art["image_path"] ?? "",
                                                "is_featured" => !empty($art["is_featured"]),
                                                "is_breaking" => !empty($art["is_breaking"])
                                            ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                            Edit
                                        </button>

                                        <!-- Delete Form with Confirmation -->
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this news article?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="delete_news">
                                            <input type="hidden" name="article_id" value="<?= (int) $art['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No news articles match your criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add / Edit News Modal -->
    <div class="modal-backdrop" id="newsModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2 id="newsModalTitle">Add News Article</h2>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeNewsModal()">✕</button>
            </div>

            <form method="POST" action="news.php" onsubmit="return validateNewsForm()">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_news">
                <input type="hidden" name="article_id" id="newsId" value="0">

                <div class="form-group">
                    <label for="newsTitle">Article Title *</label>
                    <input type="text" name="title" id="newsTitle" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label for="newsCategory">Category *</label>
                        <select name="category_id" id="newsCategory" class="form-control" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="newsStatus">Publication Status</label>
                        <select name="status" id="newsStatus" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="newsSummary">Short Summary (Optional)</label>
                    <input type="text" name="summary" id="newsSummary" class="form-control" placeholder="Brief 1-2 sentence overview...">
                </div>

                <div class="form-group">
                    <label for="newsImage">Cover Image Path / URL (Optional)</label>
                    <input type="text" name="image_path" id="newsImage" class="form-control" placeholder="assets/images/news-cover.jpg or https://...">
                </div>

                <div class="form-group">
                    <label for="newsContent">Full Content *</label>
                    <textarea name="content" id="newsContent" rows="5" class="form-control" required></textarea>
                </div>

                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                        <input type="checkbox" name="is_featured" id="newsFeatured"> Mark as Featured Story
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                        <input type="checkbox" name="is_breaking" id="newsBreaking"> Mark as Breaking News
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeNewsModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Article</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/admin.js?v=7"></script>
</body>
</html>