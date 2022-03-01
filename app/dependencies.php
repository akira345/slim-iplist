<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Slim\Views\Twig;
use Twig\Loader\FilesystemLoader;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // monolog setting
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);
            // メモリとIPなどを記録
            $logger->pushProcessor(new MemoryUsageProcessor);
            $logger->pushProcessor(new WebProcessor);

            return $logger;
        },
        // twig setting
        Twig::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $viewSettings = $settings->get('view');
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
