<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use function array_fill_callback;

final class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tagsData = [
            'Action',
            'Aventure',
            'FPS',
            'RPG',
            'Simulation',
            'Stratégie',
            'Sport',
            'Puzzle',
            'Horreur',
            'Indépendant',
            'Multijoueur',
        ];

        $tags = array_fill_callback(
            0,
            count($tagsData),
            fn(int $index): Tag => (new Tag)
                ->setName($tagsData[$index])
        );

        array_walk($tags, [$manager, 'persist']);

        $manager->flush();
    }
}
