<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Traits\Backend\Crud;

class BaseController extends Controller implements HasMiddleware
{
    use Crud;
}
