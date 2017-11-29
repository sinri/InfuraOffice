<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 15:58
 */

namespace sinri\InfuraOffice\entity;

use sinri\enoch\helper\CommonHelper;

/**
 * Class ServerGroupEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property string group_name
 * @property string[] server_name_list
 */
class ServerGroupEntity extends EntityInterface
{

    /**
     * @param null $keyChain
     * @return array
     */
    public function propertiesAndDefaults($keyChain = null)
    {
        static $dic = [
            "group_name" => null,
            "server_name_list" => [],
        ];
        if ($keyChain === null) {
            return $dic;
        }
        return CommonHelper::safeReadNDArray($dic, $keyChain);
    }

    public function primaryKey()
    {
        return $this->group_name;
    }
}