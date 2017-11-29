<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 09:55
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\entity\UserEntity;

class UserLibrary extends AbstractEntityLibrary
{


    public function getAspectName()
    {
        return "User";
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
     * @return bool|UserEntity
     */
    public function readEntityByName($platform_name)
    {
        return parent::readEntityByName($platform_name);
    }

    /**
     * @param $platform_name_hash
     * @return bool|UserEntity
     */
    public function readEntityByNameHashed($platform_name_hash)
    {
        return parent::readEntityByNameHashed($platform_name_hash);
    }

    /**
     * @param UserEntity $entity
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


    ////

    /**
     * @return bool If the admin user entity written
     */
    public function initializeAdminUser()
    {
        $admin_entity = $this->readEntityByName("admin");
        if (!$admin_entity) {
            $admin_entity = new UserEntity([
                'username' => 'admin',
                'role' => UserEntity::ROLE_ADMIN,
            ]);
            $admin_entity->updateAuthHashForPassword("InGodWeTrust");
            return $this->writeEntity($admin_entity);
        }
        return true;
    }
}