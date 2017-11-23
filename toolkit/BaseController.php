<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/23
 * Time: 09:11
 */

namespace sinri\InfuraOffice\toolkit;


use sinri\enoch\mvc\SethController;

class BaseController extends SethController
{
    public function __construct($initData = null)
    {
        parent::__construct($initData);
    }
}