<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/29
 * Time: 22:36
 */

namespace sinri\InfuraOffice\entity;

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
     * @return mixed
     */
    public function execute()
    {
        $this->assertNotRunInLastMinute();
        $this->recordExecution();

        $report = [];
        foreach ($this->server_list as $server_name) {
            // 2.0 ssh prepare
            $report[$server_name] = [
                "output" => '',
                "error" => '',
                "done" => false,
            ];
            try {
                $ssh = self::createSSHForServer($server_name);

                //TODO

                $report[$server_name]['done'] = true;
            } catch (\Exception $exception) {
                $report[$server_name]['error'] = "JOB[{$this->job_name}]-EXCEPTION! " . $exception->getMessage();
            }

        }
    }
}