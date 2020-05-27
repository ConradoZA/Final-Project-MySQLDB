<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:20',
                'email' => 'required|email|unique:users',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,16}$/g')
            ]);
            $body = $request->only(['name', 'email', 'password']);
            $body['password'] = Hash::make($body['password']);
            $user = User::create($body);
            return response($user, 201);
        } catch (\Exception $e) {
            return response([
                'message' => 'Hubo un error al crear el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,16}$/g')
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
            return response([
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => 'Hubo un error al conectarse',
                'error' => $e->getMessage()
            ], 500);
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
            return response([
                'message' => 'Hubo un error al desconectarse',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateUser(Request $request)
    {
        //ToDo: Confirmar que si no hay password no tira excepción
        try {
            $request->validate([
                'name' => 'string|max:20',
                'email' => 'email|unique:users',
                'password' => array('string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,16}$/g'),
                'image_path' => 'string'
            ]);
            $body = $request->only(['name', 'email', 'password', 'image_path']);
            $body['password'] = Hash::make($body['password']);
            $user = Auth::user();
            $user->update($body);
            return response([
                'user' => $user,
                'message' => 'Datos del usuario actualizados',
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => 'Hubo un error al actualizar los datos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function deleteUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:20',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$^+=!*()@%&]).{8,16}$/g')
            ]);
            $user = Auth::user();
            $user->delete;
            return response([
                'message' => 'Usuario borrado con éxito',
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => 'No se pudo borrar el usuario',
                'error' => $e->getMessage()
            ], 500);
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
            return response([
                'message' => 'No se pudo acceder al usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function uploadImage(Request $request)
    {
        try {

            //ToDo: averiguar lo de que se esperaba un objeto y obtiene un array y CÓMO SOLUCIONARLO
            $request->validate([
                'image' => 'required|image|max:2048|unique:users,image_path'
            ]);
            $body = $request->only('image');
            $file = $body->file('image');
            $image_name = $file->getClientOriginalName();
            $file->move('images', $image_name);
            $user = Auth::user();
            $user['image_path']->update($image_name);
        } catch (\Exception $e) {
            return response([
                'message' => 'No se pudo subir la imagen',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
