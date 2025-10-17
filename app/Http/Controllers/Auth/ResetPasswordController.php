<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    public function create(Request $request)
    {
        $email = $request->query('email');
        $token = $request->route('token');
        
        return view('content.authentications.auth-reset-password-cover', [
            'email' => $email, // Pass the $email variable
            'token' => $token, // Pass the $token variable
        ]);
    }
}
