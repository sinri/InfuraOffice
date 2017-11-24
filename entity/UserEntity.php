<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 16:48
 */

namespace sinri\InfuraOffice\entity;


/**
 * Class UserEntity
 * @package sinri\InfuraOffice\entity
 * @property string username
 * @property string auth_hash
 * @property string role
 * @property string privileges
 * @property string last_login_time
 * @property string last_login_ip
 */
class UserEntity extends EntityInterface
{
    const ROLE_ADMIN = "ADMIN";
    const ROLE_WORKER = "WORKER";
    const ROLE_WATCHER = "WATCHER";

//    protected $username;
//    protected $auth_hash;
//    protected $role;
//    protected $privileges;
//    //protected $session_token;
//    //protected $session_expiration;
//    protected $last_login_time;
//    protected $last_login_ip;

    public function __construct($json)
    {
        parent::__construct($json);
    }

//    /**
//     * @return mixed
//     */
//    public function getLastLoginTime()
//    {
//        return $this->last_login_time;
//    }
//
//    /**
//     * @param mixed $last_login_time
//     */
//    public function setLastLoginTime($last_login_time)
//    {
//        $this->last_login_time = $last_login_time;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getLastLoginIp()
//    {
//        return $this->last_login_ip;
//    }
//
//    /**
//     * @param mixed $last_login_ip
//     */
//    public function setLastLoginIp($last_login_ip)
//    {
//        $this->last_login_ip = $last_login_ip;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getUsername()
//    {
//        return $this->username;
//    }
//
//    /**
//     * @param mixed $username
//     */
//    public function setUsername($username)
//    {
//        $this->username = $username;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getAuthHash()
//    {
//        return $this->auth_hash;
//    }
//
//    /**
//     * @param mixed $auth_hash
//     */
//    public function setAuthHash($auth_hash)
//    {
//        $this->auth_hash = $auth_hash;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getRole()
//    {
//        return $this->role;
//    }
//
//    /**
//     * @param mixed $role
//     */
//    public function setRole($role)
//    {
//        $this->role = $role;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPrivileges()
//    {
//        return $this->privileges;
//    }
//
//    /**
//     * @param mixed $privileges
//     */
//    public function setPrivileges($privileges)
//    {
//        $this->privileges = $privileges;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSessionToken()
//    {
//        return $this->session_token;
//    }
//
//    /**
//     * @param mixed $session_token
//     */
//    public function setSessionToken($session_token)
//    {
//        $this->session_token = $session_token;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSessionExpiration()
//    {
//        return $this->session_expiration;
//    }
//
//    /**
//     * @param mixed $session_expiration
//     */
//    public function setSessionExpiration($session_expiration)
//    {
//        $this->session_expiration = $session_expiration;
//    }

    /**
     * @param string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        //echo $password.' vs '.$this->auth_hash.PHP_EOL;
        return (password_verify($password, $this->auth_hash));
    }

    /**
     * @param string $password
     */
    public function updateAuthHashForPassword($password)
    {
        $this->auth_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @return array
     */
    protected function propertiesAndDefaults()
    {
        return [
            'username' => null,
            'auth_hash' => null,
            'role' => null,
            'privileges' => [],
            'last_login_time' => null,
            'last_login_ip' => null,
        ];
    }
}