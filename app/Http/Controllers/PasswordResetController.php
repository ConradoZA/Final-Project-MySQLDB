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
    public $FRONT_URI;
    public function __construct()
    {
        $this->FRONT_URI = env('FRONT_URI', 'http://localhost:3000');
    }
    private function ERROR_MESSAGE($e)
    {
        return response($e->getMessage(), 500);
    }
    private function WRONG_TOKEN()
    {
        return response([
            'message' => 'El token usado no es válido.'
        ], 500);
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
                ], 500);
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

    public function reset(Request $request)
    {
        try {
            $request->validate([
                'password' => array('required', 'string', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[#$^+=!*()@%&.,_?`´:;çºª|· ¬¡¿]).{6,}$/'),
                'token' => 'required|string'
            ]);
            $body = $request->only(['password', 'token']);
            $passwordReset = PasswordReset::where('token', $body['token'])->first();
            if (!$passwordReset) {
                return $this->WRONG_TOKEN();
            }
            if (Carbon::parse($passwordReset->updated_at)->addHours(12)->isPast()) {
                $passwordReset->delete();
                return $this->WRONG_TOKEN();
            }
            $user = User::where('email', $passwordReset->email)->first();
            if (!$user) {
                return $this->WRONG_TOKEN();
            }
            $user->password = Hash::make($body['password']);
            User::where('email', $passwordReset->email)->update(["password" => $user->password]);
            PasswordReset::where('token', $body['token'])->delete();
            $user->notify(new PasswordResetSuccess($passwordReset));
            return response($user);
        } catch (\Exception $e) {
            return $this->ERROR_MESSAGE($e);
        }
    }
}
