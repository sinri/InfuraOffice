<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/28
 * Time: 09:31
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\AbstractJobEntity;
use sinri\InfuraOffice\security\SecurityDataAgent;

class JobLibrary extends AbstractEntityLibrary
{

    /**
     * @return string
     */
    public function getAspectName()
    {
        return "Job";
    }

    /**
     * @return array
     */
    public function entityArrayList()
    {
        return parent::entityArrayList();
    }

    /**
     * @return AbstractJobEntity[]
     */
    public function entityList()
    {
        $p = parent::entityList();
        $list = array_merge($p, []);
        return $list;
    }

    /**
     * @param $name_hashed
     * @return bool|AbstractJobEntity
     * @throws \sinri\enoch\mvc\BaseCodedException
     */
    public function readEntityByNameHashed($name_hashed)
    {
        $info = SecurityDataAgent::readObject($this->getAspectName(), $name_hashed, true);
        if (empty($info)) return false;
        return AbstractJobEntity::jobFactory($info);
    }

    /**
     * @param $name
     * @return bool|AbstractJobEntity
     * @throws \sinri\enoch\mvc\BaseCodedException
     */
    public function readEntityByName($name)
    {
        $info = SecurityDataAgent::readObject($this->getAspectName(), $name);
        if (empty($info)) return false;
        return AbstractJobEntity::jobFactory($info);
    }

    /**
     * @param AbstractJobEntity $entity
     * @return bool
     */
    public function writeEntity($entity)
    {
        return parent::writeEntity($entity);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function removeEntity($name)
    {
        $done = parent::removeEntity($name);
        return $done;
    }
}