<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/admin-layout.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

$message = '';
$error = '';

/**
 * Inspect existing columns of `articles` table so optional columns like `published_at` never cause errors.
 *
 * @return array<string, bool>
 */
function admin_articles_columns(PDO $pdo): array
{
    $cols = [];
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM articles");
        foreach ($stmt->fetchAll() as $row) {
            if (!empty($row['Field'])) {
                $cols[(string) $row['Field']] = true;
            }
        }
    } catch (Throwable $e) {
        // Fallback if SHOW COLUMNS is restricted
    }
    return $cols;
}

$articleCols = admin_articles_columns($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $summary = trim((string) ($_POST['summary'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $status = strtolower(trim((string) ($_POST['status'] ?? 'pending')));
        $authorId = (int) ($_SESSION['user_id'] ?? 1);

        if (!in_array($status, ['pending', 'published', 'draft'], true)) {
            $status = 'pending';
        }

        if ($title === '') {
            $error = 'Article title is required.';
        } else {
            try {
                $insertCols = ['title', 'category_id', 'status', 'created_at'];
                $placeholders = ['?', '?', '?', 'NOW()'];
                $params = [$title, $categoryId ?: null, $status];

                if (isset($articleCols['summary'])) {
                    $insertCols[] = 'summary';
                    $placeholders[] = '?';
                    $params[] = $summary;
                }
                if (isset($articleCols['content'])) {
                    $insertCols[] = 'content';
                    $placeholders[] = '?';
                    $params[] = $content !== '' ? $content : $summary;
                }
                if (isset($articleCols['author_id'])) {
                    $insertCols[] = 'author_id';
                    $placeholders[] = '?';
                    $params[] = $authorId > 0 ? $authorId : null;
                }
                if (isset($articleCols['published_at']) && $status === 'published') {
                    $insertCols[] = 'published_at';
                    $placeholders[] = 'NOW()';
                }

                $sql = sprintf(
                    "INSERT INTO articles (%s) VALUES (%s)",
                    implode(', ', $insertCols),
                    implode(', ', $placeholders)
                );
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $message = 'News article created.';
            } catch (Throwable $e) {
                $error = 'Could not save article: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'publish') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                if (isset($articleCols['published_at'])) {
                    $stmt = $pdo->prepare("UPDATE articles SET status = 'published', published_at = COALESCE(published_at, NOW()) WHERE id = ?");
                } else {
                    $stmt = $pdo->prepare("UPDATE articles SET status = 'published' WHERE id = ?");
                }
                $stmt->execute([$id]);
                $message = 'Article published successfully.';
            } catch (Throwable $e) {
                $error = 'Could not publish article: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Article deleted.';
            } catch (Throwable $e) {
                $error = 'Could not delete article: ' . $e->getMessage();
            }
        }
    }
}

$categories = [];
$articles = [];
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
} catch (Throwable $e) {
    $categories = [];
}

try {
    $articles = $pdo->query("
        SELECT a.id, a.title, a.status, a.created_at, COALESCE(c.name, 'General') AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        ORDER BY a.id DESC
    ")->fetchAll();
} catch (Throwable $e) {
    $error = $error ?: ('Could not fetch articles: ' . $e->getMessage());
}

admin_render_start('news.php', 'News Management');
?>

<?php if ($message !== ''): ?><div class="alert ok"><?= admin_h($message) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert"><?= admin_h($error) ?></div><?php endif; ?>

<div class="bar">
    <input type="text" id="adminSearchInput" class="inp" placeholder="Search articles...">
    <select id="adminStatusFilter" class="inp" style="width:auto">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="published">Published</option>
        <option value="draft">Draft</option>
    </select>
    <select id="adminCategoryFilter" class="inp" style="width:auto">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= admin_h($c['name']) ?>"><?= admin_h($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="button" class="btn" onclick="openAddNewsModal()">+ Add News</button>
</div>

<div class="card tw">
    <table>
        <thead>
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="adminFilterBody">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $art): ?>
                <?php
                $aid = (int) ($art['id'] ?? 0);
                $st = strtolower((string) ($art['status'] ?? 'pending'));
                $pillClass = $st === 'published' ? 'ok' : 'am';
                ?>
                <tr data-filter-row
                    data-search="<?= admin_h(($art['title'] ?? '') . ' ' . ($art['category_name'] ?? '')) ?>"
                    data-status="<?= admin_h($st) ?>"
                    data-category="<?= admin_h($art['category_name'] ?? '') ?>">
                    <td><b><?= admin_h($art['title'] ?? '') ?></b></td>
                    <td><?= admin_h($art['category_name'] ?? 'General') ?></td>
                    <td><span class="pill <?= $pillClass ?>"><?= admin_h(ucfirst($st)) ?></span></td>
                    <td><?= admin_h(!empty($art['created_at']) ? date('d M Y', strtotime((string) $art['created_at'])) : '—') ?></td>
                    <td style="white-space:nowrap;display:inline-flex;gap:6px">
                        <?php if ($st !== 'published'): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="publish">
                                <input type="hidden" name="id" value="<?= $aid ?>">
                                <button type="submit" class="btn s">Publish</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this article?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $aid ?>">
                            <button type="submit" class="btn r s">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" class="meta">No articles found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mod" id="addNewsModal">
    <div class="card">
        <h3 style="font-family:Inter;margin-bottom:8px">Add News Article</h3>
        <form method="POST" onsubmit="return validateNewsForm(this)">
            <input type="hidden" name="action" value="create">
            <label>Title</label>
            <input type="text" name="title" class="inp" required>

            <label>Category</label>
            <select name="category_id" class="inp">
                <option value="">Select category</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= admin_h($c['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Summary</label>
            <textarea name="summary" class="inp" rows="2"></textarea>

            <label>Content</label>
            <textarea name="content" class="inp" rows="4"></textarea>

            <label>Status</label>
            <select name="status" class="inp">
                <option value="pending">Pending</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
            </select>

            <div class="form-error"></div>

            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
                <button type="button" class="btn o" onclick="AdminUI.closeModal('addNewsModal')">Cancel</button>
                <button type="submit" class="btn">Save Article</button>
            </div>
        </form>
    </div>
</div>

<?php admin_render_end(false); ?>