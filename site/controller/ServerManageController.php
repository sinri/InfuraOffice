<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 09:15
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\ServerLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class ServerManageController extends BaseController
{
    protected $serverLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole(UserEntity::ROLE_ADMIN, true);

        $this->serverLibrary = new ServerLibrary();
    }


    public function updateServer()
    {
        try {
            $server_name = LibRequest::getRequest("server_name");
            $connect_ip = LibRequest::getRequest("connect_ip");
            $ssh_user = LibRequest::getRequest("ssh_user");

            $done = $this->serverLibrary->updateServer($server_name, $connect_ip, $ssh_user);
            if (!$done) {
                throw new \Exception("cannot update server");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function deleteServer()
    {
        try {
            $server_name = LibRequest::getRequest("server_name");
            $done = $this->serverLibrary->removeServer($server_name);
            if (!$done) {
                throw new \Exception("cannot remove server");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}