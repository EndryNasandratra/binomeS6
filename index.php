<?php
require_once __DIR__ . '/includes/article_repository.php';

$route = $_GET['route'] ?? '';
$route = trim((string) $route, '/');

$pageTitle = 'Actualites Iran | FrontOffice';
$metaDescription = 'Suivi de la guerre en Iran: analyses, diplomatie, securite et impacts internationaux.';
$pageHeading = 'Actualites sur la guerre en Iran';
$sections = fetchAvailableSections();
$contentType = 'home';
$articles = [];
$article = null;
$httpStatus = 200;

if ($route === '') {
    $articles = fetchLatestArticles(9);
} elseif (preg_match('#^section/([a-zA-Z0-9-]+)$#', $route, $matches)) {
    $contentType = 'section';
    $sectionSlug = $matches[1];
    $sectionName = ucwords(str_replace('-', ' ', $sectionSlug));
    $articles = fetchArticlesBySection($sectionName);
    $pageTitle = 'Section ' . $sectionName . ' | Actualites Iran';
    $metaDescription = 'Articles de la section ' . $sectionName . ' sur le conflit en Iran.';
    $pageHeading = 'Section: ' . $sectionName;
} elseif (preg_match('#^article/([a-zA-Z0-9-]+)$#', $route, $matches)) {
    $contentType = 'article';
    $slug = $matches[1];
    $article = fetchArticleBySlug($slug);
    if ($article === null) {
        $contentType = 'not_found';
        $httpStatus = 404;
        $pageTitle = 'Article introuvable';
        $metaDescription = 'L\'article demande est introuvable.';
        $pageHeading = 'Article introuvable';
    } else {
        $pageTitle = ($article['meta_title'] ?: $article['titre']) . ' | Actualites Iran';
        $metaDescription = substr(strip_tags((string) $article['chapeau']), 0, 155);
        $pageHeading = (string) $article['titre'];
    }
} else {
    $contentType = 'not_found';
    $httpStatus = 404;
    $pageTitle = 'Page introuvable';
    $metaDescription = 'La page demandee est introuvable.';
    $pageHeading = 'Page introuvable';
}

http_response_code($httpStatus);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="/assets/css/front.css">
</head>
<body>
    <header class="news-header">
        <div class="news-header__top">
            <p class="kicker">Edition speciale - Analyse geopolitique</p>
            <a class="admin-link" href="/admin">Acces redaction</a>
        </div>

        <div class="masthead">
            <h1>LE QUOTIDIEN INTERNATIONAL</h1>
            <p>Informations, analyses et decryptages sur le conflit Iranien</p>
        </div>

        <nav class="section-nav" aria-label="Navigation des sections">
            <a href="/">Accueil</a>
            <?php foreach ($sections as $section): ?>
                <a href="/section/<?php echo urlencode(strtolower(str_replace(' ', '-', (string) $section))); ?>">
                    <?php echo htmlspecialchars((string) $section, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </header>

    <main class="container">
        <?php if ($contentType === 'article' && $article !== null): ?>
            <article class="article-full newspaper-sheet">
                <p class="article-tag"><?php echo htmlspecialchars((string) $article['section'], ENT_QUOTES, 'UTF-8'); ?></p>
                <h2 class="article-title"><?php echo htmlspecialchars((string) $article['titre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <?php if (!empty($article['image_principale'])): ?>
                    <img
                        src="<?php echo htmlspecialchars((string) $article['image_principale'], ENT_QUOTES, 'UTF-8'); ?>"
                        alt="<?php echo htmlspecialchars((string) ($article['image_alt'] ?: $article['titre']), ENT_QUOTES, 'UTF-8'); ?>"
                    >
                <?php endif; ?>
                <h3 class="article-standfirst"><?php echo htmlspecialchars((string) $article['chapeau'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="meta">
                    Publication: <?php echo htmlspecialchars((string) $article['date_publication'], ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <div class="body-copy">
                    <?php
                    $articleBody = (string) $article['corps'];
                    $primaryImage = trim((string) ($article['image_principale'] ?? ''));
                    if ($primaryImage !== '') {
                        $escapedPrimaryImage = preg_quote($primaryImage, '/');
                        $articleBody = preg_replace(
                            '/<img[^>]+src=["\']' . $escapedPrimaryImage . '["\'][^>]*>\s*/i',
                            '',
                            $articleBody,
                            1
                        );
                    }

                    $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><img><h2><h3><h4><blockquote><table><thead><tbody><tr><th><td>';
                    echo strip_tags($articleBody, $allowedTags);
                    ?>
                </div>
                <a class="back-link" href="/">Retour aux articles</a>
            </article>
        <?php elseif ($contentType === 'not_found'): ?>
            <section class="empty-state">
                <h2>Erreur 404</h2>
                <p>La page demandee n'existe pas.</p>
                <a class="back-link" href="/">Retour a l'accueil</a>
            </section>
        <?php else: ?>
            <section class="headline-band">
                <p class="headline-band__label">A la Une</p>
                <h2><?php echo htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="headline-band__text">Une maquette FrontOffice orientee presse numerique avec URLs normalisees, titres hierarchises et balises SEO.</p>
            </section>

            <?php if ($contentType === 'section'): ?>
                <h2 class="section-title">Articles de section</h2>
            <?php endif; ?>

            <?php if (empty($articles)): ?>
                <section class="empty-state">
                    <h2>Aucun article publie</h2>
                    <p>Ajoute des contenus depuis le backoffice.</p>
                </section>
            <?php else: ?>
                <?php $featured = $articles[0] ?? null; ?>

                <?php if ($featured !== null): ?>
                    <section class="lead-layout">
                        <article class="lead-article">
                            <p class="lead-article__section"><?php echo htmlspecialchars((string) $featured['section'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <h2>
                                <a href="/article/<?php echo htmlspecialchars((string) $featured['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars((string) $featured['titre'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h2>
                            <p class="lead-article__chapeau"><?php echo htmlspecialchars((string) $featured['chapeau'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <a class="read-more" href="/article/<?php echo htmlspecialchars((string) $featured['slug'], ENT_QUOTES, 'UTF-8'); ?>">Lire l'analyse complete</a>
                        </article>

                        <aside class="brief-column">
                            <h3>Dernieres mises a jour</h3>
                            <?php foreach (array_slice($articles, 1, 4) as $item): ?>
                                <article class="brief-item">
                                    <p><?php echo htmlspecialchars((string) $item['section'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <h4>
                                        <a href="/article/<?php echo htmlspecialchars((string) $item['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars((string) $item['titre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </h4>
                                </article>
                            <?php endforeach; ?>
                        </aside>
                    </section>
                <?php endif; ?>

                <?php if (count($articles) > 1): ?>
                    <section class="grid">
                        <?php foreach (array_slice($articles, 1) as $item): ?>
                            <article class="card">
                                <p class="card__section"><?php echo htmlspecialchars((string) $item['section'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <h3>
                                    <a href="/article/<?php echo htmlspecialchars((string) $item['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars((string) $item['titre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </h3>
                                <p><?php echo htmlspecialchars((string) $item['chapeau'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <a class="read-more" href="/article/<?php echo htmlspecialchars((string) $item['slug'], ENT_QUOTES, 'UTF-8'); ?>">Lire l'article</a>
                            </article>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>Template FrontOffice inspiration presse - Projet pedagogique.</p>
    </footer>
</body>
</html>