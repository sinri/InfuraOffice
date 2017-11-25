<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/25
 * Time: 16:20
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\DatabaseEntity;
use sinri\InfuraOffice\security\SecurityDataAgent;

class DatabaseLibrary
{
    const STORE_ASPECT_DATABASE = "database";

    /**
     * @return array
     */
    public function databaseList()
    {
        $database_names = SecurityDataAgent::getObjectList(self::STORE_ASPECT_DATABASE, false);
        $databases = [];
        foreach ($database_names as $database_name_hashed) {
            $databaseEntity = $this->getDatabaseEntityByNameHash($database_name_hashed);
            if (!$databaseEntity) continue;
            $databases[] = [
                "database_name" => $databaseEntity->database_name,
                "server_type" => $databaseEntity->server_type,
                "host" => $databaseEntity->host,
                "port" => $databaseEntity->port,
                "accounts" => $databaseEntity->accounts,
            ];
        }
        return $databases;
    }

    /**
     * @param $database_name
     * @return bool|DatabaseEntity
     */
    public function getDatabaseEntityByName($database_name)
    {
        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_DATABASE, $database_name);
        if (empty($info)) return false;
        return new DatabaseEntity($info);
    }

    /**
     * @param $database_name_hash
     * @return bool|DatabaseEntity
     */
    public function getDatabaseEntityByNameHash($database_name_hash)
    {
        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_DATABASE, $database_name_hash, true);
        if (empty($info)) return false;
        return new DatabaseEntity($info);
    }

    /**
     * @param DatabaseEntity $databaseEntity
     * @return bool
     */
    public function updateDatabase($databaseEntity)
    {
        return SecurityDataAgent::writeObject(self::STORE_ASPECT_DATABASE, $databaseEntity->database_name, $databaseEntity->toJsonObject());
    }

    /**
     * @param $databaseName
     * @return bool
     */
    public function removeDatabase($databaseName)
    {
        return SecurityDataAgent::removeObject(self::STORE_ASPECT_DATABASE, $databaseName);
    }
}