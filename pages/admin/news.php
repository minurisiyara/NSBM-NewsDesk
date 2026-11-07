<?php
session_start();
require_once '../../backend/config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Toggle Featured Status
if (isset($_POST['toggle_featured'])) {
    $article_id = (int)$_POST['article_id'];
    $stmt = $conn->prepare("UPDATE articles SET is_featured = NOT is_featured WHERE id = :id");
    $stmt->execute(['id' => $article_id]);
}

// Delete News
if (isset($_POST['delete_news'])) {
    $article_id = (int)$_POST['article_id'];
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = :id");
    $stmt->execute(['id' => $article_id]);
}

// Read News
$query = "SELECT a.*, c.name as category_name, u.name as author_name 
          FROM articles a 
          LEFT JOIN categories c ON a.category_id = c.id 
          LEFT JOIN users u ON a.author_id = u.id 
          ORDER BY a.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>News Management - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php include '../../components/admin-sidebar.php'; ?>

    <main class="main-content">
        <h2>News Management</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $art): ?>
                <tr>
                    <td><?= htmlspecialchars($art['title']) ?></td>
                    <td><?= htmlspecialchars($art['category_name']) ?></td>
                    <td><?= htmlspecialchars($art['author_name']) ?></td>
                    <td><span class="badge status-<?= $art['status'] ?>"><?= $art['status'] ?></span></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="article_id" value="<?= $art['id'] ?>">
                            <button type="submit" name="toggle_featured">
                                <?= $art['is_featured'] ? '★ Featured' : '☆ Make Featured' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this article?');">
                            <input type="hidden" name="article_id" value="<?= $art['id'] ?>">
                            <button type="submit" name="delete_news" class="btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>