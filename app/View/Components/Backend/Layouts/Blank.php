<?php

namespace App\View\Components\Backend\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Blank extends Component
{
    public function __construct()
    {

    }

    public function render(): View|Closure|string
    {
        return view('backend.components.layouts.blank');
    }
}
