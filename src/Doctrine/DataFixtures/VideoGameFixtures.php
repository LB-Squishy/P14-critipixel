<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Récupération des utilisateurs et des tags existants
        $users = $manager->getRepository(User::class)->findAll();
        $tags = $manager->getRepository(Tag::class)->findAll();

        // Création des jeux vidéo
        $videoGames = array_fill_callback(
            0,
            50,
            fn(int $index): VideoGame => (new VideoGame)
                ->setTitle(sprintf('Jeu vidéo %d', $index))
                ->setDescription($this->faker->paragraphs(10, true))
                ->setReleaseDate((new DateTimeImmutable())->sub(new DateInterval(sprintf('P%dD', $index))))
                ->setTest($this->faker->paragraphs(6, true))
                ->setRating(($index % 5) + 1)
                ->setImageName(sprintf('video_game_%d.png', $index))
                ->setImageSize(2_098_872)
        );

        // TODO : Ajouter les tags aux vidéos
        foreach ($videoGames as $videoGame) {
            $randomTags = $this->faker->randomElements($tags, rand(1, 3));
            /** @var VideoGame $videoGame */
            foreach ($randomTags as $tag) {
                $videoGame->getTags()->add($tag);
            }
        }

        array_walk($videoGames, [$manager, 'persist']);
        $manager->flush();

        // TODO : Ajouter des reviews aux vidéos
        /** @var VideoGame $videoGame */
        foreach ($videoGames as $videoGame) {
            $reviewers = $this->faker->randomElements($users, rand(2, 5));
            foreach ($reviewers as $user) {
                $review = (new Review())
                    ->setVideoGame($videoGame)
                    ->setUser($user)
                    ->setRating($this->faker->numberBetween(1, 5))
                    ->setComment($this->faker->sentence());
                $videoGame->getReviews()->add($review);
                $manager->persist($review);
            }
            // Recalcul des notes après l'ajout des reviews
            $this->calculateAverageRating->calculateAverage($videoGame);
            $this->countRatingsPerValue->countRatingsPerValue($videoGame);
            $manager->persist($videoGame);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, TagFixtures::class];
    }
}
