<?php
require_once __DIR__ . '/auth_controller.php';
require_once __DIR__ . '/article_controller.php';

requireAdminAuthentication('index.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

$defaultData = [
    'titre' => '',
    'chapeau' => '',
    'corps' => '',
    'image_principale' => '',
    'image_alt' => '',
    'slug' => '',
    'section_id' => '',
    'meta_title' => '',
    'date_publication' => date('Y-m-d H:i:s'),
];

$error = '';
$success = '';

$sections = adminFetchSections();
if (!empty($sections)) {
    $defaultSectionId = (int) $sections[0]['id'];
    foreach ($sections as $section) {
        if ((string) ($section['slug'] ?? '') === 'international') {
            $defaultSectionId = (int) $section['id'];
            break;
        }
    }
    $defaultData['section_id'] = (string) $defaultSectionId;
}
if (empty($sections)) {
    $error = 'Aucune section disponible. Cree d\'abord des sections en base.';
}

$articleData = $defaultData;

function extractFirstImageFromHtml(string $html): ?string
{
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
        return trim((string) ($matches[1] ?? '')) ?: null;
    }

    return null;
}

if ($isEdit) {
    $found = adminFetchArticleById($id);
    if ($found === null) {
        $error = 'Article introuvable.';
        $isEdit = false;
    } else {
        foreach ($defaultData as $key => $value) {
            $articleData[$key] = (string) ($found[$key] ?? $value);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleData['titre'] = trim($_POST['titre'] ?? '');
    $articleData['chapeau'] = trim($_POST['chapeau'] ?? '');
    $articleData['corps'] = trim($_POST['corps'] ?? '');
    $articleData['section_id'] = trim($_POST['section_id'] ?? '');
    $articleData['date_publication'] = trim($_POST['date_publication'] ?? '');

    if ($articleData['titre'] === '' || $articleData['chapeau'] === '' || $articleData['corps'] === '') {
        $error = 'Les champs titre, chapeau et corps sont obligatoires.';
    } else {
        $slug = adminBuildUniqueSlug($articleData['titre'], $isEdit ? $id : null);
        $firstImage = extractFirstImageFromHtml($articleData['corps']);

        $payload = [
            'titre' => $articleData['titre'],
            'chapeau' => $articleData['chapeau'],
            'corps' => $articleData['corps'],
            'image_principale' => $firstImage,
            'image_alt' => $firstImage ? $articleData['titre'] : null,
            'slug' => $slug,
            'section_id' => (int) ($articleData['section_id'] !== '' ? $articleData['section_id'] : ($defaultData['section_id'] ?: 0)),
            'meta_title' => $articleData['titre'],
            'date_publication' => $articleData['date_publication'] ?: date('Y-m-d H:i:s'),
        ];

        $result = $isEdit ? adminUpdateArticle($id, $payload) : adminCreateArticle($payload);
        if ($result['success']) {
            $success = $isEdit ? 'Article modifie avec succes.' : 'Article cree avec succes.';
            if (!$isEdit) {
                foreach ($defaultData as $key => $value) {
                    $articleData[$key] = (string) $value;
                }
                $articleData['date_publication'] = date('Y-m-d H:i:s');
            }
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Modifier article' : 'Nouvel article'; ?></title>
    <link rel="stylesheet" href="assets/admin.css">
    <script src="https://cdn.tiny.cloud/1/t93ktfinnw9u6ha55jvqc94as0rq0pmdim9btbvpoe5u4guq/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
                <a href="articles.php">Tous les articles</a>
                <a class="active" href="article_form.php">Ajouter un article</a>
            </nav>
        </aside>

        <main class="main">
            <div class="container">
                <h1 class="page-title"><?php echo $isEdit ? 'Modifier un article' : 'Ajouter un article'; ?></h1>

                <section class="card">
                    <p class="muted">Renseigne titre, chapeau, section, contenu et date de publication. L'image principale est prise depuis la premiere image du corps.</p>
                </section>

                <?php if ($error !== ''): ?>
                    <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <?php if ($success !== ''): ?>
                    <div class="success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <section class="card">
                    <form method="post">
                        <label for="titre">Titre (h1)</label>
                        <input id="titre" name="titre" value="<?php echo htmlspecialchars($articleData['titre'], ENT_QUOTES, 'UTF-8'); ?>" required>

                        <label for="chapeau">Chapeau</label>
                        <input id="chapeau" name="chapeau" value="<?php echo htmlspecialchars($articleData['chapeau'], ENT_QUOTES, 'UTF-8'); ?>" required>

                        <label for="corps">Corps de l'article</label>
                        <textarea class="rich-editor" id="corps" name="corps"><?php echo htmlspecialchars($articleData['corps'], ENT_QUOTES, 'UTF-8'); ?></textarea>

                        <div class="grid">
                            <div>
                                <label for="section">Section</label>
                                <select id="section" name="section_id" required>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo (int) $section['id']; ?>" <?php echo ((string) $section['id'] === (string) $articleData['section_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars((string) $section['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="date_publication">Date publication (YYYY-MM-DD HH:MM:SS)</label>
                                <input id="date_publication" name="date_publication" value="<?php echo htmlspecialchars($articleData['date_publication'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <p class="muted">L'image principale est detectee automatiquement depuis la premiere image du corps de l'article.</p>

                        <div class="form-actions">
                            <button class="btn" type="submit"><?php echo $isEdit ? 'Enregistrer les modifications' : 'Publier'; ?></button>
                            <a class="btn-outline" href="articles.php">Retour a la liste</a>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>

    <script>
        (function () {
            const form = document.querySelector('form');
            const corpsField = document.getElementById('corps');

            function editorHasContent() {
                if (window.tinymce && tinymce.get('corps')) {
                    const editor = tinymce.get('corps');
                    const html = editor.getContent({ format: 'html' }) || '';
                    const text = editor.getContent({ format: 'text' }).replace(/\u00a0/g, ' ').trim();
                    const hasImage = /<img\b/i.test(html);
                    return text.length > 0 || hasImage;
                }

                return (corpsField.value || '').trim().length > 0;
            }

            form.addEventListener('submit', function (event) {
                if (window.tinymce) {
                    tinymce.triggerSave();
                }

                if (!editorHasContent()) {
                    event.preventDefault();
                    alert('Le champ "Corps de l\'article" est obligatoire.');

                    if (window.tinymce && tinymce.get('corps')) {
                        tinymce.get('corps').focus();
                    } else {
                        corpsField.focus();
                    }
                }
            });

            if (window.tinymce) {
                tinymce.init({
                    selector: '#corps',
                    menubar: true,
                    height: 320,
                    plugins: 'lists link image table code fullscreen wordcount',
                    toolbar: 'undo redo | styles | bold italic underline | bullist numlist | link image table | alignleft aligncenter alignright | code fullscreen',
                    automatic_uploads: true,
                    images_upload_url: 'tinymce_image_upload.php',
                    file_picker_types: 'image',
                    branding: false,
                    browser_spellcheck: true,
                    contextmenu: false
                });
            }
        })();
    </script>
</body>
</html>
