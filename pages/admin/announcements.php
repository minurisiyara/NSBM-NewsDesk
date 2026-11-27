<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/admin-layout.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

$message = '';
$error = '';

/**
 * Inspect `announcements` columns dynamically.
 *
 * @return array<string, bool>
 */
function admin_announcements_columns(PDO $pdo): array
{
    $cols = [];
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM announcements");
        foreach ($stmt->fetchAll() as $row) {
            if (!empty($row['Field'])) {
                $cols[(string) $row['Field']] = true;
            }
        }
    } catch (Throwable $e) {
        // Ignore if restricted
    }
    return $cols;
}

/**
 * Resolve a valid user ID from `users` table so fk_announcements_creator never fails.
 */
function admin_resolve_creator_id(PDO $pdo): int
{
    $sessionUserId = (int) ($_SESSION['user_id'] ?? 0);
    if ($sessionUserId > 0) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$sessionUserId]);
        $found = (int) $stmt->fetchColumn();
        if ($found > 0) {
            return $found;
        }
    }

    $adminId = (int) $pdo->query("SELECT id FROM users WHERE LOWER(role) = 'admin' ORDER BY id ASC LIMIT 1")->fetchColumn();
    if ($adminId > 0) {
        return $adminId;
    }

    return (int) $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn();
}

$cols = admin_announcements_columns($pdo);
$bodyCol = isset($cols['content']) ? 'content' : (isset($cols['description']) ? 'description' : 'body');
$dateCol = isset($cols['event_date']) ? 'event_date' : 'created_at';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $body = trim((string) ($_POST['description'] ?? ''));
        $date = trim((string) ($_POST['event_date'] ?? date('Y-m-d')));

        if ($title === '') {
            $error = 'Announcement title is required.';
        } else {
            try {
                $creatorId = admin_resolve_creator_id($pdo);
                if ($creatorId <= 0) {
                    throw new RuntimeException('No valid user found in users table for created_by.');
                }

                $insertCols = ['title', $bodyCol, $dateCol];
                $placeholders = ['?', '?', '?'];
                $params = [$title, $body, $date !== '' ? $date : date('Y-m-d')];

                if (isset($cols['created_by'])) {
                    $insertCols[] = 'created_by';
                    $placeholders[] = '?';
                    $params[] = $creatorId;
                }

                if (isset($cols['is_active'])) {
                    $insertCols[] = 'is_active';
                    $placeholders[] = '?';
                    $params[] = 1;
                }

                $sql = sprintf(
                    "INSERT INTO announcements (%s) VALUES (%s)",
                    implode(', ', $insertCols),
                    implode(', ', $placeholders)
                );
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $message = 'Announcement added successfully.';
            } catch (Throwable $e) {
                $error = 'Could not add announcement: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $body = trim((string) ($_POST['description'] ?? ''));
        $date = trim((string) ($_POST['event_date'] ?? date('Y-m-d')));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($id <= 0 || $title === '') {
            $error = 'Announcement ID and title are required.';
        } else {
            try {
                $sets = ["title = ?", "{$bodyCol} = ?", "{$dateCol} = ?"];
                $params = [$title, $body, $date !== '' ? $date : date('Y-m-d')];

                if (isset($cols['is_active'])) {
                    $sets[] = "is_active = ?";
                    $params[] = $isActive;
                }

                $params[] = $id;
                $sql = "UPDATE announcements SET " . implode(', ', $sets) . " WHERE id = ?";
                $pdo->prepare($sql)->execute($params);

                $message = 'Announcement updated successfully.';
            } catch (Throwable $e) {
                $error = 'Could not update announcement: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Announcement deleted.';
            } catch (Throwable $e) {
                $error = 'Could not delete announcement: ' . $e->getMessage();
            }
        }
    }
}

$announcements = [];
try {
    $announcements = $pdo->query("SELECT * FROM announcements ORDER BY id DESC")->fetchAll();
} catch (Throwable $e) {
    $error = $error ?: ('Could not load announcements: ' . $e->getMessage());
}

admin_render_start('announcements.php', 'Announcements');
?>

<?php if ($message !== ''): ?><div class="alert ok"><?= admin_h($message) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert"><?= admin_h($error) ?></div><?php endif; ?>

<div class="bar">
    <button type="button" class="btn" onclick="AdminUI.openModal('addAnnModal')">+ Add Announcement</button>
</div>

<div class="card tw">
    <table>
        <thead>
        <tr>
            <th>Title</th>
            <th>Details</th>
            <th>Date</th>
            <?php if (isset($cols['is_active'])): ?>
                <th>Status</th>
            <?php endif; ?>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($announcements)): ?>
            <?php foreach ($announcements as $a): ?>
                <?php
                $aid = (int) ($a['id'] ?? 0);
                $aTitle = (string) ($a['title'] ?? '');
                $desc = (string) ($a['content'] ?? $a['description'] ?? $a['body'] ?? '');
                $rawDate = (string) ($a['event_date'] ?? $a['created_at'] ?? '');
                $inputDate = $rawDate !== '' ? date('Y-m-d', strtotime($rawDate)) : date('Y-m-d');
                $active = (int) ($a['is_active'] ?? 1) === 1;
                ?>
                <tr>
                    <td><b><?= admin_h($aTitle) ?></b></td>
                    <td class="meta"><?= admin_h($desc) ?></td>
                    <td><?= admin_h($rawDate !== '' ? date('d M Y', strtotime($rawDate)) : '—') ?></td>
                    <?php if (isset($cols['is_active'])): ?>
                        <td>
                            <span class="pill <?= $active ? 'ok' : 'am' ?>">
                                <?= $active ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                    <?php endif; ?>
                    <td style="white-space:nowrap;display:inline-flex;gap:6px">
                        <button type="button"
                                class="btn o s"
                                data-id="<?= $aid ?>"
                                data-title="<?= admin_h($aTitle) ?>"
                                data-desc="<?= admin_h($desc) ?>"
                                data-date="<?= admin_h($inputDate) ?>"
                                data-active="<?= $active ? '1' : '0' ?>"
                                onclick="openEditAnnouncement(this)">
                            Edit
                        </button>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this announcement?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $aid ?>">
                            <button type="submit" class="btn r s">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="<?= isset($cols['is_active']) ? 5 : 4 ?>" class="meta">No announcements found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="mod" id="addAnnModal">
    <div class="card">
        <h3 style="font-family:Inter;margin-bottom:8px">Add Announcement</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <label>Title</label>
            <input type="text" name="title" class="inp" required>

            <label>Date</label>
            <input type="date" name="event_date" class="inp" value="<?= date('Y-m-d') ?>">

            <label>Description</label>
            <textarea name="description" class="inp" rows="3"></textarea>

            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
                <button type="button" class="btn o" onclick="AdminUI.closeModal('addAnnModal')">Cancel</button>
                <button type="submit" class="btn">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="mod" id="editAnnModal">
    <div class="card">
        <h3 style="font-family:Inter;margin-bottom:8px">Edit Announcement</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editAnnId">

            <label>Title</label>
            <input type="text" name="title" id="editAnnTitle" class="inp" required>

            <label>Date</label>
            <input type="date" name="event_date" id="editAnnDate" class="inp">

            <label>Description</label>
            <textarea name="description" id="editAnnDesc" class="inp" rows="3"></textarea>

            <?php if (isset($cols['is_active'])): ?>
                <div class="sw" style="margin-top:8px">
                    <span>Active on Homepage</span>
                    <input type="checkbox" name="is_active" id="editAnnActive" value="1">
                </div>
            <?php endif; ?>

            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
                <button type="button" class="btn o" onclick="AdminUI.closeModal('editAnnModal')">Cancel</button>
                <button type="submit" class="btn">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditAnnouncement(btn) {
    document.getElementById('editAnnId').value = btn.dataset.id || '';
    document.getElementById('editAnnTitle').value = btn.dataset.title || '';
    document.getElementById('editAnnDate').value = btn.dataset.date || '';
    document.getElementById('editAnnDesc').value = btn.dataset.desc || '';
    const activeBox = document.getElementById('editAnnActive');
    if (activeBox) {
        activeBox.checked = btn.dataset.active === '1';
    }
    AdminUI.openModal('editAnnModal');
}
</script>

<?php admin_render_end(false); ?>