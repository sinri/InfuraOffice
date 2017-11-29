<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 11:29
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\InfuraOffice\entity\UserEntity;
use sinri\InfuraOffice\library\PlatformLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class PlatformWorkController extends BaseController
{
    protected $platformLibrary;

    public function __construct($initData = null)
    {
        parent::__construct($initData);
        $this->isCurrentUserRole([UserEntity::ROLE_ADMIN, UserEntity::ROLE_WORKER], true);
        $this->platformLibrary = new PlatformLibrary();
    }

    public function platforms()
    {
        try {
            $list = $this->platformLibrary->entityArrayList();
            $this->_sayOK(['list' => $list]);
        } catch (\Exception $exception) {
            $this->_sayFail($exception->getMessage());
        }
    }
}