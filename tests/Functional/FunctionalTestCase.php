<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Model\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    /**
     * Sets up les tests fonctionnels en initialisant le client HTTP.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    /**
     * Tears down les tests fonctionnels afin de restaurer le gestionnaire d'exceptions et de libérer la mémoire.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
        unset($this->client);
    }

    /**
     * Retourne le gestionnaire d'entités Doctrine.
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->service(EntityManagerInterface::class);
    }

    /**
     * Récupère un service du conteneur d'injection de dépendances.
     *
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    protected function service(string $id): object
    {
        return $this->client->getContainer()->get($id);
    }

    /**
     * Effectue une requête GET pour les tests fonctionnels.
     */
    protected function get(string $uri, array $parameters = []): Crawler
    {
        return $this->client->request('GET', $uri, $parameters);
    }

    /**
     * Connecte un utilisateur pour les tests fonctionnels.
     */
    protected function login(string $email = 'user+0@email.com'): void
    {
        $user = $this->service(EntityManagerInterface::class)->getRepository(User::class)->findOneByEmail($email);

        $this->client->loginUser($user);
    }

    /**
     * Soumet un formulaire pour les tests fonctionnels.
     *
     * @param array<string, mixed> $formData
     */
    protected function submitForm(string $button, array $formData = [], string $method = 'POST'): Crawler
    {
        return $this->client->submitForm($button, $formData, $method);
    }
}
