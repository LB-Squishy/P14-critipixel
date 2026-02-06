<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Doctrine\Repository\VideoGameRepository;
use App\List\VideoGameList\Filter;
use App\List\VideoGameList\Pagination;
use App\Model\ValueObject\Direction;
use App\Model\ValueObject\Sorting;
use App\Tests\Functional\FunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SortingTest extends FunctionalTestCase
{
    /**
     * Scenario: affiche un nombre de jeux vidéo par page selon la sélection avec différents tris.
     */
    #[DataProvider('provideSortingAndPaginationData')]
    public function testShouldListVideoGamesPerPage(int $limit, Sorting $sorting, Direction $direction): void
    {
        // Prépare les éléments nécessaires pour la récupération des jeux vidéo en base de données en fonction des filtres sélectionnés
        $repository = $this->service(VideoGameRepository::class);
        $pagination = new Pagination(1, $limit, $sorting, $direction);
        $filter = new Filter(null, []);

        // Récupère les jeux vidéo attendus en fonction des critères de filtrage
        $paginator = $repository->getVideoGames($pagination, $filter);
        $expectedGames = iterator_to_array($paginator);
        $expectedgameCardCount = count($expectedGames);

        // prépare les données du formulaire de tri
        $formData = [
            'limit' => $limit,
            'sorting' => $sorting->name,
            'direction' => $direction->name,
        ];

        // Accède à la page d'accueil
        $crawler = $this->get('/');
        self::assertResponseIsSuccessful();

        // Remplit et soumet le formulaire de sélection du nombre de jeux vidéo par page
        $crawler = $this->client->submitForm('Trier', $formData, 'GET');
        self::assertResponseIsSuccessful();

        // Vérifie que le nombre attendu de jeux vidéo s'affiche
        self::assertSelectorCount($expectedgameCardCount, 'article.game-card', "La page d'accueil doit afficher $expectedgameCardCount jeux vidéo par page.");

        // Vérifie que les jeux vidéo s'affichent dans l'ordre attendu
        foreach ($expectedGames as $index => $expectedGame) {
            $expectedGameTitle = $expectedGame->getTitle();
            self::assertAnySelectorTextContains('article.game-card:nth-child('.($index + 1).') h5.game-card-title a', $expectedGameTitle, "Le jeu vidéo '$expectedGameTitle' doit s'afficher à la position ".($index + 1).' pour le tri sélectionné.');
        }
    }

    /**
     * Fournit des scénarios de tests avec différentes combinaisons de Tri et pagination.
     *
     * @return iterable<array{int, Sorting, Direction}>
     */
    public static function provideSortingAndPaginationData(): iterable
    {
        // Scénario 1: 10 jeux vidéo par page
        yield '10 jeux vidéo par page' => [
            10,
            Sorting::ReleaseDate,
            Direction::Descending,
        ];

        // Scénario 2: 25 jeux vidéo par page
        yield '25 jeux vidéo par page' => [
            25,
            Sorting::ReleaseDate,
            Direction::Descending,
        ];

        // Scénario 3: 50 jeux vidéo par page
        yield '50 jeux vidéo par page' => [
            50,
            Sorting::ReleaseDate,
            Direction::Descending,
        ];

        // Scénario 4: Tri croissant - 25 jeux vidéo par page
        yield 'Tri croissant - 25 jeux vidéo par page' => [
            25,
            Sorting::ReleaseDate,
            Direction::Ascending,
        ];

        // Scénario 5: Par Titre - Tri décroissant - 25 jeux vidéo par page
        yield 'Par Titre - Tri décroissant - 25 jeux vidéo par page' => [
            25,
            Sorting::Title,
            Direction::Descending,
        ];

        // Scénario 6: Par Note CritiPixel - Tri décroissant - 25 jeux vidéo par page
        yield 'Par Note CritiPixel - Tri décroissant - 25 jeux vidéo par page' => [
            25,
            Sorting::Rating,
            Direction::Descending,
        ];

        // Scénario 7: Par Note Moyenne - Tri décroissant - 25 jeux vidéo par page
        yield 'Par Note Moyenne - Tri décroissant - 25 jeux vidéo par page' => [
            25,
            Sorting::AverageRating,
            Direction::Descending,
        ];
    }
}
