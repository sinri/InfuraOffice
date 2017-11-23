<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 16:29
 */

namespace sinri\InfuraOffice\security;


class SecurityDataAgent
{
    /**
     * @param string $aspect
     * @param string $object_id
     * @return string
     */
    protected static function getObjectFilePath($aspect, $object_id)
    {
        return __DIR__ . '/../data/' . $aspect . '/' . md5($object_id) . 'db';
    }

    /**
     * @param string $aspect
     * @param string $object_id
     * @param array|string|int|bool|null $object
     * @return bool
     */
    public static function writeObject($aspect, $object_id, $object)
    {
        $original_object_json = json_encode($object);
        $encoded_content = self::encode($original_object_json);

        $path = self::getObjectFilePath($aspect, $object_id);
        if (!file_exists(dirname($path))) {
            //echo dirname($path).PHP_EOL;
            @mkdir(dirname($path), 0777, true);
        }

        $done = file_put_contents($path, $encoded_content, LOCK_EX);
        return !!$done;
    }

    /**
     * @param string $aspect
     * @param string $object_id
     * @return array|bool|int|null|string
     */
    public static function readObject($aspect, $object_id)
    {
        $path = self::getObjectFilePath($aspect, $object_id);
        if (!file_exists($path)) return null;

        $encoded_content = file_get_contents($path);
        $decoded_json = self::decode($encoded_content);

        return json_decode($decoded_json, true);
    }

    /**
     * @param string $text
     * @return string
     */
    protected static function encode($text)
    {
        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    protected static function decode($text)
    {
        return $text;
    }
}