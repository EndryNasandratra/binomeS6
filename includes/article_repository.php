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
        'SELECT a.id, a.titre, a.chapeau, a.corps, a.slug, s.nom AS section, s.slug AS section_slug,
            a.image_principale, a.image_alt, a.meta_title, a.date_publication
         FROM articles a
         INNER JOIN sections s ON s.id = a.section_id
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
        'SELECT a.id, a.titre, a.chapeau, a.corps, a.slug, s.nom AS section, s.slug AS section_slug,
            a.image_principale, a.image_alt, a.meta_title, a.date_publication
         FROM articles a
         INNER JOIN sections s ON s.id = a.section_id
         WHERE s.slug = :section_slug
         ORDER BY a.date_publication DESC'
    );
        $stmt->execute(['section_slug' => strtolower($section)]);

    return $stmt->fetchAll();
}

function fetchArticleBySlug(string $slug): ?array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT a.id, a.titre, a.chapeau, a.corps, a.slug, s.nom AS section, s.slug AS section_slug,
            a.image_principale, a.image_alt, a.meta_title, a.date_publication
         FROM articles a
         INNER JOIN sections s ON s.id = a.section_id
         WHERE a.slug = :slug
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

    $stmt = $pdo->query('SELECT nom, slug FROM sections ORDER BY nom ASC');
    $rows = $stmt->fetchAll();

    return array_map(
        static fn(array $row): array => [
            'name' => (string) $row['nom'],
            'slug' => (string) $row['slug'],
        ],
        $rows
    );
}
