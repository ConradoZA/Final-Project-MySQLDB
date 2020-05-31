<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\User;
use App\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    private function ERROR_MESSAGE($e)
    {
        return response($e->getMessage(), 500);
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
            ]);
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response([
                    'message' => 'No existe un usuario con ese e-mail.'
                ], 404);
            }
            $random = STR::random(60);
            $passwordReset = PasswordReset::updateOrCreate(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => $random
                ]
            );
            if ($user && $passwordReset) {
                $user->notify(
                    new PasswordResetRequest($passwordReset->token)
                );
            }
            return response([
                'message' => 'Te hemos enviado un correo.'
            ]);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }

    public function find($token)
    {
        try {
            $passwordReset = PasswordReset::where('token', $token)->first();
            if (!$passwordReset) {
                return response([
                    'message' => 'El token usado es inválido.'
                ], 404);
            }
            if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
                $passwordReset->delete();
                return response([
                    'message' => 'El token usado es inválido.'
                ], 404);
            }
            return response($passwordReset);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }

    public function reset(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[#$^+=!*()@%&]).{6,}$/'),
                'token' => 'required|string'
            ]);
            $body = $request->only(['email', 'password', 'token']);
            $passwordReset = PasswordReset::where([['token', $body['token']], ['email', $body['email']]])->first();
            if (!$passwordReset) {
                return response([
                    'message' => 'El token usado es inválido.'
                ], 404);
            }
            $user = User::where('email', $passwordReset->email)->first();
            if (!$user) {
                return response([
                    'message' => 'No existe un usuario con ese e-mail.'
                ], 404);
            }
            $user->password = Hash::make($body['password']);
            $user->save();
            $passwordReset->delete();
            $user->notify(new PasswordResetSuccess($passwordReset));
            return response($user);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
}
