<?php

session_start();


$articles = [
  ['id' => 1, 'title' => 'Why Our Library Needs Longer Opening Hours', 'author' => 'Nimali Perera',   'category' => 'Campus Life', 'status' => 'pending',  'rejection_reason' => '', 'submitted_at' => '2026-11-04 09:15', 'body' => "Many students rely on the library after lectures end, yet it closes at 6 PM. This article argues for extended weekday hours during exam periods."],
  ['id' => 2, 'title' => 'Inter-Faculty Cricket Finals Recap',          'author' => 'Kasun Fernando', 'category' => 'Sports',      'status' => 'pending',  'rejection_reason' => '', 'submitted_at' => '2026-11-05 14:40', 'body' => "A match report covering the final between the Engineering and Business teams, including key moments and player highlights."],
  ['id' => 3, 'title' => 'Tips for Surviving Your First Semester',      'author' => 'Ayesha Silva',   'category' => 'Advice',      'status' => 'approved', 'rejection_reason' => '', 'submitted_at' => '2026-11-01 11:05', 'body' => "Practical advice on time management, note-taking, and making friends in your first months on campus."],
  ['id' => 4, 'title' => 'The Canteen Is Terrible (Rant)',              'author' => 'Dilan Jayasuriya', 'category' => 'Opinion',    'status' => 'rejected', 'rejection_reason' => 'Contains personal attacks on staff. Please rewrite with constructive points.', 'submitted_at' => '2026-10-30 16:20', 'body' => "An unstructured complaint about canteen food and staff."],
];


$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id     = (int)($_POST['article_id'] ?? 0);
  $action = $_POST['action'] ?? '';

  if ($action === 'approve') {

    $flash = ['type' => 'success', 'text' => "Article #$id approved and ready to publish."];
  } elseif ($action === 'reject') {
    $reason = trim($_POST['reason'] ?? '');
    if ($reason === '') {
      $flash = ['type' => 'error', 'text' => 'Enter a rejection reason so the student knows what to fix.'];
    } else {

      $flash = ['type' => 'success', 'text' => "Article #$id rejected. The student will see your reason."];
    }
  }
}


$filter = $_GET['status'] ?? 'pending';
if (!in_array($filter, ['pending', 'approved', 'rejected', 'all'], true)) {
  $filter = 'pending';
}


$list = array_values(array_filter($articles, fn($a) => $filter === 'all' || $a['status'] === $filter));

$counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
foreach ($articles as $a) {
  $counts[$a['status']]++;
}

$selected = null;
if (isset($_GET['view'])) {

  foreach ($articles as $a) {
    if ($a['id'] === (int)$_GET['view']) {
      $selected = $a;
      break;
    }
  }
}

function e(string $s): string
{
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Article approvals · Admin</title>
  <style>
    :root {
      --bg: #f5f6f8;
      --surface: #ffffff;
      --ink: #1d2433;
      --muted: #5d6676;
      --line: #dde1e8;
      --accent: #1f4fd8;
      --ok: #17794a;
      --ok-bg: #e4f4ec;
      --bad: #b3261e;
      --bad-bg: #fbe9e7;
      --wait: #8a5a00;
      --wait-bg: #fdf1d6;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font: 16px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif;
      background: var(--bg);
      color: var(--ink);
    }

    header {
      background: var(--ink);
      color: #fff;
      padding: 14px 24px;
      display: flex;
      gap: 24px;
      align-items: center;
      flex-wrap: wrap;
    }

    header strong {
      font-size: 18px;
    }

    header a {
      color: #cdd5e6;
      text-decoration: none;
    }

    header a:hover,
    header a.current {
      color: #fff;
      text-decoration: underline;
    }

    main {
      max-width: 1100px;
      margin: 0 auto;
      padding: 24px 16px 48px;
    }

    h1 {
      font-size: 24px;
      margin: 0 0 4px;
    }

    .sub {
      color: var(--muted);
      margin: 0 0 20px;
    }

    .tabs {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .tabs a {
      padding: 8px 14px;
      border: 1px solid var(--line);
      border-radius: 6px;
      background: var(--surface);
      color: var(--ink);
      text-decoration: none;
    }

    .tabs a.active {
      background: var(--ink);
      color: #fff;
      border-color: var(--ink);
    }

    .flash {
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 16px;
    }

    .flash.success {
      background: var(--ok-bg);
      color: var(--ok);
    }

    .flash.error {
      background: var(--bad-bg);
      color: var(--bad);
    }

    .panel {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 8px;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 640px;
    }

    th,
    td {
      text-align: left;
      padding: 12px 16px;
      border-bottom: 1px solid var(--line);
      vertical-align: middle;
    }

    th {
      font-size: 14px;
      color: var(--muted);
      font-weight: 600;
      background: #fafbfc;
    }

    tr:last-child td {
      border-bottom: 0;
    }

    .badge {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
    }

    .badge.pending {
      background: var(--wait-bg);
      color: var(--wait);
    }

    .badge.approved {
      background: var(--ok-bg);
      color: var(--ok);
    }

    .badge.rejected {
      background: var(--bad-bg);
      color: var(--bad);
    }

    .btn {
      display: inline-block;
      padding: 8px 14px;
      border-radius: 6px;
      border: 1px solid var(--line);
      background: var(--surface);
      color: var(--ink);
      font: inherit;
      cursor: pointer;
      text-decoration: none;
    }

    .btn:hover {
      border-color: var(--ink);
    }

    .btn:focus-visible,
    a:focus-visible,
    textarea:focus-visible {
      outline: 3px solid var(--accent);
      outline-offset: 2px;
    }

    .btn.approve {
      background: var(--ok);
      border-color: var(--ok);
      color: #fff;
    }

    .btn.reject {
      background: var(--bad);
      border-color: var(--bad);
      color: #fff;
    }

    .empty {
      padding: 32px 16px;
      text-align: center;
      color: var(--muted);
    }

    .detail {
      margin-top: 24px;
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 24px;
    }

    .detail h2 {
      margin: 0 0 8px;
      font-size: 22px;
    }

    .meta {
      color: var(--muted);
      margin-bottom: 16px;
    }

    .body-text {
      max-width: 70ch;
      white-space: pre-wrap;
      margin-bottom: 24px;
    }

    .actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: flex-start;
    }

    .reject-form {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--line);
      max-width: 560px;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
    }

    textarea {
      width: 100%;
      min-height: 110px;
      padding: 10px;
      font: inherit;
      border: 1px solid var(--line);
      border-radius: 6px;
    }

    .hint {
      color: var(--muted);
      font-size: 14px;
      margin: 6px 0 12px;
    }

    .reason-box {
      background: var(--bad-bg);
      color: var(--bad);
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 16px;
    }
  </style>
</head>

<body>

  <header>
    <strong>Admin</strong>
    <a href="approvals.php" class="current">Article approvals</a>
    <a href="comments.php">Comments</a>
  </header>

  <main>
    <h1>Article approvals</h1>
    <p class="sub">Review student submissions and decide what gets published.</p>

    <?php if ($flash): ?>
      <div class="flash <?= e($flash['type']) ?>" role="status"><?= e($flash['text']) ?></div>
    <?php endif; ?>

    <nav class="tabs" aria-label="Filter by status">
      <a href="?status=pending" class="<?= $filter === 'pending'  ? 'active' : '' ?>">Pending (<?= $counts['pending'] ?>)</a>
      <a href="?status=approved" class="<?= $filter === 'approved' ? 'active' : '' ?>">Approved (<?= $counts['approved'] ?>)</a>
      <a href="?status=rejected" class="<?= $filter === 'rejected' ? 'active' : '' ?>">Rejected (<?= $counts['rejected'] ?>)</a>
      <a href="?status=all" class="<?= $filter === 'all'      ? 'active' : '' ?>">All</a>
    </nav>


    <div class="panel">
      <?php if (!$list): ?>
        <div class="empty">No <?= $filter === 'all' ? '' : e($filter) . ' ' ?>articles right now.</div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Title</th>
              <th>Author</th>
              <th>Category</th>
              <th>Submitted</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $a): ?>
              <tr>
                <td><?= e($a['title']) ?></td>
                <td><?= e($a['author']) ?></td>
                <td><?= e($a['category']) ?></td>
                <td><?= e($a['submitted_at']) ?></td>
                <td><span class="badge <?= e($a['status']) ?>"><?= ucfirst(e($a['status'])) ?></span></td>
                <td><a class="btn" href="?status=<?= e($filter) ?>&view=<?= (int)$a['id'] ?>#detail">View article</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>


    <?php if ($selected): ?>
      <section class="detail" id="detail" aria-labelledby="detail-title">
        <h2 id="detail-title"><?= e($selected['title']) ?></h2>
        <div class="meta">
          By <?= e($selected['author']) ?> · <?= e($selected['category']) ?> · Submitted <?= e($selected['submitted_at']) ?>
          · <span class="badge <?= e($selected['status']) ?>"><?= ucfirst(e($selected['status'])) ?></span>
        </div>

        <?php if ($selected['status'] === 'rejected' && $selected['rejection_reason'] !== ''): ?>
          <div class="reason-box"><strong>Rejection reason:</strong> <?= e($selected['rejection_reason']) ?></div>
        <?php endif; ?>

        <div class="body-text"><?= e($selected['body']) ?></div>

        <?php if ($selected['status'] === 'pending'): ?>
          <div class="actions">
            <form method="post">
              <input type="hidden" name="article_id" value="<?= (int)$selected['id'] ?>">
              <input type="hidden" name="action" value="approve">

              <button type="submit" class="btn approve">Approve article</button>
            </form>
          </div>


          <form method="post" class="reject-form">
            <input type="hidden" name="article_id" value="<?= (int)$selected['id'] ?>">
            <input type="hidden" name="action" value="reject">
            <label for="reason">Reason for rejection</label>
            <textarea id="reason" name="reason" required placeholder="Explain what the student should change before resubmitting."></textarea>
            <p class="hint">The student will see this message next to their submission.</p>
            <button type="submit" class="btn reject">Reject article</button>
          </form>
        <?php else: ?>
          <p class="hint">This article has already been reviewed.</p>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </main>

</body>

</html>