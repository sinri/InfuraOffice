<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/23
 * Time: 13:58
 */

namespace sinri\InfuraOffice\entity;

use sinri\enoch\helper\CommonHelper;

/**
 * Class SessionEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property string username
 * @property string token
 * @property int expiration
 * @property string ip
 */
class SessionEntity extends EntityInterface
{

    /**
     * @param null $keyChain
     * @return array
     */
    public function propertiesAndDefaults($keyChain = null)
    {
        static $dic = [
            "username" => null,
            "token" => null,
            "expiration" => 0,
            "ip" => null,
        ];
        if ($keyChain === null) {
            return $dic;
        }
        return CommonHelper::safeReadNDArray($dic, $keyChain);
    }

    public function primaryKey()
    {
        return $this->token;
    }
}