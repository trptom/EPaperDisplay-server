<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Log;

class AuthController extends BaseController
{
    /** Redirect the user to Google's OAuth page. */
    public function redirectToGoogle(Request $request)
    {
        // optional: accept `origin` param to redirect back after login
        $origin = $request->query('origin');
        if ($origin) {
            // store origin in session to use after callback
            $request->session()->put('oauth_origin', $origin);
        }

        return Socialite::driver('google')->redirect();
    }

    /** Redirect the user to Facebook's OAuth page. */
    public function redirectToFacebook(Request $request)
    {
        $origin = $request->query('origin');
        if ($origin) {
            $request->session()->put('oauth_origin', $origin);
        }

        return Socialite::driver('facebook')->redirect();
    }

    /** Handle Google callback. */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Resolve the Socialite factory from the container so we can set an
            // HTTP client on the instance used for this request only.
            $socialite = app(\Laravel\Socialite\Contracts\Factory::class);

            $googleUser = $socialite->driver('google')->user();
        } catch (\Exception $e) {
            return response()->json(['message' => 'OAuth callback error', 'error' => $e->getMessage()], 400);
        }

        $userData = [
            'provider' => 'google',
            'id' => $googleUser->getId(),
            'name' => $googleUser->getName() ?? $googleUser->getNickname(),
            'email' => $googleUser->getEmail(),
        ];

        $user = User::findOrCreateFromProvider($userData);

        Auth::login($user);

        // If an origin was provided, redirect back with intended session cookie.
        $origin = $request->session()->pull('oauth_origin', null);
        if ($origin) {
            return redirect()->away($origin);
        }

        // Otherwise return JSON with user info
        return response()->json(['user' => $user]);
    }

    /** Handle Facebook callback. */
    public function handleFacebookCallback(Request $request)
    {
        // Log incoming callback params (code, state, error, etc.) to help debugging
        Log::debug('Facebook OAuth callback received', [
            'query' => $request->query(),
            'cookies' => $request->cookies->all(),
            'session_has_state' => $request->session()->has('oauth_state'),
        ]);

        try {
            $socialite = app(\Laravel\Socialite\Contracts\Factory::class);

            $fbUser = $socialite->driver('facebook')->user();
        } catch (\Exception $e) {
            return response()->json(['message' => 'OAuth callback error', 'error' => $e->getMessage()], 400);
        }

        $userData = [
            'provider' => 'facebook',
            'id' => $fbUser->getId(),
            'name' => $fbUser->getName() ?? $fbUser->getNickname(),
            'email' => $fbUser->getEmail(),
        ];

        $user = User::findOrCreateFromProvider($userData);

        Auth::login($user);

        $origin = $request->session()->pull('oauth_origin', null);
        if ($origin) {
            return redirect()->away($origin);
        }

        return response()->json(['user' => $user]);
    }

    public function user(Request $request)
    {
        $user = Auth::user();
        return response()->json(['user' => $user]);
    }

    /** Register a new user with name/email/password. */
    public function register(Request $request)
    {
        $data = $request->only(['name', 'email', 'password', 'password_confirmation']);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Fire the Registered event so Laravel will send the email verification
        event(new Registered($user));

        Auth::login($user);

        return response()->json(['user' => $user], 201);
    }

    /** Verify the user's email using Laravel's signed verification URL. */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        // Return simple JSON success response
        return response()->json(['message' => 'Email verified']);
    }

    /** Resend verification notification to authenticated user. */
    public function resendVerification(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email resent']);
    }

    /** Login with email and password. */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember', false))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        return response()->json(['user' => $user]);
    }

    /** Logout simple endpoint */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['user' => null]);
    }

    /**
     * Dev-only: force logout without CSRF/token verification.
     * Only enabled when APP_ENV !== 'production'.
     * Useful when client can't send XSRF token (local dev). Do NOT enable in prod.
     */
    public function logoutNoCsrf(Request $request)
    {
        if (env('APP_ENV') === 'production') {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        // Log for debugging
        Log::debug('logoutNoCsrf called; invalidating session for debugging.');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out (dev-only)'], 200);
    }
}
