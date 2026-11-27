<?php
// Works from /pages/*.php and /pages/<role>/*.php
$footerScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$footerBase = preg_match('#/pages/[^/]+/[^/]+\.php$#', $footerScript) === 1 ? '../../' : '../';
?>
<footer class="home-footer">
    <div class="footer-about">
        <h2 style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <img src="<?= $footerBase ?>assets/images/nsbm-logo-footer.png"
                 alt="NSBM Green University Town"
                 height="52"
                 style="display:block;height:52px;width:auto;max-width:100%;object-fit:contain;"
                 onerror="this.style.display='none'">
            <span>NSBM NewsDesk</span>
        </h2>
        <p>Your window into life at NSBM Green University. Stories, achievements and moments from our community.</p>
        <small>© <?= date('Y') ?> NSBM Green University. All rights reserved.</small>
    </div>
    <div class="quick-links">
        <h3>🔗 Quick Links</h3>
        <a href="home.php">🏠 Homepage ↗</a>
        <a href="news.php">📰 All News ↗</a>
        <a href="search.php">🔍 Search ↗</a>
    </div>
</footer>