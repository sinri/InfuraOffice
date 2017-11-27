<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 09:15
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\PlatformEntity;

class PlatformLibrary extends AbstractEntityLibrary
{
    //const STORE_ASPECT_PLATFORM='platform';

//    /**
//     * @deprecated
//     * @return array
//     */
//    public function hardwarePlatformList(){
//        $platform_names = SecurityDataAgent::getObjectList($this->getAspectName(), false);
//        $platforms = [];
//        foreach ($platform_names as $platform_name_hashed) {
//            $platformEntity = $this->readEntityByNameHashed($platform_name_hashed);
//            if (!$platformEntity) continue;
//            $platforms[] = [
//                "platform_name" => $platformEntity->platform_name,
//                "platform_type" => $platformEntity->platform_type,
//                "auth_id" => $platformEntity->auth_id,
//                "auth_key" => $platformEntity->auth_key,
//            ];
//        }
//        return $platforms;
//    }

    /**
     * @return PlatformEntity[]
     */
    public function entityList()
    {
        return parent::entityList();
    }

    /**
     * @param $platform_name
     * @return bool|PlatformEntity
     */
    public function readEntityByName($platform_name)
    {
        return parent::readEntityByName($platform_name);
    }

    /**
     * @param $platform_name_hash
     * @return bool|PlatformEntity
     */
    public function readEntityByNameHashed($platform_name_hash)
    {
        return parent::readEntityByNameHashed($platform_name_hash);
    }

    /**
     * @param PlatformEntity $entity
     * @return bool
     */
    public function writeEntity($entity)
    {
        return parent::writeEntity($entity);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function removeEntity($name)
    {
        return parent::removeEntity($name);
    }

    public function getAspectName()
    {
        return "Platform";
    }
}