<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Doctrine\Repository\VideoGameRepository;
use App\List\VideoGameList\Filter;
use App\List\VideoGameList\Pagination;
use App\Model\Entity\Tag;
use App\Model\ValueObject\Direction;
use App\Model\ValueObject\Sorting;
use App\Tests\Functional\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FilterTest extends FunctionalTestCase
{
    /**
     * Scenario: affiche 10 jeux vidéo par page.
     */
    public function testShouldListTenVideoGames(): void
    {
        // Accède à la page d'accueil
        $this->get('/');
        self::assertResponseIsSuccessful();

        // Vérifie que le nombre attendu de jeux vidéo s'affiche
        self::assertSelectorCount(10, 'article.game-card', 'La page d\'accueil doit afficher 10 jeux vidéo par page.');
    }

    /**
     * Scenario: filtrer les jeux vidéo avec un tag inexistant doit envoyer vers la liste complète sans filtrage.
     */
    public function testShouldResetTagIfFilterByUnexistantTag(): void
    {
        // Accède à la page d'accueil avec un tag inexistant
        $this->get('/', ['filter[tags][99]' => '100']);
        self::assertResponseIsSuccessful();

        // Vérifie que les jeux vidéo attendu à la première page sans filtrage s'affichent
        self::assertSelectorCount(10, 'article.game-card', 'La page d\'accueil doit afficher 10 jeux vidéo par page.');
        self::assertAnySelectorTextContains('h5.game-card-title a', 'Jeu vidéo 0', 'Le jeu vidéo 0 doit s\'afficher.');
    }

    /**
     * Scenario: navigation vers la page 2 avec la pagination.
     */
    public function testShouldNavigateWithPagination(): void
    {
        // Accède à la page d'accueil
        $crawler = $this->get('/');
        self::assertResponseIsSuccessful();

        // Clique sur le lien de la page 2 dans la pagination
        $link = $crawler->filter('.pagination')->selectLink('2')->link();
        $this->client->click($link);
        self::assertResponseIsSuccessful();

        // Vérifie que la page 2 est active dans la pagination
        self::assertAnySelectorTextContains('.pagination .active', '2', 'La page 2 doit être active dans la pagination.');

        // Vérifie que le nombre de jeux vidéo affichés est correct sur la page 2
        self::assertSelectorCount(10, 'article.game-card', 'La page d\'accueil doit afficher 10 jeux vidéo sur la page 2.');

        // Vérifie que la page 2 renvoit le jeu vidéo attendu sur la page 2
        self::assertAnySelectorTextContains('h5.game-card-title a', 'Jeu vidéo 11', 'Le jeu vidéo 11 doit s\'afficher sur la page 2.');
    }

    /**
     * Scenario: filtre les jeux vidéo par la barre de recherche textuelle.
     */
    public function testShouldFilterVideoGamesBySearch(): void
    {
        // Accède à la page d'accueil
        $this->get('/');
        self::assertResponseIsSuccessful();

        // Soumet le formulaire de filtrage avec un critère de recherche
        self::assertSelectorCount(10, 'article.game-card', 'La page d\'accueil doit afficher 10 jeux vidéo par page.');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();

        // Vérifie que le jeu vidéo filtré correspond au critère de recherche
        self::assertSelectorCount(1, 'article.game-card');
        self::assertAnySelectorTextContains('h5.game-card-title a', 'Jeu vidéo 49', 'Le jeu vidéo filtré doit correspondre au critère de recherche.');
    }

    /**
     * Scenario: filtre les jeux vidéo par tags.
     *
     * @param array<string, mixed> $formData
     */
    #[DataProvider('provideFilterData')]
    public function testShouldFilterVideoGamesByTag(array $formData): void
    {
        // Prépare les éléments nécessaires pour la récupération des jeux vidéo en base de données en fonction des tags sélectionnés
        $repository = $this->service(VideoGameRepository::class);
        $tagIds = array_values($formData);
        $tags = $this->getEntityManager()->getRepository(Tag::class)->findBy(['id' => $tagIds]);
        $pagination = new Pagination(1, 10, Sorting::ReleaseDate, Direction::Descending);
        $filter = new Filter(null, $tags);

        // Récupère les jeux vidéo attendus en fonction des critères de filtrage
        $paginator = $repository->getVideoGames($pagination, $filter);
        $expectedGames = iterator_to_array($paginator);
        $expectedgameCardCount = count($expectedGames);

        // Accède à la page d'accueil
        $this->get('/');
        self::assertResponseIsSuccessful();

        // Soumet le formulaire de filtrage avec les données fournies
        $this->client->submitForm('Filtrer', $formData, 'GET');
        self::assertResponseIsSuccessful();

        // Vérifie les résultats en fonction des données fournies
        if (0 === $expectedgameCardCount) {
            // Vérifie la présence d'un message indiquant qu'aucun jeu vidéo n'a été trouvé
            self::assertSelectorCount($expectedgameCardCount, 'article.game-card', 'Aucun jeu vidéo ne doit être affiché après filtrage par tout les tags.');
            self::assertAnySelectorTextContains('div.fw-bold', 'Affiche 0 jeux vidéo', 'La selection de tout les Tags doit déclencher l\'affichage d\'un message dédié à l\'abscence de jeux vidéo correspondant.');
        } else {
            // Vérifie que le nombre attendu de jeux vidéo est affiché et que le jeu vidéo attendu est présent
            self::assertSelectorCount($expectedgameCardCount, 'article.game-card', 'Le nombre de jeux vidéo affichés doit correspondre au nombre attendu après filtrage par tags.');
            self::assertAnySelectorTextContains('h5.game-card-title a', $expectedGames[0]->getTitle(), 'Le jeu vidéo en question doit correspondre au critère de recherche.');
        }
    }

    /**
     * Fournit des scénarios de tests avec différentes combinaisons de Filtrages par tags.
     *
     * @return iterable<array{array<string, mixed>}>
     */
    public static function provideFilterData(): iterable
    {
        // Scénario 1: Aucun tag sélectionné
        yield 'No tags' => [
            [],
        ];

        // Scénario 2: Un seul tag "aventure"
        yield 'One tag : aventure' => [
            ['filter[tags][1]' => '2'],
        ];

        // Scénario 3: Un seul tag "fps"
        yield 'One tag : fps' => [
            ['filter[tags][2]' => '3'],
        ];

        // Scénario 4: Plusieurs tags "action" et "simulation"
        yield 'Multiple Tags' => [
            [
                'filter[tags][0]' => '1',
                'filter[tags][1]' => '2',
            ],
        ];

        // Scénario 5: Tout les Tags pour checker message "aucun résultats"
        yield 'All Tags selected to check No results message render' => [
            [
                'filter[tags][0]' => '1',
                'filter[tags][1]' => '2',
                'filter[tags][2]' => '3',
                'filter[tags][3]' => '4',
                'filter[tags][4]' => '5',
                'filter[tags][5]' => '6',
                'filter[tags][6]' => '7',
                'filter[tags][7]' => '8',
                'filter[tags][8]' => '9',
                'filter[tags][9]' => '10',
                'filter[tags][10]' => '11',
            ],
        ];
    }
}
