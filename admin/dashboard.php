<?php
require_once __DIR__ . '/auth_controller.php';
require_once __DIR__ . '/article_controller.php';

requireAdminAuthentication('index.php');

$username = $_SESSION['admin_user']['username'] ?? 'admin';
$stats = adminFetchDashboardStats();
$recentArticles = adminFetchRecentArticles(6);
$sectionDistribution = adminFetchSectionDistribution();

$totalArticles = (int) ($stats['total_articles'] ?? 0);
$totalSections = (int) ($stats['total_sections'] ?? 0);
$publishedToday = (int) ($stats['published_today'] ?? 0);
$publishedThisWeek = (int) ($stats['published_this_week'] ?? 0);
$lastPublication = (string) ($stats['last_publication'] ?? '');
$lastUpdate = (string) ($stats['last_update'] ?? '');

function formatAdminDateTime(string $dateTime): string
{
    if ($dateTime === '') {
        return 'Aucune donnee';
    }

    $timestamp = strtotime($dateTime);
    if ($timestamp === false) {
        return $dateTime;
    }

    return date('d/m/Y H:i', $timestamp);
}
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
    <div class="admin-shell dashboard-shell">
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
            <div class="container dashboard-container">
                <h1 class="page-title">Bonjour, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></h1>

                <section class="card">
                    <h2>Vue d'ensemble</h2>
                    <p class="muted">Suivi en temps reel de la redaction, des sections et des contenus recents.</p>
                </section>

                <section class="kpi-grid">
                    <article class="kpi">
                        <p>Articles publies</p>
                        <strong><?php echo (int) $totalArticles; ?></strong>
                    </article>
                    <article class="kpi">
                        <p>Sections actives</p>
                        <strong><?php echo (int) $totalSections; ?></strong>
                    </article>
                    <article class="kpi">
                        <p>Publies aujourd'hui</p>
                        <strong><?php echo (int) $publishedToday; ?></strong>
                    </article>
                    <article class="kpi">
                        <p>Publies cette semaine</p>
                        <strong><?php echo (int) $publishedThisWeek; ?></strong>
                    </article>
                    <article class="kpi">
                        <p>Derniere publication</p>
                        <strong class="kpi--small"><?php echo htmlspecialchars(formatAdminDateTime($lastPublication), ENT_QUOTES, 'UTF-8'); ?></strong>
                    </article>
                    <article class="kpi">
                        <p>Derniere mise a jour</p>
                        <strong class="kpi--small"><?php echo htmlspecialchars(formatAdminDateTime($lastUpdate), ENT_QUOTES, 'UTF-8'); ?></strong>
                    </article>
                    <article class="kpi">
                        <p>Action rapide</p>
                        <a class="btn" href="article_form.php">Rediger un article</a>
                    </article>
                </section>

                <section class="card">
                    <h2>Repartition des articles par section</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Total articles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sectionDistribution)): ?>
                                <tr>
                                    <td colspan="2">Aucune section disponible.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sectionDistribution as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) $item['section_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int) $item['total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <section class="card">
                    <h2>Articles recemment modifies</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Section</th>
                                <th>Publication</th>
                                <th>Modification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentArticles)): ?>
                                <tr>
                                    <td colspan="5">Aucun article disponible.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentArticles as $article): ?>
                                    <tr>
                                        <td><?php echo (int) $article['id']; ?></td>
                                        <td>
                                            <a href="article_form.php?id=<?php echo (int) $article['id']; ?>">
                                                <?php echo htmlspecialchars((string) $article['titre'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) $article['section'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(formatAdminDateTime((string) $article['date_publication']), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars(formatAdminDateTime((string) $article['updated_at']), ENT_QUOTES, 'UTF-8'); ?></td>
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
