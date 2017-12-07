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
    /**
     * @return DatabaseEntity[]
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
        $params['host'] = $databaseEntity->host;
        $params['port'] = $databaseEntity->port;
        $params['username'] = $username;
        $params['password'] = $databaseEntity->accounts[$username];
        switch ($databaseEntity->server_type) {
            case 'mysql-drds':
                $params['engine'] = 'mysql';
                $params['options'] = [\PDO::ATTR_EMULATE_PREPARES => true];
                break;
            case 'mysql':
                $params['engine'] = 'mysql';
                break;
            default:
                break;
        }
        $db = new LibPDO($params);
        return $db;
    }

    public function getAspectName()
    {
        return "Database";
    }

    /**
     * @return bool|int
     */
    public function refreshDothanConfig()
    {
        $entities = $this->entityList();
        $content = "# Dothan Config File updated on " . date('Y-m-d H:i:s') . PHP_EOL;
        foreach ($entities as $entity) {
            if (empty($entity->dothan_port) || $entity->dothan_port < 0) continue;
            $content .= "# " . $entity->database_name . " (" . $entity->platform_device_id . ") on " . $entity->platform_name . PHP_EOL;
            $content .= $entity->dothan_port . " " . $entity->host . ":" . $entity->port . PHP_EOL;
        }
        $written = file_put_contents(__DIR__ . '/../data/dothan.config', $content);
        return $written;
    }
}