<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmEmail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public $FRONT_URI;
    public function __construct()
    {
        $this->FRONT_URI = env('FRONT_URI', 'http://localhost:3000');
    }
    private function ERROR_MESSAGE($e)
    {
        return response($e->getMessage(), 500);
    }
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:20',
                'email' => 'required|email|unique:users',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[#$^+=!*()@%&]).{6,}$/'),
            ]);
            $body = $request->only(['name', 'email', 'password']);
            $body['password'] = Hash::make($body['password']);
            $body['image_path'] = "profile.jpg";
            $user = User::create($body);
            return response($user, 201);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[#$^+=!*()@%&]).{6,}$/'),
            ]);
            $credentials = $request->only(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return response([
                    'message' => 'Usuario o contraseña incorrectos',
                ], 400);
            }
            $user = Auth::user();
            $token = $user->createToken('authToken')->accessToken;
            $user->token = $token;
            return response($user);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function logout()
    {
        try {
            Auth::user()->token()->delete();
            return response([
                'message' => 'Usuario desconectado',
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function updateUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'string|max:20',
                'email' => 'email',
                'password' => array('string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[#$^+=!*()@%&]).{6,}$/'),
                'image_path' => 'string',
            ]);

            $body = $request->only(['name', 'email', 'password', 'image_path']);

            if (array_key_exists('password', $body)) {
                $body['password'] = Hash::make($body['password']);
            }
            $user = Auth::user();
            User::where('id', $user->id)->update($body);
            $user = DB::table('users')->where('id', $user->id)->get();
            return response($user);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function deleteUser(Request $request)
    {
        try {
            $user = Auth::user();
            $user->delete();
            return response([
                'message' => 'Usuario borrado con éxito',
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function getProfile()
    {
        try {
            $user = Auth::user();
            return response([
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function getAll()
    {
        try {
            $users = User::all();
            return response([
                'users' => $users,
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function uploadImage(Request $request)
    {
        try {

            $request->validate([
                'image' => 'required|image|max:2048|unique:users,image_path',
            ]);
            $file = $request->file('image');
            $user = Auth::user();
            $uri_exists = Str::contains($user['image_path'], 'http');
            if ($user['image_path'] && !$uri_exists && $user['image_path'] !== "profile.jpg") {
                //ToDo: En producción es posible que haya que poner public_path('images/' . $user->image_path)
                $image_path = 'images/' . $user->image_path;
                unlink($image_path);
            }
            $image_name = $file->getClientOriginalName();
            $file->move('images', $image_name);

            $user->update(['image_path' => $image_name]);
            return response([
                'user' => $user,
                'message' => 'Imagen subida con éxito',
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function sendConfirmEmail()
    {
        try {
            $token = Auth::user()->token()->get();
            $token = $token[0]->id;
            $link = $this->FRONT_URI . '/confirm/' . $token;
            Mail::to(Auth::user()->email)->send(new ConfirmEmail($link));
            return response([
                'message' => 'Email enviado',
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
    public function mailConfirmed(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string',
            ]);
            $user = Auth::user();
            $real_token = Auth::user()->token()->first();
            $token = $request->token;
            if (!$token === $real_token) {
                return response([
                    'message' => 'El token usado no es válido.',
                ], 500);
            }
            User::where('id', $user->id)->update(["email_verified" => true]);
            $user = DB::table('users')->where('id', $user->id)->get();
            return response($user);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
}
