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
     * @param bool $object_id_hashed
     * @return string
     */
    protected static function getObjectFilePath($aspect, $object_id, $object_id_hashed = false)
    {
        return __DIR__ . '/../data/' . $aspect . '/' . ($object_id_hashed ? $object_id : md5($object_id)) . '.data';
    }

    /**
     * @param $aspect
     * @param bool $returnFullPath
     * @return array
     */
    public static function getObjectList($aspect, $returnFullPath = true)
    {
        $prefix = __DIR__ . '/../data/' . $aspect . '/';
        $tail = '.data';
        $list = glob($prefix . '*' . $tail);

        if ($returnFullPath) {
            return $list;
        }

        $ids = [];
        foreach ($list as $item) {
            $ids[] = substr($item, strlen($prefix), strlen($item) - strlen($prefix) - strlen($tail));
        }

        return $ids;
    }

    /**
     * @param string $aspect
     * @param string $object_id
     * @param bool $object_id_has_hashed
     * @return bool
     */
    public static function removeObject($aspect, $object_id, $object_id_has_hashed = false)
    {
        $path = self::getObjectFilePath($aspect, $object_id, $object_id_has_hashed);
        return @unlink($path);
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
     * @param bool $object_id_hashed
     * @return array|bool|int|null|string
     */
    public static function readObject($aspect, $object_id, $object_id_hashed = false)
    {
        $path = self::getObjectFilePath($aspect, $object_id, $object_id_hashed);
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