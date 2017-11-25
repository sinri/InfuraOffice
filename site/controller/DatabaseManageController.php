<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/25
 * Time: 16:47
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\entity\DatabaseEntity;
use sinri\InfuraOffice\library\DatabaseLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class DatabaseManageController extends BaseController
{
    protected $databaseLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->databaseLibrary = new DatabaseLibrary();
    }

    public function databases()
    {
        try {
            $list = $this->databaseLibrary->databaseList();
            $this->_sayOK(['list' => $list]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function updateDatabase()
    {
        try {
            $database_name = LibRequest::getRequest("database_name", '');
            $server_type = LibRequest::getRequest("server_type", "mysql");
            $host = LibRequest::getRequest("host", null);
            $port = LibRequest::getRequest("port", 3306);
            $accounts = LibRequest::getRequest("accounts", []);

            CommonHelper::assertNotEmpty($database_name, 'database number should not be empty');

            $databaseEntity = new DatabaseEntity([
                "database_name" => $database_name,
                "server_type" => $server_type,
                "host" => $host,
                "port" => $port,
                "accounts" => $accounts,
            ]);

            $done = $this->databaseLibrary->updateDatabase($databaseEntity);
            if (!$done) {
                throw new \Exception("cannot update database");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function removeDatabase()
    {
        try {
            $database_name = LibRequest::getRequest("database_name", '');
            CommonHelper::assertNotEmpty($database_name, 'database number should not be empty');
            $done = $this->databaseLibrary->removeDatabase($database_name);
            if (!$done) {
                throw new \Exception("cannot remove database");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}