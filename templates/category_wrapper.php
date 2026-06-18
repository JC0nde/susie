<?php
// $category_name, $category_posts, $current_slug sont injectés par build.sh
$title = $category_name . ' — Articles';
$description = 'Articles dans la catégorie ' . $category_name;
$lang = 'fr';

ob_start();
?>
<section class="category-section">
    <h1>Catégorie : <?= htmlspecialchars($category_name) ?></h1>
    <div class="posts-list">
        <?php foreach ($category_posts as $post): ?>
            <article class="post-preview">
                <h2 class="post-title" style="font-size: 1.8rem; margin: 10px 0;">
                    <a href="/blog/<?= htmlspecialchars($post['slug']) ?>.html"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                <div class="excerpt">
                    <?= (new Parsedown())->text($post['excerpt_markdown']) ?>
                </div>
                <a href="/blog/<?= htmlspecialchars($post['slug']) ?>.html" class="read-more">Lire l'article complet →</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
