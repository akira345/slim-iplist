<?php

declare(strict_types=1);

namespace App\Application\Actions\IpInfo;

use App\Application\Actions\IpInfo\IpInfoAction;
use Psr\Http\Message\ResponseInterface as Response;

class ShowIpListJp extends IpInfoAction
{
  /**
   * {@inheritdoc}
   */
  protected function action(): Response
  {
    $render = array();
    $render = $this->ipInfoRepository->findJpSubnets();
    $this->logger->info("Generate IPlist for JP.");
    return $this->respondWithData(array('ip_block' => $render));
  }
}
