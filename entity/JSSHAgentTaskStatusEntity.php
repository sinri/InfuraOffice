<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/23
 * Time: 11:17
 */

namespace sinri\InfuraOffice\entity;


use sinri\enoch\helper\CommonHelper;

class JSSHAgentTaskStatusEntity
{
    const STATUS_NOT_EXIST = "NOT_EXIST";
    const STATUS_PENDING = "PENDING";
    const STATUS_EXECUTING = "EXECUTING";
    const STATUS_FINISHED = "FINISHED";
    const STATUS_FETCHED = "FETCHED";

    public $output;
    public $return_value;
    public $status;

    public $outputLines;

    /**
     * @param array $result
     * @return JSSHAgentTaskStatusEntity
     */
    public static function buildFromSeverResponse($result)
    {
        $entity = new JSSHAgentTaskStatusEntity();
        $entity->status = CommonHelper::safeReadArray($result, 'status', JSSHAgentTaskStatusEntity::STATUS_NOT_EXIST);
        $entity->return_value = CommonHelper::safeReadArray($result, 'return_value');
        $entity->output = CommonHelper::safeReadArray($result, 'output');
        if ($entity->output) {
            $entity->outputLines = explode('\n', $entity->output);
        }
        return $entity;
    }
}