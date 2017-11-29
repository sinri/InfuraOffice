<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/23
 * Time: 23:53
 */

namespace sinri\InfuraOffice\entity;

use sinri\enoch\helper\CommonHelper;

/**
 * Class ServerEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property string server_name
 * @property string connect_ip
 * @property int connect_port
 * @property string ssh_user
 * @property string platform_name
 * @property string platform_device_id
 */
class ServerEntity extends EntityInterface
{

    /**
     * @param null $keyChain
     * @return array
     */
    public function propertiesAndDefaults($keyChain = null)
    {
        static $dic = [
            "server_name" => null,
            "connect_ip" => null,
            "connect_port" => 22,
            "ssh_user" => null,
            "platform_name" => null,
            "platform_device_id" => null,
        ];
        if ($keyChain === null) {
            return $dic;
        }
        return CommonHelper::safeReadNDArray($dic, $keyChain);
    }

    public function primaryKey()
    {
        return $this->server_name;
    }
}