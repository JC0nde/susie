<?php
global $config;
$__categories_enabled = ($config['features']['generate_categories'] ?? 'true') === 'true';

if ($__categories_enabled) {
    $__categories_list = get_categories();
    if (!empty($__categories_list)):
?>
<nav class="categories-list">
    <ul>
        <?php foreach ($__categories_list as $__cat_slug => $__cat): ?>
            <li><a href="/categorie/<?= htmlspecialchars($__cat_slug) ?>.html"><?= htmlspecialchars($__cat['name']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</nav>
<?php
    endif;
}
?>
