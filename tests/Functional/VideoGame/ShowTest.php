<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ShowTest extends FunctionalTestCase
{
    /**
     * Scenario: affichage d'un jeu vidéo existant (code status 2xx)
     */
    public function testShouldShowVideoGame(): void
    {
        $this->get('/jeu-video-0');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Jeu vidéo 0', 'La page doit afficher le titre du jeu vidéo.');
    }

    /**
     * Scénario: non affichage d'un jeu vidéo inexistant (code status 404)
     */
    public function testShouldReturn404ForNonExistentVideoGame(): void
    {
        $this->get('/jeu-video-9999999');
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, 'La réponse doit avoir le code status 404 pour un jeu vidéo inexistant.');
    }
}
