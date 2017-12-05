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
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\DatabaseLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class DatabaseManageController extends BaseController
{
    protected $databaseLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole(UserEntity::ROLE_ADMIN, true);
        $this->databaseLibrary = new DatabaseLibrary();
    }

    public function updateDatabase()
    {
        try {
            $database_name = LibRequest::getRequest("database_name", '');
            $server_type = LibRequest::getRequest("server_type", "mysql");
            $host = LibRequest::getRequest("host", null);
            $port = LibRequest::getRequest("port", 3306);
            $accounts = LibRequest::getRequest("accounts", []);

            $platform_device_id = LibRequest::getRequest("platform_device_id");
            $platform_name = LibRequest::getRequest("platform_name");

            $dothan_port = LibRequest::getRequest("dothan_port", -1);
            if ($dothan_port === '') {
                $dothan_port = -1;
            }

            CommonHelper::assertNotEmpty($database_name, 'database number should not be empty');

            $databaseEntity = new DatabaseEntity([
                "database_name" => $database_name,
                "server_type" => $server_type,
                "host" => $host,
                "port" => $port,
                "accounts" => $accounts,
                "platform_name" => $platform_name,
                "platform_device_id" => $platform_device_id,
                "dothan_port" => $dothan_port,
            ]);

            $done = $this->databaseLibrary->writeEntity($databaseEntity);
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
            $done = $this->databaseLibrary->removeEntity($database_name);
            if (!$done) {
                throw new \Exception("cannot remove database");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function refreshDothanConfigFile()
    {
        try {
            $done = $this->databaseLibrary->refreshDothanConfig();
            if (!$done) {
                throw new \Exception("failed to refresh Dothan Config");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}