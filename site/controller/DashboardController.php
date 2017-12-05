<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 16:19
 */

namespace sinri\InfuraOffice\site\controller;


use sinri\InfuraOffice\library\DatabaseLibrary;
use sinri\InfuraOffice\library\JobLibrary;
use sinri\InfuraOffice\library\ServerGroupLibrary;
use sinri\InfuraOffice\library\ServerLibrary;
use sinri\InfuraOffice\toolkit\BaseController;

class DashboardController extends BaseController
{
    public function __construct($initData = null)
    {
        parent::__construct($initData);
    }

    public function index()
    {
        echo __METHOD__;
    }

    public function stat()
    {
        $server_group_entity_list = (new ServerGroupLibrary())->entityList();
        $server_entity_list = (new ServerLibrary())->entityList();
        $database_entity_list = (new DatabaseLibrary())->entityList();
        $job_entity_list = (new JobLibrary())->entityList();

        $this->_sayOK([
            'server_group_count' => count($server_group_entity_list),
            'server_count' => count($server_entity_list),
            'database_count' => count($database_entity_list),
            'job_count' => count($job_entity_list),
        ]);
    }
}