<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\IpInfo;

use App\Domain\IpInfo\IpInfoRepository;
use PhpIP\IPBlock;
use Psr\Log\LoggerInterface;

class DbIpInfoRepository implements IpInfoRepository
{
  protected $logger;
  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  public function findIpInformation(string $in_ip): array
  {
    $render = array();
    if (filter_var($in_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      //ipv4
      $sql = "SELECT lst.*,(select c.country_name from country c "
        . "where lst.country = c.country_cd) country_name "
        . "FROM iplist lst "
        . "WHERE "
        . "inet_aton(:ip) between inet_aton(lst.ip) and (inet_aton(lst.ip) + (lst.kosu -1))";
      try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':ip', $in_ip, \PDO::PARAM_STR);
        $stmt->execute();
        $data_flg = "NG";
        $render = array(
          "in_ip"   => $in_ip,
          "data_flg" => $data_flg,
          "hostname" => gethostbyaddr($in_ip),
        );
        while ($row = $stmt->fetch()) {
          $wariate = $row["wariate"];
          $country = $row["country"];
          $ip      = $row["ip"];
          $kosu    = $row["kosu"];
          $wariate_year = $row["wariate_year"];
          $jyokyo  = $row["jyokyo"];
          $netblock = $row["netblock"];
          $netblock = fix_netblock($netblock);
          $country_name = $row["country_name"];
          //SQLで抽出するが、念のためネットブロックに所属しているかチェック
          $block = IPBlock::create($netblock);
          if ($block->contains($in_ip)) {
            $data_flg = "OK";
            $whois_data = shell_exec("whois " . escapeshellcmd($in_ip));
            if ($wariate_year != 0) {
              $wariate_year = date("Y/m/d", strtotime(strval($wariate_year)));
            }
            $render = array(
              "wariate" => $wariate,
              "country" => $country,
              "ip"      => $ip,
              "kosu"    => $kosu,
              "wariate_year" => $wariate_year,
              "jyokyo"  => $jyokyo,
              "netblock" => $netblock,
              "country_name" => $country_name,
              "in_ip"   => $in_ip,
              "data_flg" => $data_flg,
              "whois_data" => $whois_data,
              "hostname" => gethostbyaddr($in_ip),
            );
            break;
          }
        }
      } catch (\PDOException $e) {
        $this->logger->error($e->getMessage());
        echo "システムエラー";
      }
    }
    return $render;
  }

  public function findJpSubnets(): array
  {
    $render = array();
    $sql = "SELECT lst.netblock "
      . "FROM iplist lst "
      . "WHERE lst.country = 'JP'";
    try {
      $db = getDB();
      $stmt = $db->prepare($sql);
      $stmt->execute();
      while ($row = $stmt->fetch()) {
        $netblock = $row["netblock"];
        $netblock = fix_netblock($netblock);
        //
        array_push($render, $netblock);
      }
    } catch (\PDOException $e) {
      $this->logger->error($e->getMessage());
      echo "システムエラー";
    }
    return $render;
  }
}
