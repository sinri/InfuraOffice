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
     * @return UserEntity[]
     */
    public function entityList()
    {
        $list = parent::entityList();
        $list = array_merge([], $list);
        return $list;
    }

    /**
     * @param $name
     * @return bool|UserEntity
     */
    public function readEntityByName($name)
    {
        return parent::readEntityByName($name);
    }

    /**
     * @param $name_hash
     * @return bool|UserEntity
     */
    public function readEntityByNameHashed($name_hash)
    {
        return parent::readEntityByNameHashed($name_hash);
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