<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/28
 * Time: 10:27
 */

namespace sinri\InfuraOffice\entity;

use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\cli\daemon\SSHToolkit;
use sinri\InfuraOffice\library\JobLibrary;
use sinri\InfuraOffice\library\ServerGroupLibrary;
use sinri\InfuraOffice\library\ServerLibrary;
use sinri\InfuraOffice\toolkit\InfuraOfficeToolkit;

/**
 * Class AbstractJobEntity
 * @package sinri\InfuraOffice\entity
 *
 * @property string job_name
 * @property string job_type
 * @property string cron_time_minute
 * @property string cron_time_hour
 * @property string cron_time_day_of_month
 * @property string cron_time_month
 * @property string cron_time_day_of_week
 * @property int last_run_timestamp
 * @property string[] server_list
 * @property string[] server_group_list
 * @property bool stopped
 */
abstract class AbstractJobEntity extends EntityInterface
{
    /**
     * @param $info
     * @return AbstractJobEntity
     * @throws \sinri\enoch\mvc\BaseCodedException
     */
    public static function jobFactory($info)
    {
        $job_type = CommonHelper::safeReadArray($info, 'job_type');
        CommonHelper::assertNotEmpty($job_type, 'seems not a job entity info');
        $class_name = 'sinri\InfuraOffice\entity\\' . $job_type . 'Entity';
        return new $class_name($info);
    }

    public function propertiesAndDefaults($keyChain = null)
    {

        $pDic = [
            "job_name" => null,
            "job_type" => $this->jobType(),
            "server_list" => [],
            "server_group_list" => [],
            "cron_time_minute" => null,
            "cron_time_hour" => null,
            "cron_time_day_of_month" => null,
            "cron_time_month" => null,
            "cron_time_day_of_week" => null,
            "last_run_timestamp" => 0,
            "stopped" => false,
        ];
        $dic = array_merge($pDic, $this->propertiesAndDefaultsOfFinalJob());


        if ($keyChain === null) {
            return $dic;
        }
        return CommonHelper::safeReadNDArray($dic, $keyChain);
    }

    abstract public function propertiesAndDefaultsOfFinalJob();

    abstract public function jobType();

    public function primaryKey()
    {
        return $this->job_name;
    }

    /**
     * @return mixed
     */
    abstract public function execute();

    /**
     * @return string[]
     */
    public function affectedServerList()
    {
        $full_server_list = [];
        $libSG = new ServerGroupLibrary();
        foreach ($this->server_group_list as $server_group_name) {
            $group = $libSG->readEntityByName($server_group_name);
            $p = $group->server_name_list;
            $full_server_list = array_merge($full_server_list, $p);
        }
        $full_server_list = array_merge($full_server_list, $this->server_list);
        return $full_server_list;
    }

    /**
     * @param bool $shouldThrowException
     * @return bool
     * @throws \Exception
     */
    public function shouldExecuteNow($shouldThrowException = false)
    {
        if (!((time() - $this->last_run_timestamp) >= 60)) {
            if ($shouldThrowException) {
                throw new \Exception("Recently executed on " . $this->last_run_timestamp . ' diff=' . (time() - $this->last_run_timestamp));
            }
            return false;
        }
        return true;
    }

    /**
     *
     * @throws \Exception
     */
    public function assertNotRunInLastMinute()
    {
        $this->shouldExecuteNow(true);
    }

    /**
     * @return bool
     */
    public function recordExecution()
    {
        $this->last_run_timestamp = time();
        return (new JobLibrary())->writeEntity($this);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function ensureTempDir()
    {
        $temp_sh_dir_path = InfuraOfficeToolkit::tempPath($this->JobType());
        if (!file_exists($temp_sh_dir_path)) {
            if (!@mkdir($temp_sh_dir_path, 0777, true)) {
                throw new \Exception("Cannot MKDIR: " . $temp_sh_dir_path);
            }
        }
        return $temp_sh_dir_path;
    }

    /**
     * Return a connected SSHToolkit Instance
     * @param string $serverName
     * @return SSHToolkit
     * @throws \Exception
     */
    public static function createSSHForServer($serverName)
    {
        $serverLibrary = new ServerLibrary();
        $serverEntity = $serverLibrary->readEntityByName($serverName);

        $host = $serverEntity->connect_ip;
        $user = $serverEntity->ssh_user;
        $port = $serverEntity->connect_port;
        $rsa_public_file = InfuraOfficeToolkit::readConfig(['daemon', 'ssh_public_file'], '~/.ssh/id_rsa.pub');
        $rsa_private_file = InfuraOfficeToolkit::readConfig(['daemon', 'ssh_key_file'], '~/.ssh/id_rsa');
        $rsa_pass_phrase = InfuraOfficeToolkit::readConfig(['daemon', 'ssh_pass_phrase'], null);
        $ssh_toolkit = new SSHToolkit($host, $user, $port, $rsa_public_file, $rsa_private_file, $rsa_pass_phrase);
        $ssh_toolkit->connect();
        return $ssh_toolkit;
    }

    /**
     * @param string $level DEBUG INFO WARNING ERROR
     * @param string $serverName
     * @param string $message
     * @param string $object
     */
    public function executeLog($level, $serverName, $message, $object = '')
    {
        $pid = getmypid();
        //echo "[" . date("Y-m-d H:i:s") . "|" . microtime(true) . "] <{$pid}:{$level}> " . $message . PHP_EOL;
        InfuraOfficeToolkit::logger('cronjob_' . $this->job_name, false)
            ->log($level, "[" . microtime(true) . "] <{$pid}> ({$serverName})" . $message, $object);
    }

    /**
     * @param array $report
     */
    public function exportReportToLog($report)
    {
        $outputFile = InfuraOfficeToolkit::logger('cronjob_' . $this->job_name, false)->decideTargetFile();
        $output = '';
        $output .= "====== FINAL REPORT ======" . PHP_EOL;
        foreach ($report as $serverName => $serverReport) {
            $output .= "--- SERVER: " . $serverName . " ---" . PHP_EOL;
            foreach ($serverReport as $partName => $part) {
                $output .= "* PART " . $partName . " *" . PHP_EOL;
                $output .= $part . PHP_EOL;
            }
        }
        $output .= "======= REPORT FIN =======" . PHP_EOL;
        file_put_contents($outputFile, $output, FILE_APPEND);
    }
}

/*
 * Certain # m h dom mon dow
 */