<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BaseController;

class DashboardController extends BaseController
{
    protected $module = 'dashboard';
    protected $isDataTable = false;
    protected $view = 'dashboard.index';
}
