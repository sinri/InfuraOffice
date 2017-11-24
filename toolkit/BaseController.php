<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/23
 * Time: 09:11
 */

namespace sinri\InfuraOffice\toolkit;


use sinri\enoch\helper\CommonHelper;
use sinri\enoch\mvc\SethController;
use sinri\InfuraOffice\entity\UserEntity;

class BaseController extends SethController
{
    /**
     * @var UserEntity|null
     */
    protected $currentUserEntity = null;

    public function __construct($initData = null)
    {
        parent::__construct($initData);

        $this->currentUserEntity = CommonHelper::safeReadArray($initData, 'current_user');
    }

    /**
     * @param string|string[] $roles
     * @param bool $needExit
     * @return bool
     */
    public function isCurrentUserRole($roles, $needExit = false)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->isCurrentUserRole($role)) {
                    return true;
                }
            }
            if ($needExit) {
                $this->_sayFail("Role not in " . implode(', ', $roles) . "!");
                exit();
            } else {
                return false;
            }
        } else {
            $pass = ($this->currentUserEntity && $this->currentUserEntity->role === $roles);
            if ($needExit && !$pass) {
                $this->_sayFail("Not Admin!");
                exit();
            }
        }
        return $pass;
    }

}