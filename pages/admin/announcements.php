
<?php
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/config/database.php';

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['user_role'] ?? '') !== 'admin'
) {
    header('Location: ../auth/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['save_announcement'])
) {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid form token.');
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if ($title === '' || $content === '') {
        $error = 'Title and content are required.';
    } elseif (mb_strlen($title) > 255) {
        $error = 'Title is too long.';
    } elseif (!in_array($status, ['draft', 'published', 'archived'], true)) {
        $error = 'Invalid status.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO announcements
                (title, content, status, created_by, published_at)
                VALUES
                (:title, :content, :status, :created_by, :published_at)
            ");

            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'status' => $status,
                'created_by' => (int) $_SESSION['user_id'],
                'published_at' => $status === 'published'
                    ? date('Y-m-d H:i:s')
                    : null
            ]);

            header('Location: announcements.php');
            exit;
        } catch (PDOException $e) {
            error_log('Announcement insert failed: ' . $e->getMessage());
            $error = 'Unable to save announcement.';
        }
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'delete'
) {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Invalid form token.');
    }

    $id = filter_var(
        $_POST['announcement_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if (!$id) {
        $error = 'Invalid announcement ID.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "DELETE FROM announcements WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            header('Location: announcements.php');
            exit;
        } catch (PDOException $e) {
            error_log('Announcement delete failed: ' . $e->getMessage());
            $error = 'Unable to delete announcement.';
        }
    }
}

$stmt = $pdo->query("
    SELECT id, title, content, status, created_at
    FROM announcements
    ORDER BY created_at DESC, id DESC
");

$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements | NSBM NewsDesk</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f8f5;
            color: #1f2937;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        h1 {
            color: #14532d;
        }

        button {
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .add-btn {
            background: #166534;
            color: white;
        }

        .edit-btn {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .delete-btn {
            background: #fee2e2;
            color: #b91c1c;
        }

        .table-wrapper {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px #00000010;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #e8f5e9;
            color: #14532d;
        }

        .status {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .published {
            background: #dcfce7;
            color: #166534;
        }

        .draft {
            background: #fef3c7;
            color: #92400e;
        }

        .archived {
            background: #e5e7eb;
            color: #374151;
        }

        .search {
            padding: 11px;
            width: 100%;
            max-width: 350px;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: #00000080;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 100%;
            max-width: 450px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        textarea {
            min-height: 100px;
        }

        .save-btn {
            background: #166534;
            color: white;
        }

        .cancel-btn {
            background: #e5e7eb;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <h1>Manage Announcements</h1>
            <p>Create and manage campus announcements</p>
        </div>

        <button class="add-btn" onclick="openModal()">
            + Add Announcement
        </button>
    </div>

    <input
        type="text"
        class="search"
        id="searchInput"
        placeholder="Search announcements..."
        onkeyup="searchAnnouncements()"
    >

    <div class="table-wrapper">
        <table id="announcementTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($announcements as $item): ?>
                <tr>
                    <td><?= (int) $item['id'] ?></td>

                    <td>
                        <?= htmlspecialchars($item['title']) ?>
                    </td>

                    <td>
                        <span class="status <?= htmlspecialchars($item['status']) ?>">
                            <?= ucfirst(htmlspecialchars($item['status'])) ?>
                        </span>
                    </td>

                    <td><?= htmlspecialchars($item['created_at']) ?></td>

                    <td>
                        <button class="edit-btn" onclick="openModal()">
                            Edit
                        </button>

                        
<form method="POST" style="display:inline"
      onsubmit="return confirm('Are you sure you want to delete this announcement?')">

    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

    <input type="hidden" name="action" value="delete">

    <input type="hidden" name="announcement_id"
           value="<?= (int) $item['id'] ?>">

    <button type="submit" class="delete-btn">
        Delete
    </button>
</form>

                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<div class="modal" id="announcementModal">
    <div class="modal-content">

        
<form method="POST" action="">

    <input type="hidden" name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

    <input type="hidden" name="announcement_id" id="announcementId">

    <h3 id="modalHeading">Add Announcement</h3>

    <label>Title</label>
    <input type="text"
           id="announcementTitle"
           name="title"
           maxlength="255"
           required>

    <label>Content</label>
    <textarea id="announcementContent"
              name="content"
              required></textarea>

    <label>Status</label>
    <select id="announcementStatus" name="status">
        <option value="draft">Draft</option>
        <option value="published">Published</option>
        <option value="archived">Archived</option>
    </select>

    <button type="submit"
            name="save_announcement"
            class="save-btn">
        Save Announcement
    </button>

    <button type="button"
            class="cancel-btn"
            onclick="closeModal()">
        Cancel
    </button>

</form>


    </div>
</div>

<script>
    function openModal() {
        document.getElementById('announcementModal')
            .style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('announcementModal')
            .style.display = 'none';
    }

    function confirmDelete() {
        if (confirm('Are you sure you want to delete this announcement?')) {
            alert('Demo only: No announcement was deleted.');
        }
    }


    function searchAnnouncements() {
        const search = document.getElementById('searchInput')
            .value.toLowerCase();

        const rows = document.querySelectorAll(
            '#announcementTable tbody tr'
        );

        rows.forEach(row => {
            const title = row.cells[1].textContent.toLowerCase();

            row.style.display = title.includes(search) ? '' : 'none';
        });
    }
</script>

</body>
</html>
