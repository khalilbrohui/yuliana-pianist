<?php
try {
    $db = getDBConnection();

    // Fetch published posts
    $postsStmt = $db->query("
        SELECT b.*, u.name AS author_name 
        FROM blog_posts b 
        JOIN users u ON b.author_id = u.id 
        WHERE b.status = 'published' 
        ORDER BY b.created_at DESC
    ");
    $posts = $postsStmt->fetchAll();

    // Fetch categories for filters
    $catStmt = $db->query("SELECT DISTINCT category FROM blog_posts WHERE status = 'published'");
    $categories = $catStmt->fetchAll();

} catch (Exception $e) {
    die("Error al cargar el blog: " . $e->getMessage());
}
?>

<section class="container" style="padding-top: 140px;">
    <div class="section-title">
        <h2>Diario de la Academia</h2>
        <p>Guías técnicas, metodologías de práctica y novedades musicales directas de nuestro equipo.</p>
    </div>

    <!-- Blog search and category filters -->
    <div class="blog-header-row">
        <!-- Category Pill Selectors -->
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-secondary category-btn active" data-category="all" style="padding: 8px 16px; font-size:0.85rem;">Todos los Artículos</button>
            <?php foreach ($categories as $cat): ?>
                <button class="btn btn-secondary category-btn" data-category="<?= htmlspecialchars($cat['category']) ?>" style="padding: 8px 16px; font-size:0.85rem;"><?= htmlspecialchars($cat['category']) ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Search Box -->
        <div class="search-input-wrapper">
            <input type="text" id="blogSearchInput" class="form-control" placeholder="Buscar artículos..." style="padding-right: 40px;">
            <i class="fas fa-search"></i>
        </div>
    </div>

    <!-- Articles Grid -->
    <?php if (empty($posts)): ?>
        <div class="glass-card" style="text-align:center; padding:50px 20px;">
            <i class="far fa-edit" style="font-size:3rem; color:var(--accent); margin-bottom:15px;"></i>
            <p>No se han publicado artículos todavía. ¡Visítanos pronto!</p>
        </div>
    <?php else: ?>
        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
                <div class="blog-card glass-card reveal" data-category="<?= htmlspecialchars($post['category']) ?>">
                    <!-- Render dynamic images (using pianist photos) -->
                    <?php if (!empty($post['image_url']) && file_exists($post['image_url'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($post['image_url']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="blog-card-img" style="object-position: center 20%;">
                    <?php else: ?>
                        <div style="background: linear-gradient(135deg, var(--secondary), var(--bg-light)); height:200px; border-radius:12px; margin-bottom:20px; display:flex; align-items:center; justify-content:center; font-size:3.5rem; color:rgba(255,255,255,0.15);">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="blog-card-meta">
                        <span><?= htmlspecialchars($post['category']) ?></span>
                        <span style="color:var(--text-muted);"><?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
                    </div>
                    
                    <h3><?= htmlspecialchars($post['title']) ?></h3>
                    <p><?= htmlspecialchars($post['excerpt']) ?></p>
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--glass-border); padding-top:15px; margin-top:10px;">
                        <span style="font-size:0.8rem; color:var(--text-muted);"><i class="fas fa-user-edit"></i> Por <?= htmlspecialchars($post['author_name']) ?></span>
                        <a href="<?= BASE_URL ?>/blog/<?= htmlspecialchars($post['slug']) ?>" class="btn btn-secondary" style="padding: 6px 14px; font-size:0.8rem;">Leer <i class="fas fa-angle-right" style="margin-left:5px;"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
