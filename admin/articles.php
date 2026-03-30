<?php
require_once __DIR__ . '/auth_controller.php';
require_once __DIR__ . '/article_controller.php';

requireAdminAuthentication('index.php');

$articles = adminFetchAllArticles();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion articles</title>
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
                <a href="dashboard.php">Tableau de bord</a>
                <a class="active" href="articles.php">Tous les articles</a>
                <a href="article_form.php">Ajouter un article</a>
            </nav>
        </aside>

        <main class="main">
            <div class="container">
                <h1 class="page-title">Articles</h1>

                <section class="card">
                    <p class="muted">Gestion complete des contenus: creation, edition, suppression et controle des metadonnees.</p>
                    <a class="btn" href="article_form.php">Ajouter un nouvel article</a>
                </section>

                <section class="card">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Section</th>
                                <th>Publication</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="5">Aucun article disponible.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td><?php echo (int) $article['id']; ?></td>
                                        <td><?php echo htmlspecialchars((string) $article['titre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $article['section'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $article['date_publication'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="actions">
                                            <a class="btn-outline" href="article_form.php?id=<?php echo (int) $article['id']; ?>">Modifier</a>
                                            <a class="btn-danger" href="article_delete.php?id=<?php echo (int) $article['id']; ?>" onclick="return confirm('Supprimer cet article ?');">Supprimer</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
