<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 16:09
 */

require_once __DIR__ . '/../autoload.php';

date_default_timezone_set("Asia/Shanghai");

if ($_SERVER['SERVER_NAME'] === 'localhost') {
//    print_r($_SERVER);

    header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Credentials: true");
//header("Access-Control-Expose-Headers: Foobar");

    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: X-PINGOTHER, Content-Type");
    header("Access-Control-Max-Age: 86400");

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        echo "";
        exit();
    }
}

$lamech = new \sinri\enoch\mvc\Lamech();

$lamech->getRouter()->setErrorHandler(function ($errorData) {
    header("Content-Type: application/json");
    echo json_encode(['error' => $errorData]);
});

// routes

// for root, to frontend
$lamech->getRouter()->get("", function () {
    // it should be used to redirect to FRONTEND
    header("Location: frontend");
});

$lamech->getRouter()->loadAllControllersInDirectoryAsCI(
    __DIR__ . '/controller',
    'api/',
    'sinri\InfuraOffice\site\controller\\',
    ['sinri\InfuraOffice\security\SiteAuthAgent']
);


//new \sinri\InfuraOffice\site\controller\DashboardController();

$lamech->handleRequestForWeb();