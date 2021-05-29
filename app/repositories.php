<?php

declare(strict_types=1);

use App\Domain\IpInfo\IpInfoRepository;
use App\Infrastructure\Persistence\IpInfo\DbIpInfoRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        IpInfoRepository::class => \DI\autowire(DbIpInfoRepository::class),
    ]);
};
