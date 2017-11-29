<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/28
 * Time: 11:03
 */

namespace sinri\InfuraOffice\cli\cronjob;


use sinri\InfuraOffice\entity\AbstractJobEntity;
use sinri\InfuraOffice\library\JobLibrary;
use sinri\InfuraOffice\toolkit\InfuraOfficeToolkit;
use sinri\InfuraOffice\toolkit\RuntimeConfigToolkit;

class CronJobWorker
{
    protected $jobLibrary;

    public function __construct()
    {
        $this->jobLibrary = new JobLibrary();
    }

    /**
     * @return array
     */
    public function getJobList()
    {
        $jobs = $this->jobLibrary->entityList();
        return $jobs;
    }

    /**
     * @return AbstractJobEntity[]
     */
    public function seekJobsForNow()
    {
        $jobs = $this->getJobList();
        $result = [];
        foreach ($jobs as $job) {
            if ($job->stopped) continue;

            //Certain # m h dom mon dow
            $minute = intval(date('i'), 10);
            $hour = date('G');
            $day = date('j');
            $month = date('n');
            $weekday = date('w');

            if (
                self::isCronValueMatch($job->cron_time_minute, $minute)
                && self::isCronValueMatch($job->cron_time_hour, $hour)
                && self::isCronValueMatch($job->cron_time_day_of_month, $day)
                && self::isCronValueMatch($job->cron_time_month, $month)
                && self::isCronValueMatch($job->cron_time_day_of_week, $weekday)
            ) {
                $result[$job->primaryKey()] = $job;
            }
        }
        return $result;
    }

    /**
     * @param string|int $cron_config_value
     * @param int $real_value
     * @return bool
     */
    public static function isCronValueMatch($cron_config_value, $real_value)
    {
        if ($cron_config_value === '*') return true;
        if (is_int($cron_config_value)) {
            return $cron_config_value === $real_value;
        }
        if (is_string($cron_config_value)) {
            // x
            // x,y
            // */y

            if (preg_match('/^\d+(,\d+)*$/', $cron_config_value)) {
                $list = explode(",", $cron_config_value);
                return in_array($real_value, $list);
            }
            if (preg_match('/^\*\/(\d+)$/', $cron_config_value, $matches)) {
                $mod_value = $matches[1];
                return ($real_value % $mod_value === 0 ? true : false);
            }
        }
        return false;
    }

    /**
     * @param string $level DEBUG INFO WARNING ERROR
     * @param string $message
     * @param string $object
     */
    public static function log($level, $message, $object = '')
    {
        $pid = getmypid();
        //echo "[" . date("Y-m-d H:i:s") . "|" . microtime(true) . "] <{$pid}:{$level}> " . $message . PHP_EOL;
        InfuraOfficeToolkit::logger('cronjob')
            ->log($level, "[" . microtime(true) . "] <{$pid}> " . $message, $object);
    }

    const RUNTIME_COMMAND_EMPTY = '';
    const RUNTIME_COMMAND_PAUSE = 'pause';//in fact no use
    const RUNTIME_COMMAND_STOP = 'stop';

    /**
     * @return string
     */
    public static function readRuntimeCommand()
    {
        $cmd = InfuraOfficeToolkit::readConfig(['cronjob', 'command'], self::RUNTIME_COMMAND_EMPTY);
        return strtolower($cmd);
    }

    /**
     * @param string $cmd
     * @return bool
     */
    public static function setRuntimeCommand($cmd)
    {
        if (!in_array($cmd, [self::RUNTIME_COMMAND_PAUSE, self::RUNTIME_COMMAND_STOP, self::RUNTIME_COMMAND_EMPTY])) {
            return false;
        }
        return RuntimeConfigToolkit::writeRuntimeConfig(['cronjob', 'command'], $cmd);
    }
}