<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/25
 * Time: 21:02
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibPDO;
use sinri\enoch\core\LibRequest;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\DatabaseLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class DatabaseWorkController extends BaseController
{
    protected $databaseLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole([UserEntity::ROLE_ADMIN, UserEntity::ROLE_WORKER], true);
        $this->databaseLibrary = new DatabaseLibrary();
    }

    /**
     * @param $databaseName
     * @param $username
     * @return LibPDO
     * @throws \Exception
     */
    protected function getDatabaseClient($databaseName, $username = null)
    {
        $databaseEntity = $this->databaseLibrary->getDatabaseEntityByName($databaseName);
        if (!$databaseEntity) {
            throw new \Exception("no such database");
        }
        if (empty($databaseEntity->accounts)) {
            throw new \Exception("no accounts registered");
        }
        if ($username === null) {
            $username = array_rand($databaseEntity->accounts);
        }
        if (!isset($databaseEntity->accounts[$username])) {
            throw new \Exception("no such user");
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

    public function ping()
    {
        try {
            $database_name = LibRequest::getRequest("database_name", '');
            $username = LibRequest::getRequest("username", null);

            $db = $this->getDatabaseClient($database_name, $username);
            $result = $db->safeQueryOne("select version()");
            if (!$result) {
                $this->_sayFail("done");
                return false;
            }
            $this->_sayOK(["result" => $result]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}