<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 09:19
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\ServerEntity;

class ServerLibrary extends AbstractEntityLibrary
{

    /**
     * @return ServerEntity[]
     */
    public function entityList()
    {
        $list = parent::entityList();
        $list = array_merge([], $list);
        return $list;
    }

    public function getAspectName()
    {
        return "Server";
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
     * @return bool|ServerEntity
     */
    public function readEntityByName($platform_name)
    {
        return parent::readEntityByName($platform_name);
    }

    /**
     * @param $platform_name_hash
     * @return bool|ServerEntity
     */
    public function readEntityByNameHashed($platform_name_hash)
    {
        return parent::readEntityByNameHashed($platform_name_hash);
    }

    /**
     * @param ServerEntity $entity
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
        $done = parent::removeEntity($name);
        if ($done) {
            $lib = new ServerGroupLibrary();
            $lib->whenOneServerRemoved($name);
        }
        return $done;
    }
}