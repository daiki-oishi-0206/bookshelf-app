<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse
        {
            public function toResponse($request)
            {
                return redirect('/login');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::registerView(function () {
            return view('auth.register');
        });
        Fortify::loginView(function () {
            return view('auth.login');
        });
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        Fortify::authenticateUsing(function (Request $request) {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => [
                        'required',
                        'email',
                        'max:255',
                    ],
                    'password' => [
                        'required',
                        'min:8',
                        'max:255',
                    ],
                ],
                [
                    'email.required' => 'メールアドレスは必須です。',
                    'email.email' => 'メールアドレスはメールアドレス形式で入力してください',
                    'email.max' => 'メールアドレスは255文字以内で入力してください',

                    'password.required' => 'パスワードは必須です。',
                    'password.min' => 'パスワードは8文字以上で入力してください',
                    'password.max' => 'パスワードは255文字以内で入力してください',
                ]
            );

            $validator->validate();

            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

    }
}
