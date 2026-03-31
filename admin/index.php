<?php
require_once __DIR__ . '/auth_controller.php';

ensureSessionStarted();

if (isAdminAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $authResult = authenticateAdmin($username, $password);
    if ($authResult['success']) {
        header('Location: dashboard.php');
        exit;
    }

    $error = $authResult['error'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion BackOffice</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <main class="login-shell">
        <section class="login-card">
            <h1>Connexion redaction</h1>
            <p>Accedez au panneau de gestion des contenus.</p>

            <?php if ($error !== ''): ?>
                <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="username">Identifiant</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="admin"
                    autocomplete="username"
                    required
                >

                <label for="password">Mot de passe</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    value="admin123"
                    autocomplete="current-password"
                    required
                >

                <button class="btn" type="submit">Se connecter</button>
            </form>

            <p class="login-hint">Acces local: localhost:8080/admin</p>
        </section>
    </main>
</body>
</html>
