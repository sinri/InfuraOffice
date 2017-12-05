<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/28
 * Time: 13:16
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\entity\ExplodeLogJobEntity;
use sinri\InfuraOffice\entity\RemoveAntiquityJobEntity;
use sinri\InfuraOffice\entity\ShellCommandJobEntity;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\JobLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class JobConfigController extends BaseController
{
    protected $jobLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole([UserEntity::ROLE_ADMIN], true);
        $this->jobLibrary = new JobLibrary();
    }

    public function jobs($job_type = '')
    {
        $jobs = $this->jobLibrary->entityArrayList();
        if (strlen($job_type) > 0) {
            $jobs = array_filter($jobs, function ($var) use ($job_type) {
                return $var['job_type'] == $job_type;
            });
        }
        $jobs = array_values($jobs);
        $this->_sayOK(['list' => $jobs]);
    }

    public function removeJob()
    {
        try {
            $job_name = LibRequest::getRequest("job_name");
            CommonHelper::assertNotEmpty($job_name, 'job name should not be empty!');

            $done = $this->jobLibrary->removeEntity($job_name);
            if (!$done) {
                throw new \Exception("remove job failed");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function updateJob()
    {
        try {
            $json = [];
            $json['job_name'] = LibRequest::getRequest("job_name");
            $json['job_type'] = LibRequest::getRequest("job_type");
            $json['cron_time_minute'] = LibRequest::getRequest("cron_time_minute");
            $json['cron_time_hour'] = LibRequest::getRequest("cron_time_hour");
            $json['cron_time_day_of_month'] = LibRequest::getRequest("cron_time_day_of_month");
            $json['cron_time_month'] = LibRequest::getRequest("cron_time_month");
            $json['cron_time_day_of_week'] = LibRequest::getRequest("cron_time_day_of_week");
            $json['server_list'] = LibRequest::getRequest("server_list", []);
            $json['server_group_list'] = LibRequest::getRequest("server_group_list", []);
            //$last_run_timestamp=LibRequest::getRequest("last_run_timestamp");

            CommonHelper::assertNotEmpty($json['job_name'], 'job name should not be empty');

            $job_entity = $this->jobLibrary->readEntityByName($json['job_name']);

            $class_name = null;

            switch ($json['job_type']) {
                case 'ShellCommandJob':
                    $json['command_content'] = LibRequest::getRequest("command_content", '');
                    $class_name = ShellCommandJobEntity::class;
                    break;
                case 'ExplodeLogJob':
                    $json['file'] = LibRequest::getRequest('file');
                    $json['left_tail_lines'] = LibRequest::getRequest("left_tail_lines", 0);
                    $json['keep_backup'] = LibRequest::getRequest('keep_backup', 'NO');

                    $json['left_tail_lines'] = intval($json['left_tail_lines'], 10);
                    $json['keep_backup'] = ($json['keep_backup'] === 'YES');

                    CommonHelper::assertNotEmpty($json['file'], 'file format incorrect');

                    $class_name = ExplodeLogJobEntity::class;
                    break;
                case 'RemoveAntiquityJob':
                    $json['files'] = LibRequest::getRequest("files", []);
                    $json['keep_days'] = LibRequest::getRequest("keep_days", 0);
                    $json['date_format'] = LibRequest::getRequest("date_format", 'Y-m-d');

                    $class_name = RemoveAntiquityJobEntity::class;
                    break;
                default:
                    throw new \Exception("unsupported job type");
            }
            if (!$job_entity) {
                $job_entity = new $class_name($json);
            } else {
                $job_entity->updateFromArray($json);
            }
            $done = $this->jobLibrary->writeEntity($job_entity);
            if (!$done) {
                throw new \Exception("update job failed");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function changeJobRunningSwitch()
    {
        try {
            $job_name = LibRequest::getRequest("job_name");
            $stop_it = LibRequest::getRequest("stop_it", 'YES');
            CommonHelper::assertNotEmpty($job_name, "job name should not be empty");
            if (!in_array($stop_it, ['YES', 'NO'])) {
                throw new \Exception("Field stop_it accepts only YES / NO.");
            }

            $job_entity = $this->jobLibrary->readEntityByName($job_name);
            if ($stop_it === 'YES') {
                $job_entity->stopped = true;
            } else {
                $job_entity->stopped = false;
            }
            $done = $this->jobLibrary->writeEntity($job_entity);
            if (!$done) {
                throw new \Exception("update job failed");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}