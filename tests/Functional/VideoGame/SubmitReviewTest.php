<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Review;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class SubmitReviewTest extends FunctionalTestCase
{
    /**
     * Scenario: un utilisateur ajoute une note avec des données valides
     */
    public function testCanSubmitValidReviewWhenAuthenticated(): void
    {
        $this->login();
        $this->get('/jeu-video-0');
        self::assertResponseIsSuccessful();

        // Soumission du formulaire avec des données valides
        $this->submitForm('Poster', [
            'review[rating]' => 4,
            'review[comment]' => 'Test569835: Super jeu !',
        ]);

        // Vérification de l'ajout de la review dans la base de données
        $review = $this->getEntityManager()
            ->getRepository(Review::class)
            ->findOneBy(['comment' => 'Test569835: Super jeu !']);
        self::assertNotNull($review, 'La review doit être présente en base de données après soumission du formulaire.');
        self::assertSame(4, $review->getRating(), 'La note de la review doit être correcte.');
        self::assertSame('Test569835: Super jeu !', $review->getComment(), 'Le commentaire de la review doit être correct.');

        // Vérification de la redirection après la soumission du formulaire(code status 302)
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Suivi de la redirection
        $this->client->followRedirect();

        // Vérification de l'ajout de la review sur la page
        self::assertAnySelectorTextContains('div.list-group-item:last-child h3', 'user+0', 'La page doit afficher le pseudo de l\'utilisateur ayant soumis la review.');
        self::assertAnySelectorTextContains('div.list-group-item:last-child p', 'Test569835: Super jeu !', 'La page doit afficher le commentaire de la review.');
        self::assertAnySelectorTextContains('div.list-group-item:last-child span.value', '4', 'La page doit afficher la note de la review.');

        // Vérification de l'absence du formulaire pour un utilisateur authentifié ayant déjà soumis une review
        self::assertSelectorNotExists('form[name="review"]', 'Le formulaire de review ne doit pas être affiché pour un utilisateur ayant déjà soumis une review.');
    }

    /**
     * Scenario: un utilisateur ajoute une note avec des données invalides
     * @param array<string, mixed> $formData
     */
    #[DataProvider('provideReviewsData')]
    public function testCannotSubmitInvalidReviewWhenAuthenticated(array $formData): void
    {
        $this->login();

        $reviewBefore = $this->getEntityManager()
            ->getRepository(Review::class)
            ->count([]);

        $this->get('/jeu-video-0');
        self::assertResponseIsSuccessful();

        // Soumission du formulaire avec des données invalides
        $this->submitForm('Poster', $formData);

        // Vérification après soumission de mauvaise données (code status 422)
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, 'La réponse doit avoir le code status 422 pour des données de formulaire invalides.');

        // Vérification de l'absence d'ajout de la review dans la base de données
        $reviewAfter = $this->getEntityManager()
            ->getRepository(Review::class)
            ->count([]);
        self::assertSame($reviewBefore, $reviewAfter);
    }

    /** 
     * Fournit des scénarios de tests avec différentes combinaisons de Reviews
     * @return iterable<array{array<string, mixed>}>
     */
    public static function provideReviewsData(): iterable
    {
        // Scénario 1: Note manquante
        yield 'Missing rating' => [
            [
                'review[comment]' => 'Commentaire sans note',
            ]
        ];

        // Scénario 2: Commentaire trop long
        yield 'Comment too long' => [
            [
                'review[rating]' => 4,
                'review[comment]' => str_repeat('a', 1001),
            ]
        ];
    }

    /**
     * Scenario: un utilisateur non authentifié tente d'envoyer une note via un POST
     */
    public function testCannotSubmitReviewByPostWhenNotAuthenticated(): void
    {
        $reviewBefore = $this->getEntityManager()
            ->getRepository(Review::class)
            ->count([]);

        // Tentative de soumission en POST du formulaire de review
        $this->client->request('POST', '/jeu-video-0', [
            'review' => [
                'rating' => 4,
                'comment' => 'Super jeu !',
            ],
        ]);

        // Vérification de la redirection vers la page de login
        self::assertResponseRedirects('/auth/login');

        // Vérification de l'absence d'ajout de la review dans la base de données
        $reviewAfter = $this->getEntityManager()
            ->getRepository(Review::class)
            ->count([]);
        self::assertSame($reviewBefore, $reviewAfter, 'Aucune review ne doit être ajoutée en base de données par un utilisateur non authentifié.');
    }

    /**
     * Scenario: un utilisateur non authentifié ne doit pas voir le formulaire d'ajout de note
     */
    public function testDontShowReviewFormWhenNotAuthenticated(): void
    {
        $this->get('/jeu-video-0');
        self::assertResponseIsSuccessful();

        // Vérification de l'abscence du formulaire pour un utilisateur non authentifié
        self::assertSelectorNotExists('form[name="review"]', 'Le formulaire de review ne doit pas être affiché pour un utilisateur non authentifié.');
    }
}
