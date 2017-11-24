<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 21:02
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\SessionEntity;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\security\SecurityDataAgent;

class SessionLibrary
{
    const STORE_ASPECT_USER = "user";
    const STORE_ASPECT_SESSION = "session";

    /**
     * @param UserEntity $userEntity
     * @return bool
     */
    public function storeUser($userEntity)
    {
        $json = $userEntity->toJsonObject();
        return SecurityDataAgent::writeObject(self::STORE_ASPECT_USER, $userEntity->username, $json);
    }

    /**
     * @param string $username
     * @return bool
     */
    public function removeUserByName($username)
    {
        return SecurityDataAgent::removeObject(self::STORE_ASPECT_USER, $username);
    }

    /**
     * @param $username
     * @return bool|UserEntity
     */
    public function getUserEntity($username)
    {
        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_USER, $username);
        if (empty($info)) return false;
        return new UserEntity($info);
    }

    /**
     * @param $username_hash
     * @return bool|UserEntity
     */
    public function getUserEntityByNameHash($username_hash)
    {
        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_USER, $username_hash, true);
        if (empty($info)) return false;
        return new UserEntity($info);
    }

    /**
     * @return bool If the admin user entity written
     */
    public function initializeAdminUser()
    {
        $admin_entity = $this->getUserEntity("admin");
        if (!$admin_entity) {
            $admin_entity = new UserEntity([
                'username' => 'admin',
                'role' => UserEntity::ROLE_ADMIN,
            ]);
            $admin_entity->updateAuthHashForPassword("InGodWeTrust");
            return $this->storeUser($admin_entity);
        }
        return true;
    }

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
        return SecurityDataAgent::writeObject(self::STORE_ASPECT_SESSION, $session_entity->token, $json);
    }

    /**
     * @param $token
     * @return bool|SessionEntity
     */
    public function loadSessionByToken($token)
    {
        $info = SecurityDataAgent::readObject(self::STORE_ASPECT_SESSION, $token);
        if (empty($info)) return false;
        return new SessionEntity($info);
    }
}