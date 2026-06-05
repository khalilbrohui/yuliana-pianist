<?php
try {
    $db = getDBConnection();
    
    // Check if slug is provided
    $slug = isset($params['slug']) ? cleanInput($params['slug']) : '';
    if (empty($slug)) {
        header("Location: " . BASE_URL . "/blog");
        exit;
    }

    // Query article details
    $stmt = $db->prepare("
        SELECT b.*, u.name AS author_name 
        FROM blog_posts b 
        JOIN users u ON b.author_id = u.id 
        WHERE b.slug = ? AND b.status = 'published'
    ");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();

    if (!$post) {
        echo "<div class='container' style='padding:140px 20px; text-align:center;'><h2>Artículo no encontrado</h2><p>Lo sentimos, el artículo de blog solicitado no existe.</p><a href='".BASE_URL."/blog' class='btn btn-primary' style='margin-top:20px;'>Volver al Blog</a></div>";
        return;
    }

    // Dynamic titles
    $pageTitle = $post['title'] . " - Diario Yuliana Pianist";
    $pageDescription = $post['excerpt'];

    // Query Related Articles
    $relStmt = $db->prepare("
        SELECT id, slug, title, category, excerpt, created_at, image_url 
        FROM blog_posts 
        WHERE category = ? AND id != ? AND status = 'published' 
        LIMIT 2
    ");
    $relStmt->execute([$post['category'], $post['id']]);
    $related = $relStmt->fetchAll();

    if (empty($related)) {
        $relStmt = $db->prepare("
            SELECT id, slug, title, category, excerpt, created_at, image_url 
            FROM blog_posts 
            WHERE id != ? AND status = 'published' 
            LIMIT 2
        ");
        $relStmt->execute([$post['id']]);
        $related = $relStmt->fetchAll();
    }

} catch (Exception $e) {
    die("Error al cargar el artículo: " . $e->getMessage());
}
?>

<article class="post-layout">
    <!-- Meta details -->
    <span class="post-meta">
        <?= htmlspecialchars($post['category']) ?> &nbsp;•&nbsp; <?= date('d/m/Y', strtotime($post['created_at'])) ?>
    </span>
    
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    
    <div style="display:flex; gap:15px; align-items:center; border-bottom:1px solid var(--glass-border); padding-bottom:25px; margin-bottom:40px;">
        <div style="width:40px; height:40px; border-radius:50%; background:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.1rem;">
            <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
        </div>
        <div>
            <span style="display:block; font-weight:bold; color:#fff;">Escrito por <?= htmlspecialchars($post['author_name']) ?></span>
            <small style="color:var(--text-muted); font-size:0.8rem;">Instructor de la Academia</small>
        </div>
    </div>

    <!-- Article Header Graphic (uses user's actual image if available) -->
    <?php if (!empty($post['image_url']) && file_exists($post['image_url'])): ?>
        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($post['image_url']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-banner" style="object-position: center 20%;">
    <?php else: ?>
        <div style="background: linear-gradient(135deg, var(--primary), var(--secondary)); width:100%; height:320px; border-radius:24px; margin-bottom:40px; display:flex; align-items:center; justify-content:center; font-size:7rem; color:rgba(255,255,255,0.15); border:1px solid var(--glass-border); box-shadow:var(--shadow);">
            <i class="far fa-newspaper"></i>
        </div>
    <?php endif; ?>

    <!-- Article Body -->
    <div class="post-body">
        <?= $post['content'] ?>
    </div>

    <div style="margin-top:60px; display:flex; justify-content:center;">
        <a href="<?= BASE_URL ?>/blog" class="btn btn-secondary"><i class="fas fa-arrow-left" style="margin-right:10px;"></i> Volver al listado</a>
    </div>
</article>

<!-- Related Articles Section -->
<?php if (!empty($related)): ?>
    <section style="background: rgba(0, 0, 0, 0.2); border-top:1px solid var(--glass-border); padding: 80px 0;">
        <div class="container reveal">
            <h3 style="font-size:1.8rem; margin-bottom:40px; text-align:center;">Artículos Relacionados</h3>
            
            <div class="blog-grid" style="max-width:900px; margin: 0 auto;">
                <?php foreach ($related as $rel): ?>
                    <div class="blog-card glass-card">
                        <div class="blog-card-meta">
                            <span><?= htmlspecialchars($rel['category']) ?></span>
                            <span style="color:var(--text-muted);"><?= date('d/m/Y', strtotime($rel['created_at'])) ?></span>
                        </div>
                        <h4 style="font-size:1.3rem; margin-bottom:10px; line-height:1.3;"><a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($rel['slug']) ?>" style="color:#fff;"><?= htmlspecialchars($rel['title']) ?></a></h4>
                        <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:20px;"><?= htmlspecialchars($rel['excerpt']) ?></p>
                        <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($rel['slug']) ?>" class="btn btn-secondary" style="padding:6px 14px; font-size:0.8rem; width:fit-content;">Leer Artículo</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
