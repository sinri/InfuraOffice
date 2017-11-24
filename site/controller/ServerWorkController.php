<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 17:09
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\InfuraOffice\cli\handler\ShellCommandHandler;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\DaemonQueryLibrary;
use sinri\InfuraOffice\library\ServerLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class ServerWorkController extends BaseController
{
    protected $serverLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole([UserEntity::ROLE_ADMIN, UserEntity::ROLE_WORKER], true);

        $this->serverLibrary = new ServerLibrary();
    }

    public function servers()
    {
        try {
            $servers = $this->serverLibrary->serverList();
            $this->_sayOK(['list' => $servers]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function pingWithSudo()
    {
        try {
            $server_name = LibRequest::getRequest("server_name");
            $entity = $this->serverLibrary->getServerEntityByName($server_name);
            if (!$entity) {
                throw new \Exception('No such server');
            }

            $query = ShellCommandHandler::buildQueryForSync($server_name, "sudo uname -a");

            $daemonQueryLibrary = new DaemonQueryLibrary();
            $result = $daemonQueryLibrary->query($query);

            $this->_sayOK(['result' => json_decode($result), 'plain_result' => $result]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function checkDF()
    {
        try {
            $server_name = LibRequest::getRequest("server_name");
            $entity = $this->serverLibrary->getServerEntityByName($server_name);
            if (!$entity) {
                throw new \Exception('No such server');
            }

            $command = 'sudo df -h';
            $query = ShellCommandHandler::buildQueryForSync($server_name, $command);

            $daemonQueryLibrary = new DaemonQueryLibrary();
            $result = $daemonQueryLibrary->query($query);

            $this->_sayOK(['result' => json_decode($result), 'plain_result' => $result]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}