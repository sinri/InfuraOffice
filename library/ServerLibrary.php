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
    //const STORE_ASPECT_SERVER = "server";

//    /**
//     * @deprecated
//     * @return array
//     */
//    public function serverList()
//    {
//        $server_names = SecurityDataAgent::getObjectList($this->getAspectName(), false);
//        $servers = [];
//        foreach ($server_names as $server_name_hashed) {
//            $serverEntity = $this->readEntityByNameHashed($server_name_hashed);
//            if (!$serverEntity) continue;
//            $servers[] = [
//                "server_name" => $serverEntity->server_name,
//                "connect_ip" => $serverEntity->connect_ip,
//                "ssh_user" => $serverEntity->ssh_user,
//            ];
//        }
//        return $servers;
//    }

//    /**
//     * @deprecated
//     * @param $server_name
//     * @return bool|ServerEntity
//     */
//    public function getServerEntityByName($server_name)
//    {
//        $info = SecurityDataAgent::readObject($this->getAspectName(), $server_name);
//        if (empty($info)) return false;
//        return new ServerEntity($info);
//    }

//    /**
//     * @deprecated
//     * @param $server_name_hash
//     * @return bool|ServerEntity
//     */
//    public function getServerEntityByNameHash($server_name_hash)
//    {
//        $info = SecurityDataAgent::readObject($this->getAspectName(), $server_name_hash, true);
//        if (empty($info)) return false;
//        return new ServerEntity($info);
//    }

//    /**
//     * @deprecated
//     * @param $server_name
//     * @param $connect_ip
//     * @param $ssh_user
//     * @return bool
//     */
//    public function updateServer($server_name, $connect_ip, $ssh_user)
//    {
//        $serverEntity = new ServerEntity([
//            "server_name" => $server_name,
//            "connect_ip" => $connect_ip,
//            "ssh_user" => $ssh_user,
//        ]);
//        return $this->storeServer($serverEntity);
//    }

//    /**
//     * @deprecated
//     * @param ServerEntity $serverEntity
//     * @return bool
//     */
//    public function storeServer($serverEntity)
//    {
//        $json = $serverEntity->toJsonObject();
//        return SecurityDataAgent::writeObject($this->getAspectName(), $serverEntity->server_name, $json);
//    }

//    /**
//     * @deprecated
//     * @param $server_name
//     * @return bool
//     */
//    public function removeServer($server_name)
//    {
//        return SecurityDataAgent::removeObject($this->getAspectName(), $server_name);
//    }

    public function getAspectName()
    {
        return "Server";
    }

    /**
     * @return ServerEntity[]
     */
    public function entityList()
    {
        return parent::entityList();
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
        return parent::removeEntity($name);
    }
}