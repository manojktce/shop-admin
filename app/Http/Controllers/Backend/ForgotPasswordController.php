<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function showForm(Request $request)
    {
        return view('backend.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->with('danger', 'Email not found.');
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );
        
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Password reset link sent to your email.');
        }
        
        return back()->with('danger', 'Something went wrong.');
    }
}
