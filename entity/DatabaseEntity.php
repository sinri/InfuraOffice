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
 * @property string database_name
 * @property string server_type
 * @property string host
 * @property int port
 * @property array accounts
 */
class DatabaseEntity extends EntityInterface
{

    /**
     * @return array
     */
    protected function propertiesAndDefaults()
    {
        return [
            "database_name" => null,
            "server_type" => "mysql",
            "host" => null,
            "port" => 3306,
            "accounts" => [],
        ];
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
}