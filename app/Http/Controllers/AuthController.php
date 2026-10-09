<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginFrom()
    {
        return view('auth.login');
    }
    public function showRegisterFrom()
    {
    return view('auth.register');
    }
    
 public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required|max:255',
    ]);

    if ($validator->fails()) {
        return Redirect::back()->withInput()->withErrors($validator);
    }

    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        // figure out which field was wrong
        $user = User::where('email', $request->email)->first();

        if ($user) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['password' => 'Incorrect password']);
        }

        return redirect()->back()
            ->withInput()
            ->withErrors(['email' => 'Invalid email']);
    }

    /* -------- Logged in successfully -------- */
    $user = Auth::user();

    // 1) Inactive user
    if ((int) $user->status !== 1) {
        Auth::logout();
        return redirect()->back()
            ->withInput()
            ->with('error', 'Your account is deactivated. Please contact admin.');
    }

    // 2) Determine if user is an admin-panel user
    $adminRoleSlugs = ['superadmin', 'admin', 'manager'];

    $isAdminPanel = false;

    if ($user->roleRelation && in_array($user->roleRelation->slug, $adminRoleSlugs, true)) {
        $isAdminPanel = true;
    } elseif ((int) $user->role === 2) {
        // legacy flag: 2 = admin
        $isAdminPanel = true;
    }

    if ($isAdminPanel) {
        return redirect()->route('admin.dashboard');
    }

    // 3) Non-admin (regular user) — redirect wherever you want
    //    If you truly only allow admins to log in, logout and show error:
    Auth::logout();
    return redirect()->back()
        ->withInput()
        ->with('error', 'You do not have access to the admin panel.');
}
      public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'You have been logged out successfully.');
    }

    public function registerSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required',
        ]);

      
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->role = 2;
        $user->status = 1;

        $user->save();

        Auth::login($user);

        return Redirect::route('login')->with('success', 'User successfully registered');
    }
   

}
