<?php
// Expects $article associative array from parent page (home.php, news.php, search.php)
if (!isset($article) || !is_array($article)) {
    return;
}

$cardFallbackImg = 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800';
$rawImg = trim((string) ($article['image_path'] ?? $article['image'] ?? ''));

if ($rawImg === '') {
    $cardImg = $cardFallbackImg;
} elseif (preg_match('#^https?://#i', $rawImg) || str_starts_with($rawImg, '../') || str_starts_with($rawImg, '/')) {
    $cardImg = $rawImg;
} else {
    $cardImg = '../' . ltrim($rawImg, '/');
}

$cardId = (int) ($article['id'] ?? 0);
$cardTitle = (string) ($article['title'] ?? 'Untitled Article');
$cardCategory = (string) ($article['category_name'] ?? $article['category'] ?? 'General');
$rawDate = (string) ($article['display_date'] ?? $article['created_at'] ?? 'now');
$cardDate = date('M j, Y', strtotime($rawDate));

$contentStr = (string) ($article['content'] ?? '');
$wordCount = str_word_count(strip_tags($contentStr));
$readMinutes = max(1, (int) ceil($wordCount / 200));
$cardReadTime = $readMinutes . ' min read';

$cardExcerpt = trim((string) ($article['summary'] ?? $article['description'] ?? ''));
if ($cardExcerpt === '' && $contentStr !== '') {
    $cardExcerpt = mb_strimwidth(strip_tags($contentStr), 0, 110, '...');
}
?>
<article class="news-card">
    <a href="article.php?id=<?= $cardId ?>" style="text-decoration: none; color: inherit; display: block;">
        <img
            src="<?= htmlspecialchars($cardImg, ENT_QUOTES, 'UTF-8') ?>"
            alt="<?= htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8') ?>"
        >

        <div class="news-card-content">
            <span class="news-category">
                <?= htmlspecialchars($cardCategory, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <h3><?= htmlspecialchars($cardTitle, ENT_QUOTES, 'UTF-8') ?></h3>

            <p><?= htmlspecialchars($cardExcerpt, ENT_QUOTES, 'UTF-8') ?></p>

            <div class="news-card-footer">
                <span><?= htmlspecialchars($cardDate, ENT_QUOTES, 'UTF-8') ?></span>
                <span><?= htmlspecialchars($cardReadTime, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </a>
</article>