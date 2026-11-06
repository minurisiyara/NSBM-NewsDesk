<?php

session_start();


$comments = [
  ['id' => 11, 'article_title' => 'Tips for Surviving Your First Semester', 'username' => 'ruwan_k',   'comment_text' => 'Great advice, the note-taking section really helped me.', 'created_at' => '2026-11-05 18:22'],
  ['id' => 12, 'article_title' => 'Tips for Surviving Your First Semester', 'username' => 'spam_bot9', 'comment_text' => 'Buy cheap followers now at example.com!!!',                   'created_at' => '2026-11-05 19:03'],
  ['id' => 13, 'article_title' => 'Inter-Faculty Cricket Finals Recap',      'username' => 'thisara_m', 'comment_text' => 'That last over was unreal. Well played, both teams.',        'created_at' => '2026-11-06 08:47'],
  ['id' => 14, 'article_title' => 'Inter-Faculty Cricket Finals Recap',      'username' => 'anon_77',   'comment_text' => 'The Business team are idiots and should quit.',               'created_at' => '2026-11-06 09:10'],
];


$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
  $id = (int)($_POST['comment_id'] ?? 0);

  $flash = ['type' => 'success', 'text' => "Comment #$id removed."];
  $comments = array_values(array_filter($comments, fn($c) => $c['id'] !== $id));
}


$q = trim($_GET['q'] ?? '');

$list = $q === '' ? $comments : array_values(array_filter($comments, function ($c) use ($q) {
  return stripos($c['comment_text'], $q) !== false || stripos($c['username'], $q) !== false;
}));

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
  <title>Comment moderation · Admin</title>
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

    .flash {
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 16px;
      background: var(--ok-bg);
      color: var(--ok);
    }

    .search {
      display: flex;
      gap: 8px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }

    .search input {
      flex: 1;
      min-width: 200px;
      padding: 8px 12px;
      font: inherit;
      border: 1px solid var(--line);
      border-radius: 6px;
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
      min-width: 700px;
    }

    th,
    td {
      text-align: left;
      padding: 12px 16px;
      border-bottom: 1px solid var(--line);
      vertical-align: top;
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

    td.text {
      max-width: 380px;
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
    input:focus-visible {
      outline: 3px solid var(--accent);
      outline-offset: 2px;
    }

    .btn.remove {
      background: var(--bad);
      border-color: var(--bad);
      color: #fff;
    }

    .empty {
      padding: 32px 16px;
      text-align: center;
      color: var(--muted);
    }
  </style>
</head>

<body>

  <header>
    <strong>Admin</strong>
    <a href="approvals.php">Article approvals</a>
    <a href="comments.php" class="current">Comments</a>
  </header>

  <main>
    <h1>Comment moderation</h1>
    <p class="sub">Remove comments that are spam, abusive, or off-topic.</p>

    <?php if ($flash): ?>
      <div class="flash" role="status"><?= e($flash['text']) ?></div>
    <?php endif; ?>

    <form class="search" method="get" role="search">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by username or comment text" aria-label="Search comments">
      <button type="submit" class="btn">Search</button>
      <?php if ($q !== ''): ?><a class="btn" href="comments.php">Clear</a><?php endif; ?>
    </form>

    <div class="panel">
      <?php if (!$list): ?>
        <div class="empty"><?= $q === '' ? 'No comments to moderate.' : 'No comments match your search.' ?></div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Comment</th>
              <th>Article</th>
              <th>Posted</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($list as $c): ?>
              <tr>
                <td><?= (int)$c['id'] ?></td>
                <td><?= e($c['username']) ?></td>
                <td class="text"><?= e($c['comment_text']) ?></td>
                <td><?= e($c['article_title']) ?></td>
                <td><?= e($c['created_at']) ?></td>
                <td>
                  <form method="post" onsubmit="return confirm('Remove this comment? This cannot be undone.');">
                    <input type="hidden" name="comment_id" value="<?= (int)$c['id'] ?>">
                    <input type="hidden" name="action" value="delete">

                    <button type="submit" class="btn remove">Remove comment</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>

</body>

</html>