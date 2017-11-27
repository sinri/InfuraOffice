<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 15:51
 */

namespace sinri\InfuraOffice\job;


class ShellCommandJob extends AbstractJob
{

    /**
     * @return string
     */
    public function JobType()
    {
        return "ShellCommand";
    }

    /**
     * @param string $output
     * @return bool
     */
    public function execute(&$output = null)
    {
        // TODO: Implement execute() method.
        return false;
    }
}