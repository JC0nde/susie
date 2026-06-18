<?php
// ==========================================
// 1. BLOC DE MÉTADONNÉES (Pour le haut de l'article)
// ==========================================
ob_start(); 
?>
<div class="post-meta-header" style="margin-bottom: 30px; padding-bottom: 20px;">
    <span class="post-category" style="text-transform: uppercase; font-size: 0.8rem; color: #888;"><?= htmlspecialchars($category ?? 'Général') ?></span>
    <h1 style="margin: 10px 0 5px 0; font-size: 2.5rem;"><?= htmlspecialchars($title) ?></h1>
    <div class="post-meta" style="font-size: 0.9rem; color: #666;">
        Par&nbsp;<strong><?= htmlspecialchars($author ?? 'Jonathan Conde') ?></strong>&nbsp;le&nbsp;<time><?= htmlspecialchars($date) ?></time>
    </div>
</div>
<?php 
$post_header_html = ob_get_clean();


// ==========================================
// 2. BLOC DE NAVIGATION ET COMMENTAIRES (Pour le bas de l'article)
// ==========================================
ob_start(); 
?>
<?php 
// Assemblage parfait en utilisant les variables natives de Susie
$full_post_url = rtrim($base_url, '/') . '/blog/' . $filename_slug . '.html'; 
$_masto_id = $meta['mastodon_id'] ?? '';
?>

<hr class="comment-separator">

<section class="post-comments-cta">
    <h3>Envie de donner votre opinion ?</h3>
    <p>Susie est un site statique sans trackers. Pour réagir, vous pouvez :</p>
    
    <div class="comment-buttons">
        <a href="mailto:ton-email@domaine.ch?subject=<?php echo urlencode('À propos de : ' . $title); ?>" class="btn-comment btn-email">
            ✉️ Répondre par Email
        </a>
        
        <?php if (!empty($_masto_id)): ?>
            <a href="https://tooting.ch/statuses/<?= $_masto_id ?>" target="_blank" rel="noopener" class="btn-comment btn-social">
                🐘 Commenter sur Mastodon
            </a>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($_masto_id)): ?>
    <div id="comments-section" style="padding-top: 2rem;">
        <h3 style="color: var(--accent-color); margin-bottom: 0.5rem;">/ commentaires :</h3>
        <p style="color: var(--muted-color); font-size: 14px; margin-bottom: 1.5rem; line-height: 1.4;">
            Tu as un compte Mastodon ? Réponds directement&nbsp;à&nbsp;<a href="https://tooting.ch/statuses/<?= $_masto_id ?>" target="_blank" rel="noopener" style="color: var(--accent-color); text-decoration: none; border-bottom: 1px dashed var(--accent-color);">ce pouet</a>&nbsp;pour commenter cet article.
        </p>
        <div id="comments-list">chargement des réactions...</div>
    </div>

    <script>
    (function() {
        var postId = "<?= $_masto_id ?>";
        var list = document.getElementById('comments-list');
        if (!list) return;

        fetch('https://tooting.ch/api/v1/statuses/' + postId + '/context')
            .then(function(res) {
                if (!res.ok) throw new Error('Erreur réseau');
                return res.json();
            })
            .then(function(data) {
                list.innerHTML = '';
                if (!data.descendants || data.descendants.length === 0) {
                    list.innerHTML = '<p style="color: var(--muted-color); font-size: 14px;">[ aucun commentaire pour le moment ]</p>';
                    return;
                }
                var commentsMap = {};
                data.descendants.forEach(function(reply) {
                    reply.replies = [];
                    commentsMap[reply.id] = reply;
                });
                var rootComments = [];
                data.descendants.forEach(function(reply) {
                    if (reply.in_reply_to_id && commentsMap[reply.in_reply_to_id]) {
                        commentsMap[reply.in_reply_to_id].replies.push(reply);
                    } else {
                        rootComments.push(reply);
                    }
                });
                function renderCommentTree(comment, depth) {
                    var handle = comment.account.acct.includes('@') ? comment.account.acct : comment.account.acct + '@tooting.ch';
                    var marginLeft = Math.min(depth * 20, 80);
                    var commentDate = new Date(comment.created_at);
                    var formattedDate = new Intl.DateTimeFormat('fr-CH', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }).format(commentDate);
                    var commentEl = document.createElement('div');
                    commentEl.style.margin = '20px 0 20px ' + marginLeft + 'px';
                    commentEl.style.paddingLeft = '15px';
                    commentEl.style.borderLeft = depth > 0 ? '1px dashed var(--muted-color)' : '1px solid var(--accent-color)';
                    commentEl.innerHTML = '<div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px;"><img src="' + comment.account.avatar + '" alt="Avatar" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(128,128,128,0.2); margin-top: 2px;" /><div style="flex: 1; font-size: 14px; line-height: 1.3;"><div style="display: flex; justify-content: space-between; align-items: baseline; gap: 10px;"><strong style="color: var(--text-color); font-weight: bold; font-size: 15px;">' + (comment.account.display_name || comment.account.username) + '</strong><span style="font-size: 12px; color: var(--muted-color); opacity: 0.8; font-family: monospace;">' + formattedDate + '</span></div><div style="color: var(--muted-color); font-size: 13px; margin-top: 2px;">@' + handle + '</div></div></div><div class="masto-comment-content" style="color: var(--text-color); font-size: 15px; line-height: 1.6; word-break: break-word; padding-left: 48px;">' + comment.content + '</div>';
                    list.appendChild(commentEl);
                    if (comment.replies && comment.replies.length > 0) {
                        comment.replies.sort(function(a, b) {
                            return new Date(a.created_at) - new Date(b.created_at);
                        });
                        comment.replies.forEach(function(child) {
                            renderCommentTree(child, depth + 1);
                        });
                    }
                }
                rootComments.forEach(function(root) {
                    renderCommentTree(root, 0);
                });
            })
            .catch(function(err) {
                list.innerHTML = '<p style="color: var(--muted-color); font-size: 14px;">[ Impossible de charger les commentaires : ' + err.message + ' ]</p>';
            });
    })();
    </script>
<?php endif; ?>

<div class="post-nav" style="display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; border-top: 1px solid #333;">
    <div>
        <?php if ($prev_post): ?>
            <a href="/blog/<?= htmlspecialchars($prev_post['slug']) ?>.html">← <?= htmlspecialchars($prev_post['title']) ?></a>
        <?php endif; ?>
    </div>
    <div>
        <?php if ($next_post): ?>
            <a href="/blog/<?= htmlspecialchars($next_post['slug']) ?>.html"><?= htmlspecialchars($next_post['title']) ?> →</a>
        <?php endif; ?>
    </div>
</div>
<?php
$navigation_html = ob_get_clean();


// ==========================================
// 3. INCLUSION DU LAYOUT
// ==========================================
include __DIR__ . '/../layouts/main.php';
