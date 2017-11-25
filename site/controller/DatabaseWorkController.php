<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/25
 * Time: 21:02
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\enoch\helper\CommonHelper;
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

    public function databases()
    {
        try {
            $list = $this->databaseLibrary->databaseList();
            $this->_sayOK(['list' => $list]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }


    public function ping()
    {
        try {
            $database_name = LibRequest::getRequest("database_name", '');
            $username = LibRequest::getRequest("username", null);

            $db = $this->databaseLibrary->getDatabaseClient($database_name, $username);
            $result = $db->safeQueryOne("select version()");
            if (!$result) {
                $this->_sayFail("done");
                return;
            }
            $this->_sayOK(["result" => $result]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function showFullProcessList()
    {
        try {
            $database_name = LibRequest::getRequest("database_name", '');
            $username = LibRequest::getRequest("username", null);

            $db = $this->databaseLibrary->getDatabaseClient($database_name, $username);

            $process_list = $db->getAll("show full processlist");

            if (!is_array($process_list)) {
                $this->_sayFail("database response error");
                return;
            }
            $this->_sayOK(['process_list' => $process_list]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function killProcess()
    {
        try {
            $database_name = LibRequest::getRequest("database_name");
            $username = LibRequest::getRequest("username");
            $pid = LibRequest::getRequest("pid", null, '/^\d+$/');

            CommonHelper::assertNotEmpty($database_name, 'database name should not be empty');
            CommonHelper::assertNotEmpty($username, 'username should not be empty');
            CommonHelper::assertNotEmpty($pid, 'process id should not be empty');

            $db = $this->databaseLibrary->getDatabaseClient($database_name, $username);

            $afw = $db->exec("kill " . intval($pid));

            $this->_sayOK(['afw' => $afw]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}