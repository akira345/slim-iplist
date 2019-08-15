<?php
declare(strict_types=1);

use App\Application\Actions\IpInfo\ShowIpInfo;
use App\Application\Actions\IpInfo\ShowIpListJp;
use Slim\App;


return function (App $app) {
    $app->get('/', ShowIpInfo::class);
    $app->get('/index.php', ShowIpInfo::class);
    $app->get('/json', ShowIpListJp::class);
};
