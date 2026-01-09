<img src="assets/images/logo.png" alt="CritiPixel" width="200" />

# CritiPixel

## Pré-requis

-   PHP >= 8.2
-   Composer
-   Extension PHP Xdebug
-   Symfony (binaire)

## Installation

### Composer

Dans un premier temps, installer les dépendances :

```bash
composer install
```

### Docker (optionnel)

Si vous souhaitez utiliser Docker Compose, il vous suffit de lancer la commande suivante :

```bash
docker compose up -d
```

## Configuration

### Base de données

Actuellement, le fichier `.env` est configuré pour la base de données PostgreSQL mise en place dans `docker-compose.yml`.
Cependant, vous pouvez créer un fichier `.env.local` si nécessaire pour configurer l'accès à la base de données.
Exemple :

```dotenv
DATABASE_URL=mysql://root:Password123!@host:3306/criti-pixel
```

### PHP (optionnel)

Vous pouvez surcharger la configuration PHP en créant un fichier `php.local.ini`.

De même pour la version de PHP que vous pouvez spécifier dans un fichier `.php-version`.

## Usage

### Base de données

#### Supprimer la base de données

```bash
symfony console doctrine:database:drop --force --if-exists
```

#### Créer la base de données

```bash
symfony console doctrine:database:create
```

#### Exécuter les migrations

```bash
symfony console doctrine:migrations:migrate -n
```

#### Charger les fixtures

```bash
symfony console doctrine:fixtures:load -n --purge-with-truncate
```

_Note : Vous pouvez exécuter ces commandes avec l'option `--env=test` pour les exécuter dans l'environnement de test._

### SASS

#### Compiler les fichiers SASS

```bash
symfony console sass:build
```

_Note : le fichier `.symfony.local.yaml` est configuré pour surveiller les fichiers SASS et les compiler automatiquement quand vous lancez le serveur web de Symfony._

### Tests

```bash
symfony php bin/phpunit
```

_Note : Penser à charger les fixtures avant chaque éxécution des tests._

### Rapport de couverture de code

#### Générer le rapport de couverture

```bash
vendor/bin/phpunit --coverage-html public/test-coverage
```

#### Ouvrir le rapport dans le navigateur

```bash
start public/test-coverage/index.html
```

_Note : Le rapport doit être régénéré après chaque modification du code ou des tests._

### Serveur web

```bash
symfony serve
```
