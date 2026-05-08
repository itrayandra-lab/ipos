<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if($user) {
            if (in_array($user->role, ['super_admin', 'store_manager', 'finance', 'admin', 'sales'])) {
                return redirect()->intended('/admin');
            }
        }
        return view('auth.index');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->to('/')
                ->withErrors(['message' => 'Email & Password wajib diisi!'])
                ->withInput($request->all());
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (in_array($user->role, ['super_admin', 'store_manager', 'finance', 'admin', 'sales'])) {
                return redirect()->intended('/admin');
            } else {
                Auth::logout();
                return redirect()->to('/')
                    ->withErrors(['message' => 'Role tidak dikenali']);
            }
        }

        return redirect()->to('/')
            ->withErrors(['message' => 'Email atau Password salah'])
            ->withInput($request->all());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }
}
