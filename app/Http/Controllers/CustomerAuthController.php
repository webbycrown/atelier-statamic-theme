<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    private const ACCOUNTS_KEY = 'atelier_customer_accounts';
    private const USER_KEY = 'atelier_customer';
    private const NAME_KEY = 'atelier_customer_name';
    private const EMAIL_KEY = 'atelier_customer_email';

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

        $customers = session(self::ACCOUNTS_KEY, []);
        $customers[$data['email']] = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];
        session([self::ACCOUNTS_KEY => $customers]);
        $this->storeIdentity($data['name'], $data['email']);

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

        $customers = session(self::ACCOUNTS_KEY, []);
        $user = $customers[$data['email']] ?? null;
        $hash = is_array($user) ? (string) ($user['password'] ?? '') : '';

        if (! $user || $hash === '' || ! Hash::check($data['password'], $hash)) {
            return back()->withErrors(['email' => 'The email or password is incorrect.'])->withInput();
        }

        $this->storeIdentity($user['name'], $user['email']);

        return redirect('/my-account');
    }

    public function logout()
    {
        session()->forget([self::USER_KEY, self::NAME_KEY, self::EMAIL_KEY]);

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

    private function storeIdentity(string $name, string $email): void
    {
        session([
            self::USER_KEY => ['name' => $name, 'email' => $email],
            self::NAME_KEY => $name,
            self::EMAIL_KEY => $email,
        ]);
    }
}
