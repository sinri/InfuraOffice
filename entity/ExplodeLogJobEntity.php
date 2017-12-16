<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/29
 * Time: 14:08
 */

namespace sinri\InfuraOffice\entity;


use sinri\enoch\core\LibLog;
use sinri\InfuraOffice\cli\cronjob\CronJobWorker;

/**
 * Class ExplodeLogJobEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property string file
 * @property int left_tail_lines
 * @property bool keep_backup
 */
class ExplodeLogJobEntity extends AbstractJobEntity
{

    public function jobType()
    {
        return "ExplodeLogJob";
    }

    public function propertiesAndDefaultsOfFinalJob()
    {
        return [
            "file" => null,
            "left_tail_lines" => 0,
            "keep_backup" => false,
        ];
    }

    /**
     * @param null $targetServerName
     * @return mixed|void
     * @throws \Exception
     */
    public function execute($targetServerName = null)
    {
        $this->assertNotRunInLastMinute();
        $this->recordExecution();

        // seek files
        // find / -path '/var/log/*/access.*'

        $report = [];
        $affected_servers = $this->affectedServerList();
        foreach ($affected_servers as $server_name) {
            // 2.0 ssh prepare
            $report[$server_name] = [
                "output" => '',
                "error" => '',
                "done" => false,
            ];
            try {
                $ssh = self::createSSHForServer($server_name);

                $command = "sudo find / -path " . escapeshellarg($this->file);
                $output = $ssh->exec($command);
                $return_var = $ssh->getLastExecReturnVar();
                $this->executeLog(LibLog::LOG_DEBUG, $server_name, "FIND response", [$command, $output, $return_var]);
                if (!empty($return_var)) {
                    throw new \Exception("Find Returned " . $return_var);
                }
                $files = explode(PHP_EOL, $output);

                CronJobWorker::log(LibLog::LOG_DEBUG, "find files", $files);

                foreach ($files as $file) {
                    if ($this->keep_backup) {
                        $backup_file = $file . '.' . date('YmdHis') . '.bak';
                        $command = "cp " . escapeshellarg($file) . ' ' . escapeshellarg($backup_file);
                        $output = $ssh->exec($command);
                        $return_var = $ssh->getLastExecReturnVar();
                        $this->executeLog(LibLog::LOG_DEBUG, $server_name, "FIND response", [$command, $output, $return_var]);
                        $report[$server_name]['output'] .= $output;
                        if (!empty($return_var)) {
                            throw new \Exception("CP Returned " . $return_var);
                        }
                    }
                    if ($this->left_tail_lines > 0) {
                        $command = "tail -n " . intval($this->left_tail_lines) . " " . escapeshellarg($file) . " > " . escapeshellarg($file);
                    } else {
                        $command = 'echo "---EXPLODED BY INFURA-OFFICE---" > ' . escapeshellarg($file);
                    }
                    $output = $ssh->exec($command);
                    $return_var = $ssh->getLastExecReturnVar();
                    $this->executeLog(LibLog::LOG_DEBUG, $server_name, "EXPLOSION response", [$command, $output, $return_var]);
                    $report[$server_name]['output'] .= $output;
                    if (!empty($return_var)) {
                        throw new \Exception("Explosion Returned " . $return_var);
                    }
                }

                $report[$server_name]['done'] = true;
            } catch (\Exception $exception) {
                $report[$server_name]['error'] = "JOB[{$this->job_name}]-EXCEPTION! " . $exception->getMessage();
            }
        }
    }


}