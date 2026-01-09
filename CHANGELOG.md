# Historique d'installation et configuration du projet

## Environnement technique

-   PHP 8.3.14
-   Composer
-   Extension PHP Xdebug 3.5.0 (pour la couverture de code)
-   Symfony CLI
-   MySQL 9.1.0
-   WAMP64

## Historique des actions effectuées

### 1. Récupération du projet et installation des dépendances

Clonage du projet et installation des dépendances :

```bash
git clone <url-du-repo>
cd P14-critipixel
composer install
```

### 2. Configuration de la base de données

#### Environnement de développement

Création d'un fichier `.env.local` avec la configuration d'accès à la base de données :

```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/criti-pixel?serverVersion=9.1.0&charset=utf8mb4"
```

#### Environnement de test

Création d'un fichier `.env.test` avec la configuration suivante :

```dotenv
KERNEL_CLASS='App\Kernel'
APP_SECRET='$ecretf0rt3st'
SYMFONY_DEPRECATIONS_HELPER=999999
PANTHER_APP_ENV=panther
PANTHER_ERROR_SCREENSHOT_DIR=./var/error-screenshots

###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://root:@127.0.0.1:3306/criti-pixel?serverVersion=9.1.0&charset=utf8mb4"
###< doctrine/doctrine-bundle ###
```

### 3. Création des bases de données

#### Base de données de développement

Création de la base de données de développement et chargement des fixtures :

```bash
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate -n
symfony console doctrine:fixtures:load -n --purge-with-truncate
```

#### Base de données de test

Même processus pour l'environnement de test :

```bash
symfony console doctrine:database:create --env=test
symfony console doctrine:migrations:migrate -n --env=test
symfony console doctrine:fixtures:load -n --purge-with-truncate --env=test
```

### 4. Mise à jour des dépendances Composer

Le projet contenait des dépendances obsolètes. Mise à jour effectuée :

```bash
composer update
```

**Note :** Cette mise à jour majeure a inclus Symfony, Doctrine, PHPUnit et autres packages.

### 5. Création des fixtures

#### TagFixtures

Création du fichier `src/Doctrine/DataFixtures/TagFixtures.php` pour générer des tags (Action, Aventure, FPS, RPG, Simulation, Stratégie, Sport, Puzzle, Horreur, Indépendant, Multijoueur).

#### Mise à jour de VideoGameFixtures

Modification du fichier `src/Doctrine/DataFixtures/VideoGameFixtures.php` pour :

-   Associer des tags aléatoires aux jeux vidéo
-   Générer des avis (reviews) pour chaque jeu

### 6. Création d'une migration pour MySQL

Utilisation de MySQL. Création et exécution des migrations :

```bash
symfony console make:migration
symfony console doctrine:migrations:migrate -n
symfony console doctrine:migrations:migrate -n --env=test
```

### 7. Installation de Xdebug (pour la couverture de code)

#### Windows avec WAMP

1. Téléchargement de la DLL Xdebug compatible avec PHP 8.3 depuis https://xdebug.org/download

    - Version utilisée : `php_xdebug-3.3.1-8.3-vs16-x86_64.dll`

2. Placement du fichier dans le dossier `ext` de PHP

    - Chemin : `C:\wamp64\bin\php\php8.3.14\ext\`

3. Ajout de la configuration dans le fichier `php.ini` (à `C:\wamp64\bin\php\php8.3.14\php.ini`) :

    ```ini
    zend_extension="C:\wamp64\bin\php\php8.3.14\ext\php_xdebug-3.3.1-8.3-vs16-x86_64.dll"
    xdebug.mode=coverage
    ```

4. Redémarrage du serveur web et du terminal

5. Vérification de l'installation :
    ```bash
    php -v
    ```
    Confirmation : Xdebug v3.5.0 détecté.

### 8. Installation de DAMA Doctrine Test Bundle

Installation de DAMA Doctrine Test Bundle :

```bash
composer require --dev dama/doctrine-test-bundle
```

Acceptation de l'exécution de la recette Symfony lors de l'installation.

### 9. Configuration de PHPUnit pour PHPUnit 12

Configuration du fichier `phpunit.xml.dist` :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         colors="true"
         bootstrap="tests/bootstrap.php"
>
    <php>
        <ini name="display_errors" value="1" />
        <ini name="error_reporting" value="-1" />
        <server name="APP_ENV" value="test" force="true" />
        <server name="SHELL_VERBOSITY" value="-1" />
        <server name="SYMFONY_PHPUNIT_REMOVE" value="" />
        <server name="SYMFONY_PHPUNIT_VERSION" value="9.6" />
        <env name="SYMFONY_DEPRECATIONS_HELPER" value="disabled" />
        <env name="SYMFONY_PHPUNIT_LOCALE" value="fr_FR"/>
    </php>

    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <source>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </source>

    <extensions>
        <bootstrap class="DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension"/>
    </extensions>
</phpunit>
```

**Modifications appliquées pour PHPUnit 12 :**

-   Utilisation de `<source>` au lieu de `<coverage>`
-   Suppression de la balise `<listeners>` (obsolète)
-   Conservation d'une seule ligne `<bootstrap>` pour DAMA dans `<extensions>`

### 10. Vérification de la configuration DAMA

Vérification du contenu du fichier `config/packages/dama_doctrine_test_bundle.yaml` :

```yaml
when@test:
    dama_doctrine_test:
        enable_static_connection: true
        enable_static_meta_data_cache: true
        enable_static_query_cache: true
```

### 11. Rechargement des fixtures dans l'environnement de test

Après toutes ces configurations, rechargement des fixtures de test :

```bash
symfony console doctrine:fixtures:load -n --purge-with-truncate --env=test
```

## Résumé de la configuration de l'environnement de test

### Base de données de test configurée

À l'issue de ces étapes, la base de données de test est créée et les fixtures sont chargées.

**Important :** Les fixtures n'ont besoin d'être chargées qu'une seule fois. DAMA s'occupe de restaurer l'état initial de la base de données après chaque test grâce aux transactions.

## Exécution des tests

### Lancer tous les tests

```bash
symfony php bin/phpunit
```

ou

```bash
vendor/bin/phpunit
```

### Générer le rapport de couverture de code

```bash
vendor/bin/phpunit --coverage-html public/test-coverage
```

### Ouvrir le rapport dans le navigateur

```bash
start public/test-coverage/index.html
```

**Note :** Le rapport doit être régénéré après chaque modification du code ou des tests.

## Fonctionnement de DAMA Doctrine Test Bundle

DAMA entoure chaque test dans une transaction qui est automatiquement annulée (rollback) à la fin du test :

1. Les fixtures sont chargées une seule fois dans la base de test
2. Chaque test démarre une transaction
3. Le test peut créer, modifier ou supprimer des données
4. À la fin du test, la transaction est annulée
5. La base de données revient à son état initial

Cela accélère considérablement l'exécution des tests et garantit leur isolation.

## Résolution des problèmes courants

### Erreur "ext-pgsql is missing"

Si vous n'utilisez pas PostgreSQL, vous pouvez ignorer cette exigence :

```bash
composer install --ignore-platform-req=ext-pgsql
```

Ou activez l'extension dans votre `php.ini` :

```ini
extension=pgsql
```

### Erreur "No filter is configured, code coverage will not be processed"

Vérifiez que :

1. Xdebug est installé et activé avec `xdebug.mode=coverage`
2. La balise `<source>` est présente dans `phpunit.xml.dist`

### Erreur de validation XML dans phpunit.xml.dist

PHPUnit 12 a changé la syntaxe :

-   Remplacer `<coverage>` par `<source>`
-   Supprimer `<listeners>`
-   Utiliser `<bootstrap>` au lieu de `<extension>` pour DAMA dans PHPUnit 10+
