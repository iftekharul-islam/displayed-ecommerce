<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\ForgotPasswordNotification;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{

    public function login(LoginRequest $request)
    {

        try {
            $validated = $request->validated();

            $user = User::query()->where(['email' => $validated['email']])->first();

            if (!$user) {
                abort(403, 'User not found');
            }

            if (!@$user->is_active) {
                abort(403, 'User is not active');
            }

            if (!$user || !Hash::check($validated['password'], @$user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            return response()->json([
                'access_token' => $user->createToken(config('app.access_token_key'))->plainTextToken
            ]);
        } catch (HttpException $th) {
            logExceptionInSlack($th);

            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function user()
    {
        try {
            $data = Auth::user()->load('roles.permissions');

            return new UserResource($data);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function logout()
    {
        try {
            Auth::user()->tokens()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {

        try {
            $validated = $request->validated();

            $passwordResetsToken = DB::table('password_reset_tokens')->where(['email' => $validated['email']])->first();

            if (@$passwordResetsToken) {
                DB::table('password_reset_tokens')->where(['email' => $validated['email']])->delete();
            }

            $user = User::query()->where(['email' => $validated['email']])->first();

            if (!@$user) {
                abort(403, 'The provided credentials are incorrect');
            }

            if (!@$user->is_active) {
                abort(403, 'User is not active');
            }

            $token = Str::random(60);

            DB::table('password_reset_tokens')->insert([
                'email' => $validated['email'],
                'token' => $token,
                'created_at' => now()
            ]);

            $data = [
                'token' => $token,
                'email' => $validated['email'],
            ];

            Notification::send($user, new ForgotPasswordNotification($data));

            return response()->json([
                "message" => 'We have e-mailed your password reset link!'
            ]);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {

        try {
            $validated = $request->validated();

            $updatePassword = DB::table('password_reset_tokens')
                ->where([
                    'email' => $validated['email'],
                    'token' => $validated['token']
                ])
                ->first();

            if (!@$updatePassword) {
                abort(403, 'Invalid token!');
            }

            $user = User::query()->where(['email' => $validated['email']])->first();

            if (!@$user) {
                abort(403, 'The provided credentials are incorrect');
            }

            if (!@$user->is_active) {
                abort(403, 'User is not active');
            }

            DB::transaction(function () use ($user, $validated) {
                $user->update(['password' => $validated['password']]);

                DB::table('password_reset_tokens')->where(['email' => $validated['email']])->delete();
            });

            Notification::send($user, new ResetPasswordNotification());

            return response()->json([
                "message" => 'Your password has been changed!'
            ]);
        } catch (HttpException $th) {
            logExceptionInSlack($th);
            Log::error($th);
            abort($th->getStatusCode(), $th->getMessage());
        }
    }
}
