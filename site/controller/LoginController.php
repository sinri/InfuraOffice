<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 20:50
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\InfuraOffice\library\SessionLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class LoginController extends BaseController
{
    protected $sessionLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);

        $this->sessionLibrary = new SessionLibrary();
    }

    /**
     * http://localhost/PHPStorm/InfuraOffice/site/api/LoginController/initializeAdminUser
     */
    public function initializeAdminUser()
    {
        if ($this->sessionLibrary->initializeAdminUser()) {
            $this->_sayOK('User [admin] confirmed existing!');
        } else {
            $this->_sayFail("Failed to initialize the user [admin]!");
        }
    }


    /**
     * http://localhost/PHPStorm/InfuraOffice/site/api/LoginController/loginWithUsernameAndPassword
     */
    public function loginWithUsernameAndPassword()
    {
        try {
            $username = LibRequest::getRequest("username", "");
            $password = LibRequest::getRequest("password", "");

            if ($username === "") {
                throw new \Exception("Username Empty!");
            }

            $user_entity = $this->sessionLibrary->getUserEntity($username);
            if (!$user_entity) {
                throw new \Exception("User does not exists!");
            }

            if (!$user_entity->validatePassword($password)) {
                throw new \Exception("Password is not correct!");
            }

            $current_ip = LibRequest::ip_address();

            $last_login_time = $user_entity->getLastLoginTime();
            $last_login_ip = $user_entity->getLastLoginIp();

            $token = uniqid(md5($username));
            $user_entity->setSessionToken($token);
            $user_entity->setSessionExpiration(time() + 3600 * 4);
            $user_entity->setLastLoginIp($current_ip);
            $user_entity->setLastLoginTime(time());

            $done = $this->sessionLibrary->storeUser($user_entity);
            if (!$done) {
                throw new \Exception("Cannot create session!");
            }

            $this->_sayOK([
                "token" => $token,
                "last_login_time" => date('Y-m-d H:i:s', $last_login_time),
                "last_login_ip" => $last_login_ip,
            ]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}