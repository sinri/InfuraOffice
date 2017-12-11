<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/28
 * Time: 09:29
 */

namespace sinri\InfuraOffice\entity;

use sinri\enoch\core\LibLog;
use sinri\enoch\core\LibRequest;

/**
 * Class ShellCommandJobEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property string command_content
 */
class ShellCommandJobEntity extends AbstractJobEntity
{
//    /**
//     * @param null $keyChain
//     * @return array
//     */
//    public function propertiesAndDefaults($keyChain = null)
//    {
//        static $dic =null;
//
//        if(!$dic) {
//            $pDic=parent::propertiesAndDefaults();
//            $dic=array_merge($pDic,[
//                "command_content" => "",
//            ]);
//        }
//
//        if ($keyChain === null) {
//            return $dic;
//        }
//        return CommonHelper::safeReadNDArray($dic, $keyChain);
//    }

    public function jobType()
    {
        return "ShellCommandJob";
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function execute()
    {
        $this->assertNotRunInLastMinute();
        $this->recordExecution();

        // 1. prepare tmp file
        $temp_sh_dir_path = $this->ensureTempDir();
        //$temp_sh_file_path=$temp_sh_dir_path.DIRECTORY_SEPARATOR.md5($this->primaryKey()).".sh";
        $temp_sh_file_path = tempnam($temp_sh_dir_path, md5($this->primaryKey()));

        $written_to_local_temp = file_put_contents($temp_sh_file_path, $this->command_content);
        if (!$written_to_local_temp) {
            $error = "Cannot write local temp file: " . $temp_sh_file_path;
            $this->executeLog(LibLog::LOG_ERROR, '-', $error);
            throw new \Exception($error);
        }

        $remote_sh_file_path = '/tmp/' . md5($this->JobType() . '-' . $this->primaryKey()) . '.' . time() . '.sh';

        // 2. remote each
        $report = [];
        $affected_servers = $this->affectedServerList();
        foreach ($affected_servers as $server_name) {
            if (LibRequest::isCLI()) {
                echo __METHOD__ . '@' . __LINE__ . ' server name: ' . json_encode($server_name) . PHP_EOL;
            }

            // 2.0 ssh prepare
            $report[$server_name] = [
                "output" => '',
                "error" => '',
                "done" => false,
            ];
            try {
                $ssh = self::createSSHForServer($server_name);
                echo __METHOD__ . '@' . __LINE__ . PHP_EOL;
                $ssh->establishSFTP();

                // 2.1 scp to remote
                //$scp_done = $ssh->scpSend($temp_sh_file_path, $remote_sh_file_path);
                $scp_done = $ssh->sftpSend($temp_sh_file_path, $remote_sh_file_path, 0777);
                if (!$scp_done) {
                    $report[$server_name]['error'] = "SCP FAILED when writing into remote file path " . $remote_sh_file_path;
                    continue;
                }

                // 2.2 run shell
                $report[$server_name]['output'] = $ssh->exec("/bin/bash " . escapeshellarg($remote_sh_file_path) . " 2>&1");

                $shell_return_var = $ssh->getLastExecReturnVar();// exec("echo $?");
                if ($shell_return_var != 0) {
                    $report[$server_name]['error'] = 'Shell Return Value is ' . $shell_return_var;
                } else {
                    $report[$server_name]['done'] = true;
                }

                echo __METHOD__ . '@' . __LINE__ . PHP_EOL;

                // 2.3 unlink remote file
                $ssh->sftpUnlink($remote_sh_file_path);
            } catch (\Exception $exception) {
                echo __METHOD__ . '@' . __LINE__ . PHP_EOL;
                $report[$server_name]['error'] = "JOB[{$this->job_name}]-EXCEPTION! " . $exception->getMessage();
            }
        }

        echo __METHOD__ . '@' . __LINE__ . PHP_EOL;

        // 3. unlink

        $removed = unlink($temp_sh_file_path);
        if ($removed) {
            $this->executeLog(LibLog::LOG_INFO, '-', "Temp File Removed", $temp_sh_file_path);
        } else {
            $this->executeLog(LibLog::LOG_ERROR, '-', "Temp File Failed in Removing", $temp_sh_file_path);
        }

        $this->executeLog(LibLog::LOG_DEBUG, '-', 'REPORT' . PHP_EOL . print_r($report, true) . PHP_EOL);

        echo __METHOD__ . '@' . __LINE__ . PHP_EOL;

        return $report;
    }

    public function propertiesAndDefaultsOfFinalJob()
    {
        return [
            "command_content" => "",
        ];
    }
}