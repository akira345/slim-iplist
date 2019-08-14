<?php
declare(strict_types=1);

namespace App\Application\Actions\IpInfo;

use App\Application\Actions\Action;
use App\Domain\IpInfo\IpInfoRepository;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

abstract class IpInfoAction extends Action
{
  protected $ipInfoRepository;

  public function __construct(LoggerInterface $logger,Twig $view,IpInfoRepository $ipInfoRepository)
  {
    parent::__construct($logger,$view);
    $this->ipInfoRepository = $ipInfoRepository;
  }
}
