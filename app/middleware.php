<?php
declare(strict_types=1);

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\App;

return function (App $app) {
     //バグで動かない
    //   $app->add(TwigMiddleware::create($app));
};
