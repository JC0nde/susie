<?php
require __DIR__ . '/../functions.php';

if (function_exists('get_blog_posts')) {
    global $site_lang;

    $posts = get_blog_posts();
    $rss = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . PHP_EOL;
    $rss .= '<channel>' . PHP_EOL;
    $rss .= '  <title>' . htmlspecialchars($site_title) . '</title>' . PHP_EOL;
    $rss .= '  <link>' . $base_url . '</link>' . PHP_EOL;
    $rss .= '  <description>Suckless Minimalist Space Ecosystem Feed</description>' . PHP_EOL;
    $rss .= '  <language>' . $site_lang . '</language>' . PHP_EOL;
    $rss .= '  <atom:link href="' . $base_url . '/feed.xml" rel="self" type="application/rss+xml" />' . PHP_EOL;

    // On instancie Parsedown une seule fois pour le traitement
    $parsedown = new Parsedown();

    foreach ($posts as $post) {
        // CORRECTION SUCKLESS : On repart du fichier Markdown source pour éviter le bruit du HTML final
        $md_file = __DIR__ . '/../posts/' . $post['slug'] . '.md';
        if (!file_exists($md_file)) continue;

        // On extrait le markdown propre (sans le front matter)
        $parsed = parse_front_matter($md_file);
        $clean_markdown = apply_responsive_images($parsed['markdown']);

        // On convertit le markdown en HTML pur (sans templates, sans scripts, sans commentaires)
        $article_content = trim($parsedown->text($clean_markdown));
        
        $date_timestamp = strtotime($post['date']);
        $pub_date = $date_timestamp ? date('D, d M Y H:i:s O', $date_timestamp) : date('D, d M Y H:i:s O');

        $rss .= '  <item>' . PHP_EOL;
        $rss .= '    <title>' . htmlspecialchars($post['title']) . '</title>' . PHP_EOL;
        $rss .= '    <link>' . $base_url . '/blog/' . $post['slug'] . '.html</link>' . PHP_EOL;
        $rss .= '    <guid isPermaLink="true">' . $base_url . '/blog/' . $post['slug'] . '.html</guid>' . PHP_EOL;
        $rss .= '    <pubDate>' . $pub_date . '</pubDate>' . PHP_EOL;
        $rss .= '    <description>' . htmlspecialchars($post['description'] ?? '') . '</description>' . PHP_EOL;
        $rss .= '    <content:encoded><![CDATA[' . $article_content . ']]></content:encoded>' . PHP_EOL;
        $rss .= '  </item>' . PHP_EOL;
    }

    $rss .= '</channel>' . PHP_EOL;
    $rss .= '</rss>' . PHP_EOL;
    file_put_contents('dist_tmp/feed.xml', $rss);
    echo "[BUILD] Syndication Module: feed.xml generated." . PHP_EOL;
}
