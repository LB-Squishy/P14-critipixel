<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use PHPUnit\Framework\Attributes\DataProvider;
use App\Tests\Functional\FunctionalTestCase;

final class SortingTest extends FunctionalTestCase
{
    /**
     * Scenario: affiche un nombre de jeux vidéo par page selon la sélection avec différents tris
     * @param array<string, mixed> $formData
     * @param int $expectedCardCount
     * @param string $expectedFirstGameTitle
     */
    #[DataProvider('provideSortingAndPaginationData')]
    public function testShouldListVideoGamesPerPage(array $formData, int $expectedCardCount, string $expectedFirstGameTitle): void
    {
        // Accède à la page d'accueil
        $crawler = $this->get('/');
        self::assertResponseIsSuccessful();

        // Remplit et soumet le formulaire de sélection du nombre de jeux vidéo par page
        $crawler = $this->client->submitForm('Trier', $formData, 'GET');

        // Vérifie que le nombre attendu de jeux vidéo s'affiche
        self::assertSelectorCount($expectedCardCount, 'article.game-card', "La page d'accueil doit afficher $expectedCardCount jeux vidéo par page.");

        // Vérifie le titre du premier jeu vidéo affiché
        $firstGameTitle = $crawler->filter('h5.game-card-title a')->first()->text();
        // Vérifie que le premier jeu vidéo affiché est celui attendu
        self::assertSame($expectedFirstGameTitle, $firstGameTitle, "Le $expectedFirstGameTitle doit s'afficher en premier pour un tri croissant.");
    }

    /** 
     * Fournit des scénarios de tests avec différentes combinaisons de Tri et pagination
     * @return iterable<array{array<string, mixed>, int, string}>
     */
    public static function provideSortingAndPaginationData(): iterable
    {
        // Scénario 1: 10 jeux vidéo par page
        yield '10 jeux vidéo par page' => [
            [
                'limit' => '10',
                'sorting' => 'ReleaseDate',
                'direction' => 'Descending',
            ],
            10,
            'Jeu vidéo 0'
        ];

        // Scénario 2: 25 jeux vidéo par page
        yield '25 jeux vidéo par page' => [
            [
                'limit' => '25',
                'sorting' => 'ReleaseDate',
                'direction' => 'Descending',
            ],
            25,
            'Jeu vidéo 0'
        ];

        // Scénario 3: 50 jeux vidéo par page
        yield '50 jeux vidéo par page' => [
            [
                'limit' => '50',
                'sorting' => 'ReleaseDate',
                'direction' => 'Descending',
            ],
            50,
            'Jeu vidéo 0'
        ];

        // Scénario 4: Tri croissant - 25 jeux vidéo par page
        yield 'Tri croissant - 25 jeux vidéo par page' => [
            [
                'limit' => '25',
                'sorting' => 'ReleaseDate',
                'direction' => 'Ascending',
            ],
            25,
            'Jeu vidéo 49'
        ];

        // Scénario 5: Par Titre - Tri décroissant - 25 jeux vidéo par page
        yield 'Par Titre - Tri décroissant - 25 jeux vidéo par page' => [
            [
                'limit' => '25',
                'sorting' => 'Title',
                'direction' => 'Descending',
            ],
            25,
            'Jeu vidéo 9'
        ];

        // Scénario 6: Par Note CritiPixel - Tri décroissant - 25 jeux vidéo par page
        yield 'Par Note CritiPixel - Tri décroissant - 25 jeux vidéo par page' => [
            [
                'limit' => '25',
                'sorting' => 'Rating',
                'direction' => 'Descending',
            ],
            25,
            'Jeu vidéo 4'
        ];

        // Scénario 6: Par Note Moyenne - Tri décroissant - 25 jeux vidéo par page
        yield 'Par Note Moyenne - Tri décroissant - 25 jeux vidéo par page' => [
            [
                'limit' => '25',
                'sorting' => 'AverageRating',
                'direction' => 'Descending',
            ],
            25,
            'Jeu vidéo 2'
        ];
    }
}
