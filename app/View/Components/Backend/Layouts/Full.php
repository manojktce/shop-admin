<?php

namespace App\View\Components\Backend\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Full extends Component
{
    public function __construct()
    {

    }

    public function render(): View|Closure|string
    {
        $data['user'] = auth()->user();

        return view('backend.components.layouts.full', $data);
    }
}
