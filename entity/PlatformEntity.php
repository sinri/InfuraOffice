<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 09:11
 */

namespace sinri\InfuraOffice\entity;

use sinri\enoch\helper\CommonHelper;

/**
 * Class HardwarePlatformEntity
 * @package sinri\InfuraOffice\entity
 * @property string platform_name
 * @property string platform_type
 * @property string auth_id
 * @property string auth_key
 */
class PlatformEntity extends EntityInterface
{

    const PLATFORM_IDC = "IDC";
    const PLATFORM_ALIYUN = "Aliyun";

    /**
     * @param null $keyChain
     * @return array
     */
    public function propertiesAndDefaults($keyChain = null)
    {
        static $dic = [
            "platform_name" => null,
            "platform_type" => self::PLATFORM_IDC,
            "auth_id" => '',
            "auth_key" => '',
        ];
        if ($keyChain === null) {
            return $dic;
        }
        return CommonHelper::safeReadNDArray($dic, $keyChain);
    }

    public function primaryKey()
    {
        return $this->platform_name;
    }
}