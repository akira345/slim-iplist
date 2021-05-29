<?php

declare(strict_types=1);

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\App;

return function (App $app) {
     $app->add(TwigMiddleware::createFromContainer($app, Twig::class));
};
