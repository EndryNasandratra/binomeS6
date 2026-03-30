<?php
require_once __DIR__ . '/auth_controller.php';
require_once __DIR__ . '/article_controller.php';

requireAdminAuthentication('index.php');

$username = $_SESSION['admin_user']['username'] ?? 'admin';
$totalArticles = adminCountArticles();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-shell">
        <header class="topbar">
            <p class="topbar__title">BackOffice - Redaction</p>
            <div class="topbar__links">
                <a href="../" target="_blank" rel="noopener noreferrer">Voir le front</a>
                <a href="logout.php">Se deconnecter</a>
            </div>
        </header>

        <aside class="sidebar">
            <span class="sidebar__brand">CMS REDACTION</span>
            <nav class="sidebar__menu">
                <a class="active" href="dashboard.php">Tableau de bord</a>
                <a href="articles.php">Tous les articles</a>
                <a href="article_form.php">Ajouter un article</a>
            </nav>
        </aside>

        <main class="main">
            <div class="container">
                <h1 class="page-title">Bonjour, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></h1>

                <section class="card">
                    <h2>Vue d'ensemble</h2>
                    <p class="muted">Backoffice inspire des interfaces CMS editoriales pour piloter les contenus du FrontOffice.</p>
                </section>

                <section class="kpi-grid">
                    <article class="kpi">
                        <p>Articles publies</p>
                        <strong><?php echo (int) $totalArticles; ?></strong>
                    </article>
                    <article class="kpi">
                        <p>Statut SEO</p>
                        <strong>Suivi actif</strong>
                    </article>
                    <article class="kpi">
                        <p>Action rapide</p>
                        <a class="btn" href="article_form.php">Rediger un article</a>
                    </article>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
