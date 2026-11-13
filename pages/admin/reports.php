
<?php
require_once __DIR__ . '/../../backend/config/session.php';

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['user_role'] ?? '') !== 'admin'
) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// Update report status
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'update_report'
) {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Invalid form token.');
    }

    $reportId = filter_var(
        $_POST['report_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $newStatus = $_POST['status'] ?? '';

    if (
        !$reportId ||
        !in_array(
            $newStatus,
            ['reviewing', 'resolved', 'dismissed'],
            true
        )
    ) {
        $error = 'Invalid report update.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE content_reports
                SET
                    status = :status,
                    reviewed_by = :reviewed_by,
                    reviewed_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'status' => $newStatus,
                'reviewed_by' => (int) $_SESSION['user_id'],
                'id' => $reportId
            ]);

            header('Location: reports.php');
            exit;
        } catch (PDOException $e) {
            error_log('Report update failed: ' . $e->getMessage());
            $error = 'Unable to update report.';
        }
    }
}

// Get report statistics
$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total_reports,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_reports,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved_reports
    FROM content_reports
");

$stats = $stmt->fetch();

// Get all reports
$stmt = $pdo->query("
    SELECT
        id,
        target_type,
        article_id,
        comment_id,
        reason,
        reporter_id,
        status,
        created_at
    FROM content_reports
    ORDER BY created_at DESC, id DESC
");

$reports = $stmt->fetchAll();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports | NSBM NewsDesk</title>

    <style>
        * {
            box-sizing: border-box;
        }

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

        h1 {
            color: #176b3a;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #718075;
            margin-bottom: 30px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
            border-left: 5px solid #16834a;
        }

        .card h3 {
            font-size: 15px;
            color: #68796d;
            margin: 0 0 12px;
        }

        .card .number {
            font-size: 32px;
            font-weight: bold;
            color: #176b3a;
        }

        .table-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .table-header h2 {
            margin: 0;
            font-size: 20px;
        }

        #searchInput {
            padding: 12px;
            border: 1px solid #d5e4d9;
            border-radius: 8px;
            width: 250px;
            max-width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e7eee9;
        }

        th {
            background: #edf6ef;
            color: #176b3a;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .pending {
            background: #fff3cd;
            color: #8a6500;
        }

        .resolved {
            background: #d9f3e2;
            color: #16733d;
        }

        .empty {
            text-align: center;
            padding: 35px;
            color: #7a8b7e;
        }
    </style>
</head>

<body>
    <main class="container">
        <h1>Manage Reports</h1>
        <p class="subtitle">
            Review and manage reported campus news content.
        </p>

        <section class="stats">
            <div class="card">
                <h3>Total Reports</h3>
                <div class="number"><?= (int) $stats['total_reports'] ?></div>
            </div>

            <div class="card">
                <h3>Pending Reports</h3>
                <div class="number"><?= (int) $stats['pending_reports'] ?></div>
            </div>

            <div class="card">
                <h3>Resolved Reports</h3>
                <div class="number"><div class="number"><?= (int) $stats['resolved_reports'] ?></div>
            </div>
        </section>

        <section class="table-box">
            <div class="table-header">
                <h2>Reports List</h2>
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search reports..."
                    onkeyup="searchReports()"
                >
            </div>

            <table id="reportsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Article</th>
                        <th>Reason</th>
                        <th>Reported By</th>
                        <th>Status</th>
                        <th>Reported Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

<tbody>
    <?php if (empty($reports)): ?>
        <tr>
            <td colspan="7" class="empty">
                No reports found.
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($reports as $report): ?>
            <tr>
                <td><?= (int) $report['id'] ?></td>

                <td>
                    <?= htmlspecialchars(
                        ucfirst($report['target_type']) .
                        ' #' .
                        ($report['target_type'] === 'article'
                            ? $report['article_id']
                            : $report['comment_id']),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars($report['reason'], ENT_QUOTES, 'UTF-8') ?>
                </td>

                <td>
                    User #<?= (int) $report['reporter_id'] ?>
                </td>

                <td>
                    <span class="status <?= htmlspecialchars($report['status'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars(ucfirst($report['status']), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>

                <td>
                    <?= htmlspecialchars($report['created_at'], ENT_QUOTES, 'UTF-8') ?>
                </td>

                
<td>
    <form method="POST" action=""
          style="display:flex; gap:6px; flex-wrap:wrap;">

        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <input type="hidden" name="action"
               value="update_report">

        <input type="hidden" name="report_id"
               value="<?= (int) $report['id'] ?>">

        <button type="submit" name="status"
                value="reviewing">
            Review
        </button>

        <button type="submit" name="status"
                value="resolved">
            Resolve
        </button>

        <button type="submit" name="status"
                value="dismissed">
            Dismiss
        </button>
    </form>
</td>

            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>

            </table>
        </section>
    </main>

    <script>
        function searchReports() {
            const search = document.getElementById('searchInput')
                .value.toLowerCase();

            const rows = document.querySelectorAll(
                '#reportsTable tbody tr'
            );

            rows.forEach(row => {
                row.style.display =
                    row.textContent.toLowerCase().includes(search)
                    ? ''
                    : 'none';
            });
        }
    </script>
</body>
</html>
