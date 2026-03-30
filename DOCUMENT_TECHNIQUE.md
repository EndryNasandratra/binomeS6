# Document Technique - Template Projet

## 1) Infos etudiant
- Nom complet:
- Numero ETU:
- Binome:

## 2) Stack technique
- Langage: PHP 8.2 + Apache
- Base: MySQL 8
- Conteneurs: Docker Compose
- URL rewriting: `.htaccess` + `mod_rewrite`

## 3) Architecture
- FrontOffice: `index.php`, `assets/css/front.css`, `includes/article_repository.php`
- BackOffice: `admin/index.php`, `admin/dashboard.php`, `admin/articles.php`, `admin/article_form.php`
- Authentification BO: `admin/auth_controller.php`
- Connexion DB: `db.php`

## 4) Compte BO par defaut
- URL: `http://localhost:8080/admin`
- User: `admin`
- Password: `admin123`

## 5) Captures ecran a inserer
- Capture FrontOffice (accueil)
- Capture FrontOffice (detail article)
- Capture BackOffice (login)
- Capture BackOffice (liste articles)
- Capture BackOffice (formulaire article)

## 6) Modelisation base
- Table `users`
- Table `articles`
- Fichier SQL: `base.sql`

## 7) Verification consignes
- URL normalisee (rewriting): OK
- Structure h1-h6: OK (h1 titre page, h2/h3 contenus)
- Titles page: OK
- Meta description: OK
- ALT images: OK
- Test Lighthouse desktop: A COMPLETER
- Test Lighthouse mobile: A COMPLETER

## 8) Deploiement public
- Lien GitHub/GitLab:
- Lien demo:
- Date livraison:
