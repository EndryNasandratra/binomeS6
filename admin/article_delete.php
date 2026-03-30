<?php
require_once __DIR__ . '/auth_controller.php';
require_once __DIR__ . '/article_controller.php';

requireAdminAuthentication('index.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    adminDeleteArticle($id);
}

header('Location: articles.php');
exit;
