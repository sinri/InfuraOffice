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

    /**
     * @param $status
     * @return string
     */
    public static function describePcntlWaitStatus($status)
    {
        // pcntl_wifexited(), pcntl_wifstopped(), pcntl_wifsignaled(), pcntl_wexitstatus(), pcntl_wtermsig() and pcntl_wstopsig().
        $str = "";
        if (pcntl_wifexited($status)) {
            $str .= "The child status code represents a normal exit.";
        }
        if (pcntl_wifstopped($status)) {
            $str .= "The child process is currently stopped by signal " . pcntl_wstopsig($status) . ".";
        }
        if (pcntl_wifsignaled($status)) {
            $str .= "The child process exited because of an uncaught signal " . pcntl_wtermsig($status) . ".";
        }
        $str .= "The return code of a terminated child is " . pcntl_wexitstatus($status);
        return $str;
    }
}