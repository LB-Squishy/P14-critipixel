<?php

declare(strict_types=1);

namespace App\Tests\Unit\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Model\Entity\NumberOfRatingPerValue;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CountRatingsPerValueTest extends TestCase
{
    /**
     * Teste le comptage des notes par valeur pour différentes combinaisons de Reviews
     */
    #[DataProvider('provideRatingsData')]
    public function testCountRatingsPerValue(VideoGame $videoGame, NumberOfRatingPerValue $expectedNumberOfRatingPerValue): void
    {
        $ratingHandler = new RatingHandler();
        $ratingHandler->countRatingsPerValue($videoGame);

        $this->assertEquals($expectedNumberOfRatingPerValue, $videoGame->getNumberOfRatingsPerValue());
    }

    /** 
     * Fournit des scénarios de tests avec différentes combinaisons de Reviews
     * @return iterable<array{VideoGame,NumberOfRatingPerValue}>
     */
    public static function provideRatingsData(): iterable
    {
        // Scénario 1: Pas de reviews
        yield 'No reviews' => [
            self::createVideoGame(),
            self::createNumberOfRatingPerValue(0, 0, 0, 0, 0),
        ];

        // Scénario 2: Une seule review
        yield 'One review' => [
            self::createVideoGame(3),
            self::createNumberOfRatingPerValue(0, 0, 1, 0, 0),
        ];

        // Scénario 3: Plusieurs reviews avec des notes différentes
        yield 'Multiple Reviews' => [
            self::createVideoGame(1, 2, 2, 3, 4, 4, 4, 5, 5, 5, 5),
            self::createNumberOfRatingPerValue(1, 2, 1, 3, 4),
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

    /**
     * Crée une instance de NumberOfRatingPerValue avec les compteurs spécifiés
     * @param int $one Nombre de notes 1
     * @param int $two Nombre de notes 2
     * @param int $three Nombre de notes 3
     * @param int $four Nombre de notes 4
     * @param int $five Nombre de notes 5
     * @return NumberOfRatingPerValue
     */
    private static function createNumberOfRatingPerValue(int $one, int $two, int $three, int $four, int $five): NumberOfRatingPerValue
    {
        $numberOfRatingPerValue = new NumberOfRatingPerValue();

        for ($i = 0; $i < $one; $i++) {
            $numberOfRatingPerValue->increaseOne();
        }
        for ($i = 0; $i < $two; $i++) {
            $numberOfRatingPerValue->increaseTwo();
        }
        for ($i = 0; $i < $three; $i++) {
            $numberOfRatingPerValue->increaseThree();
        }
        for ($i = 0; $i < $four; $i++) {
            $numberOfRatingPerValue->increaseFour();
        }
        for ($i = 0; $i < $five; $i++) {
            $numberOfRatingPerValue->increaseFive();
        }

        return $numberOfRatingPerValue;
    }
}
