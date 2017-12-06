<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/25
 * Time: 16:07
 */

namespace sinri\InfuraOffice\entity;

use sinri\enoch\helper\CommonHelper;

/**
 * Class DatabaseEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property string database_name
 * @property string server_type
 * @property string host
 * @property int port
 * @property array accounts
 * @property string platform_name
 * @property string platform_device_id
 * @property string platform_area
 * @property int dothan_port
 */
class DatabaseEntity extends EntityInterface
{

    /**
     * @param null $keyChain
     * @return array
     */
    public function propertiesAndDefaults($keyChain = null)
    {
        static $dic = [
            "database_name" => null,
            "server_type" => "mysql",
            "host" => null,
            "port" => 3306,
            "accounts" => [],
            "platform_name" => null,
            "platform_device_id" => null,
            "platform_area" => null,
            "dothan_port" => -1,
        ];
        if ($keyChain === null) {
            return $dic;
        }
        return CommonHelper::safeReadNDArray($dic, $keyChain);
    }

    /**
     * @param $username
     * @param $password
     */
    public function updateOneAccount($username, $password)
    {
        CommonHelper::safeWriteNDArray($this->accounts, [$username], $password);
    }

    /**
     * @param $username
     */
    public function removeOneAccount($username)
    {
        if (isset($this->accounts[$username])) {
            unset($this->accounts[$username]);
        }
    }

    public function primaryKey()
    {
        return $this->database_name;
    }
}