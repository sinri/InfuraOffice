<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/25
 * Time: 16:20
 */

namespace sinri\InfuraOffice\library;


use sinri\enoch\core\LibPDO;
use sinri\InfuraOffice\entity\DatabaseEntity;

class DatabaseLibrary extends AbstractEntityLibrary
{
    //const STORE_ASPECT_DATABASE = "database";

//    /**
//     * @deprecated
//     * @return array
//     */
//    public function databaseList()
//    {
//        $database_names = SecurityDataAgent::getObjectList($this->getAspectName(), false);
//        $databases = [];
//        foreach ($database_names as $database_name_hashed) {
//            $databaseEntity = $this->readEntityByNameHashed($database_name_hashed);
//            if (!$databaseEntity) continue;
//            $databases[] = [
//                "database_name" => $databaseEntity->database_name,
//                "server_type" => $databaseEntity->server_type,
//                "host" => $databaseEntity->host,
//                "port" => $databaseEntity->port,
//                "accounts" => $databaseEntity->accounts,
//            ];
//        }
//        return $databases;
//    }

//    /**
//     * @deprecated
//     * @param $database_name
//     * @return bool|DatabaseEntity
//     */
//    public function getDatabaseEntityByName($database_name)
//    {
//        $info = SecurityDataAgent::readObject($this->getAspectName(), $database_name);
//        if (empty($info)) return false;
//        return new DatabaseEntity($info);
//    }

//    /**
//     * @deprecated
//     * @param $database_name_hash
//     * @return bool|DatabaseEntity
//     */
//    public function getDatabaseEntityByNameHash($database_name_hash)
//    {
//        $info = SecurityDataAgent::readObject($this->getAspectName(), $database_name_hash, true);
//        if (empty($info)) return false;
//        return new DatabaseEntity($info);
//    }

//    /**
//     * @deprecated
//     * @param DatabaseEntity $databaseEntity
//     * @return bool
//     */
//    public function updateDatabase($databaseEntity)
//    {
//        return SecurityDataAgent::writeObject($this->getAspectName(), $databaseEntity->database_name, $databaseEntity->toJsonObject());
//    }

//    /**
//     * @deprecated
//     * @param $databaseName
//     * @return bool
//     */
//    public function removeDatabase($databaseName)
//    {
//        return SecurityDataAgent::removeObject($this->getAspectName(), $databaseName);
//    }

    /**
     * @return array
     */
    public function entityArrayList()
    {
        return parent::entityArrayList();
    }

    /**
     * @param $name
     * @return bool|DatabaseEntity
     */
    public function readEntityByName($name)
    {
        return parent::readEntityByName($name);
    }

    /**
     * @param $name_hashed
     * @return bool|DatabaseEntity
     */
    public function readEntityByNameHashed($name_hashed)
    {
        return parent::readEntityByNameHashed($name_hashed);
    }

    /**
     * @param DatabaseEntity $entity
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
     * @param $databaseName
     * @param $username
     * @return LibPDO
     * @throws \Exception
     */
    public function getDatabaseClient($databaseName, $username = null)
    {
        $databaseEntity = $this->readEntityByName($databaseName);
        if (!$databaseEntity) {
            throw new \Exception("no such database: " . $databaseName);
        }
        if (empty($databaseEntity->accounts)) {
            throw new \Exception("no accounts registered");
        }
        if ($username === null) {
            $username = array_rand($databaseEntity->accounts);
        }
        if (!isset($databaseEntity->accounts[$username])) {
            throw new \Exception("no such user: " . $username);
        }
        $params = [];
        switch ($databaseEntity->server_type) {
            case 'mysql':
            default:
                $params['host'] = $databaseEntity->host;
                $params['port'] = $databaseEntity->port;
                $params['username'] = $username;
                $params['password'] = $databaseEntity->accounts[$username];
                $params['engine'] = 'mysql';
                break;
        }
        $db = new LibPDO($params);
        return $db;
    }

    public function getAspectName()
    {
        return "Database";
    }
}