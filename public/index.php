<?php
require '../vendor/autoload.php';
require 'db.php';
require 'function.php';

// Prepare app
$app = new \Slim\Slim(array(
    'templates.path' => '../templates',
));

// Create monolog logger and store logger in container as singleton 
// (Singleton resources retrieve the same log resource definition each time)
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('slim-skeleton');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

// Define routes
$app->get('/', function () use ($app) {
    $render = array();
    // Get get
    $in_ip = $app->request->get('in_ip');
    $in_ip = gethostbyname($in_ip);
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
            $app->log->error($e -> getMessage());
            echo $e -> getMessage();
        }
    }
    // Sample log message
    $app->log->info("Top Area '/' route");
    // Render index view
    $app->render('index.html',$render);
});

// Run app
$app->run();
