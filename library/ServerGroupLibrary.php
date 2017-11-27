<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 16:00
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\ServerGroupEntity;

class ServerGroupLibrary extends AbstractEntityLibrary
{

    /**
     * @return string
     */
    public function getAspectName()
    {
        return "ServerGroup";
    }

    /**
     * @return ServerGroupEntity[]
     */
    public function entityList()
    {
        return parent::entityList();
    }

    /**
     * @param $platform_name
     * @return bool|ServerGroupEntity
     */
    public function readEntityByName($platform_name)
    {
        return parent::readEntityByName($platform_name);
    }

    /**
     * @param $platform_name_hash
     * @return bool|ServerGroupEntity
     */
    public function readEntityByNameHashed($platform_name_hash)
    {
        return parent::readEntityByNameHashed($platform_name_hash);
    }

    /**
     * @param ServerGroupEntity $entity
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

    /**
     * @param string $server_name
     */
    public function whenOneServerRemoved($server_name)
    {
        $groups = $this->entityList();
        foreach ($groups as $group) {
            if (in_array($server_name, $group->server_name_list)) {
                $group->server_name_list = array_diff($group->server_name_list, [$server_name]);
                $done = $this->writeEntity($group);
            }
        }
    }
}