<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/admin-layout.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));

        if ($name === '') {
            $error = 'Category name is required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description !== '' ? $description : 'Campus category']);
                $message = 'Category added successfully.';
            } catch (Throwable $e) {
                $error = 'Could not add category: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
                $countStmt->execute([$id]);
                $linkedCount = (int) $countStmt->fetchColumn();

                if ($linkedCount > 0) {
                    $error = "Cannot delete category because {$linkedCount} article(s) are linked to it.";
                } else {
                    $delStmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    $delStmt->execute([$id]);
                    $message = 'Category deleted.';
                }
            } catch (Throwable $e) {
                $error = 'Could not delete category: ' . $e->getMessage();
            }
        }
    }
}

$categories = [];
try {
    $categories = $pdo->query("
        SELECT c.id, c.name, c.description, COUNT(a.id) AS article_count
        FROM categories c
        LEFT JOIN articles a ON a.category_id = c.id
        GROUP BY c.id, c.name, c.description
        ORDER BY c.name ASC
    ")->fetchAll();
} catch (Throwable $e) {
    $error = $error ?: ('Error loading categories: ' . $e->getMessage());
}

admin_render_start('categories.php', 'Categories');
?>

<?php if ($message !== ''): ?>
    <div class="alert ok"><?= admin_h($message) ?></div>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <div class="alert"><?= admin_h($error) ?></div>
<?php endif; ?>

<div class="two" style="grid-template-columns:1fr 1.6fr;margin-top:0">
    <div class="card pad">
        <h3 style="font-family:Inter;margin-bottom:10px">Add Category</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <label for="catName">Name</label>
            <input type="text" id="catName" name="name" class="inp" placeholder="e.g. Academic" required>

            <label for="catDesc">Description</label>
            <textarea id="catDesc" name="description" class="inp" rows="3" placeholder="Short description"></textarea>

            <button type="submit" class="btn" style="margin-top:14px">Save Category</button>
        </form>
    </div>

    <div class="card pad tw">
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
                    <tr>
                        <td><b><?= admin_h($cat['name'] ?? '') ?></b></td>
                        <td class="meta"><?= admin_h($cat['description'] ?? '') ?></td>
                        <td><span class="pill cnt"><?= (int) ($cat['article_count'] ?? 0) ?></span></td>
                        <td>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this category?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) ($cat['id'] ?? 0) ?>">
                                <button type="submit" class="btn r s">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="meta">No categories found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php admin_render_end(false); ?>