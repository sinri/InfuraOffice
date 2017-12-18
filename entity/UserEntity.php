<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 16:48
 */

namespace sinri\InfuraOffice\entity;


use sinri\enoch\helper\CommonHelper;

/**
 * Class UserEntity
 * @package sinri\InfuraOffice\entity
 *
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
    const ROLE_SLK_READER = "SLK_READER";

    public function __construct($json)
    {
        parent::__construct($json);
    }

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
     * @param null $keyChain
     * @return array
     */
    public function propertiesAndDefaults($keyChain = null)
    {
        static $dic = [
            'username' => null,
            'auth_hash' => null,
            'role' => null,
            'privileges' => [],
            'last_login_time' => null,
            'last_login_ip' => null,
        ];
        if ($keyChain === null) {
            return $dic;
        }
        return CommonHelper::safeReadNDArray($dic, $keyChain);
    }

    public function primaryKey()
    {
        return $this->username;
    }
}