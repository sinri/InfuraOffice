<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/23
 * Time: 13:59
 */

namespace sinri\InfuraOffice\entity;


use sinri\enoch\helper\CommonHelper;

abstract class EntityInterface
{
    /**
     * @param null $keyChain
     * @return array
     */
    abstract public function propertiesAndDefaults($keyChain = null);

    abstract public function primaryKey();

    protected $properties = [];

    public function __get($name)
    {
        return CommonHelper::safeReadArray($this->properties, $name, $this->propertiesAndDefaults([$name]));
    }

    public function __isset($name)
    {
        return isset($this->properties, $name);
    }

    public function __set($name, $value)
    {
        CommonHelper::safeWriteNDArray($this->properties, [$name], $value);
    }

    /**
     * EntityInterface constructor.
     * @param array $json
     */
    public function __construct($json)
    {
        foreach ($this->propertiesAndDefaults() as $key => $default) {
            $this->$key = CommonHelper::safeReadArray($json, $key, $default);
        }
    }

    /**
     * @return array
     */
    public function toJsonObject()
    {
        $json = [];
        foreach ($this->propertiesAndDefaults() as $key => $default) {
            CommonHelper::safeWriteNDArray($json, [$key], $this->$key);
        }
        return $json;
    }

    /**
     * @param $array
     */
    public function updateFromArray($array)
    {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
    }
}