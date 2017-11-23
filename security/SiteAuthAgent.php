<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 16:22
 */

namespace sinri\InfuraOffice\security;


use sinri\enoch\core\LibRequest;
use sinri\enoch\mvc\MiddlewareInterface;

class SiteAuthAgent extends MiddlewareInterface
{
    public function shouldAcceptRequest($path, $method, $params, &$preparedData = null)
    {
        //echo $path.PHP_EOL;//die();

        // Accept login without token to validate
        if (self::hasPrefixAmong($path, [
            '/api/LoginController/'
        ])) {
            return true;
        }

        $token = LibRequest::getCookie("infura-office-token", null);

        //TODO check token

        return false;
    }
}