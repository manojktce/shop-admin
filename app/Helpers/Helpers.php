<?php

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

function baseURL($param = '') 
{
    $adminPath = env('ADMIN_PATH');

    if (str_contains(url()->current(), '/' . $adminPath)) {
        return url($adminPath . '/' . ltrim($param, '/'));
    }

    return url($param);
}

function dateFormat($type, $value) 
{
    if ($type == 1) return Carbon::parse($value)->format('Y-m-d');
    else if ($type == 2) return Carbon::parse($value)->format('Y-m-d H:i:s');
    else if ($type == 3) return Carbon::parse($value)->format('d/m/Y');
    else if ($type == 4) return Carbon::parse($value)->format('d/m/Y H:i:s');
}

function badges($type, $value) 
{
    if ($type == 'status') {
        $badgeClass = 'primary';
        $badgeValue = 'Active';

        if ($value == 0) {
            $badgeClass = 'danger';
            $badgeValue = 'InActive';
        }
    } 
        
    return "<span class='fs-2 badge text-bg-{$badgeClass}'>".ucfirst($badgeValue)."</span>";
}

function fileExists($file) 
{
    if ($file != '') {
        $file = str_replace(url('/storage'), '', $file);
        
        if (Storage::disk('public')->exists($file)) {
            return true;
        } 
    }
    
    return false;
}

function apiResponse($status, $message, $result, $code = 200)
{
    return response()->json(["status" => $status, "message" => $message, "result" => $result], $code);  
}