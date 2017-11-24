<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 11:51
 */

namespace sinri\InfuraOffice\cli\daemon;


use sinri\InfuraOffice\toolkit\InfuraOfficeToolkit;

class DaemonHelper
{
    /**
     * @param string $level DEBUG INFO WARNING ERROR
     * @param string $message
     * @param string $object
     */
    public static function log($level, $message, $object = '')
    {
        $pid = getmypid();
        //echo "[" . date("Y-m-d H:i:s") . "|" . microtime(true) . "] <{$pid}:{$level}> " . $message . PHP_EOL;
        InfuraOfficeToolkit::logger('daemon')
            ->log($level, "[" . microtime(true) . "] <{$pid}> " . $message, $object);
    }

    public static function clientLog($level, $message, $object = '')
    {
        $pid = getmypid();
        InfuraOfficeToolkit::logger('daemon_client')
            ->log($level, "[" . microtime(true) . "] <{$pid}> " . $message, $object);
    }

    /**
     * @param array $signals
     * @param callable $callback
     */
    public static function defineSignalHandler($signals, $callback)
    {
        // in doc sb. saith, as of PHP 5.3, use pcntl_signal_dispatch instead
        // but that would be not able to operate the real time signal
        declare(ticks=1);
        foreach ($signals as $signal) {
            pcntl_signal($signal, $callback);
        }
    }
}