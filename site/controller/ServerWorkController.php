<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 17:09
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\cli\handler\ShellCommandHandler;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\DaemonQueryLibrary;
use sinri\InfuraOffice\library\ServerGroupLibrary;
use sinri\InfuraOffice\library\ServerLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class ServerWorkController extends BaseController
{
    protected $serverLibrary;
    protected $serverGroupLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole([UserEntity::ROLE_ADMIN, UserEntity::ROLE_WORKER], true);

        $this->serverLibrary = new ServerLibrary();
        $this->serverGroupLibrary = new ServerGroupLibrary();
    }

    public function servers()
    {
        try {
            $servers = $this->serverLibrary->entityArrayList();
            $this->_sayOK(['list' => $servers]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function serverGroups()
    {
        try {
            $servers = $this->serverGroupLibrary->entityArrayList();
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
                //$output = implode(PHP_EOL, $output);

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
                //$output = implode(PHP_EOL, $output);

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
                //$output = implode(PHP_EOL, $output);

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

    public function runShellCommand()
    {
        try {
            $server_name_list = LibRequest::getRequest("server_name_list", []);
            $command = LibRequest::getRequest("command", "echo 'no command given';");
            $server_command_results = [];
            foreach ($server_name_list as $server_name) {
                $entity = $this->serverLibrary->readEntityByName($server_name);
                if (!$entity) {
                    throw new \Exception('No such server');
                }

                $query = ShellCommandHandler::buildQueryForSync($server_name, $command);

                $daemonQueryLibrary = new DaemonQueryLibrary();
                $result = @$daemonQueryLibrary->query($query);

                $output = $daemonQueryLibrary->parseResponse($result, $parse_error);
                //$output = implode(PHP_EOL, $output);

                $server_command_results[] = [
                    'server_name' => $server_name,
                    'shell_command' => $command,
                    'output' => $output,
                    'error' => $parse_error,
                ];
            }

            $this->_sayOK(['result_list' => $server_command_results]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function listSLKFiles()
    {
        try {
            $server_name = LibRequest::getRequest("server_name");

            $serverEntity = (new ServerLibrary())->readEntityByName($server_name);
            CommonHelper::assertNotEmpty($serverEntity, 'no such server');

            $patterns = [];
            if (is_string($serverEntity->slk_paths)) {
                $patterns = preg_split('/\s*[\r\n]\s*/', $serverEntity->slk_paths);
            }

            $files = [];
            foreach ($patterns as $pattern) {
                $pattern = trim($pattern);
                if (strlen($pattern) <= 0) continue;

                $command = "sudo find / -path " . escapeshellarg($pattern);
                $query = ShellCommandHandler::buildQueryForSync($server_name, $command, true);

                $daemonQueryLibrary = new DaemonQueryLibrary();
                $result = @$daemonQueryLibrary->query($query);

                $output = $daemonQueryLibrary->parseResponse($result, $parse_error);
                $list = explode("\n", $output);
                $list = array_filter($list);
                $files = array_merge($files, $list);
            }

            $this->_sayOK(['files' => $files]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function readSLKLogs()
    {
        try {
            $server_name = LibRequest::getRequest("target_server", '');
            $target_file = LibRequest::getRequest("target_file", '');
            $range_start = LibRequest::getRequest("range_start", '');
            $range_end = LibRequest::getRequest("range_end", '');
            $around_lines = LibRequest::getRequest("around_lines", '0');
            $is_case_sensitive = LibRequest::getRequest("is_case_sensitive", 'NO');
            $keyword = LibRequest::getRequest("keyword", '');

            // cat -n '/var/log/tomcat7/catalina.out'|awk '{if($1>=26726 && $1<=27726) print $0}'|grep -C 10 -m 2000 ''
            // cat -n '/var/log/tomcat7/catalina.out'|awk '{if($1>=28773 && $1<=29773) print $0}'|grep -C 10 -m 2000 'xx'

            // get total lines
            $command = 'wc -l ' . escapeshellarg($target_file) . '|awk \'{print $1}\'';
            $query = ShellCommandHandler::buildQueryForSync($server_name, $command, true);
            $daemonQueryLibrary = new DaemonQueryLibrary();
            $result = @$daemonQueryLibrary->query($query);
            $total_lines = $daemonQueryLibrary->parseResponse($result, $parse_error);
            $total_lines = intval($total_lines, 10);

            if (empty($range_start)) $range_start = 0;
            else {
                $range_start = intval($range_start, 10);
                if ($range_start < 0) {
                    $range_start = $total_lines - $range_start;
                }
            }

            if (empty($range_end)) $range_end = $total_lines;
            else {
                $range_end = intval($range_end, 10);
                if ($range_end < 0) {
                    $range_end = $total_lines - $range_end;
                }
            }

            $around_lines = intval($around_lines, 10);

            $command = 'cat -n ' . escapeshellarg($target_file)
                . '|awk \'{if($1>=' . $range_start . ' && $1<=' . $range_end . ') print $0}\''
                . '|grep -C ' . intval($around_lines) . ' -m 2000 ' . escapeshellarg($keyword);
            $query = ShellCommandHandler::buildQueryForSync($server_name, $command, true);

            $daemonQueryLibrary = new DaemonQueryLibrary();
            $result = @$daemonQueryLibrary->query($query);

            $output = $daemonQueryLibrary->parseResponse($result, $parse_error);
            $list = explode("\n", $output);

            $this->_sayOK(['output' => $output, 'lines' => $list, 'total_lines' => $total_lines, 'command' => $command]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}