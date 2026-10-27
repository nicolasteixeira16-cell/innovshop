InnovShop

Site e-commerce de produits innovants, réalisé avec Symfony. Les visiteurs consultent un catalogue, remplissent un panier et passent commande ; les administrateurs gèrent la boutique ; des vendeurs peuvent proposer leurs propres produits (marketplace) et une catégorie « Prototypes » permet de faire tester des produits en avant-première.

Site en ligne : https://ntx.alwaysdata.net/innovshop/
Dépôt GitHub : https://github.com/nicolasteixeira16-cell/innovshop
Kanban public (Trello) : https://trello.com/b/840nCSqj/innovshop-backlog
Sommaire
Fonctionnalités
Technologies
Installation en local
Comptes de démonstration
Lancer les tests
Sécurité
Déploiement sur Alwaysdata
Structure du projet
Documentation et diagrammes
Fonctionnalités

Visiteur

Page d'accueil : produits « À la une », prototypes à tester, nouveautés
Catalogue avec recherche par mot-clé, filtre par catégorie et tri (prix, nom, date)
Fiche produit avec ses options
Panier (ajout, modification des quantités, suppression, vidage)
Inscription et connexion
Candidature « Devenir testeur » pour essayer des prototypes

Client connecté

Tunnel de commande en 4 étapes (panier, livraison, paiement, confirmation)
E-mail de confirmation de commande et e-mail à chaque changement de statut
Historique « Mes commandes » et facture PDF
Gestion du profil, de l'adresse et du mot de passe
Demande pour devenir vendeur

Vendeur

Espace vendeur : création, modification et suppression de ses propres produits
Tout produit créé ou modifié passe « en attente de validation »

Testeur

Candidature « Devenir testeur » ; l'administrateur la valide en attribuant le rôle Testeur
Les produits de la catégorie « Prototypes » ne peuvent être commandés que par un testeur validé (ou un administrateur)

Administrateur (EasyAdmin)

Gestion des catégories, produits, utilisateurs, commandes et candidatures testeurs
Modération des produits des vendeurs (publier ou refuser)
Attribution des rôles vendeur et testeur
Technologies
Domaine	Choix
Framework	Symfony 7.4 (PHP 8.2 ou supérieur)
Base de données	MariaDB / MySQL, Doctrine ORM et migrations
Administration	EasyAdmin 5
Interface	Twig, Bootstrap 5.3, Turbo, AssetMapper
E-mails	Symfony Mailer (Mailpit en local, SMTP Alwaysdata en production)
Tests	PHPUnit, DAMA Doctrine Test Bundle
Qualité du code	PHP-CS-Fixer (règles Symfony)
Hébergement	Alwaysdata
Installation en local
Prérequis
PHP 8.2 ou supérieur, avec les extensions habituelles de Symfony (intl, pdo_mysql, mbstring, xml, ctype, iconv)
Composer
Symfony CLI
MariaDB ou MySQL (par exemple via XAMPP)
Étapes

1. Récupérer le projet

bash
git clone https://github.com/nicolasteixeira16-cell/innovshop.git
cd innovshop

2. Installer les dépendances

bash
composer install

3. Configurer la base de données

Le fichier .env contient une connexion par défaut (utilisateur root sans mot de passe, base innovshop, pour XAMPP). Si votre configuration est différente, créez un fichier .env.local (ignoré par Git) et indiquez votre propre connexion :

dotenv
DATABASE_URL="mysql://UTILISATEUR:MOT_DE_PASSE@127.0.0.1:3306/innovshop?serverVersion=mariadb-10.4.28&charset=utf8mb4"

4. Créer la base et les tables

bash
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate

5. Charger les données de démonstration (catégories, produits avec images, comptes de test)

bash
symfony console doctrine:fixtures:load

Cette commande vide la base avant de la remplir. Elle n'est disponible qu'en développement.

6. Lancer le serveur

bash
symfony server:start

Le site est alors disponible à l'adresse indiquée par la commande (en général https://127.0.0.1:8000).

Créer un administrateur

Les fixtures créent déjà un compte administrateur de démonstration. Pour créer un administrateur avec vos propres identifiants :

bash
symfony console app:creer-admin

La commande demande l'adresse e-mail, le prénom, le nom et le mot de passe (12 caractères minimum, non affiché).

E-mails en local (facultatif)

Par défaut, l'envoi d'e-mails est désactivé (MAILER_DSN=null://null). Pour voir les e-mails de confirmation de commande, lancez Mailpit et ajoutez dans .env.local :

dotenv
MAILER_DSN=smtp://127.0.0.1:1025

Les e-mails sont alors consultables sur http://127.0.0.1:8025.

Comptes de démonstration

Les fixtures créent quatre comptes. Ils partagent le mot de passe de démonstration Demo-InnovShop-2026! :

Rôle	Adresse e-mail
Administrateur	admin@innovshop.local
Client	client@innovshop.local
Vendeur	vendeur@innovshop.local
Testeur	testeur@innovshop.local

Ces comptes n'existent qu'en local. Le compte administrateur du site en ligne n'est volontairement pas publié ici.

Scénario de démonstration rapide :

En client : ajouter un produit au panier, passer commande, consulter « Mes commandes ».
En vendeur : ouvrir l'espace vendeur, constater le produit « Drone de poche Pocket Fly » en attente de validation.
En administrateur : ouvrir l'administration, publier ce produit, puis vérifier qu'il apparaît dans le catalogue.
Prototypes : avec le compte client, constater que le produit de la catégorie « Prototypes » propose « Devenir testeur » au lieu d'« Ajouter » ; avec le compte testeur, l'ajouter au panier et commander.
Lancer les tests

Les tests utilisent une base de données dédiée (environnement test), à créer une fois :

bash
symfony console doctrine:database:create --env=test
symfony console doctrine:migrations:migrate --env=test --no-interaction

Puis :

bash
symfony php bin/phpunit

Les tests couvrent notamment le catalogue, le panier, la commande, les droits d'accès, la protection CSRF et la marketplace. Chaque test s'exécute dans une transaction annulée à la fin (DAMA), la base de test reste donc propre.

Vérification du style de code :

bash
symfony php vendor/bin/php-cs-fixer fix --dry-run --diff
Sécurité
Authentification par formulaire, mots de passe hachés (algorithme automatique de Symfony)
Rôles ROLE_USER, ROLE_VENDEUR et ROLE_ADMIN avec hiérarchie, et règles d'accès (access_control) par zone du site
Autorisations fines avec des Voters : un client ne voit que ses commandes, un vendeur ne modifie que ses produits
Prototypes réservés aux testeurs validés : règle centralisée dans un Voter (PRODUIT_COMMANDER), appliquée à l'affichage, à l'ajout au panier et à la validation de la commande
Jeton CSRF sur tous les formulaires et actions sensibles (panier, commande, connexion)
Validation des saisies côté serveur, et rejet des caractères < et > dans les champs de texte libre
Descriptions de produits nettoyées avec sanitize_html
Secrets uniquement dans .env.local (jamais dans le dépôt)
Déploiement sur Alwaysdata

Le site est hébergé à l'adresse https://ntx.alwaysdata.net/innovshop/. La configuration de production (APP_ENV=prod, accès à la base, SMTP) se trouve uniquement dans un fichier .env.local présent sur le serveur.

Mise à jour du site après un git push (connexion en SSH, puis dans le dossier du site) :

bash
cd ~/www/innovshop
git pull
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console asset-map:compile
php bin/console cache:clear

Performance : les fichiers statiques sont servis avec un cache navigateur d'un an et compressés en gzip (configuration dans public/.htaccess).

Structure du projet
assets/            styles (theme.css), images (logo, bandeau), JavaScript
config/            configuration Symfony (sécurité, services, packages)
docs/uml/          diagrammes de conception (voir ci-dessous)
fixtures/images/   images utilisées par les données de démonstration
migrations/        migrations Doctrine
public/            point d'entrée du site et fichiers publics
src/
  Command/         commande app:creer-admin
  Controller/      contrôleurs (site, compte, commande, espace vendeur, admin)
  DataFixtures/    données de démonstration
  Entity/          entités Doctrine
  Form/            formulaires
  Repository/      requêtes Doctrine
  Security/Voter/  règles d'autorisation
  Service/         panier, commande, e-mails
templates/         vues Twig
tests/             tests PHPUnit
Documentation et diagrammes

Les diagrammes de conception sont dans docs/uml/ :

Diagramme	Fichier
Diagramme de classes	docs/uml/01-classes.png
Cas d'utilisation	docs/uml/02-cas-utilisation.png
Séquence : passer une commande	docs/uml/03-sequence-commande.png
Séquence : marketplace (soumission et validation d'un produit)	docs/uml/04-sequence-marketplace.png
Modèle physique de données	docs/uml/05-mpd.png

Diagramme de classes

Modèle physique de données

## Documents du projet

### Backlog (user stories)
- [Backlog complet : 31 user stories](docs/backlog/BACKLOG.md)
- [Tableau Kanban public (Trello)](https://trello.com/b/840nCSqj/innovshop-backlog)

![Kanban Trello](docs/backlog/kanban-trello.png)

### Diagramme de classes
- [Voir le diagramme (image)](docs/uml/01-classes.png)
- [Fichier source .drawio](docs/uml/InnovShop_class_diagram.drawio)

![Diagramme de classes](docs/uml/01-classes.png)

### Autres documents
Dossier de projet, présentation de soutenance et livret : [dépôt des documents](https://github.com/nicolasteixeira16-cell/Dossier-Professionnel-DWWM)
