<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

/*
 * Call to function method_exists() with 'Symfony\\Component\\Dotenv\\Dotenv' and 'bootEnv' will always evaluate to true.
 * Pas besoin de vérifier si la méthode bootEnv existe.
 */
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
