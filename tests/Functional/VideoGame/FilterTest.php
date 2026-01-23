<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use PHPUnit\Framework\Attributes\DataProvider;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class FilterTest extends FunctionalTestCase
{
    /**
     * Scenario: affiche 10 jeux vidéo par page
     */
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();

        // Vérifie que le nombre attendu d'éléments de sélecteur se trouve dans la réponse
        self::assertSelectorCount(10, 'article.game-card', 'La page d\'accueil doit afficher 10 jeux vidéo par page.');
    }

    /**
     * Scenario: navigation vers la page 2 avec la pagination
     */
    public function testShouldNavigateWithPagination(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();

        // Teste la pagination en cliquant sur la page 2
        $this->clickLinkBySelector('.pagination', '2');
        self::assertResponseIsSuccessful();
        self::assertAnySelectorTextContains('.pagination .active', '2', 'La page 2 doit être active dans la pagination.');
    }

    /**
     * Scenario: filtre les jeux vidéo par la barre de recherche textuelle
     * @param array<string, mixed> $formData
     */
    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card', 'La page d\'accueil doit afficher 10 jeux vidéo par page.');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
        self::assertAnySelectorTextContains('h5.game-card-title a', '49', 'Le jeu vidéo filtré doit correspondre au critère de recherche.');
    }

    /**
     * Scenario: filtre les jeux vidéo par tags
     * @param array<string, mixed> $formData
     * @param int $gameCardCount
     * @param int|null $gameNumber
     * @return void
     */
    #[DataProvider('provideFilterData')]
    public function testShouldFilterVideoGamesByTag(array $formData, int $gameCardCount, ?int $gameNumber): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();

        if (empty($formData)) {
            self::assertSelectorCount(10, 'article.game-card', 'La page d\'accueil doit afficher 10 jeux vidéo par page.');
        } else {
            $this->client->submitForm('Filtrer', $formData, 'GET');
            self::assertResponseIsSuccessful();
            self::assertSelectorCount($gameCardCount, 'article.game-card');
            self::assertAnySelectorTextContains('h5.game-card-title a', (string) $gameNumber, 'Le jeu vidéo en question doit correspondre au critère de recherche.');
        }
    }

    /** 
     * Fournit des scénarios de tests avec différentes combinaisons de Filtrages
     * @return iterable<array{array<string, mixed>, int, int|null}>
     */
    public static function provideFilterData(): iterable
    {
        // Scénario 1: Pas de tags
        yield 'No tags' => [
            [],
            10,
            0,
        ];

        // Scénario 2: Une seule tag "aventure"
        yield 'One tag : aventure' => [
            ['filter[tags][1]' => '2'],
            4,
            25,
        ];

        // Scénario 2: Une seule tag "fps"
        yield 'One tag : fps' => [
            ['filter[tags][2]' => '3'],
            5,
            9,
        ];

        // Scénario 3: Plusieurs tags "action" et "simulation"
        yield 'Multiple Tags' => [
            [
                'filter[tags][0]' => '1',
                'filter[tags][1]' => '2',
            ],
            2,
            12,
        ];
    }
}
