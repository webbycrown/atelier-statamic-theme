<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->only(['name', 'email', 'password']);
        $validator = Validator::make($data, [
            'name' => 'required|string|max:120',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $customers = session('yoga_customers', []);
        $customers[$data['email']] = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ];
        session(['yoga_customers' => $customers]);
        session(['yoga_customer' => ['name' => $data['name'], 'email' => $data['email']]]);

        return redirect('/my-account');
    }

    public function login(Request $request)
    {
        $data = $request->only(['email', 'password']);
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $customers = session('yoga_customers', []);
        $user = $customers[$data['email']] ?? null;
        if (!$user && $data['email'] === 'admin@example.com' && $data['password'] === 'password') {
            $user = ['name' => 'Pranavya Guest', 'email' => $data['email'], 'password' => $data['password']];
        }
        if (!$user || $user['password'] !== $data['password']) {
            return back()->withErrors(['email' => 'The email or password is incorrect.'])->withInput();
        }

        session(['yoga_customer' => ['name' => $user['name'], 'email' => $user['email']]]);
        return redirect('/my-account');
    }

    public function logout()
    {
        session()->forget('yoga_customer');
        return redirect('/login');
    }

    public function forgot(Request $request)
    {
        $validator = Validator::make($request->only('email'), ['email' => 'required|email']);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        return redirect('/forgot-password')->with('status', 'If that email is registered, reset instructions are on the way.');
    }
}
