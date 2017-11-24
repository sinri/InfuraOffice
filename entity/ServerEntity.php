<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/23
 * Time: 23:53
 */

namespace sinri\InfuraOffice\entity;

/**
 * Class ServerEntity
 * @package sinri\InfuraOffice\entity
 * @property string server_name
 * @property string connect_ip
 * @property string ssh_user
 */
class ServerEntity extends EntityInterface
{

    /**
     * @return array
     */
    protected function propertiesAndDefaults()
    {
        return [
            "server_name" => null,
            "connect_ip" => null,
            "ssh_user" => null,
        ];
    }
}