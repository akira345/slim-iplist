<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => false, // Should be set to false in production
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name' => 'slim-skeleton',
                    'path' => __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                // twig setting
                'view' => [
                    'path' => '../templates',
                    'settings' => [
                        'charset' => 'utf-8',
                        'cache' => realpath('../templates/cache'),
                        'auto_reload' => true,
                        'strict_variables' => false,
                        'autoescape' => true
                    ],
                ],
            ]);
        }
    ]);
};