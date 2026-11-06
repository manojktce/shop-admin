<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
            
            if (Auth::attempt($credentials)) {
                if (Auth::user()->status == '1') {
                    $request->session()->regenerate();
     
                    return redirect()->intended(baseURL('dashboard'));
                } else {
                    Auth::logout();

                    return back()->with('danger', 'Your account is not active.');
                }
            }
     
            return back()->with('danger', 'Invalid Credentials.');
        }

        return view('backend.auth.login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
 
        $request->session()->invalidate();
        $request->session()->regenerateToken();
     
        return redirect(baseURL());
    }
}
