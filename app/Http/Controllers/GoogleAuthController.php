<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
      return Socialite::driver('google')->redirect();
    }
    
    
    public function callbackGoogle(Request $req)
    {
      try {
        $google_user = Socialite::driver('google')->user();
        $user = User::where('google_id', $google_user->getId())->first();
        
        if(!$user)
        {
          $new_user = User::create([
            'name' => $google_user->getName(),
            'email' => $google_user->getEmail(),
            'google_id' => $google_user->getId()
            ]);
            
          Auth::login($new_user);
          
          $req->session()->put('user', $google_user->getEmail());
          
          return redirect()->intended('/dashboard');
        }
        else {
          Auth::login($user);
          return redirect()->intended('/dashboard');
        }
      } catch(\Throwable $th) {
        dd("ERROR: " . $th->getMessage());
      }
    }
    
    
    public function dashboard()
    {
      $users = User::all();
      return view('dashboard', compact('users'));
    }
    
    
    public function login()
    {
      if(session()->has('user'))
      {
        return to_route('dashboard');
      }
      return view('login');
    }
    
    
    public function logout()
    {
      if(session()->has('user'))
      {
        session()->pull('user');
      }
      return redirect()->route('login.form');
    }
    
}
