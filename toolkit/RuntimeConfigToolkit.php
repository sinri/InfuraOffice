<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/29
 * Time: 11:53
 */

namespace sinri\InfuraOffice\toolkit;


use sinri\enoch\helper\CommonHelper;

class RuntimeConfigToolkit
{

    protected static function getRuntimeConfigFilePath()
    {
        return __DIR__ . '/../data/runtime.config';
    }

    /**
     * @return array
     */
    public static function loadEntireRuntimeConfig()
    {
        $file = self::getRuntimeConfigFilePath();
        if (!file_exists($file)) {
            return [];
        }
        $config = file_get_contents($file);
        $config = json_decode($config, true);
        if (empty($config)) {
            $config = [];
        }
        return $config;
    }

    /**
     * @param array $keyChain
     * @param $value
     * @return bool
     */
    public static function writeRuntimeConfig($keyChain, $value)
    {
        $config = self::loadEntireRuntimeConfig();
        CommonHelper::safeWriteNDArray($config, $keyChain, $value);
        $written = file_put_contents(self::getRuntimeConfigFilePath(), json_encode($config), LOCK_EX);
        return !!$written;
    }

    /**
     * @param $keyChain
     * @param null $default
     * @return mixed|null
     */
    public static function readRuntimeConfig($keyChain, $default = null)
    {
        $config = self::loadEntireRuntimeConfig();
        return CommonHelper::safeReadNDArray($config, $keyChain, $default);
    }
}