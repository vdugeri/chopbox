<?php

namespace ChopBox\Http\Controllers\Auth;

use ChopBox\Http\Controllers\Controller;
use ChopBox\User;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ChopBox\ChopBox\Authenticate\SocialAuthenticateUser;
use Socialite;
use Validator;

class AuthController extends Controller
{
    /*
       * |--------------------------------------------------------------------------
       * | Registration & Login Controller
       * |--------------------------------------------------------------------------
       * |
       * | This controller handles the registration of new users, as well as the
       * | authentication of existing users. By default, this controller uses
       * | a simple trait to add these behaviors. Why don't you explore it?
       * |
       */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;
    protected $loginPath = '/login';
    protected $registerPath = '/register';
    protected $redirectPath = '/';

    /**
     * Create a new authentication controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest', [
            'except' => 'getLogout'
        ]);
    }

    public function socialLogin(SocialAuthenticateUser $authenticateUser, Request $request, $provider = null)
    {
        $socialProvidders = array(
            "facebook",
            "google"
        );

        if (in_array(strtolower($provider), $socialProvidders)) {
            return $authenticateUser->execute($request, $this, $provider);
        } else {
            return redirect($this->registerPath)->withErrors('Invalid Login Provider');
        }
    }

    /**
     * Authenticate users.
     *
     * @param array $request
     *
     * @return User
     */
    public function doLogin(Request $request)
    {

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);
        $field = (filter_var($credentials ['email'], FILTER_VALIDATE_EMAIL)) ? "email" : "username";
        if (Auth::attempt([
            $field => $credentials ['email'],
            'password' => $credentials ['password'],
            'status' => true
        ], $request->has('remember'))
        ) {
            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        /*
         * If the login attempt was unsuccessful we will increment the number of attempts
         * to login and redirect the user back to the login form. Of course, when this
         * user surpasses their maximum number of attempts they will get locked out.
         */
        if ($throttles) {
            $this->incrementLoginAttempts($request);
        }

        return redirect($this->loginPath())->withInput($request->only($this->loginUsername(), 'remember'))->withErrors([
            $this->loginUsername() => $this->getFailedLoginMessage()
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        $this->sanitizeInputs($request);

        $validator = $this->validator($request->all());


        if ($validator->fails()) {
            return redirect($this->registerPath)->withInput()->withErrors($validator);
        }

        Auth::login($this->create($request->all()));

        return redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'invalid|required|max:255|unique:users,username|min:3',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:8'
        ]);
    }

    public function getSocialPassword(Request $request)
    {
        if (! $request->session()->has('socialUser')) {
            return redirect()->intended('/login');
        }

        return view('auth.set_social_password');
    }

    public function postSocialPassword(Request $request)
    {
        if (Auth::check()) {
            return redirect()->intended('/');
        }

        $validation = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:8',
            'name' => 'invalid|required|max:255|unique:users,username|min:3'
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation->errors());
        } else {
            $user_array = array(
                'email' => $request->session()->get('socialUser')->getEmail(),
                'name' => $request->input('name'),
                'password' => $request->input('password')
            );

            $user = $this->create($user_array);
            $this->setAvatar($request, $user);

            Auth::login($user);

            $request->session()->forget('socialUser');

            return redirect($this->redirectPath());
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'email' => $data ['email'],
            'username' => $data ['name'],
            'password' => bcrypt($data ['password']),
            'status' => true,
            'profile_state' => false
        ]);
    }

    /**
     * Sanitize the Inputs.
     *
     */
    public function sanitizeInputs(Request $request)
    {
        $input = $request->all();

        $input['name'] = trim(filter_var($request->input('name'), FILTER_SANITIZE_STRING));
        $input['email'] = trim(filter_var($request->input('email'), FILTER_SANITIZE_EMAIL));

        $request->replace($input);
    }

    /**
     * Set user's avatar to that on social platform
     *
     * @param Request $request
     */
    protected function setAvatar(Request $request, User $user)
    {
        $avatar = $request->session()->get('socialUser')->getAvatar();

        if (! is_null($avatar)) {
            $user->image_uri = $avatar;
            $user->save();
        }
    }
}
