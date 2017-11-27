<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 11:26
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\enoch\core\LibRequest;
use sinri\InfuraOffice\entity\PlatformEntity;
use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\PlatformLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class PlatformManageController extends BaseController
{
    protected $platformLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole(UserEntity::ROLE_ADMIN, true);
        $this->platformLibrary = new PlatformLibrary();
    }

//    public function platformTypeList(){
//        $this->_sayOK([
//            'list'=>[
//                PlatformEntity::PLATFORM_IDC=>"IDC",
//                PlatformEntity::PLATFORM_ALIYUN=>'Aliyun',
//            ]
//        ]);
//    }

    public function updatePlatformAccount()
    {
        try {
            $platform_name = LibRequest::getRequest("platform_name");
            $platform_type = LibRequest::getRequest("platform_type");
            $auth_id = LibRequest::getRequest("auth_id");
            $auth_key = LibRequest::getRequest("auth_key");

            $platform_entity = new PlatformEntity([
                'platform_name' => $platform_name,
                'platform_type' => $platform_type,
                'auth_id' => $auth_id,
                'auth_key' => $auth_key,
            ]);
            $done = $this->platformLibrary->writeEntity($platform_entity);
            if (!$done) {
                throw new \Exception("cannot write platform account");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }

    public function removePlatformAccount()
    {
        try {
            $platform_name = LibRequest::getRequest("platform_name");
            $done = $this->platformLibrary->removeEntity($platform_name);
            if (!$done) {
                throw new \Exception("cannot remove platform account");
            }
            $this->_sayOK();
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}