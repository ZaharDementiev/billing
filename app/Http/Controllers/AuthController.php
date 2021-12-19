<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $errors = $this->validator($request->all())->errors()->all();
        if ($errors) return response()->json($errors,400);

        $data = $request->all();

        return User::create([
            'name' => $data['name'] ?? null,
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
        ]);
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        if ($user = User::where('email', $email)->first()) {
            if (Hash::check($password, $user->password)) {
                Auth::login($user);

                return true;
            }
        }

        return false;
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);
    }
}
