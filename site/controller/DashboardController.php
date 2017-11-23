<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/22
 * Time: 16:19
 */

namespace sinri\InfuraOffice\site\controller;


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
}