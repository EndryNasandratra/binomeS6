<?php

require_once __DIR__ . '/../db.php';

function fetchLatestArticles(int $limit = 6): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [];
    }

    $limit = max(1, min($limit, 30));
    $stmt = $pdo->prepare(
        'SELECT id, titre, chapeau, corps, slug, section, image_principale, image_alt, meta_title, date_publication
         FROM articles
         ORDER BY date_publication DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function fetchArticlesBySection(string $section): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT id, titre, chapeau, corps, slug, section, image_principale, image_alt, meta_title, date_publication
         FROM articles
         WHERE LOWER(section) = LOWER(:section)
         ORDER BY date_publication DESC'
    );
    $stmt->execute(['section' => $section]);

    return $stmt->fetchAll();
}

function fetchArticleBySlug(string $slug): ?array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT id, titre, chapeau, corps, slug, section, image_principale, image_alt, meta_title, date_publication
         FROM articles
         WHERE slug = :slug
         LIMIT 1'
    );
    $stmt->execute(['slug' => $slug]);

    $article = $stmt->fetch();
    return $article ?: null;
}

function fetchAvailableSections(): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [];
    }

    $stmt = $pdo->query('SELECT DISTINCT section FROM articles ORDER BY section ASC');
    $rows = $stmt->fetchAll();

    return array_map(static fn(array $row): string => (string) $row['section'], $rows);
}
