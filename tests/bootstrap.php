<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Si vous utilisez LiipTestFixturesBundle, vous pourriez avoir quelque chose comme :
// Pas strictement nécessaire ici si le bundle est bien configuré et auto-chargé par Symfony.
// Mais c'est un bon endroit pour des helpers globaux de test si besoin.

// Vous pouvez aussi initialiser ici des constantes ou des variables globales pour vos tests
// define('YOUR_TEST_CONSTANT', 'some_value');
