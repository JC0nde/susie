<?php
$title = "Développeur Web & Logiciel | Suisse Romande";
$description = "Pas d'usines à gaz, pas de jargon. Du code robuste (Vue, React, PHP, SQL) et une liberté totale.";
$lang = "fr";

// On récupère les articles
$posts = get_blog_posts();
?>

<section class="blog-section">
    <?php if (empty($posts)): ?>
        <p>Aucun article pour le moment. C'est le début de l'aventure !</p>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <article class="post-preview" style="padding-bottom: 40px;">
                    <h2 class="post-title" style="font-size: 1.8rem; margin: 10px 0;">
                        <a href="/blog/<?= $post['slug']; ?>.html"><?= htmlspecialchars($post['title']); ?></a>
                    </h2>
                    
                    <div class="excerpt">
                        <?php 
                        $parsedown = new Parsedown();
                        $markdown_filtre = apply_responsive_images($post['excerpt_markdown']);
                        echo $parsedown->text($markdown_filtre);
                        ?>
                    </div>
                    
                    <a href="/blog/<?= $post['slug']; ?>.html" class="read-more">Lire l'article complet →</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
