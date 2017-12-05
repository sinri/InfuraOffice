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
    /**
     * @return PlatformEntity[]
     */
    public function entityList()
    {
        $list = parent::entityList();
        $list = array_merge([], $list);
        return $list;
    }

    /**
     * @return array
     */
    public function entityArrayList()
    {
        return parent::entityArrayList();
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