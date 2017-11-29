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
    //const STORE_ASPECT_USER = "User";
    //const STORE_ASPECT_SESSION = "Session";

//    /**
//     * @deprecated
//     * @param UserEntity $userEntity
//     * @return bool
//     */
//    public function storeUser($userEntity)
//    {
//        $json = $userEntity->toJsonObject();
//        return SecurityDataAgent::writeObject(self::STORE_ASPECT_USER, $userEntity->username, $json);
//    }

//    /**
//     * @param string $username
//     * @return bool
//     */
//    public function removeUserByName($username)
//    {
//        return SecurityDataAgent::removeObject(self::STORE_ASPECT_USER, $username);
//    }

//    /**
//     * @param $username
//     * @return bool|UserEntity
//     */
//    public function getUserEntity($username)
//    {
//        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_USER, $username);
//        if (empty($info)) return false;
//        return new UserEntity($info);
//    }

//    /**
//     * @param $username_hash
//     * @return bool|UserEntity
//     */
//    public function getUserEntityByNameHash($username_hash)
//    {
//        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_USER, $username_hash, true);
//        if (empty($info)) return false;
//        return new UserEntity($info);
//    }



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
     * @param $platform_name
     * @return bool|SessionEntity
     */
    public function readEntityByName($platform_name)
    {
        return parent::readEntityByName($platform_name);
    }

    /**
     * @param $platform_name_hash
     * @return bool|SessionEntity
     */
    public function readEntityByNameHashed($platform_name_hash)
    {
        return parent::readEntityByNameHashed($platform_name_hash);
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