<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/12/18
 * Time: 09:45
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\entity\JSSHAgentTaskStatusEntity;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\JSSHAgentLibrary;
use sinri\InfuraOffice\library\ServerLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class SLKController extends BaseController
{
    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole([
            UserEntity::ROLE_ADMIN, UserEntity::ROLE_WORKER, UserEntity::ROLE_WATCHER, UserEntity::ROLE_SLK_READER
        ], true);
    }

    public function servers()
    {
        try {
            $servers = (new ServerLibrary())->entityArrayList();
            $this->_sayOK(['list' => $servers]);
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

            // try to fix Issue #1
            $command = '';
            foreach ($patterns as $pattern) {
                if (strlen($pattern) <= 0) continue;
                //$command .= "sudo find / -path " . escapeshellarg($pattern) . ' 2>&1;';
                $command .= <<<PYTHON_COMMAND
echo -e 'import glob\nlist=glob.glob("{$pattern}")\nfor item in list:\n\tprint(item)'|python -;
PYTHON_COMMAND;
            }

            $proxy = new JSSHAgentLibrary();
            $taskIndex = $proxy->newTask($serverEntity->connect_ip, $serverEntity->ssh_user, $command);
            if (empty($taskIndex)) throw new \Exception("Cannot raise task");
            sleep(1);
            $entity = $proxy->checkTask($taskIndex);
            while (!in_array(
                $entity->status, [
                    JSSHAgentTaskStatusEntity::STATUS_FINISHED,
                    JSSHAgentTaskStatusEntity::STATUS_FETCHED,
                ]
            )) {
                sleep(1);
                $entity = $proxy->checkTask($taskIndex);
            }
            $output = $entity->output;
            $list = explode("\n", $output);
            $files = array_filter($list);

            $this->_sayOK(['files' => $files]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function getFileInfoAsync()
    {
        try {
            $server_name = LibRequest::getRequest("target_server", '');
            $target_file = LibRequest::getRequest("target_file", '');

            $proxy = new JSSHAgentLibrary();

            $command1 = 'sudo ls -al ' . escapeshellarg($target_file) . '|awk \'{print $5}\'';
            $taskIndex1 = $proxy->newTaskForRegisteredServer($server_name, $command1);
            if (empty($taskIndex1)) throw new \Exception("Cannot raise task 1");

            $command2 = 'sudo wc -l ' . escapeshellarg($target_file) . '|awk \'{print $1}\'';
            $taskIndex2 = $proxy->newTaskForRegisteredServer($server_name, $command2);
            if (empty($taskIndex2)) throw new \Exception("Cannot raise task 2");

            $this->_sayOK(['task_index_for_file_size' => $taskIndex1, 'task_index_for_file_lines' => $taskIndex2]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function readSLKLogsAsync()
    {
        try {
            $server_name = LibRequest::getRequest("target_server", '');
            $target_file = LibRequest::getRequest("target_file", '');
            $range_start = LibRequest::getRequest("range_start", '');
            $range_end = LibRequest::getRequest("range_end", '');
            $around_lines = LibRequest::getRequest("around_lines", '0');
            $is_case_sensitive = LibRequest::getRequest("is_case_sensitive", 'NO');
            $keyword = LibRequest::getRequest("keyword", '');
            $total_lines = LibRequest::getRequest("total_lines", '0');

            $proxy = new JSSHAgentLibrary();

            $total_lines = intval($total_lines, 10);

            if (empty($range_start)) $range_start = 0;
            else {
                $range_start = intval($range_start, 10);
                if ($range_start < 0) {
                    $range_start = $total_lines + $range_start;
                }
            }

            if (empty($range_end)) $range_end = $total_lines;
            else {
                $range_end = intval($range_end, 10);
                if ($range_end < 0) {
                    $range_end = $total_lines + $range_end;
                }
            }

            $around_lines = intval($around_lines, 10);

            $command = 'sudo cat -n ' . escapeshellarg($target_file)
                . '|awk \'{if($1>=' . $range_start . ' && $1<=' . $range_end . ') print $0}\''
                . '|grep -C ' . intval($around_lines) . ($is_case_sensitive ? ' -i ' : '') . ' -m 2000 ' . escapeshellarg($keyword);


            $taskIndex = $proxy->newTaskForRegisteredServer($server_name, $command);
            if (empty($taskIndex)) throw new \Exception("Cannot raise task");

            $this->_sayOK(['task_index' => $taskIndex]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function checkAsyncTaskResult()
    {
        try {
            $task_index = LibRequest::getRequest("task_index", '');
            $proxy = new JSSHAgentLibrary();
            $entity = $proxy->checkTask($task_index);
            $this->_sayOK($entity);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}
