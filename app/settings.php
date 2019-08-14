<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => false, // Should be set to false in production
            'logger' => [
                'name' => 'slim-skeleton',
                'path' => '../logs/app.log',
                'level' => Logger::DEBUG,
            ],
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
        ],
    ]);
};
