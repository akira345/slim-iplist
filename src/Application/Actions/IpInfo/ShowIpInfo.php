<?php

declare(strict_types=1);

namespace App\Application\Actions\IpInfo;

use App\Application\Actions\IpInfo\IpInfoAction;
use Psr\Http\Message\ResponseInterface as Response;

class ShowIpInfo extends IpInfoAction
{
  /**
   * {@inheritdoc}
   */
  protected function action(): Response
  {
    $render = array();
    // Get get
    $query = $this->resolveQuery('in_ip');
    $this->logger->info("Search Query is " . $query);
    if (!is_null($query)) {
      $in_ip = trim($query);
      //IPアドレスの箇所にURLが貼られた場合、ホスト名からIPアドレスを割り出す
      $in_hostname = parse_url($in_ip, PHP_URL_HOST);
      if ($in_hostname) {
        $in_ip = gethostbyname($in_hostname);
      } else {
        $in_ip = gethostbyname($in_ip);
      }
      $render = $this->ipInfoRepository->findIpInformation($in_ip);
    }
    // Sample log message
    $this->logger->info("Action complate.");
    // Render index view
    return $this->view->render($this->response, 'index.html', $render);
  }
}
