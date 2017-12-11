<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 21:02
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\SessionEntity;
use sinri\InfuraOffice\security\SecurityDataAgent;

class SessionLibrary extends AbstractEntityLibrary
{


    /**
     * @param string $username
     * @param string $token
     * @param int $expiration
     * @param string $ip
     * @return bool
     */
    public function createSession($username, $token, $expiration, $ip)
    {
        $session_entity = new SessionEntity([
            'username' => $username,
            'token' => $token,
            'expiration' => $expiration,
            'ip' => $ip,
        ]);
        $json = $session_entity->toJsonObject();
        return SecurityDataAgent::writeObject($this->getAspectName(), $session_entity->token, $json);
    }

    /**
     * @deprecated use readEntityByName instead
     * @param $token
     * @return bool|SessionEntity
     */
    public function loadSessionByToken($token)
    {
        $info = SecurityDataAgent::readObject($this->getAspectName(), $token);
        if (empty($info)) return false;
        return new SessionEntity($info);
    }

    public function getAspectName()
    {
        return "Session";
    }

    /**
     * @return array
     */
    public function entityArrayList()
    {
        return parent::entityArrayList();
    }

    /**
     * @return SessionEntity[]
     */
    public function entityList()
    {
        $list = parent::entityList();
        $list = array_merge([], $list);
        return $list;
    }

    /**
     * @param $name
     * @return bool|SessionEntity
     */
    public function readEntityByName($name)
    {
        return parent::readEntityByName($name);
    }

    /**
     * @param $name_hash
     * @return bool|SessionEntity
     */
    public function readEntityByNameHashed($name_hash)
    {
        return parent::readEntityByNameHashed($name_hash);
    }

    /**
     * @param SessionEntity $entity
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
        return parent::removeEntity($name);
    }
}