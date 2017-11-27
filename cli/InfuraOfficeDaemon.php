<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 11:49
 */

require_once __DIR__ . '/../autoload.php';

date_default_timezone_set("Asia/Shanghai");

$address = \sinri\InfuraOffice\toolkit\InfuraOfficeToolkit::readConfig(['daemon', 'address'], '127.0.0.1');
$port = \sinri\InfuraOffice\toolkit\InfuraOfficeToolkit::readConfig(['daemon', 'port'], '12345');

$socketAgent = new \sinri\InfuraOffice\cli\daemon\SocketAgent();
$socketAgent->configSocketAsTcpIp($address, $port);

$single = new \sinri\InfuraOffice\cli\daemon\Daemon($socketAgent);

$single->listen();