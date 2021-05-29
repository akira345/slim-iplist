<?php

declare(strict_types=1);

namespace App\Domain\IpInfo;

interface IpInfoRepository
{
  /**
   * @return array()
   */
  public function findIpInformation(string $in_ip): array;

  /**
   * @return array()
   */
  public function findJpSubnets(): array;
}
