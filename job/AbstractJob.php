<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 15:52
 */

namespace sinri\InfuraOffice\job;


abstract class AbstractJob
{
    protected $config = [];

    /**
     * AbstractJob constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    abstract public function JobType();

    /**
     * @param string $output
     * @return bool
     */
    abstract public function execute(&$output = null);
}