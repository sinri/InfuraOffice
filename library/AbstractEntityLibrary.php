<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 09:27
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\EntityInterface;
use sinri\InfuraOffice\security\SecurityDataAgent;

abstract class AbstractEntityLibrary
{
    //const STORE_ASPECT_DATABASE = "database";

    /**
     * @return string
     */
    abstract public function getAspectName();

    protected function getEntityClassPath()
    {
        $class_name = 'sinri\InfuraOffice\entity\\' . $this->getAspectName() . 'Entity';
        //echo __METHOD__." ".$class_name.PHP_EOL;
        return $class_name;
    }

    /**
     * @return array
     */
    public function entityArrayList()
    {
        //echo "DEBUG : ".__METHOD__." aspect: ".$this->getAspectName().PHP_EOL;
        $entity_names = SecurityDataAgent::getObjectList($this->getAspectName(), false);
        //print_r($entity_names);
        $entities = [];
        foreach ($entity_names as $entity_name_hashed) {
            $entity = $this->readEntityByNameHashed($entity_name_hashed);

            //echo "IN FOREACH ".$entity_name_hashed." -> ".PHP_EOL;var_dump($entity);

            if (!$entity) continue;

            $entity_array = [];
            foreach ($entity->propertiesAndDefaults() as $key => $default_value) {
                $entity_array[$key] = $entity->$key;
            }

            $entities[$entity->primaryKey()] = $entity_array;
        }

        ksort($entities);
        $entities = array_values($entities);

        return $entities;
    }

    /**
     * @return EntityInterface[]
     */
    public function entityList()
    {
        //echo "DEBUG : ".__METHOD__." aspect: ".$this->getAspectName().PHP_EOL;
        $entity_names = SecurityDataAgent::getObjectList($this->getAspectName(), false);
        //print_r($entity_names);
        $entities = [];
        foreach ($entity_names as $entity_name_hashed) {
            $entity = $this->readEntityByNameHashed($entity_name_hashed);

            //echo "IN FOREACH ".$entity_name_hashed." -> ".PHP_EOL;var_dump($entity);

            if (!$entity) continue;

            $entities[$entity->primaryKey()] = $entity;
        }
        ksort($entities);
        $entities = array_values($entities);
        return $entities;
    }

    /**
     * @param $name_hashed
     * @return bool|EntityInterface
     */
    public function readEntityByNameHashed($name_hashed)
    {
        $info = SecurityDataAgent::readObject($this->getAspectName(), $name_hashed, true);
        if (empty($info)) return false;
        $class_name = $this->getEntityClassPath();
        return new $class_name($info);
    }

    /**
     * @param $name
     * @return bool|EntityInterface
     */
    public function readEntityByName($name)
    {
        $info = SecurityDataAgent::readObject($this->getAspectName(), $name);
        if (empty($info)) return false;
        $class_name = $this->getEntityClassPath();
        return new $class_name($info);
    }

    /**
     * @param EntityInterface $entity
     * @return bool
     */
    public function writeEntity($entity)
    {
        return SecurityDataAgent::writeObject($this->getAspectName(), $entity->primaryKey(), $entity->toJsonObject());
    }

    /**
     * @param string $name
     * @return bool
     */
    public function removeEntity($name)
    {
        return SecurityDataAgent::removeObject($this->getAspectName(), $name);
    }
}