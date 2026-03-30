<?php

require_once __DIR__ . '/db.php';

function adminFetchAllArticles(): array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return [];
    }

    $stmt = $pdo->query(
        'SELECT id, titre, slug, section, date_publication, updated_at
         FROM articles
         ORDER BY date_publication DESC'
    );

    return $stmt->fetchAll();
}

function adminFetchArticleById(int $id): ?array
{
    $pdo = getPDOConnection();
    if ($pdo === null) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = :id LIMIT 1');
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
         (titre, chapeau, corps, image_principale, image_alt, slug, section, meta_title, date_publication)
         VALUES
         (:titre, :chapeau, :corps, :image_principale, :image_alt, :slug, :section, :meta_title, :date_publication)'
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
             section = :section,
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
