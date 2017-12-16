<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/29
 * Time: 22:36
 */

namespace sinri\InfuraOffice\entity;

use sinri\enoch\core\LibLog;

/**
 * Class RemoveAntiquityJobEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property string[] files
 * @property int keep_days
 * @property string date_format
 */
class RemoveAntiquityJobEntity extends AbstractJobEntity
{

    public function propertiesAndDefaultsOfFinalJob()
    {
        return [
            "files" => [],
            "keep_days" => 1,
            "date_format" => null,// Y(2017) y(17) m(12) d(31)
        ];
    }

    public function jobType()
    {
        return "RemoveAntiquityJob";
    }

    /**
     * @param null $targetServerName
     * @return array
     * @throws \Exception
     */
    public function execute($targetServerName = null)
    {
        $this->assertNotRunInLastMinute();
        $this->recordExecution();

        switch ($this->date_format) {
            case 'Y-m-d':
                $filename_regex = '/20\d\d\-[01]\d\-[0123]\d/';
                break;
            case 'y-m-d':
                $filename_regex = '/\d\d\-[01]\d\-[0123]\d/';
                break;
            case 'Ymd':
                $filename_regex = '/20\d\d[01]\d[0123]\d/';
                break;
            case 'ymd':
                $filename_regex = '/\d\d[01]\d[0123]\d/';
                break;
            default:
                throw new \Exception("data format is not supported!");
        }
        $deadline = date($this->date_format, strtotime((-$this->keep_days) . ' day'));

        $report = [];
        if ($targetServerName === null) {
            $affected_servers = $this->affectedServerList();
        } else {
            $affected_servers = [$targetServerName];
        }
        foreach ($affected_servers as $server_name) {
            // 2.0 ssh prepare
            $report[$server_name] = [
                "output" => '',
                "error" => '',
                "warning" => '',
                "done" => false,
            ];
            try {
                $ssh = self::createSSHForServer($server_name);

                //find files
                $files = [];
                foreach ($this->files as $file) {
                    $command = "sudo find / -path " . escapeshellarg($file);
                    $output = $ssh->exec($command);
                    $return_var = $ssh->getLastExecReturnVar();
                    $this->executeLog(LibLog::LOG_DEBUG, $server_name, "FIND response", [$command, $output, $return_var]);
                    if (!empty($return_var)) {
                        //throw new \Exception("Find Returned " . $return_var);
                        $report[$server_name]['warning'] .= "Find Returned " . $return_var . " for pattern " . $file . PHP_EOL;
                        break;
                    }
                    $part_of_files = explode(PHP_EOL, $output);
                    $files = array_merge($files, $part_of_files);
                }

                sort($files);

                foreach ($files as $file) {
                    if (preg_match($filename_regex, $file, $matches)) {
                        $file_date = $matches[0];
                        if ($deadline > $file_date) {
                            //stat stat -c "%s" 1.log | awk '{printf("%.2f M\n",($1/1024/1024))}'
                            $command = 'stat -c "%s" ' . escapeshellarg($file) . ' | awk \'{printf("%.2f M",($1/1024/1024))}\'';
                            $file_size = $ssh->exec($command);
                            $return_var = $ssh->getLastExecReturnVar();
                            $this->executeLog(LibLog::LOG_DEBUG, $server_name, "STAT response", [$command, $file_size, $return_var]);
                            //remove
                            $command = 'sudo rm -f ' . escapeshellarg($file);
                            $output = $ssh->exec($command);
                            $return_var = $ssh->getLastExecReturnVar();
                            $this->executeLog(LibLog::LOG_DEBUG, $server_name, "RM response", [$command, $output, $return_var]);
                            if ($return_var) {
                                $report[$server_name]['error'] .= "RM returned " . $return_var . PHP_EOL;
                                $this->executeLog(LibLog::LOG_ERROR, $server_name, "RM " . $file . " failed, returned " . $return_var);
                            } else {
                                $report[$server_name]['output'] .= "Removed [{$file_size}] " . $file . PHP_EOL;
                                $this->executeLog(LibLog::LOG_INFO, $server_name, "Removed [{$file_size}] " . $file);
                            }
                        }
                    }
                }

                $report[$server_name]['done'] = true;
            } catch (\Exception $exception) {
                $report[$server_name]['error'] = "JOB[{$this->job_name}]-EXCEPTION! " . $exception->getMessage();
            }

        }

        return $report;
    }
}