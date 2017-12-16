<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/28
 * Time: 10:47
 */

use sinri\enoch\core\LibLog;
use sinri\InfuraOffice\cli\cronjob\CronJobWorker;

require_once __DIR__ . '/../autoload.php';

date_default_timezone_set("Asia/Shanghai");

//echo $argc.PHP_EOL;
//print_r($argv);

if ($argc < 2) {
    $cmd = 'help';
} else {
    $cmd = $argv[1];
}

switch ($cmd) {
    case 'start':
        //reset runtime command and keep alive
        CronJobWorker::setRuntimeCommand(CronJobWorker::RUNTIME_COMMAND_EMPTY);
        break;
    case 'pause':
        CronJobWorker::setRuntimeCommand(CronJobWorker::RUNTIME_COMMAND_PAUSE);
        echo "Set Runtime Command as " . CronJobWorker::RUNTIME_COMMAND_PAUSE . PHP_EOL;
        exit();
        break;
    case 'stop':
        CronJobWorker::setRuntimeCommand(CronJobWorker::RUNTIME_COMMAND_STOP);
        echo "Set Runtime Command as " . CronJobWorker::RUNTIME_COMMAND_STOP . PHP_EOL;
        exit();
        break;
    case 'continue':
        CronJobWorker::setRuntimeCommand(CronJobWorker::RUNTIME_COMMAND_EMPTY);
        echo "Set Runtime Command as " . CronJobWorker::RUNTIME_COMMAND_EMPTY . PHP_EOL;
        exit();
        break;
    case 'help':
    default:
        echo "Usage: php InfuraOfficeCronjob.php [start|stop|pause|continue|help]" . PHP_EOL;
        exit();
        break;
}


$worker = new CronJobWorker();
$alive_children = [];
CronJobWorker::log(LibLog::LOG_INFO, "InfuraOfficeCronjob set off");
while (true) {
    $runtime_command = CronJobWorker::readRuntimeCommand();
    if ($runtime_command === CronJobWorker::RUNTIME_COMMAND_STOP
        || $runtime_command === CronJobWorker::RUNTIME_COMMAND_PAUSE
    ) {
        $jobs = [];
    } else {
        $jobs = $worker->seekJobsForNow();
    }
    if (!empty($jobs)) {
        CronJobWorker::log(LibLog::LOG_INFO, "It is time to work now", array_keys($jobs));
        CronJobWorker::log(LibLog::LOG_DEBUG, "Current worker process count", count($alive_children));
        foreach ($jobs as $job_name => $job) {
            $servers = $job->affectedServerList();
            foreach ($servers as $server_name) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    //failed
                    CronJobWorker::log(LibLog::LOG_ERROR, "Cannot create child process to handle job, stop trying.", $job_name);
                    break;
                } elseif ($pid) {
                    //as parent
                    CronJobWorker::log("INFO", "CronJobWorker Created child process [{$pid}]!", $job_name);
                    $alive_children[$pid] = $pid;
                } else {
                    //as child
                    //$child_pid = getmypid();
                    try {
                        CronJobWorker::log(LibLog::LOG_INFO, "Job [{$job_name}] begins");
                        $report = $job->execute($server_name);
                        CronJobWorker::log(LibLog::LOG_INFO, "Job [{$job_name}] executed", $report);
                        $job->exportReportToLog($report);
                    } catch (Exception $exception) {
                        CronJobWorker::log(LibLog::LOG_ERROR, "Job [{$job_name}] failed", $exception->getMessage());
                    }
                    exit();
                }
            }
        }
    }

    for ($i = 0; $i < count($alive_children); $i++) {
        $wait_start_time = microtime(true);
        $done_pid = pcntl_wait($status, WNOHANG);//(WNOHANG | WUNTRACED)
        $wait_end_time = microtime(true);
        if ($done_pid) {
            CronJobWorker::log(LibLog::LOG_INFO, "Child Process [{$done_pid}] confirmed death, cost seconds: " . ($wait_end_time - $wait_start_time));
            unset($alive_children[$done_pid]);
        }
    }

    if ($runtime_command === CronJobWorker::RUNTIME_COMMAND_STOP) {
        CronJobWorker::log(LibLog::LOG_INFO, "It is time to die");
        break;
    }

    sleep(30);
}