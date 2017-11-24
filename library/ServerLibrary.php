<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 09:19
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\ServerEntity;
use sinri\InfuraOffice\security\SecurityDataAgent;

class ServerLibrary
{
    const STORE_ASPECT_SERVER = "server";

    /**
     * @return array
     */
    public function serverList()
    {
        $server_names = SecurityDataAgent::getObjectList(self::STORE_ASPECT_SERVER, false);
        $servers = [];
        foreach ($server_names as $server_name_hashed) {
            $serverEntity = $this->getServerEntityByNameHash($server_name_hashed);
            if (!$serverEntity) continue;
            $servers[] = [
                "server_name" => $serverEntity->server_name,
                "connect_ip" => $serverEntity->connect_ip,
                "ssh_user" => $serverEntity->ssh_user,
            ];
        }
        return $servers;
    }

    /**
     * @param $server_name
     * @return bool|ServerEntity
     */
    public function getServerEntityByName($server_name)
    {
        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_SERVER, $server_name);
        if (empty($info)) return false;
        return new ServerEntity($info);
    }

    /**
     * @param $server_name_hash
     * @return bool|ServerEntity
     */
    public function getServerEntityByNameHash($server_name_hash)
    {
        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_SERVER, $server_name_hash, true);
        if (empty($info)) return false;
        return new ServerEntity($info);
    }

    /**
     * @param $server_name
     * @param $connect_ip
     * @param $ssh_user
     * @return bool
     */
    public function updateServer($server_name, $connect_ip, $ssh_user)
    {
        $serverEntity = new ServerEntity([
            "server_name" => $server_name,
            "connect_ip" => $connect_ip,
            "ssh_user" => $ssh_user,
        ]);
        return $this->storeServer($serverEntity);
    }

    /**
     * @param ServerEntity $serverEntity
     * @return bool
     */
    public function storeServer($serverEntity)
    {
        $json = $serverEntity->toJsonObject();
        return SecurityDataAgent::writeObject(self::STORE_ASPECT_SERVER, $serverEntity->server_name, $json);
    }

    /**
     * @param $server_name
     * @return bool
     */
    public function removeServer($server_name)
    {
        return SecurityDataAgent::removeObject(self::STORE_ASPECT_SERVER, $server_name);
    }
}