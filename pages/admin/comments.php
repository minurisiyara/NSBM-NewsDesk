<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/admin-layout.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            if ($action === 'approve') {
                $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?")->execute([$id]);
                $message = 'Comment approved.';
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
                $message = 'Comment deleted.';
            }
        } catch (Throwable $e) {
            $error = 'Comment action failed: ' . $e->getMessage();
        }
    }
}

$comments = [];
try {
    $comments = $pdo->query("SELECT * FROM comments ORDER BY id DESC")->fetchAll();
} catch (Throwable $e) {
    $error = $error ?: ('Could not load comments: ' . $e->getMessage());
}

admin_render_start('comments.php', 'Comments');
?>
<?php if ($message !== ''): ?><div class="alert ok"><?= admin_h($message) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert"><?= admin_h($error) ?></div><?php endif; ?>

<div class="card tw">
    <table>
        <thead>
        <tr><th>Comment</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $c): ?>
                <?php $txt = $c['comment'] ?? $c['content'] ?? $c['body'] ?? ''; ?>
                <?php $st = strtolower((string) ($c['status'] ?? 'pending')); ?>
                <tr>
                    <td><?= admin_h($txt) ?></td>
                    <td><span class="pill <?= $st === 'approved' ? 'ok' : 'am' ?>"><?= admin_h(ucfirst($st)) ?></span></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id" value="<?= (int) ($c['id'] ?? 0) ?>">
                            <button type="submit" class="btn s">Approve</button>
                        </form>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this comment?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) ($c['id'] ?? 0) ?>">
                            <button type="submit" class="btn r s">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3" class="meta">No comments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php admin_render_end(false); ?>