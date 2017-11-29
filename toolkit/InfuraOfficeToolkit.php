<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 15:42
 */

namespace sinri\InfuraOffice\toolkit;


use sinri\enoch\core\LibLog;
use sinri\enoch\helper\CommonHelper;

class InfuraOfficeToolkit
{
    /**
     * @param array $keyChain
     * @param null $default
     * @return mixed|null
     */
    public static function readConfig($keyChain, $default = null)
    {
        $file = __DIR__ . '/../config/config.php';
        if (!file_exists($file)) {
            return $default;
        }
        $config = [];
        require $file;
        $file_config_value = CommonHelper::safeReadNDArray($config, $keyChain, $default);

        return RuntimeConfigToolkit::readRuntimeConfig($keyChain, $file_config_value);
    }

    /**
     * @param null $subPath
     * @return string
     */
    public static function tempPath($subPath = null)
    {
        return __DIR__ . '/../data/tmp' . ($subPath === null ? '' : DIRECTORY_SEPARATOR . $subPath);
    }

    private static $loggers = [];

    /**
     * @param string $prefix
     * @param bool $cliUseStdOut
     * @return LibLog
     */
    public static function logger($prefix = '', $cliUseStdOut = true)
    {
        if (!isset(self::$loggers[$prefix])) {
            $dir = self::readConfig(['log', 'dir'], __DIR__ . '/../log');
            self::$loggers[$prefix] = new LibLog($dir, $prefix, $cliUseStdOut);
        }
        return self::$loggers[$prefix];
    }
}