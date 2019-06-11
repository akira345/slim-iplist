<?php
require '../vendor/autoload.php';
require '../config/db.php';
require 'function.php';
use \Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

// Prepare app
$app = new App();

$container = $app->getContainer();

// Create monolog logger and store logger in container as singleton
// (Singleton resources retrieve the same log resource definition each time)
$container['logger'] = function($c) {
    $log = new \Monolog\Logger('slim-skeleton');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
};

// Prepare view
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../templates', [
            'charset' => 'utf-8',
            'cache' => realpath('../templates/cache'),
            'auto_reload' => true,
            'strict_variables' => false,
            'autoescape' => true
    ]);
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new Slim\Views\TwigExtension($router,$uri));

    return $view;
};
// Define routes
$app->get('/', function (Request $req, Response $res,$args = []){
    $render = array();
    // Get get
    $in_hostname = parse_url($in_ip, PHP_URL_HOST);
    if ($in_hostname){
        $in_ip = gethostbyname($in_hostname);
    }else{
        $in_ip = gethostbyname($in_ip);
    }
    if (filter_var($in_ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){
        //ipv4
       $sql = "SELECT lst.*,(select c.country_name from country c "
            . "where lst.country = c.country_cd) country_name "
            . "FROM iplist lst "
            . "WHERE "
            . "inet_aton(:ip) between inet_aton(lst.ip) and (inet_aton(lst.ip) + (lst.kosu -1))";
        try{
          $db = getDB();
          $stmt = $db->prepare($sql);
          $stmt -> bindParam(':ip',$in_ip,PDO::PARAM_STR);
          $stmt -> execute();
          $data_flg = "NG";
          $render = array(
                     "in_ip"   => $in_ip,
                     "data_flg" => $data_flg,
                     "hostname" => gethostbyaddr($in_ip),
                     );
          while($row = $stmt -> fetch()){
             $wariate = $row["wariate"];
             $country = $row["country"];
             $ip      = $row["ip"];
             $kosu    = $row["kosu"];
             $wariate_year = $row["wariate_year"];
             $jyokyo  = $row["jyokyo"];
             $netblock = $row["netblock"];
             $netblock = fix_netblock($netblock);
             $country_name = $row["country_name"];
             $block = IPBlock::create($netblock);
             if ($block->contains($in_ip)){
                 $data_flg = "OK";
                 $whois_data = shell_exec("whois " . escapeshellcmd($in_ip));
                 if ($wariate_year !=0){
                     $wariate_year = date("Y/m/d",strtotime($wariate_year));
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
        } catch(PDOException $e) {
            $this->logger->error($e -> getMessage());
            echo $e -> getMessage();
        }
    }
    // Sample log message
    $this->logger->info("Top Area '/' route");
    // Render index view
    $this->view->render($res,'index.html',$render);
});
$app->get('/json', function (Request $req, Response $res,$args = []) {
       $render = array();
        //ipv4
       $sql = "SELECT lst.*,(select c.country_name from country c "
            . "where lst.country = c.country_cd) country_name "
            . "FROM iplist lst "
            . "WHERE lst.country = 'JP'";
        try{
          $db = getDB();
          $stmt = $db->prepare($sql);
          $stmt -> execute();
          while($row = $stmt -> fetch()){
             $wariate = $row["wariate"];
             $country = $row["country"];
             $ip      = $row["ip"];
             $kosu    = $row["kosu"];
             $wariate_year = $row["wariate_year"];
             $jyokyo  = $row["jyokyo"];
             $netblock = $row["netblock"];
             $netblock = fix_netblock($netblock);
             $country_name = $row["country_name"];
             $block = IPBlock::create($netblock);
             //
             array_push($render,$netblock);
          }
        } catch(PDOException $e) {
            $this->logger->error($e -> getMessage());
            echo $e -> getMessage();
        }
        // Sample log message
        $this->logger->info("JSON Area '/' route");
        // Render index view
        //header('Content-Type: application/json; charset=utf-8');
        //echo json_encode(array('ip_block' => $render));
        $res = $res->withJson(array('ip_block' => $render));
        return $res;
});

// Run app
$app->run();
