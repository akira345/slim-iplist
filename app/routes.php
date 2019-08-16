<?php
declare(strict_types=1);

use App\Application\Actions\IpInfo\ShowIpInfo;
use App\Application\Actions\IpInfo\ShowIpListJp;
use Slim\App;


return function (App $app) {
    $base = $_SERVER["BASE"];
    $app->get($base . '/', ShowIpInfo::class);
    $app->get($base . '/index.php', ShowIpInfo::class);
    $app->get($base . '/json', ShowIpListJp::class);
};
