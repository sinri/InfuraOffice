<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 15:08
 */

namespace sinri\InfuraOffice\cli\handler;


interface RequestHandlerInterface
{
    /**
     * @param $data
     * @return mixed object to be encoded into JSON finally in Daemon
     */
    public function handle($data);
}