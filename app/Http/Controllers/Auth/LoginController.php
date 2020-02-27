<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Util;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    public const CLIENT_GUARDNAME  = 'client';
    public const MANAGER_GUARDNAME = 'web';
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:client')->except('logout');
    }

    public function userLogin(Request $request)
    {
        if (Auth::guard('web')->attempt(
            [
                'email'    => $request->email,
                'password' => $request->password
            ],
            $request->get('remember')
        )) {
            return redirect()->route('requests.index');
        }
        if (Auth::guard('client')->attempt(
            [
                'email'    => $request->email,
                'password' => $request->password
            ],
            $request->get('remember')
        )) {
            return redirect()->route('feedbacks.create');
        }
        return $this->REDIRECT_BACK_TO_LOGIN();
    }

    public function logout() : View
    {
        if (Auth::guard('client')->check()) {
            Auth::guard('client')->logout();
        }
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }
        return Util::notify_and_redirect_view(
            "You've been logged out, redirecting to login... "
        );
    }

    public static function REDIRECT_BACK_TO_LOGIN() : RedirectResponse
    {
        return redirect()->route('login');
    }

    public static function logged_in_as_manager() : bool
    {
        return Auth::guard(LoginController::MANAGER_GUARDNAME)->check();
    }

    public static function logged_in_as_client() : bool
    {
        return Auth::guard(LoginController::CLIENT_GUARDNAME)->check();
    }
}
