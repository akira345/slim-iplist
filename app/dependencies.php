<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'];
            $log = new Logger($loggerSettings['name']);
            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $log->pushHandler($handler);
            return $log;
        },
        Twig::class => function(ContainerInterface $c){
            $settings = $c->get('settings');
            $viewSettings = $settings['view'];
            $view = new Twig($viewSettings['path'], $viewSettings['settings']);
            return $view;
        }
    ]);
};
