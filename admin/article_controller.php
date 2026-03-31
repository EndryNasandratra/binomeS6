<?php

require_once __DIR__ . '/db.php';

function adminFetchAllArticles(): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [];
    }

    $stmt = $pdo->query(
           'SELECT a.id, a.titre, a.slug, a.section_id, s.nom AS section, a.date_publication, a.updated_at
            FROM articles a
            INNER JOIN sections s ON s.id = a.section_id
            ORDER BY a.date_publication DESC'
    );

    return $stmt->fetchAll();
}

function adminFetchArticleById(int $id): ?array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT a.*, s.nom AS section
         FROM articles a
         INNER JOIN sections s ON s.id = a.section_id
         WHERE a.id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $article = $stmt->fetch();

    return $article ?: null;
}

function adminCreateArticle(array $data): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return ['success' => false, 'error' => 'Base de donnees indisponible.'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO articles
            (titre, chapeau, corps, image_principale, image_alt, slug, section_id, meta_title, date_publication)
         VALUES
            (:titre, :chapeau, :corps, :image_principale, :image_alt, :slug, :section_id, :meta_title, :date_publication)'
    );

    try {
        $stmt->execute($data);
        return ['success' => true, 'error' => ''];
    } catch (PDOException $exception) {
        return ['success' => false, 'error' => 'Erreur SQL: ' . $exception->getMessage()];
    }
}

function adminUpdateArticle(int $id, array $data): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return ['success' => false, 'error' => 'Base de donnees indisponible.'];
    }

    $data['id'] = $id;
    $stmt = $pdo->prepare(
        'UPDATE articles
         SET titre = :titre,
             chapeau = :chapeau,
             corps = :corps,
             image_principale = :image_principale,
             image_alt = :image_alt,
             slug = :slug,
             section_id = :section_id,
             meta_title = :meta_title,
             date_publication = :date_publication
         WHERE id = :id'
    );

    try {
        $stmt->execute($data);
        return ['success' => true, 'error' => ''];
    } catch (PDOException $exception) {
        return ['success' => false, 'error' => 'Erreur SQL: ' . $exception->getMessage()];
    }
}

function adminDeleteArticle(int $id): bool
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return false;
    }

    $stmt = $pdo->prepare('DELETE FROM articles WHERE id = :id');
    return $stmt->execute(['id' => $id]);
}

function adminCountArticles(): int
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return 0;
    }

    $stmt = $pdo->query('SELECT COUNT(*) AS total FROM articles');
    $row = $stmt->fetch();

    return (int) ($row['total'] ?? 0);
}

function adminFetchSections(): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [];
    }

    $stmt = $pdo->query('SELECT id, nom, slug FROM sections ORDER BY nom ASC');
    return $stmt->fetchAll();
}

function adminFetchDashboardStats(): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [
            'total_articles' => 0,
            'total_sections' => 0,
            'published_today' => 0,
            'published_this_week' => 0,
            'last_publication' => null,
            'last_update' => null,
        ];
    }

    $stmt = $pdo->query(
        'SELECT
            (SELECT COUNT(*) FROM articles) AS total_articles,
            (SELECT COUNT(*) FROM sections) AS total_sections,
            (SELECT COUNT(*) FROM articles WHERE DATE(date_publication) = CURDATE()) AS published_today,
            (SELECT COUNT(*) FROM articles WHERE YEARWEEK(date_publication, 1) = YEARWEEK(CURDATE(), 1)) AS published_this_week,
            (SELECT MAX(date_publication) FROM articles) AS last_publication,
            (SELECT MAX(updated_at) FROM articles) AS last_update'
    );

    $row = $stmt->fetch();
    if (!$row) {
        return [
            'total_articles' => 0,
            'total_sections' => 0,
            'published_today' => 0,
            'published_this_week' => 0,
            'last_publication' => null,
            'last_update' => null,
        ];
    }

    return [
        'total_articles' => (int) ($row['total_articles'] ?? 0),
        'total_sections' => (int) ($row['total_sections'] ?? 0),
        'published_today' => (int) ($row['published_today'] ?? 0),
        'published_this_week' => (int) ($row['published_this_week'] ?? 0),
        'last_publication' => $row['last_publication'] ?? null,
        'last_update' => $row['last_update'] ?? null,
    ];
}

function adminFetchSectionDistribution(): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [];
    }

    $stmt = $pdo->query(
        'SELECT s.nom AS section_name, COUNT(a.id) AS total
         FROM sections s
         LEFT JOIN articles a ON a.section_id = s.id
         GROUP BY s.id, s.nom
         ORDER BY total DESC, s.nom ASC'
    );

    return $stmt->fetchAll();
}

function adminFetchRecentArticles(int $limit = 5): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [];
    }

    $limit = max(1, min($limit, 20));
    $stmt = $pdo->prepare(
        'SELECT a.id, a.titre, a.slug, s.nom AS section, a.date_publication, a.updated_at
         FROM articles a
         INNER JOIN sections s ON s.id = a.section_id
         ORDER BY a.updated_at DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function adminBuildUniqueSlug(string $title, ?int $excludeId = null): string
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        $fallback = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
        return $fallback !== '' ? $fallback : 'article-' . date('YmdHis');
    }

    $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
    if ($base === '') {
        $base = 'article';
    }

    $candidate = $base;
    $index = 2;

    while (true) {
        if ($excludeId !== null) {
            $stmt = $pdo->prepare('SELECT id FROM articles WHERE slug = :slug AND id <> :id LIMIT 1');
            $stmt->execute(['slug' => $candidate, 'id' => $excludeId]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM articles WHERE slug = :slug LIMIT 1');
            $stmt->execute(['slug' => $candidate]);
        }

        $exists = $stmt->fetch();
        if (!$exists) {
            return $candidate;
        }

        $candidate = $base . '-' . $index;
        $index++;
    }
}
