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
use sinri\InfuraOffice\library\SessionLibrary;

class SiteAuthAgent extends MiddlewareInterface
{
    public function shouldKeepSameIpInOneSession()
    {
        return false;
    }

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

        // check token
        $session_library = new SessionLibrary();

        $sessionEntity = $session_library->loadSessionByToken($token);
        if (!$sessionEntity) return false;
        if ($sessionEntity->expiration <= time()) {
            return false;
        }
        if ($this->shouldKeepSameIpInOneSession() && $sessionEntity->ip != LibRequest::ip_address()) {
            return false;
        }

        $preparedData['current_user'] = $session_library->getUserEntity($sessionEntity->username);

        return true;
    }
}