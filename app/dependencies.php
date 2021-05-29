<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Twig\Loader\FilesystemLoader;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // monolog setting
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'];
            $log = new Logger($loggerSettings['name']);
            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $log->pushHandler($handler);
            return $log;
        },
        // twig setting
        Twig::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $viewSettings = $settings['view'];
            $loader = new FilesystemLoader();
            $paths = [$viewSettings['path']];
            foreach ($paths as $namespace => $path) {
                if (is_string($namespace)) {
                    $loader->setPaths($path, $namespace);
                } else {
                    $loader->addPath($path);
                }
            }
            $view = new Twig($loader, $viewSettings['settings']);
            return $view;
        }
    ]);
};
