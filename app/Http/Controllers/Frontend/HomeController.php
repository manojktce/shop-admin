<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        return view('frontend.home.index');
    }
}
