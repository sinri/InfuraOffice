<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 09:15
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\entity\ServerEntity;
use sinri\InfuraOffice\entity\ServerGroupEntity;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\ServerGroupLibrary;
use sinri\InfuraOffice\library\ServerLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class ServerManageController extends BaseController
{
    protected $serverLibrary;
    protected $serverGroupLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole(UserEntity::ROLE_ADMIN, true);

        $this->serverLibrary = new ServerLibrary();
        $this->serverGroupLibrary = new ServerGroupLibrary();
    }


    public function updateServer()
    {
        try {
            $server_name = LibRequest::getRequest("server_name");
            $connect_ip = LibRequest::getRequest("connect_ip");
            $ssh_user = LibRequest::getRequest("ssh_user");

            $platform_device_id = LibRequest::getRequest("platform_device_id");
            $platform_name = LibRequest::getRequest("platform_name");
            $platform_area = LibRequest::getRequest("platform_area");

            $slk_paths = LibRequest::getRequest("slk_paths", []);

            CommonHelper::assertNotEmpty($server_name, 'server name should not be empty');

            $entity = new ServerEntity([
                "server_name" => $server_name,
                "connect_ip" => $connect_ip,
                "ssh_user" => $ssh_user,
                "platform_name" => $platform_name,
                "platform_device_id" => $platform_device_id,
                "platform_area" => $platform_area,
                "slk_paths" => $slk_paths,
            ]);

            $done = $this->serverLibrary->writeEntity($entity);
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

            CommonHelper::assertNotEmpty($server_name, 'server name should not be empty');

            $done = $this->serverLibrary->removeEntity($server_name);
            if (!$done) {
                throw new \Exception("cannot remove server");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function updateServerGroup()
    {
        try {
            $group_name = LibRequest::getRequest("group_name");
            $server_name_list = LibRequest::getRequest("server_name_list", []);

            CommonHelper::assertNotEmpty($group_name, 'server group name should not be empty');
            if ($group_name === 'all') throw new \Exception("You cannot use a reserved server group name!");

            $entity = new ServerGroupEntity(['group_name' => $group_name, 'server_name_list' => $server_name_list]);
            $done = $this->serverGroupLibrary->writeEntity($entity);
            if (!$done) {
                throw new \Exception("cannot update server group");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function removeServerGroup()
    {
        try {
            $group_name = LibRequest::getRequest("group_name");
            CommonHelper::assertNotEmpty($group_name, 'server group name should not be empty');
            if ($group_name === 'all') throw new \Exception("You cannot use a reserved server group name!");
            $done = $this->serverGroupLibrary->removeEntity($group_name);
            if (!$done) {
                throw new \Exception("cannot remove server group");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}