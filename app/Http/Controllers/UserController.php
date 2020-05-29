<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmEmail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public $FRONT_URI;
    public function __construct()
    {
        $this->FRONT_URI = env('FRONT_URI', 'https:');
    }
    private function ERROR_MESSAGE($e, $message)
    {
        return response([
            'message' => $message,
            'error' => $e->getMessage()
        ], 500);
    }
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:20',
                'email' => 'required|email|unique:users',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,16}$/')
            ]);
            $body = $request->only(['name', 'email', 'password']);
            $body['password'] = Hash::make($body['password']);
            $user = User::create($body);
            return response($user, 201);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'Hubo un error al crear el usuario');
        }
    }
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,16}$/')
            ]);
            $credentials = $request->only(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return response([
                    'message' => 'Usuario o contraseña incorrectos'
                ], 400);
            }
            $user = Auth::user();
            $token = $user->createToken('authToken')->accessToken;
            $user->token = $token;
            return response($user);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'Hubo un error al conectarse');
        }
    }
    public function logout()
    {
        try {
            Auth::user()->token()->delete();
            return response([
                'message' => 'Usuario desconectado'
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'Hubo un error al desconectarse');
        }
    }
    public function updateUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'string|max:20',
                'email' => 'email|unique:users',
                'password' => array('string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,16}$/'),
                'image_path' => 'string'
            ]);
            $body = $request->only(['name', 'email', 'password', 'image_path']);
            if ($body['password']) {
                $body['password'] = Hash::make($body['password']);
            }
            $user = Auth::user();
            $user->update($body);
            return response([
                'user' => $user,
                'message' => 'Datos del usuario actualizados',
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'Hubo un error al actualizar los datos');
        }
    }
    public function deleteUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:20',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,16}$/')
            ]);
            $user = Auth::user();
            $user->delete;
            return response([
                'message' => 'Usuario borrado con éxito',
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'No se pudo borrar el usuario');
        }
    }
    public function getProfile(Request $request)
    {
        try {
            $user = Auth::user();
            return response([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'No se pudo acceder al usuario');
        }
    }
    public function uploadImage(Request $request)
    {
        try {

            $request->validate([
                'image' => 'required|image|max:2048|unique:users,image_path'
            ]);
            $file = $request->file('image');
            $image_name = $file->getClientOriginalName();
            $file->move('images', $image_name);
            $user = Auth::user();
            $user->update(['image_path' => $image_name]);
            return response([
                'user' => $user,
                'message' => 'Imagen subida con éxito'
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'No se pudo subir la imagen');
        }
    }
    public function sendConfirmEmail()
    {
        try {
            //ToDo: poner bien la dirección total del componente de confirmar contraseña
            $link = $FRONT_URI . '/' . Auth::token();
            Mail::to(Auth::email())->send(new ConfirmEmail($link));
            return response([
                'message' => 'Email enviado'
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'No se pudo enviar el mail');
        }
    }
    public function emalConfirmed()
    {
        try {
            $user = Auth::user();
            $user->update(['email_verified' => true]);
            return response([
                'user' => $user,
                'message' => 'Email verificado'
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'No se pudo verificar el mail');
        }
    }
    public function sendRecoverPasswordEmail(Request $request)
    {
        try {
            //ToDo: poner bien la dirección total del componente de confirmar contraseña
            $link = $this->FRONT_URI . '/' . Auth::token();
            Mail::to(Auth::email())->send(new ConfirmEmail($link));
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e, 'No se pudo enviar el mail');
        }
    }
}
