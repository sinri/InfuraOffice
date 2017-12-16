<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/12/8
 * Time: 11:26
 */

namespace sinri\InfuraOffice\entity;


use sinri\enoch\core\LibLog;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\toolkit\InfuraOfficeToolkit;

/**
 * Class TrashJobEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property array[] explosion_list
 * @property array[] antiquity_list
 * @property array[] zombie_list
 */
class MixedJobEntity extends AbstractJobEntity
{
    const JobConfigKeyOfFiles = "files";
    const JobConfigKeyOfKeepTailLines = "keep_tail_lines";
    const JobConfigKeyOfKeepBackup = "keep_backup";
    const JobConfigKeyOfNotModifiedDays = "not_modified_days";
    const JobConfigKeyOfNotAccessedDays = "not_accessed_days";

    public function propertiesAndDefaultsOfFinalJob()
    {
        return [
            "explosion_list" => [
                //["files"=>["/a/b*"],"keep_tail_lines"=>10,"keep_backup"=>1]
            ],
            "antiquity_list" => [
                //["files"=>["/a/b*"],"not_modified_days"=>10]
            ],
            "zombie_list" => [
                //["files"=>["/a/b*"],"not_modified_days"=>10]
            ],
        ];
    }

    public function jobType()
    {
        return "MixedJob";
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function execute()
    {
        $this->assertNotRunInLastMinute();
        $this->recordExecution();

        // 1. prepare tmp file
        $temp_sh_dir_path = $this->ensureTempDir();
        $temp_sh_file_path = tempnam($temp_sh_dir_path, md5($this->primaryKey()));

        $command_content = file_get_contents(__DIR__ . '/../docs/shell_sample/base_job_func.py');
        $command_content .= PHP_EOL . PHP_EOL;

        InfuraOfficeToolkit::cliMemoryDebug(__METHOD__ . '@' . __LINE__);

        if (!empty($this->explosion_list)) {
            foreach ($this->explosion_list as $explosion_config) {
                $files = CommonHelper::safeReadArray($explosion_config, self::JobConfigKeyOfFiles, []);
                $keep_tail_lines = CommonHelper::safeReadArray($explosion_config, self::JobConfigKeyOfKeepTailLines, 100);
                $keep_backup = CommonHelper::safeReadArray($explosion_config, self::JobConfigKeyOfKeepBackup, 0);

                $command_content .= "explosion_func([";
                foreach ($files as $file) {
                    $command_content .= escapeshellarg($file) . ", ";
                }
                $command_content .= "],";
                $command_content .= intval($keep_tail_lines, 10) . "," . intval($keep_backup, 10) . ")" . PHP_EOL;
            }
        }
        InfuraOfficeToolkit::cliMemoryDebug(__METHOD__ . '@' . __LINE__);
        if (!empty($this->antiquity_list)) {
            foreach ($this->antiquity_list as $antiquity_config) {
                $files = CommonHelper::safeReadArray($antiquity_config, self::JobConfigKeyOfFiles, []);
                $not_modified_days = CommonHelper::safeReadArray($antiquity_config, self::JobConfigKeyOfNotModifiedDays, 7);

                $command_content .= "antiquity_func([";
                foreach ($files as $file) {
                    $command_content .= escapeshellarg($file) . ", ";
                }
                $command_content .= "],";
                $command_content .= intval($not_modified_days, 10) . ")" . PHP_EOL;
            }
        }
        InfuraOfficeToolkit::cliMemoryDebug(__METHOD__ . '@' . __LINE__);
        if (!empty($this->zombie_list)) {
            foreach ($this->zombie_list as $zombie_config) {
                $files = CommonHelper::safeReadArray($zombie_config, self::JobConfigKeyOfFiles, []);
                $not_accessed_days = CommonHelper::safeReadArray($zombie_config, self::JobConfigKeyOfNotAccessedDays, 7);

                $command_content .= "zombie_func([";
                foreach ($files as $file) {
                    $command_content .= escapeshellarg($file) . ", ";
                }
                $command_content .= "],";
                $command_content .= intval($not_accessed_days, 10) . ")" . PHP_EOL;
            }
        }
        InfuraOfficeToolkit::cliMemoryDebug(__METHOD__ . '@' . __LINE__);
        //$this->executeLog(LibLog::LOG_DEBUG, '-', $command_content);
        $written_to_local_temp = @file_put_contents($temp_sh_file_path, $command_content);
        if (!$written_to_local_temp) {
            $error = "Cannot write local temp file: " . $temp_sh_file_path;
            $this->executeLog(LibLog::LOG_ERROR, '-', $error);
            throw new \Exception($error);
        } else {
            $this->executeLog(LibLog::LOG_INFO, '-', 'Local temp file: ', $temp_sh_file_path);
        }

        $remote_sh_file_path = '/tmp/' . md5($this->JobType() . '-' . $this->primaryKey()) . '.' . time() . '.py';

        InfuraOfficeToolkit::cliMemoryDebug(__METHOD__ . '@' . __LINE__);
        // 2. remote each
        $report = [];
        $affected_servers = $this->affectedServerList();
        foreach ($affected_servers as $server_name) {
            InfuraOfficeToolkit::cliMemoryDebug(__METHOD__ . '@' . __LINE__);
            // 2.0 ssh prepare
            $report[$server_name] = [
                "output" => '',
                "error" => '',
                "done" => false,
//                "remote_file"=>'',
            ];
            try {
                $ssh = self::createSSHForServer($server_name);

                $ssh->establishSFTP();

                // 2.1 scp to remote
                //$scp_done = $ssh->scpSend($temp_sh_file_path, $remote_sh_file_path);
                $scp_done = $ssh->sftpSend($temp_sh_file_path, $remote_sh_file_path, 0777);
                if (!$scp_done) {
                    $report[$server_name]['error'] = "SCP FAILED when writing into remote file path " . $remote_sh_file_path;
                    continue;
                }

                // 2.2 run shell
                $report[$server_name]['output'] = $ssh->exec("sudo python " . escapeshellarg($remote_sh_file_path) . " 2>&1");

                $shell_return_var = $ssh->getLastExecReturnVar();// exec("echo $?");
                if ($shell_return_var != 0) {
                    $report[$server_name]['error'] = 'Shell Return Value is ' . $shell_return_var;
                } else {
                    $report[$server_name]['done'] = true;
                }

                // 2.3 unlink remote file
//                $report[$server_name]['remote_file']=$remote_sh_file_path;
                $ssh->sftpUnlink($remote_sh_file_path);
            } catch (\Exception $exception) {
                $report[$server_name]['error'] = "JOB[{$this->job_name}]-EXCEPTION! " . $exception->getMessage();
            }
            InfuraOfficeToolkit::cliMemoryDebug(__METHOD__ . '@' . __LINE__);
        }

        // 3. unlink

        $removed = unlink($temp_sh_file_path);
        if ($removed) {
            $this->executeLog(LibLog::LOG_INFO, '-', "Temp File Removed", $temp_sh_file_path);
        } else {
            $this->executeLog(LibLog::LOG_ERROR, '-', "Temp File Failed in Removing", $temp_sh_file_path);
        }

        $this->executeLog(LibLog::LOG_DEBUG, '-', 'REPORT' . PHP_EOL . print_r($report, true) . PHP_EOL);

        InfuraOfficeToolkit::cliMemoryDebug(__METHOD__ . '@' . __LINE__);

        return $report;
    }
}