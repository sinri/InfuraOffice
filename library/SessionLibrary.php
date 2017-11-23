<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 21:02
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\security\SecurityDataAgent;

class SessionLibrary
{
    const STORE_ASPECT_USER = "user";

    /**
     * @param UserEntity $userEntity
     * @return bool
     */
    public function storeUser($userEntity)
    {
        $json = $userEntity->toJsonObject();
        return SecurityDataAgent::writeObject(self::STORE_ASPECT_USER, $userEntity->getUsername(), $json);
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

}