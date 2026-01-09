<?php

declare(strict_types=1);

namespace App\Tests\Unit\Rating;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Model\Entity\VideoGame;
use App\Model\Entity\Review;
use App\Rating\RatingHandler;

final class CalculateAverageRatingTest extends TestCase
{
    /**
     * Teste le calcul de la note moyenne pour différentes combinaisons de Reviews
     */
    #[DataProvider('provideReviewsData')]
    public function testCalculateAverageRating(VideoGame $videoGame, ?int $expectedAverageRating): void
    {
        $ratingHandler = new RatingHandler();
        $ratingHandler->calculateAverage($videoGame);

        $this->assertSame($expectedAverageRating, $videoGame->getAverageRating());
    }

    /** 
     * Fournit des scénarios de tests avec différentes combinaisons de Reviews
     * @return iterable<string, array{0: VideoGame, 1: ?int}>
     */
    public static function provideReviewsData(): iterable
    {
        // Scénario 1: Pas de reviews
        yield 'No reviews' => [
            self::createVideoGame(),
            null,
        ];

        // Scénario 2: Une seule review
        yield 'One review' => [
            self::createVideoGame(5),
            5,
        ];

        // Scénario 3: Plusieurs reviews avec des notes différentes
        yield 'Multiple Reviews' => [
            self::createVideoGame(1, 2, 3, 4, 5),
            3,
        ];

        // Scénario 4: 3 reviews avec arrondi vers le haut
        yield 'Rounding up' => [
            self::createVideoGame(4, 5, 5),
            5,
        ];
    }

    /**
     * Crée une instance de VideoGame avec des Reviews ayant les notes spécifiées
     * @param int ...$ratings Les notes des reviews à ajouter
     * @return VideoGame
     */
    private static function createVideoGame(int ...$ratings): VideoGame
    {
        $videoGame = new VideoGame();

        foreach ($ratings as $rating) {
            $videoGame->getReviews()->add((new Review())->setRating($rating));
        }

        return $videoGame;
    }
}
