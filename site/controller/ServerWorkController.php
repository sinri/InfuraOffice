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
            $servers = $this->serverLibrary->entityList();
            $this->_sayOK(['list' => $servers]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function pingWithSudo()
    {
        try {
            $server_name = LibRequest::getRequest("server_name");
            $entity = $this->serverLibrary->readEntityByName($server_name);
            if (!$entity) {
                throw new \Exception('No such server');
            }

            $query = ShellCommandHandler::buildQueryForSync($server_name, "sudo uname -a");

            $daemonQueryLibrary = new DaemonQueryLibrary();
            $result = @$daemonQueryLibrary->query($query);

            $this->_sayOK(['result' => json_decode($result), 'plain_result' => $result]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function checkDF()
    {
        try {
            $server_name_list = LibRequest::getRequest("server_name_list", []);
            $server_df_results = [];
            foreach ($server_name_list as $server_name) {
                $entity = $this->serverLibrary->readEntityByName($server_name);
                if (!$entity) {
                    throw new \Exception('No such server');
                }

                $command = 'sudo df -h';
                $query = ShellCommandHandler::buildQueryForSync($server_name, $command);

                $daemonQueryLibrary = new DaemonQueryLibrary();
                $result = @$daemonQueryLibrary->query($query);

                $output = $daemonQueryLibrary->parseResponse($result, $parse_error);
                $output = implode(PHP_EOL, $output);

                $server_df_results[] = [
                    'server_name' => $server_name,
                    'output' => $output,
                    'error' => $parse_error,
                ];
            }

            $this->_sayOK(['df_list' => $server_df_results]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function checkDU()
    {
        try {
            $server_name_list = LibRequest::getRequest("server_name_list", []);
            $dir = LibRequest::getRequest("dir", "/");

            $server_du_results = [];
            foreach ($server_name_list as $server_name) {
                $entity = $this->serverLibrary->readEntityByName($server_name);
                if (!$entity) {
                    throw new \Exception('No such server');
                }

                $command = 'sudo du -h -d1 ' . escapeshellarg($dir);
                $query = ShellCommandHandler::buildQueryForSync($server_name, $command, true);

                $daemonQueryLibrary = new DaemonQueryLibrary();
                $result = @$daemonQueryLibrary->query($query);

                $output = $daemonQueryLibrary->parseResponse($result, $parse_error);
                $output = implode(PHP_EOL, $output);

                $server_du_results[] = [
                    'server_name' => $server_name,
                    'dir' => $dir,
                    'output' => $output,
                    'error' => $parse_error,
                ];
            }
            $this->_sayOK(['du_list' => $server_du_results]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function checkLS()
    {
        try {
            $server_name_list = LibRequest::getRequest("server_name_list", []);
            $dir = LibRequest::getRequest("dir", "/");

            $server_ls_results = [];
            foreach ($server_name_list as $server_name) {
                $entity = $this->serverLibrary->readEntityByName($server_name);
                if (!$entity) {
                    throw new \Exception('No such server');
                }

                $command = 'sudo ls -alh ' . escapeshellarg($dir);
                $query = ShellCommandHandler::buildQueryForSync($server_name, $command, true);

                $daemonQueryLibrary = new DaemonQueryLibrary();
                $result = @$daemonQueryLibrary->query($query);

                $output = $daemonQueryLibrary->parseResponse($result, $parse_error);
                $output = implode(PHP_EOL, $output);

                $server_ls_results[] = [
                    'server_name' => $server_name,
                    'dir' => $dir,
                    'output' => $output,
                    'error' => $parse_error,
                ];
            }
            $this->_sayOK(['ls_list' => $server_ls_results]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}