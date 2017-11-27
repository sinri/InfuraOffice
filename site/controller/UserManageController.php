<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/23
 * Time: 13:49
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\SessionLibrary;
use sinri\InfuraOffice\library\UserLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class UserManageController extends BaseController
{
    protected $sessionLibrary;
    protected $userLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole(UserEntity::ROLE_ADMIN, true);
        $this->sessionLibrary = new SessionLibrary();
        $this->userLibrary = new UserLibrary();
    }

    /**
     * http://localhost/PHPStorm/InfuraOffice/site/api/UserManageController/users
     */
    public function users()
    {
        $users = $this->userLibrary->entityList();

//        $list = SecurityDataAgent::getObjectList(SessionLibrary::STORE_ASPECT_USER, false);
//
//        $users = [];
//        foreach ($list as $hashed_user_id) {
//            $user_entity = $this->sessionLibrary->getUserEntityByNameHash($hashed_user_id);
//            if ($user_entity) {
//                $users[$user_entity->username] = [
//                    'username' => $user_entity->username,
//                    'role' => $user_entity->role,
//                    'privileges' => $user_entity->privileges,
//                    'last_login_ip' => $user_entity->last_login_ip,
//                    'last_login_time' => $user_entity->last_login_time,
//                ];
//            }
//        }

        $this->_sayOK(['list' => $users]);
    }

    public function updateUser()
    {
        try {
            $username = LibRequest::getRequest("username", '');
            $password = LibRequest::getRequest("password", '');
            $role = LibRequest::getRequest("role", '');
            $privileges = LibRequest::getRequest('privileges', []);

            if (!is_string($username) || $username === '') {
                throw new \Exception("Field username not correct!");
            }

            if ($username === 'admin' && $role != UserEntity::ROLE_ADMIN) {
                throw new \Exception("Admin is admin. Rebellion?");
            }

            $user_entity = $this->userLibrary->readEntityByName($username);
            if (!$user_entity) {
                $user_entity = new UserEntity(['username' => $username]);
            }
            if ($password !== '') $user_entity->updateAuthHashForPassword($password);
            $user_entity->role = $role;
            $user_entity->privileges = $privileges;

            $done = $this->userLibrary->writeEntity($user_entity);
            if (!$done) {
                throw new \Exception("cannot update user");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function deleteUser()
    {
        try {
            $username = LibRequest::getRequest("username", '');
            if (!is_string($username) || $username === '' || $username === 'admin') {
                throw new \Exception("Field username not correct!");
            }
            $done = $this->userLibrary->removeEntity($username);
            if (!$done) {
                throw new \Exception("cannot remove user");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}