<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct() {
        $this->middleware('auth:api', ['except' => ['create', 'login']]);
    }

    public function create(Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            $array['error'] = 'Dados Incorretos!';
            return $array;
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        if (User::where('email', $email)->count() > 0) {
            $array['error'] = 'E-mail já cadastrado!';
            return $array;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $newUser = new User();
        $newUser->name = $name;
        $newUser->email = $email;
        $newUser->password = $hash;
        $newUser->save();

        $token = auth()->attempt([
            'email' => $email,
            'password' => $password
        ]);

        if (!$token) {
            $array['error'] = 'Ocorreu erro!';
            return $array;
        }

        $array['data'] = auth()->user();
        $array['token'] = $token;

        return $array;
    }
}
