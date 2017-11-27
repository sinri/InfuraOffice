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
use sinri\InfuraOffice\library\UserLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class LoginController extends BaseController
{
    protected $sessionLibrary;
    protected $userLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);

        $this->sessionLibrary = new SessionLibrary();
        $this->userLibrary = new UserLibrary();
    }

    /**
     * http://localhost/PHPStorm/InfuraOffice/site/api/LoginController/initializeAdminUser
     * @param bool $quietMode
     * @return bool
     */
    public function initializeAdminUser($quietMode = false)
    {
        if ($this->userLibrary->initializeAdminUser()) {
            if (!$quietMode) $this->_sayOK('User [admin] confirmed existing!');
            return true;
        } else {
            if (!$quietMode) $this->_sayFail("Failed to initialize the user [admin]!");
            return false;
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

            if ($username === 'admin') {
                $this->initializeAdminUser(true);
            }

            $user_entity = $this->userLibrary->readEntityByName($username);
            if (!$user_entity) {
                throw new \Exception("User does not exists!");
            }

            if (!$user_entity->validatePassword($password)) {
                throw new \Exception("Password is not correct!");
            }

            $current_ip = LibRequest::ip_address();

            $last_login_time = $user_entity->last_login_time;
            $last_login_ip = $user_entity->last_login_ip;

            $user_entity->last_login_ip = ($current_ip);
            $user_entity->last_login_time = (time());

            $token = uniqid(md5($username));
            $life = 3600 * 4;
            $done = $this->sessionLibrary->createSession($username, $token, time() + $life, $current_ip);
            if (!$done) {
                throw new \Exception("Cannot create session!");
            }
            $done = $this->userLibrary->writeEntity($user_entity);
            if (!$done) {
                $this->sessionLibrary->removeEntity($token);
                throw new \Exception("Cannot update user info with last session info!");
            }

            $this->_sayOK([
                "token" => $token,
                "username" => $user_entity->username,
                "life" => $life,
                "last_login_time" => date('Y-m-d H:i:s', $last_login_time),
                "last_login_ip" => $last_login_ip,
            ]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}