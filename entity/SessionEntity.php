<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/23
 * Time: 13:58
 */

namespace sinri\InfuraOffice\entity;

/**
 * Class SessionEntity
 * @package sinri\InfuraOffice\entity
 * @property string username
 * @property string token
 * @property int expiration
 * @property string ip
 */
class SessionEntity extends EntityInterface
{

    /**
     * @return array
     */
    protected function propertiesAndDefaults()
    {
        return [
            "username" => null,
            "token" => null,
            "expiration" => 0,
            "ip" => null,
        ];
    }
}