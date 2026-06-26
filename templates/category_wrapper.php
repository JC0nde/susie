<?php
/**
 * Susie - Category Archive Wrapper Template
 * Renders lists of compiled post previews filtering by localized tags or categories.
 *
 * @package Susie
 */

// Contextual variables ($category_name, $category_posts, $current_slug) are injected via orchestrator runtime.
global $site_lang;

$title = $category_name . ' — Articles';
$description = 'Archive listing for ' . $category_name;
$lang = $site_lang;

// Instantiate Parsedown once to minimize runtime memory footprint
$parsedown = new Parsedown();

ob_start();
?>
<section class="category-section">
    <h1>Category: <?= htmlspecialchars($category_name) ?></h1>
    
    <div class="posts-list">
        <?php foreach ($category_posts as $post): ?>
            <article class="post-preview">
                <h2 class="post-title" style="font-size: 1.8rem; margin: 10px 0;">
                    <a href="/blog/<?= htmlspecialchars($post['slug']) ?>.html"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                
                <div class="excerpt">
                    <?= $parsedown->text($post['excerpt_markdown']) ?>
                </div>
                
                <a href="/blog/<?= htmlspecialchars($post['slug']) ?>.html" class="read-more">Read full article →</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php
$content = ob_get_clean();

// Render inside global layout wrapper architecture
include __DIR__ . '/../layouts/main.php';
