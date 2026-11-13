
<?php
require_once __DIR__ . '/../../backend/config/session.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

require_once __DIR__ . '/../../backend/config/database.php';

$totalUsers = (int) $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

$totalNews = (int) $pdo->query(
    "SELECT COUNT(*) FROM articles"
)->fetchColumn();

$totalComments = (int) $pdo->query(
    "SELECT COUNT(*) FROM comments"
)->fetchColumn();

$totalBookmarks = (int) $pdo->query(
    "SELECT COUNT(*) FROM bookmarks"
)->fetchColumn();

$totalReports = (int) $pdo->query(
    "SELECT COUNT(*) FROM content_reports"
)->fetchColumn();

$pendingSubmissions = (int) $pdo->query(
    "SELECT COUNT(*) FROM articles WHERE status = 'pending'"
)->fetchColumn();

$popularCategories = $pdo->query("
    SELECT c.name, COUNT(a.id) AS article_count
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id
    GROUP BY c.id, c.name
    ORDER BY article_count DESC
    LIMIT 5
")->fetchAll();

$mostViewedArticles = $pdo->query("
    SELECT a.title, COUNT(av.id) AS view_count
    FROM articles a
    LEFT JOIN article_views av ON av.article_id = a.id
    GROUP BY a.id, a.title
    ORDER BY view_count DESC
    LIMIT 5
")->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | NSBM NewsDesk</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f8f5;
            color: #263b2b;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        h1 { color: #176b3a; }

        .subtitle {
            color: #718075;
            margin-bottom: 30px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .card, .chart-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .card {
            border-left: 5px solid #16834a;
        }

        .card h3 {
            color: #68796d;
            font-size: 15px;
            margin: 0 0 12px;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
            color: #176b3a;
        }

        .chart-box h2 {
            font-size: 20px;
            color: #176b3a;
        }

        .chart {
            display: flex;
            align-items: flex-end;
            gap: 25px;
            height: 250px;
            padding-top: 20px;
        }

        .bar-group {
            flex: 1;
            text-align: center;
        }

        .bar {
            background: #16834a;
            border-radius: 8px 8px 0 0;
            min-height: 3px;
        }

        .bar-label {
            margin-top: 10px;
            font-size: 13px;
            color: #68796d;
        }
    </style>
</head>

<body>
<main class="container">

    <h1>Admin Dashboard</h1>
    <p class="subtitle">
        NSBM NewsDesk - System Overview
    </p>

    <section class="stats">
        <div class="card">
            <h3>Total Users</h3>
            <div class="number"><?= $totalUsers ?></div>
        </div>

        <div class="card">
            <h3>Total News</h3>
            <div class="number"><?= $totalNews ?></div>
        </div>

        <div class="card">
            <h3>Total Comments</h3>
            <div class="number"><?= $totalComments ?></div>
        </div>

        <div class="card">
            <h3>Total Bookmarks</h3>
            <div class="number"><?= $totalBookmarks ?></div>
        </div>

        <div class="card">
            <h3>Total Reports</h3>
            <div class="number"><?= $totalReports ?></div>
        </div>
    </section>

    <section class="chart-box">
        <h2>Platform Activity Overview</h2>
        <div class="chart" id="activityChart"></div>
    </section>

</main>

<script>
    const activity = {
        Users: <?= (int) $totalUsers ?>,
        News: <?= (int) $totalNews ?>,
        Comments: <?= (int) $totalComments ?>,
        Bookmarks: <?= (int) $totalBookmarks ?>,
        Reports: <?= (int) $totalReports ?>
    };

    const maxValue = Math.max(...Object.values(activity), 1);
    const chart = document.getElementById('activityChart');

    Object.entries(activity).forEach(([label, value]) => {
        const group = document.createElement('div');
        group.className = 'bar-group';

        const bar = document.createElement('div');
        bar.className = 'bar';
        bar.style.height = `${Math.max((value / maxValue) * 180, 3)}px`;

        const count = document.createElement('div');
        count.textContent = value;

        const name = document.createElement('div');
        name.className = 'bar-label';
        name.textContent = label;

        group.appendChild(count);
        group.appendChild(bar);
        group.appendChild(name);
        chart.appendChild(group);
    });
</script>

</body>
</html>

