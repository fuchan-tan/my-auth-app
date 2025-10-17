<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TwoFactorSetup extends Controller
{
    public function index()
    {
        return view('content.authentications.auth-two-factor-setup');
    }
}
